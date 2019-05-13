<?php

namespace RRZE\XLIFF;

class Export
{
    /**
     * Die ID des Inhalts, für den ein Export erstellt werden soll.
     *
     * @var int|null
     */
	protected $post_id = null;
	
	protected $filename = '';

	protected $xliff_file = '';

    /**
     * Initialisierung des Exports.
     */
    public function __construct()
    {
        /**
         * @todo Check if user is allowed to export.
         */

        if (is_admin() && isset($_GET['xliff-export']) && absint($_GET['xliff-export'])) {
            $this->post_id = $_GET['xliff-export'];
            $this->send_xliff_download();
        }
    }

    protected function send_xliff_download()
    {
        // Get the XLIFF string.
        $this->xliff_file = $this->get_xliff_string();
        
        if (is_wp_error($this->xliff_file)) {
            echo $this->xliff_file->get_error_message();
            wp_die();
        }
        
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
		
        $this->filename = sanitize_file_name(sprintf(
            '%s%s-%s-%s-%s.xliff',
            $domain,
            $path !== '/' ? "-$path" : '',
            $blog_id,
            $this->post_id,
            date('Ymd')
		));
		
		// Entscheiden, ob die Datei heruntergeladen oder per Mail verschickt werden soll.
		if (!isset($_GET['email_adress']) || filter_var($_GET['email_adress'], FILTER_VALIDATE_EMAIL) === false) {
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename=' . $filename);
			header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
			echo $this->xliff_file;
			exit;
		} else {
			$to = $_GET['email_adress'];
			$subject = __('XLIFF-Export', 'rrze-xliff');
			$body = __('Here comes the email export', 'rrze-xliff');
			$headers = ['Content-Type: text/html; charset=UTF-8'];
			
			add_action('phpmailer_init', function(&$phpmailer) {
				$phpmailer->AddStringAttachment($this->xliff_file, $this->filename);
			});
			wp_mail($to, $subject, $body, $headers);
		}
    }

    /**
     * XKIFF-Markup genieren.
     * 
     * @return string|\WP_Error Xliff-String.
     */
    protected function get_xliff_string()
    {
        $export_post = get_post($this->post_id, OBJECT);
        if ($export_post === null) {
            return new \WP_Error('no_post', __('The submitted ID for export does not match a post', 'rrze-xliff'));
        }
        
        $source_language_code = get_post_meta($this->post_id, '_translate_from_lang_post_meta', true);
        if ($source_language_code == '') {
            return new \WP_Error('no_source_lang_code', __('No source language code set.', 'rrze-xliff'));
        }
        $source_language_code = substr($source_language_code, 0, 2);

        $language_code = get_post_meta($this->post_id, '_translate_to_lang_post_meta', true);
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

        $post_meta = get_post_meta($this->post_id);
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

        $this->xliff_file = sprintf(
            '<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="%1$s" trgLang="%2$s">
    <file id="f1">
%3$s
    </file>
</xliff>',
            $source_language_code,
            $language_code,
            $translation_units
        );
        
        return $this->xliff_file;
    }
}
