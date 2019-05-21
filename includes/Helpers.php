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
        if (! \is_user_logged_in()) {
            return false;
        }

        $user = \wp_get_current_user();
        $user_caps = $user->allcaps;
        $needed_role = get_role($this->options->rrze_xliff_export_import_role);

        if ($needed_role === null) {
            return false;
        }

        $needed_caps = $needed_role->capabilities;

        $missing_caps = array_diff_key($needed_caps, $user_caps);

        if (! empty($missing_caps)) {
            return false;
        } else {
            return true;
        }
    }
}
