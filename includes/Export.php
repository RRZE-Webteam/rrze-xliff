<?php

namespace RRZE\XLIFF;

class Export
{
    protected $xliff_files = [];
    
    protected $helpers;

    /**
     * Initialisierung des Exporters.
     */
    public function __construct()
    {		
        $this->helpers = new Helpers();
        
        add_action('admin_init', function() {
            // Bulk-Export-Optionen für alle ausgewählten Beitragstypen anzeigen.
            $post_types = Options::get_options()->rrze_xliff_export_import_post_types;
            foreach ($post_types as $post_type) {
                add_filter("bulk_actions-edit-$post_type", [$this, 'bulk_export_action']);
                add_filter("handle_bulk_actions-edit-$post_type", [$this, 'bulk_export_handler'], 10, 3);
            }

            // Script für Bulk-Export einbinden.
            add_action('admin_enqueue_scripts', [$this, 'enqueue_bulk_export_script']);

            // Meta-Box registrieren, wenn der Block-Editor nicht genutzt wird.
            add_action('current_screen', function($screen) {
                if (! $screen->is_block_editor) {
                    add_action('add_meta_boxes', [$this, 'meta_box']);
                }
            });
            if ($this->helpers->is_user_capable() && isset($_GET['xliff-export']) && absint($_GET['xliff-export'])) {
                // XLIFF-String holen.
                $xliff_file = $this->get_xliff_file($_GET['xliff-export']);
                if (is_wp_error($xliff_file)) {
                    echo $xliff_file->get_error_message();
                    wp_die();
                }
                if (isset($_GET['xliff_export_email_address'])) {
                    $this->send_xliff_download($_GET['xliff_export_email_address']);
                } else {
                    $this->send_xliff_download();
                }
            }
        });
    }
    
    /**
     * Bulk-Action für Mehrfachexport einfügen.
     */
    public function bulk_export_action(array $bulk_actions)
    {
        if ($this->helpers->is_user_capable()) {
            $bulk_actions['xliff_bulk_export'] = __('Bulk XLIFF export', 'rrze-xliff');
        }
        return $bulk_actions;
    }

    /**
     * Bulk-Mehrfachexport ausführen.
     */
    public function bulk_export_handler(string $redirect_to, string $doaction, array $post_ids): string
    {
        if ($doaction !== 'xliff_bulk_export' || $this->helpers->is_user_capable() === false) {
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
            $subject = Options::get_options()->rrze_xliff_export_email_subject;
            // Platzhalter ersetzen. Wenn es um einen Bulk-Export geht, Platzhalter rauslöschen.
            if (isset($zip)) {
                $subject = str_replace('%%POST_ID%%', '', $subject);
                $subject = str_replace('%%POST_TITLE%%', '', $subject);
            } else {
                $subject = str_replace('%%POST_ID%%', $this->xliff_files[0]['post_id'], $subject);
                $subject = str_replace('%%POST_TITLE%%', get_the_title($this->xliff_files[0]['post_id']), $subject);
            }

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
        
        $source_language_code = \get_bloginfo('language');
        if ($source_language_code == '') {
            return new \WP_Error('no_source_lang_code', __('No source language code set.', 'rrze-xliff'));
        }
        $source_language_code = substr($source_language_code, 0, 2);

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
            '<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="%1$s">
    <file id="f1">
%3$s
    </file>
</xliff>',
            $source_language_code,
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
            'post_id' => $post_id,
        ]);
        
        return $file;
    }
    
    /**
     * Metaboxen registrieren.
     */
    public function meta_box()
    {
        if ($this->helpers->is_user_capable()) {
            add_meta_box(
                'rrze_xliff_export',
                __('XLIFF export', 'rrze-xliff'),
                [$this, 'the_export_meta_box'],
                Options::get_options()->rrze_xliff_export_import_post_types,
                'side',
                'low'
            );
        }
    }

    /**
     * Ausgabe der Metabox für den XLIFF-Export.
     *
     * @param object $post Post object.
     */
    public function the_export_meta_box(object $post)
    {
        printf(
            '<p><a href="%s" class="button">%s</a></p>
            <p><strong>%s</strong></p>
            <p>
                <label style="display: block" for="xliff_export_email_address">%s</label>
                <input type="email" value="%s" id="xliff_export_email_address" name="xliff_export_email_address">
            </p>
            <p><a href="%s" class="button" id="xliff-export-email-address-link">%s</a></p>',
            trailingslashit(get_admin_url()) . "?xliff-export=$post->ID",
            __('Download XLIFF file', 'rrze-xliff'),
            __('Or send the file to an email address:', 'rrze-xliff'),
            __('Email address', 'rrze-xliff'),
            Options::get_options()->rrze_xliff_export_email_address,
            trailingslashit(get_admin_url()) . "?xliff-export=$post->ID",
            __('Send XLIFF file', 'rrze-xliff')
        );

        printf(
            '<script>
            (function(){
                document.querySelector("#xliff_export_email_address").addEventListener("blur", function(e) {
                    var currentUrl = window.location,
                        exportLink = document.querySelector("#xliff-export-email-address-link"),
                        exportUrl = currentUrl.protocol + "//" + currentUrl.host + currentUrl.pathname + "?xliff-export=%s&xliff_export_email_address=" + e.target.value;

                    exportLink.setAttribute("href", exportUrl);
                });
            })();
            </script>',
            $post->ID
        );
    }
    
    /**
     * Einbinden des Skripts für den Bulk-Export.
     */
    public function enqueue_bulk_export_script()
    {
        if ($this->helpers->is_user_capable()) {
            global $current_screen;
            $post_types = Options::get_options()->rrze_xliff_export_import_post_types;
            foreach ($post_types as $post_type) {
                if ($current_screen->id === "edit-$post_type") {
                    wp_enqueue_script('rrze-xliff-bulk-export', plugins_url('assets/dist/js/bulk-export-functions.js', plugin_basename(RRZE_PLUGIN_FILE)), [], false, true);
                    break;
                }
            }
        }
    }
}
