<?php

namespace RRZE\XLIFF;

use RRZE\XLIFF\Main;

defined('ABSPATH') || exit;

class Settings
{
    /**
     * Optionsname
     * @var string
     */
    protected $option_name;

    /**
     * Einstellungsoptionen
     * @var object
     */
    protected $options;

    /**
     * "Screen ID" der Einstellungsseite.
     * @var string
     */
    protected $admin_settings_page;

    /**
     * Settings-Klasse wird instanziiert.
     */
    public function __construct()
    {
        $this->option_name = Options::get_option_name();
        $this->options = Options::get_options();

        add_action('admin_menu', [$this, 'admin_settings_page']);
        add_action('admin_init', [$this, 'admin_settings']);

        add_filter('plugin_action_links_' . plugin_basename(RRZE_PLUGIN_FILE), [$this, 'plugin_action_link']);
    }

    /**
     * Füge einen Einstellungslink hinzu, der auf der Plugins-Seite angezeigt wird.
     * @param  array $links Linkliste
     * @return array        zusammengeführte Liste von Links
     */
    public function plugin_action_link($links)
    {
        if (! current_user_can('manage_options')) {
            return $links;
        }
        return array_merge($links, array(sprintf('<a href="%s">%s</a>', add_query_arg(array('page' => 'rrze-xliff'), admin_url('options-general.php')), __('Settings', 'rrze-xliff'))));
    }

    /**
     * Füge eine Einstellungsseite in das Menü "Einstellungen" hinzu.
     */
    public function admin_settings_page()
    {
        $this->admin_settings_page = add_options_page(__('CMS Basis', 'rrze-xliff'), __('CMS Basis', 'rrze-xliff'), 'manage_options', 'rrze-xliff', [$this, 'settings_page']);
        add_action('load-' . $this->admin_settings_page, [$this, 'admin_help_menu']);
    }

    /**
     * Die Ausgabe der Einstellungsseite.
     */
    public function settings_page()
    {
        ?>
        <div class="wrap">
            <h2><?php echo __('Settings &rsaquo; CMS Basis', 'rrze-xliff'); ?></h2>
            <form method="post" action="options.php">
            <?php
            settings_fields('rrze_xliff_options');
            do_settings_sections('rrze_xliff_options');
            submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Legt die Einstellungen der Einstellungsseite fest.
     */
    public function admin_settings()
    {
        register_setting('rrze_xliff_options', $this->option_name, [$this, 'options_validate']);
        add_settings_section('rrze_xliff_section_1', false, '__return_false', 'rrze_xliff_options');
        add_settings_field('rrze_xliff_field_1', __('Field 1', 'rrze-xliff'), [$this, 'rrze_xliff_field_1'], 'rrze_xliff_options', 'rrze_xliff_section_1');
    }

    /**
     * Validiert die Eingabe der Einstellungsseite.
     * @param array $input
     * @return array
     */
    public function options_validate($input)
    {
        $input['rrze_xliff_text'] = !empty($input['rrze_xliff_field_1']) ? $input['rrze_xliff_field_1'] : '';
        return $input;
    }

    /**
     * Erstes Feld der Einstellungsseite.
     */
    public function rrze_xliff_field_1()
    {
        ?>
        <input type='text' name="<?php printf('%s[rrze_xliff_field_1]', $this->option_name); ?>" value="<?php echo $this->options->rrze_xliff_field_1; ?>">
        <?php
    }

    /**
     * Erstellt die Kontexthilfe der Einstellungsseite.
     * @return void
     */
    public function admin_help_menu()
    {
        $content = [
            '<p>' . __('Here comes the Context Help content.', 'rrze-xliff') . '</p>',
        ];


        $help_tab = [
            'id' => $this->admin_settings_page,
            'title' => __('Overview', 'rrze-xliff'),
            'content' => implode(PHP_EOL, $content),
        ];

        $help_sidebar = sprintf('<p><strong>%1$s:</strong></p><p><a href="http://blogs.fau.de/webworking">RRZE-Webworking</a></p><p><a href="https://github.com/RRZE-Webteam">%2$s</a></p>', __('For more information', 'rrze-xliff'), __('RRZE Webteam on Github', 'rrze-xliff'));

        $screen = get_current_screen();

        if ($screen->id != $this->admin_settings_page) {
            return;
        }

        $screen->add_help_tab($help_tab);

        $screen->set_help_sidebar($help_sidebar);
    }
}
