<?php

namespace RRZE\XLIFF;

class Export
{
    protected $xliff_files = [];

    /**
     * Initialisierung des Exports.
     */
    public function __construct()
    {
        add_filter('bulk_actions-edit-post', [$this, 'bulk_export_action']);
        add_filter('handle_bulk_actions-edit-post', [$this, 'bulk_export_handler'], 10, 3);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_bulk_export_script']);

        /**
         * @todo Check if user is allowed to export.
         */
        if (is_admin() && isset($_GET['xliff-export']) && absint($_GET['xliff-export'])) {
            // XLIFF-String holen.
            $xliff_file = $this->get_xliff_file($_GET['xliff-export']);
            if (is_wp_error($xliff_file)) {
                echo $xliff_file->get_error_message();
                wp_die();
            }
            if (isset($_GET['email_address'])) {
                $this->send_xliff_download($_GET['email_address']);
            } else {
                $this->send_xliff_download();
            }
        }
    }
    
    /**
     * Bulk-Action für Mehrfachimport einfügen.
     */
    public function bulk_export_action(array $bulk_actions)
    {
        $bulk_actions['xliff_bulk_export'] = __('Bulk XLIFF export', 'rrze-xliff');
        return $bulk_actions;
    }

    /**
     * Bulk-Mehrfachexport ausführen.
     */
    public function bulk_export_handler(string $redirect_to, string $doaction, array $post_ids): string
    {
        if ($doaction !== 'xliff_bulk_export') {
            return $redirect_to;
        }
        foreach ($post_ids as $post_id) {
            $file = $this->get_xliff_file($post_id);
        }
        if ((isset($_GET['xliff-bulk-export-choice']) && $_GET['xliff-bulk-export-choice'] === 'xliff-bulk-export-choice-download') || !isset($_GET['xliff-bulk-export-choice'])) {
            $this->send_xliff_download();
        } else {
            if (isset($_GET['xliff-bulk-export-email'])) {
                $this->send_xliff_download($_GET['xliff-bulk-export-email']);
            } else {
                $this->send_xliff_download();
            }
        }
        $redirect_to = add_query_arg('xliff_bulk_export', count($post_ids), $redirect_to);
        return $redirect_to;
    }

    /**
     * XLIFF-Datei als Download bereitstellen oder via Mail versenden.
     */
    protected function send_xliff_download(string $email = '')
    {
        // Prüfen ob keine Datei(en) in $this->xliff_files sind.
        if (empty($this->xliff_files)) {
            _e('No file for download.', 'rrze-xliff');
            wp_die();
        }

        // Prüfen, ob mehr als eine Datei in dem Array vorhanden sind.
        // Falls ja, als ZIP packen. Andernfalls einfach die Datei direkt versenden/als Download anbieten.
        $zip_filename = 'xliff-dateien.zip';
        if (count($this->xliff_files) > 1) {
            // @link https://www.virendrachandak.com/techtalk/how-to-create-a-zip-file-using-php/.
            $zip = new \ZipArchive();
            if ($zip->open($zip_filename, \ZipArchive::CREATE) === TRUE) {
                // Die XLIFF-Dateien durchlaufen und zum ZIP hinzufügen.
                foreach ($this->xliff_files as $xliff_file) {
                    $zip->addFromString($xliff_file['filename'], $xliff_file['file_content']);
                }

                $zip->close();
            }
        }

        // Entscheiden, ob die Datei heruntergeladen oder per Mail verschickt werden soll.
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            if (isset($zip)) {
                header("Content-Description: File Transfer");
                header("Content-Type: application/octet-stream");
                header('Content-Disposition: attachment; filename=' . $zip_filename);
                readfile($zip_filename);
                unlink($zip_filename);
            } else {
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename=' . $this->xliff_files[0]['filename']);
                header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
                echo $this->xliff_files[0]['file_content'];
                exit;
            }
        } else {
            $to = $email;
            $subject = __('XLIFF-Export', 'rrze-xliff');
            $body = __('Here comes the email export', 'rrze-xliff');
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            
            if (!isset($zip)) {
                add_action('phpmailer_init', function(&$phpmailer) {
                    $phpmailer->AddStringAttachment($this->xliff_files[0]['file_content'], $this->xliff_files[0]['filename']);
                });

                wp_mail($to, $subject, $body, $headers);
            } else {
                wp_mail($to, $subject, $body, $headers, [$zip_filename]);
                unlink($zip_filename);
            }
        }
    }

    /**
     * XLIFF-Markup genieren und dem Array hinzufügen.
     * 
     * @return string|\WP_Error Error bei Fehler, andernfalls Dateistring.
     */
    protected function get_xliff_file($post_id)
    {
        $export_post = get_post($post_id, OBJECT);
        if ($export_post === null) {
            return new \WP_Error('no_post', __('The submitted ID for export does not match a post', 'rrze-xliff'));
        }
        
        /**
         * @todo: die Translate-Meta-Strings dynamisch holen.
         */
        //$source_language_code = get_post_meta($post_id, '_translate_from_lang_post_meta', true);
        $source_language_code = 'de_DE';
        if ($source_language_code == '') {
            return new \WP_Error('no_source_lang_code', __('No source language code set.', 'rrze-xliff'));
        }
        $source_language_code = substr($source_language_code, 0, 2);

        //$language_code = get_post_meta($post_id, '_translate_to_lang_post_meta', true);
        $language_code = 'en_US';
        if ($language_code == '') {
            return new \WP_Error('no_target_lang_code', __('No target language code set.', 'rrze-xliff'));
        }
        $language_code = substr($language_code, 0, 2);

        // XLIFF-Markup erstellen.
        $elements = [
            (object) [
                'field_type' => 'title',
                'field_data' => $export_post->post_title,
                'field_data_translated' => $export_post->post_title,
            ],
            (object) [
                'field_type' => 'body',
                'field_data' => $export_post->post_content,
                'field_data_translated' => $export_post->post_content,
            ],
            (object) [
                'field_type' => 'excerpt',
                'field_data' => $export_post->post_excerpt,
                'field_data_translated' => $export_post->post_excerpt,
            ]
        ];

        $post_meta = get_post_meta($post_id);
        foreach ($post_meta as $meta_key => $meta_value) {
            if (strpos($meta_key, '_') === 0) {
                continue;
            }
            
            if (empty($meta_value)) {
                continue;
            }        
            
            $meta_value = array_map('maybe_unserialize', $meta_value);
            $meta_value = $meta_value[0];
            
            if (empty($meta_value) || is_array($meta_value) || is_numeric($meta_value)) {
                continue;
            }
                    
            $elements[] = (object) array(
                'field_type' => '_meta_' . $meta_key,
                'field_data' => $meta_value,
                'field_data_translated' => $meta_value,            
            );
        }

        $translation = (object) [
            'original' => sanitize_file_name(sprintf('%1$s-%2$s', $export_post->post_title, $export_post->ID)),
            'source_language_code' => $source_language_code,
            'language_code' => $language_code,
            'elements' => $elements
        ];

        $translation_units = '';

        foreach ($translation->elements as $element) {
            $field_data = $element->field_data;
            $field_data_translated = $element->field_data_translated;
            if ($field_data != '') {
                $field_data = str_replace(PHP_EOL, '<br class="xliff-newline" />', $field_data);
                $field_data_translated = str_replace(PHP_EOL, '<br class="xliff-newline" />', $field_data_translated);
                $translation_units .= sprintf(
                    '        <unit id="%1$s">
            <segment>
                <source><![CDATA[%2$s]]></source>
                <target><![CDATA[%3$s]]></target>
            </segment>
        </unit>',
                    $element->field_type,
                    $field_data,
                    $field_data_translated
                );
            }
        }
        
        $file = sprintf(
            '<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="%1$s" trgLang="%2$s">
    <file id="f1">
%3$s
    </file>
</xliff>',
            $source_language_code,
            $language_code,
            $translation_units
        );

        if (is_multisite()) {
            global $current_blog;
            $domain = $current_blog->domain;
            $path = $current_blog->path;
            $blog_id = $current_blog->blog_id;
        } else {
            $site_url = \get_home_url();
            $parsed_url = parse_url($site_url);
            $domain = $parsed_url['host'];
            $path = isset($parsed_url['path']) ? $parsed_url['path'] : '/';
            $blog_id = 1;
        }
        
        $filename = sanitize_file_name(sprintf(
            '%s%s-%s-%s-%s.xliff',
            $domain,
            $path !== '/' ? "-$path" : '',
            $blog_id,
            $post_id,
            date('Ymd')
        ));

        array_push($this->xliff_files, [
            'filename' => $filename,
            'file_content' => $file,
        ]);
        
        return $file;
    }
    
    /**
     * Einbinden des Skripts für den Bulk-Export.
     */
    public function enqueue_bulk_export_script($hook_suffix)
    {
        global $current_screen;
        if ($current_screen->id === 'edit-post') {
            wp_enqueue_script('rrze-xliff-bulk-export', plugins_url('assets/dist/js/bulk-export-functions.js', plugin_basename(RRZE_PLUGIN_FILE)), [], false, true);
        }
    }
}
