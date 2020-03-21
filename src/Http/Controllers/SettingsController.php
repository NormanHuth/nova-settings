<?php

namespace NormanHuth\NovaValuestore\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use NormanHuth\NovaValuestore\NovaValuestore;
use Laravel\Nova\Contracts\Resolvable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\ResolvesFields;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Laravel\Nova\Fields\FieldCollection;
use Illuminate\Support\Facades\Validator;
use Spatie\Valuestore\Valuestore;

class SettingsController extends Controller
{
    use ResolvesFields, ConditionallyLoadsAttributes;

    protected $setting = '';

    public function __construct()
    {
        $this->setting = Valuestore::make(config('nova-valuestore.settings_file'));
    }

    public function get(Request $request)
    {
        $fields = $this->assignToPanels(__('Settings'), $this->availableFields());
        $panels = $this->panelsWithDefaultLabel(__('Settings'), app(NovaRequest::class));

        $addResolveCallback = function (&$field) {
            if (!empty($field->attribute)) {
                $field->resolve([$field->attribute => $this->setting->get($field->attribute)]);
            }

            if (!empty($field->meta['fields'])) {
                foreach ($field->meta['fields'] as $_field) {
                    $_field->resolve([$_field->attribute => $this->setting->get($_field->attribute)]);
                }
            }
        };

        $fields->each(function (&$field) use ($addResolveCallback) {
            $addResolveCallback($field);
        });

        return response()->json([
            'panels' => $panels,
            'fields' => $fields->map->jsonSerialize(),
        ], 200);
    }

    public function save(NovaRequest $request)
    {
        $fields = $this->availableFields();

        // NovaDependencyContainer support
        $fields = $fields->map(function ($field) {
            if (!empty($field->attribute)) {
                return $field;
            }
            if (!empty($field->meta['fields'])) {
                return $field->meta['fields'];
            }
            return null;
        })->filter()->flatten();

        $rules = [];
        foreach ($fields as $field) {
            $fakeResource = new \stdClass;
            $fakeResource->{$field->attribute} = $this->setting->get($field->attribute);
            $field->resolve($fakeResource, $field->attribute); // For nova-translatable support
            $rules = array_merge($rules, $field->getUpdateRules($request));
        }

        Validator::make($request->all(), $rules)->validate();

        $fields->whereInstanceOf(Resolvable::class)->each(function ($field) use ($request) {
            if (empty($field->attribute)) {
                return;
            }
            if ($field->isReadonly(app(NovaRequest::class))) {
                return;
            }

            // For nova-translatable support
            if (!empty($field->meta['translatable']['original_attribute'])) {
                $field->attribute = $field->meta['translatable']['original_attribute'];
            }

            //$existingRow = $this->setting->get($field->attribute);;

            $tempResource =  new \stdClass;
            $field->fill($request, $tempResource);

            if (!property_exists($tempResource, $field->attribute)) {
                return;
            }

            if ($tempResource->{$field->attribute}) {
                $this->setting->put($field->attribute, $tempResource->{$field->attribute});
            } else {
                $this->setting->forget($field->attribute);
            }
        });

        if (config('nova-valuestore.reload_page_on_save', false) === true) {
            return response()->json(['reload' => true]);
        }

        return response('', 204);
    }

    public function deleteImage(Request $request, $fieldName)
    {
        $this->setting->forget($fieldName);
        /*$existingRow = Settings::where('key', $fieldName)->first();
        if (isset($existingRow)) $existingRow->update(['value' => null]);*/
        return response('', 204);
    }

    protected function availableFields()
    {
        return new FieldCollection(($this->filter(NovaValuestore::getFields())));
    }

    protected function fields(Request $request)
    {
        return NovaValuestore::getFields();
    }
}
