<?php

namespace RRZE\XLIFF;

defined('ABSPATH') || exit;

class Main
{
    /**
     * Main-Klasse wird instanziiert.
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_script']);

		new Settings();
		new Export();
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
        wp_register_script('rrze-xliff-block-editor-script', plugins_url('assets/dist/js/block-editor-functions.js', plugin_basename(RRZE_PLUGIN_FILE)), ['wp-plugins', 'wp-element', 'wp-edit-post']);

        wp_enqueue_script('rrze-xliff-block-editor-script');
    }
}
