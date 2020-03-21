<?php

namespace NormanHuth\NovaValuestore\Fields;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Password;

class PlainPassword extends Password
{
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $model->{$attribute} = $request[$requestAttribute];
    }

    public function jsonSerialize()
    {
        return array_merge([
            'component' => $this->component(),
            'prefixComponent' => true,
            'indexName' => $this->name,
            'name' => $this->name,
            'attribute' => $this->attribute,
            'value' => $this->value,
            'panel' => $this->panel,
            'sortable' => $this->sortable,
            'nullable' => $this->nullable,
            'readonly' => $this->isReadonly(app(NovaRequest::class)),
            'required' => $this->isRequired(app(NovaRequest::class)),
            'textAlign' => $this->textAlign,
            'sortableUriKey' => $this->sortableUriKey(),
            'stacked' => $this->stacked,
        ], $this->meta());
    }
}
