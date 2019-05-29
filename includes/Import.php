<?php

namespace RRZE\XLIFF;

class Import
{
    protected $xliff_files = [];
    
    protected $helpers;

    /**
     * Initialisierung des Importers.
     */
    public function __construct()
    {
        $this->helpers = new Helpers();

        // Meta-Box registrieren, wenn der Block-Editor nicht genutzt wird.
        add_action('current_screen', function($screen) {
            if (! $screen->is_block_editor) {
                add_action('add_meta_boxes', [$this, 'meta_box']);
            }
        });
        add_action('save_post', [$this, 'save_post']);
        add_action('post_edit_form_tag', [$this, 'update_edit_form']);
    }
    
    /**
     * Metabox registrieren.
     */
    public function meta_box()
    {
        if ($this->helpers->is_user_capable()) {
            add_meta_box(
                'rrze_xliff_import',
                __('XLIFF import', 'rrze-xliff'),
                [$this, 'the_import_meta_box'],
                Options::get_options()->rrze_xliff_export_import_post_types,
                'side',
                'low'
            );
        }
    }

    /**
     * Ausgabe der Metabox für den XLIFF-Import.
     *
     * @param object $post Post object.
     */
    public function the_import_meta_box($post)
    {
        wp_nonce_field(plugin_basename(__FILE__), 'rrze_xliff_file_import_nonce');
        printf(
            '<p>
                <label style="display: block" for="xliff_import_file">%s</label>
                <input type="file" id="xliff_import_file" name="xliff_import_file" accept=".xliff">
            </p>
            <p><button class="button" type="submit">%s</button></p>',
            __('Choose XLIFF file to import', 'rrze-xliff'),
            __('Import XLIFF file', 'rrze-xliff')
        );
    }

    /**
     * Import anstoßen.
     */
    public function save_post($post_id)
    {
        // Nonce prüfen.
        if (!isset($_POST['rrze_xliff_file_import_nonce']) || !\wp_verify_nonce($_POST['rrze_xliff_file_import_nonce'], plugin_basename(__FILE__))) {
            return;
        }

        // Bei automatischem Speichern nichts tun.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Auf Berechtigung prüfen.
        if ($this->helpers->is_user_capable() === false) {
            return;
        }

        if (wp_is_post_revision($post_id)) {
            return;
        }

        if (isset($_FILES['xliff_import_file']) && $_FILES['xliff_import_file']['tmp_name'] !== '') {
            remove_action('save_post', [$this, 'save_post']);
            $import = $this->import_file($post_id, $_FILES['xliff_import_file']);
            if (is_wp_error($import)) {
                Notices::add_notice($import->get_error_message(), 'error');
            } else {
                Notices::add_notice(__('Import erfolgreich', 'rrze-xliff'), 'success');
            }
            add_action('save_post', [$this, 'save_post']);
        }
    }

    /**
     * Importieren einer XLIFF-Datei.
     * 
     * @param int $post_id Die Beitrags-ID.
     * @param array $file Der Dateiinhalt.
     * 
     * @return \WP_Error|true
     */
    protected function import_file($post_id, $file)
    {
        $post = get_post($post_id);

        if ($post === null) {
            return new \WP_Error('get_post_error', __('Der übergebenen Post-ID konnte kein Inhalt zugeordnet werden.', 'rrze-xliff'));
        }

        $fh = fopen($file['tmp_name'], 'r');

        if ($fh === false) {
            return new \WP_Error('file_not_found', __('Die Datei wurde nicht gefunden.', 'rrze-xliff')); 
        }

        $data = fread($fh, $file['size']);

        fclose($fh);

        unlink($file['tmp_name']);

        $xml = simplexml_load_string($data);

        if (!$xml) {
            return new \WP_Error('load_xml_error', __('Der Dateiinhalt ist kein XLIFF.', 'rrze-xliff'));
        }

        $post_array = [
            'ID' => $post_id,
        ];

        $post_meta_array = [];

        foreach ($xml->file->unit as $unit) {
            $attr = $unit->attributes();
            if ((string) $attr['id'] === 'title') {
                $post_array['post_title'] = (string) $unit->segment->target;
            } elseif ((string) $attr['id'] === 'body') {
                $post_array['post_content'] = (string) $unit->segment->target;
            } elseif ((string) $attr['id'] === 'excerpt') {
                $post_array['post_excerpt'] = (string) $node->target;
            } elseif (strpos((string) $attr['id'], '_meta_') === 0) {
                $meta_key = (string) substr((string) $attr['id'], strlen('_meta_'));
                $meta_value = (string) $unit->segment->target;
                if (!empty($meta_value) && !is_numeric($meta_value)) {
                    $post_meta_array[$meta_key] = $meta_value;
                } 
            }
        }

        if (!wp_update_post($post_array)) {
            return new \WP_Error('post_update_error', __('Ein unbekannter Fehler ist aufgetreten. Das Dokument konnte nicht gespeichert werden.', 'rrze-xliff'));
        }

        $post_meta = get_post_meta($post_id);
        foreach ($post_meta as $meta_key => $prev_value) {
            if (strpos($meta_key, '_') === 0) {
                continue;
            }

            if (empty($meta_value)) {
                continue;
            }

            $prev_value = array_map('maybe_unserialize', $prev_value);
            $prev_value = $prev_value[0];

            if (empty($prev_value) || is_array($prev_value) || is_numeric($prev_value)) {
                continue;
            }
            
            if(isset($post_meta_array[$meta_key])) {
                update_post_meta($post_id, $meta_key, $post_meta_array[$meta_key], $prev_value);
            } else {
                add_post_meta($post_id, $meta_key, $post_meta_array[$meta_key]);
            }
        }

        return true;
    }

    /**
     * Anpassung des Formulars des Classic Editors.
     */
    public function update_edit_form()
    {
        echo ' enctype="multipart/form-data"';
    }
    
    /**
     * Einbinden des Skripts für den Bulk-Export.
     */
    public function enqueue_bulk_export_script()
    {
        global $current_screen;
        if ($current_screen->id === 'edit-post') {
            wp_enqueue_script('rrze-xliff-bulk-export', plugins_url('assets/dist/js/bulk-export-functions.js', plugin_basename(RRZE_PLUGIN_FILE)), [], false, true);
        }
    }
}
