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
        new Notices();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_script']);

        // Classic-Editor-Script einbinden.
        add_action('current_screen', function($screen) {
            if (! $screen->is_block_editor) {
                add_action('admin_enqueue_scripts', [$this, 'enqueue_classic_editor_script']);
            }
        });
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

    /**
     * Enqueue des Block-Editor-Skripts.
     */
    public function enqueue_classic_editor_script()
    {
        $post_types = Options::get_options()->rrze_xliff_export_import_post_types;
        $current_post_type = get_post_type();
        global $current_screen;
        if ($this->helpers->is_user_capable() && in_array($current_post_type, $post_types) && in_array($current_screen->id, $post_types)) {
            wp_register_script('rrze-xliff-classic-editor-script', plugins_url('assets/dist/js/classic-editor-functions.js', plugin_basename(RRZE_PLUGIN_FILE)), [], null, true);

            wp_enqueue_script('rrze-xliff-classic-editor-script');
            
            wp_localize_script('rrze-xliff-classic-editor-script', 'rrzeXliffJavaScriptData', [
                'post_id' => get_the_ID(),
                'nonce' => wp_create_nonce('xliff_export'),
            ]);
        }
    }
}
