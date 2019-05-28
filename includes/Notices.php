<?php

namespace RRZE\XLIFF;

defined('ABSPATH') || exit;

class Notices
{
    /**
     * Notices-Klasse wird instanziiert.
     */
    public function __construct()
    {
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Eine Notice hinzufügen.
     * 
     * @param string $notice Text der Nachricht.
     * @param string $type Optional. Art der Nachticht. WordPress hat Styles für: info|success|warning|error
     */
    public static function add_notice(string $notice, string $type = 'info') {
        $type = esc_attr($type);
        if ($notice !== '' && $type !== '') {
            $current_user_id = get_current_user_id();
            $transient_value = [];
            $notice_transient = get_transient("rrze-xliff-admin-notice-$current_user_id");

            if ($notice_transient !== false) {
                $transient_value = $notice_transient;
            }

            array_push($transient_value, [
                'notice' => $notice,
                'type' => $type,
            ]);

            set_transient("rrze-xliff-admin-notice-$current_user_id", $transient_value, 600);
        } else {
            return new \WP_Error('missing_arguments', 'Es wurden nicht alle notwendigen Argumente angegeben.');
        }
    }

    public function admin_notices() {
        // Get the transient.
        $current_user_id = get_current_user_id();
        $notice_transient = get_transient("rrze-xliff-admin-notice-$current_user_id");
        if ($notice_transient !== false && is_array($notice_transient)) {
            $notices = [];
            foreach ($notice_transient as $notice) {
                // Prüfen, ob es sich um einen AJAX-Request handelt. In dem Fall JSON zurückgeben.
                if (isset($_POST['action']) && $_POST['action'] === 'xliff_email_export') {
                    array_push($notices, $notice);
                } else {
                    printf(
                        '<div class="notice notice-%s is-dismissible">
                            <p>%s</p>
                        </div>',
                        $notice['type'],
                        $notice['notice']
                    );
                }
            }

            delete_transient("rrze-xliff-admin-notice-$current_user_id");

            if (isset($_POST['action']) && $_POST['action'] === 'xliff_email_export') {
                wp_send_json($notices);
            }
        }

        delete_transient("rrze-xliff-admin-notice-$current_user_id");
    }
}
