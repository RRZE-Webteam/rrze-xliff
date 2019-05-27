<?php

namespace RRZE\XLIFF;

defined('ABSPATH') || exit;

class Main
{
    protected $helpers;
    /**
     * Main-Klasse wird instanziiert.
     */
    public function __construct()
    {
        new Settings();
        new Export();
        new Import();
        $this->helpers = new Helpers();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_script']);
    }

    /**
     * Enqueue der Skripte und Stylesheets.
     */
    public function enqueue_scripts()
    {
        wp_register_style('rrze-xliff', plugins_url('assets/css/rrze-xliff.min.css', plugin_basename(RRZE_PLUGIN_FILE)));
    }
        
    /**
     * Enqueue des Block-Editor-Skripts.
     */
    public function enqueue_block_editor_script()
    {
        $post_types = Options::get_options()->rrze_xliff_export_import_post_types;
        $current_post_type = get_post_type();
        if ($this->helpers->is_user_capable() && in_array($current_post_type, $post_types)) {
            wp_register_script('rrze-xliff-block-editor-script', plugins_url('assets/dist/js/block-editor-functions.js', plugin_basename(RRZE_PLUGIN_FILE)), ['wp-plugins', 'wp-element', 'wp-edit-post', 'wp-block-serialization-default-parser']);

            wp_enqueue_script('rrze-xliff-block-editor-script');
            
            wp_localize_script('rrze-xliff-block-editor-script', 'rrzeXliffJavaScriptData', [
                'email_address' => Options::get_options()->rrze_xliff_export_email_address
            ]);
        }
    }
}
