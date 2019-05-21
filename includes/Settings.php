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
        $this->admin_settings_page = add_options_page(__('XLIFF Import/Export', 'rrze-xliff'), __('XLIFF Import/Export', 'rrze-xliff'), 'manage_options', 'rrze-xliff', [$this, 'settings_page']);
        add_action('load-' . $this->admin_settings_page, [$this, 'admin_help_menu']);
    }

    /**
     * Die Ausgabe der Einstellungsseite.
     */
    public function settings_page()
    {
        ?>
        <div class="wrap">
            <h2><?php echo __('Settings &rsaquo; XLIFF Import/Export', 'rrze-xliff'); ?></h2>
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

        add_settings_section(
            'rrze_xliff_section_export',
            false,
            [$this, 'rrze_xliff_section_export'],
            'rrze_xliff_options'
        );

        add_settings_field(
            'rrze_xliff_export_email_address',
            __('Default email address', 'rrze-xliff'),
            [$this, 'rrze_xliff_export_email_address'],
            'rrze_xliff_options',
            'rrze_xliff_section_export',
            [
                'label_for' => sprintf('%s[rrze_xliff_export_email_address]', $this->option_name)
            ]
        );

        add_settings_field(
            'rrze_xliff_export_email_subject',
            __('Default email subject', 'rrze-xliff'),
            [$this, 'rrze_xliff_export_email_subject'],
            'rrze_xliff_options',
            'rrze_xliff_section_export',
            [
                'label_for' => sprintf('%s[rrze_xliff_export_email_subject]', $this->option_name),
                'description' => __('You can use the following template tags: %%POST_ID%%, %%POST_TITLE%%, %%TARGET_LANGUAGE%%')
            ]
        );
        
        add_settings_section(
            'rrze_xliff_section_general',
            false,
            [$this, 'rrze_xliff_section_general'],
            'rrze_xliff_options'
        );

        add_settings_field(
            'rrze_xliff_export_import_role',
            __('Role that a user needs for export and import', 'rrze-xliff'),
            [$this, 'rrze_xliff_export_import_role'],
            'rrze_xliff_options',
            'rrze_xliff_section_general',
            [
                'label_for' => sprintf('%s[rrze_xliff_export_import_role]', $this->option_name),
            ]
        );

        add_settings_field(
            'rrze_xliff_export_import_post_types',
            __('Post types to show import/export options for', 'rrze-xliff'),
            [$this, 'rrze_xliff_export_import_post_types'],
            'rrze_xliff_options',
            'rrze_xliff_section_general'
        );
    }

    /**
     * Validiert die Eingabe der Einstellungsseite.
     * @param array $input
     * @return array
     */
    public function options_validate($input)
    {
        /**
         * @todo: Die Einstellungen validieren.
         */
        //$input['rrze_xliff_text'] = !empty($input['rrze_xliff_export_email_address']) ? $input['rrze_xliff_export_email_address'] : '';
        return $input;
    }
    
    /**
     * Header für den Export-Bereich der Einstellungen.
     */
    public function rrze_xliff_section_export()
    {
        printf(
            '<h3>%s</h3>',
            __('Export', 'rrze-xliff')
        );
    }

    /**
     * Feld für Festlegen der Standard-E-Mail-Adresse zum Senden des Exports.
     */
    public function rrze_xliff_export_email_address()
    {
        ?>
        <input type='text' name="<?php printf('%s[rrze_xliff_export_email_address]', $this->option_name); ?>" id="<?php printf('%s[rrze_xliff_export_email_address]', $this->option_name); ?>" value="<?php echo $this->options->rrze_xliff_export_email_address; ?>">
        <?php
    }

    /**
     * Feld für Einstellung des E-Mail-Betreffs.
     */
    public function rrze_xliff_export_email_subject($args)
    {
        ?>
        <input type='text' name="<?php printf('%s[rrze_xliff_export_email_subject]', $this->option_name); ?>" id="<?php printf('%s[rrze_xliff_export_email_subject]', $this->option_name); ?>" value="<?php echo $this->options->rrze_xliff_export_email_subject; ?>">
        <?php
        if ('' !== $args['description']) { ?>
            <p class="description">
                <?php echo $args['description']; ?>
            </p>
            <?php
        }
    }
    
    /**
     * Header für den Allgemein-Bereich der Einstellungen.
     */
    public function rrze_xliff_section_general()
    {
        printf(
            '<h3>%s</h3>',
            __('General settings', 'rrze-xliff')
        );
    }

    /**
     * Feld für Export und Import-Rolle.
     */
    public function rrze_xliff_export_import_role()
    {
        $roles = get_editable_roles();
        $role_option_elements = '';
        foreach ($roles as $role_slug => $role_array) {
            $role_option_elements .= sprintf(
                '<option value="%s" %s>%s</option>',
                $role_slug,
                $this->options->rrze_xliff_export_import_role === $role_slug ? 'selected' : '',
                $role_array['name']
            );
        }
        printf(
            '<select name="%1$s" id="%1$s">%2$s</select>',
            sprintf('%s[rrze_xliff_export_import_role]', $this->option_name),
            $role_option_elements
        );
    }

    /**
     * Feld für unterstützte Post-Types.
     */
    public function rrze_xliff_export_import_post_types()
    {
        $saved_settings = $this->options->rrze_xliff_export_import_post_types;
        $post_types = get_post_types(['public' => true], 'objects');
        unset($post_types['attachment']);
        $post_type_options = '';
        foreach ($post_types as $post_type) {
            $post_type_options .= sprintf(
                '<p><input type="checkbox" value="%1$s" name="%2$s" id="%3$s" %5$s><label for="%3$s">%4$s</label></p>',
                $post_type->name,
                sprintf('%s[rrze_xliff_export_import_post_types][%s]', $this->option_name, $post_type->name),
                sprintf('rrze-xliff-export-import-post-types-%s', $post_type->name),
                $post_type->label,
                in_array($post_type->name, $saved_settings) ? 'checked' : ''
            );
        }
        echo $post_type_options;        
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
