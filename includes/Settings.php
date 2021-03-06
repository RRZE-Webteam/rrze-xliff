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
     * [protected description]
     * @var object
     */
	protected $helpers;
	
	/**
	 * Mögliche XLIFF-Versionen für die XLIFF-Version-Option und deren Validierung.
	 * @var array
	 */
	protected $xliff_versions;

    /**
     * Settings-Klasse wird instanziiert.
     */
    public function __construct()
    {
        $this->option_name = Options::get_option_name();
        $this->options = Options::get_options();

		$this->helpers = new Helpers();
		
		$this->xliff_versions = [
			[
				'label' => __('XLIFF 1', 'rrze-xliff'),
				'value' => '1',
			],
			[
				'label' => __('XLIFF 2', 'rrze-xliff'),
				'value' => '2',
			]
		];

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
        $this->admin_settings_page = add_options_page(__('XLIFF Export/Import', 'rrze-xliff'), __('XLIFF Export/Import', 'rrze-xliff'), 'manage_options', 'rrze-xliff', [$this, 'settings_page']);
    }

    /**
     * Die Ausgabe der Einstellungsseite.
     */
    public function settings_page()
    {
        ?>
        <div class="wrap">
            <h2><?php echo __('Settings &rsaquo; XLIFF Export/Import', 'rrze-xliff'); ?></h2>
            <form method="post" action="options.php">
            <?php settings_fields('rrze_xliff_options'); ?>
            <?php do_settings_sections('rrze_xliff_options'); ?>
            <?php submit_button(); ?>
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
                'description' => __('You can use the following template tags: %%POST_ID%%, %%POST_TITLE%%')
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
            __('Post types to show Export/Import options for', 'rrze-xliff'),
            [$this, 'rrze_xliff_export_import_post_types'],
            'rrze_xliff_options',
            'rrze_xliff_section_general'
        );

        add_settings_field(
            'rrze_xliff_export_xliff_version',
            __('XLIFF Version', 'rrze-xliff'),
            [$this, 'rrze_xliff_export_xliff_version'],
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
        // Prüfen, ob keine Post-Types angehakt sind und dann den Standard nehmen.
        if (! isset($input['rrze_xliff_export_import_post_types'])) {
            $input['rrze_xliff_export_import_post_types'] = Options::get_default_options()->rrze_xliff_export_import_post_types;
        } else {
            // Prüfen, ob die Post-Types gültig sind.
            foreach ($input['rrze_xliff_export_import_post_types'] as $index => $post_type) {
                if (get_post_type_object($post_type) === null) {
                    unset($input['rrze_xliff_export_import_post_types'][$index]);
                }
            }

            if (empty($input['rrze_xliff_export_import_post_types'])) {
                $input['rrze_xliff_export_import_post_types'] = Options::get_default_options()->rrze_xliff_export_import_post_types;
            }
        }

        // Prüfen, ob die Benutzerrolle gültig ist.
        $selected_role = $input['rrze_xliff_export_import_role'];
        if (get_role($selected_role) === null) {
            $input['rrze_xliff_export_import_role'] = Options::get_default_options()->rrze_xliff_export_import_role;
        }

        // Prüfen, ob E-Mail-Adresse angegeben wurde.
        $email_address = $input['rrze_xliff_export_email_address'];
        if ($email_address === '' || filter_var($email_address, FILTER_VALIDATE_EMAIL) === false) {
            $input['rrze_xliff_export_email_address'] = Options::get_default_options()->rrze_xliff_export_email_address;
        }

        // Prüfen, ob E-Mail-Betreff angegeben wurde.
        $email_subject = $input['rrze_xliff_export_email_subject'];
        if ($email_subject === '') {
            $input['rrze_xliff_export_email_subject'] = Options::get_default_options()->rrze_xliff_export_email_subject;
        }

        // Prüfen, ob eine gültige XLIFF-Version angegeben wurde.
		$xliff_version = $input['rrze_xliff_export_xliff_version'];
		$valid_xliff_version = false;
		foreach ($this->xliff_versions as $valid_version) {
			if ($xliff_version === $valid_version['value']) {
				$valid_xliff_version = true;
				continue;
			}
		}
        if ($valid_xliff_version === false) {
            $input['rrze_xliff_export_xliff_version'] = Options::get_default_options()->rrze_xliff_export_xliff_version;
        }

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
        if ('' !== $args['description']) {
            ?>
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
        global $wp_roles;
        $allowed_roles = $this->helpers->allowed_roles();
        $role_option_elements = '';
        foreach (array_keys($allowed_roles) as $role) {
            $role_option_elements .= sprintf(
                '<option value="%s" %s>%s</option>',
                $role,
                $this->options->rrze_xliff_export_import_role === $role ? 'selected' : '',
                translate_user_role($wp_roles->roles[$role]['name'])
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
     * Feld für unterstützte Post-Types.
     */
    public function rrze_xliff_export_xliff_version()
    {
		$saved_setting = $this->options->rrze_xliff_export_xliff_version;
		$versions = [
			[
				'label' => __('XLIFF 1', 'rrze-xliff'),
				'value' => '1',
			],
			[
				'label' => __('XLIFF 2', 'rrze-xliff'),
				'value' => '2',
			]
		];
		$xliff_version_options = '';
		foreach ($this->xliff_versions as $version) {
            $xliff_version_options .= sprintf(
                '<p><input type="radio" value="%1$s" name="%2$s" id="%3$s" %5$s><label for="%3$s">%4$s</label></p>',
                $version['value'],
                sprintf('%s[rrze_xliff_export_xliff_version]', $this->option_name),
                sprintf('rrze-xliff-export-xliff-version-%s', $version['value']),
                $version['label'],
                $saved_setting === $version['value'] ? 'checked' : ''
            );
		}
		
		echo $xliff_version_options;
    }
}
