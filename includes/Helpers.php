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
        $allowed_roles = $this->allowed_roles();
        if (isset($allowed_roles[$this->options->rrze_xliff_export_import_role])
            && current_user_can($allowed_roles[$this->options->rrze_xliff_export_import_role])) {
            return true;
        }
        return false;
    }

    /**
     * Zugelassene Rollen
     * @return array [description]
     */
    public function allowed_roles() {
        return [
            'administrator' => 'manage_options',
            'editor' => 'manage_categories',
            'author' => 'upload_files'
        ];
    }
}
