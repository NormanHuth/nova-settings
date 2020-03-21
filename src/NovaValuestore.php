<?php

namespace NormanHuth\NovaValuestore;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Spatie\Valuestore\Valuestore;

class NovaValuestore extends Tool
{
    protected static $cache = [];

    protected static $fields = [];
    protected static $casts = [];

    protected static $setting = '';

    public function __construct($component = null)
    {
        parent::__construct($component);

        self::$setting = Valuestore::make(config('nova-valuestore.settings_file'));
    }

    public function boot()
    {
        Nova::script('nova-valuestore', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-valuestore', __DIR__ . '/../dist/css/tool.css');
    }

    public function renderNavigation()
    {
        return view('nova-valuestore::navigation');
    }

    /**
     * Define settings fields and an optional casts.
     *
     * @param array|callable $fields Array of fields/panels to be displayed or callable that returns an array.
     * @param array $casts Associative array same as Laravel's $casts on models.
     **/
    public static function addSettingsFields($fields = [], $casts = [])
    {
        if (is_callable($fields)) {
            $fields = [$fields];
        }
        self::$fields = array_merge(self::$fields, $fields ?? []);
        self::$casts = array_merge(self::$casts, $casts ?? []);
    }

    public static function getFields()
    {
        $rawFields = array_map(function ($fieldItem) {
            return is_callable($fieldItem) ? call_user_func($fieldItem) : $fieldItem;
        }, self::$fields);

        $fields = [];
        foreach ($rawFields as $rawField) {
            if (is_array($rawField)) {
                $fields = array_merge($fields, $rawField);
            } else {
                $fields[] = $rawField;
            }
        }

        return $fields;
    }

}
