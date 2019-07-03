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
        if (current_user_can($this->options->rrze_xliff_export_import_role)) {
            return true;
        }
        return false;
    }
}
