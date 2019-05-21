<?php

namespace RRZE\XLIFF;

defined('ABSPATH') || exit;

class Options
{
    /**
     * Optionsname
     * @var string
     */
    protected static $option_name = 'rrze_xliff';

    /**
     * Standard Einstellungen werden definiert
     * @return array
     */
    protected static function default_options()
    {
        $options = [
            'rrze_xliff_export_email_address' => '',
            'rrze_xliff_export_email_subject' => 'XLIFF-Export',
            'rrze_xliff_export_import_role' => 'editor',
            'rrze_xliff_export_import_post_types' => ['post', 'page'],
        ];

        return $options;
    }

    /**
     * Gibt die Einstellungen zurück.
     * @return object
     */
    public static function get_options()
    {
        $defaults = self::default_options();

        $options = (array) get_option(self::$option_name);
        $options = wp_parse_args($options, $defaults);
        $options = array_intersect_key($options, $defaults);

        return (object) $options;
    }

    /**
     * Gibt die Standardeinstellungen.
     * @return object
     */
    public static function get_default_options()
    {
        $options = self::default_options();
        
        return (object) $options;
    }

    /**
     * Gibt den Namen der Option zurück.
     * @return string
     */
    public static function get_option_name()
    {
        return self::$option_name;
    }
}
