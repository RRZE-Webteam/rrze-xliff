<?php

namespace RRZE\XLIFF;

defined('ABSPATH') || exit;

class Helpers
{
    /**
     * Einstellungsoptionen
     * @var object
     */
    protected $options;

    public function __construct()
    {
        $this->options = Options::get_options();
    }

    /**
     * Prüfen, ob der Nutzer des aktuellen Requests die Mindestrolle hat,
     * um Export/Import zu machen.
     */
    public function is_user_capable()
    {
        $current_user = wp_get_current_user();
        if ($current_user->ID) {
            $allowed_roles = [$this->options->rrze_xliff_export_import_role];
            if (array_intersect($allowed_roles, $current_user->roles)) {
                return true;
            }
        }
        return false;
    }
}
