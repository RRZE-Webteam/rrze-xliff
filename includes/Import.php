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

        add_action('save_post', [$this, 'save_post']);
        add_action('post_edit_form_tag', [$this, 'update_edit_form']);
    }

    /**
     * Import anstoßen.
     */
    public function save_post($post_id)
    {
        // Nonce prüfen.
        if (!isset($_POST['rrze_xliff_file_import_nonce']) || !\wp_verify_nonce($_POST['rrze_xliff_file_import_nonce'], 'rrze-xliff/includes/Main')) {
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
                Notices::add_notice(__('Import successful', 'rrze-xliff'), 'success');
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
            return new \WP_Error('get_post_error', __('No content was found for the passed post ID.', 'rrze-xliff'));
        }

        $fh = fopen($file['tmp_name'], 'r');

        if ($fh === false) {
            return new \WP_Error('file_not_found', __('The file was not found.', 'rrze-xliff')); 
        }

        $data = fread($fh, $file['size']);

        fclose($fh);

        unlink($file['tmp_name']);

        $xml = simplexml_load_string($data);

        if (!$xml) {
            return new \WP_Error('load_xml_error', __('The file’s content is no XLIFF.', 'rrze-xliff'));
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
            return new \WP_Error('post_update_error', __('An unknown error occurred. The post couldn’t be saved.', 'rrze-xliff'));
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
