<?php

if (!defined('ABSPATH'))
{
	die();
}

class we_are_open
{
	public
		$days = array(),
		$regular = array(),
		$special = array(),
		$closure = array(),
		$notes = FALSE;

	private
		$dashboard = NULL,
		$section = NULL,
		$class_name = NULL,
		$alias = NULL,
		$prefix = NULL,
		$data = array(),
		$consolidation = array(),
		$google_data = array(),
		$google_result = array(),
		$google_result_valid = NULL,
		$settings_updated = FALSE,
		$request_count = NULL,
		$day_range_min = NULL,
		$day_formats = array(),
		$time_formats = array(),
		$consolidation_types = array(),
		$consolidation_labels = FALSE,
		$offset = NULL,
		$offset_changes = NULL,
		$week_start = NULL,
		$current_timestamp = NULL,
		$today_timestamp = NULL,
		$today = NULL,
		$yesterday_timestamp = NULL,
		$yesterday = NULL,
		$tomorrow_timestamp = NULL,
		$tomorrow = NULL,
		$weekdays = array(),
		$weekend = array(),
		$week_start_timestamp = NULL,
		$next_week_start_timestamp = NULL,
		$synchronization = NULL,
		$business_types = array(),
		$price_ranges = array(),
		$logo_image_id = NULL,
		$logo_image_url = NULL,
		$image_url = NULL,
		$api_key = NULL,
		$place_id = NULL,
		$accepted_day_format = NULL;
	
	public function __construct()
	{
		// Class contructor that starts everything
		
		$this->dashboard = (is_admin() || defined('DOING_CRON'));
		$this->section = NULL;
		$this->class_name = 'we_are_open';
		$this->alias = 'we_are_open';
		$this->prefix = $this->alias . '_';
		$this->settings_updated = FALSE;
		$this->request_count = 0;
		$this->google_result_valid = NULL;
		$this->day_range_min = 3;
		$this->days = array();
		$this->offset = round(floatval(get_option('gmt_offset')) * HOUR_IN_SECONDS);
		$this->offset_changes = NULL;
		$this->logo_image_id = NULL;
		$this->image_url = NULL;
		$this->accepted_day_format = '#^(?:[dDjlSwzFMmntYy ,.:;_()/—–-]+|[dDjlSwzFMmntYy ,.:;_()/—–-]+[^][S][dDjlSwzFMmntYy ,.:;_()/—–-]+)$#';
		$this->current_timestamp = time();
		$this->week_start = 0;
		$this->week_start_timestamp = NULL;
		$this->next_week_start_timestamp = NULL;
		$this->today_timestamp = $this->get_day_timestamp();
		$this->yesterday_timestamp = $this->get_day_timestamp(-1);
		$this->tomorrow_timestamp = $this->get_day_timestamp(1);
		$this->synchronization = NULL;
		$this->today = wp_date("w", $this->today_timestamp);
		$this->yesterday = wp_date("w", $this->yesterday_timestamp);
		$this->tomorrow = wp_date("w", $this->tomorrow_timestamp);
		$this->week_start = (intval(get_option($this->prefix . 'week_start')) < 0) ? ((intval(get_option($this->prefix . 'week_start')) == -2) ? wp_date("w", $this->yesterday_timestamp) : wp_date("w", $this->today_timestamp)) : get_option($this->prefix . 'week_start');
		
		for ($i = 0; $i < 7; $i++)
		{
			$this->days[$i] = $this->sentence_case(wp_date("l", 1590883200 + $i * DAY_IN_SECONDS + ($this->offset * -1) + HOUR_IN_SECONDS));
			
			if ($this->week_start_timestamp == NULL && $this->week_start == wp_date("w", mktime(0, 0, 0, wp_date("m"), wp_date("j") + $i, wp_date("Y"))))
			{
				$this->week_start_timestamp = $this->get_day_timestamp((($i > 0) ? $i - 7 : 0));
				$this->next_week_start_timestamp = $this->get_day_timestamp((($i > 0) ? $i : 7));
			}
		}
		
		if (!is_numeric($this->week_start))
		{
			$this->week_start = 0;
		}

		$this->time_formats = array(
			'12_colon_gap' => array('9:30 am – 5:00 pm', 'g:i a', FALSE),
			'12_colon_gap_uc' => array('9:30 AM – 5:00 PM', 'g:i A', FALSE),
			'12_colon_gap_trim' => array('9:30 am – 5 pm', 'g:i a', TRUE),
			'12_colon_gap_uc_trim' => array('9:30 AM – 5 PM', 'g:i A', TRUE),
			'12_colon' => array('9:30am – 5:00pm', 'g:ia', FALSE),
			'12_colon_uc' => array('9:30AM – 5:00PM', 'g:iA', FALSE),
			'12_colon_trim' => array('9:30am – 5pm', 'g:ia', TRUE),
			'12_colon_uc_trim' => array('9:30AM – 5PM', 'g:iA', TRUE),
			'12_dot_gap' => array('9.30am – 5.00 pm', 'g.i a', FALSE),
			'12_dot_gap_uc' => array('9.30AM – 5.00 PM', 'g.i A', FALSE),
			'12_dot_gap_trim' => array('9.30 am – 5 pm', 'g.i a', TRUE),
			'12_dot_gap_uc_trim' => array('9.30 AM – 5 PM', 'g.i A', TRUE),
			'12_dot' => array('9.30am – 5.00pm', 'g.ia', FALSE),
			'12_dot_uc' => array('9.30AM – 5.00PM', 'g.iA', FALSE),
			'12_dot_trim' => array('9.30am – 5pm', 'g.ia', TRUE),
			'12_dot_uc_trim' => array('9.30AM – 5PM', 'g.iA', TRUE),
			'24_none' => array('0930 – 1700', 'Hi', FALSE),
			'24_colon' => array('09:30 – 17:00', 'H:i', FALSE),
			'24_dot_single_digit' => array('9.30 – 17:00', 'G:i', FALSE),
			'24_colon_trim' => array('09:30 – 17', 'H:i', TRUE),
			'24_colon_dash' => array('09:30 – 17:–', 'H:i', '–'),
			'24_colon_mdash' => array('09:30 – 17:—', 'H:i', '—'),
			'24_dot_single_digit_dash' => array('9.30 – 17:–', 'G:i', '–'),
			'24_dot_single_digit_mdash' => array('9.30 – 17:—', 'G:i', '—'),
			'24_dot' => array('09.30 – 17.00', 'H.i', FALSE),
			'24_dot_single_digit' => array('9.30 – 17.00', 'G.i', FALSE),
			'24_dot_trim' => array('09.30 – 17', 'H.i', FALSE),
			'24_dot_dash' => array('09.30 – 17.–', 'H.i', '–'),
			'24_dot_mdash' => array('09.30 – 17.—', 'H.i', '—'),
			'24_dot_single_digit_dash' => array('9.30 – 17.–', 'G.i', '–'),
			'24_dot_single_digit_mdash' => array('9.30 – 17.—', 'G.i', '—'),
			'24_h' => array('09h30 – 17h00', 'H\\hi', FALSE),
			'24_h_single_digit' => array('9h30 – 17h00', 'G\\hi', FALSE),
			'24_h_trim' => array('09h30 – 17h', 'H\\hi', FALSE),
			'24_h_dash' => array('09h30 – 17h–', 'H\\hi', '–'),
			'24_h_mdash' => array('09h30 – 17h—', 'H\\hi', '—'),
			'24_h_single_digit_dash' => array('9h30 – 17h–', 'G\\hi', '–'),
			'24_h_single_digit_mdash' => array('9h30 – 17h—', 'G\\hi', '—')
		);
		
		$this->consolidation_types = array(
			NULL => __('None', 'opening-hours'),
			'weekdays' => __('Weekdays only', 'opening-hours'),
			'weekend' => __('Weekend only', 'opening-hours'),
			'separate' => __('Weekdays and weekend, separately', 'opening-hours'),
			'all' => __('All days', 'opening-hours')
		);
		
		$this->admin_init();
		$this->wp_init();
		return TRUE;
	}
	
	public static function activate()
	{
		// Activate plugin
		
		if (!current_user_can('activate_plugins', __CLASS__))
		{
			return;
		}

		if (function_exists('version_compare') && function_exists('get_bloginfo') && version_compare(get_bloginfo('version'), '5.3', '<'))
		{
			wp_die(__('This plugin requires a more recent version of WordPress.', 'opening-hours'));
		}
		
		$language = 'en';
		$google_api_languages = array(
			'af',
			'am',
			'ar',
			'az',
			'be',
			'bg',
			'bn',
			'bs',
			'ca',
			'cs',
			'da',
			'de',
			'el',
			'en',
			'en-AU',
			'en-GB',
			'es',
			'es-419',
			'et',
			'eu',
			'fa',
			'fi',
			'fil',
			'fr',
			'fr-CA',
			'gl',
			'gu',
			'hi',
			'hr',
			'hu',
			'hy',
			'id',
			'is',
			'it',
			'iw',
			'ja',
			'ka',
			'kk',
			'km',
			'kn',
			'ko',
			'ky',
			'lo',
			'lt',
			'lv',
			'mk',
			'ml',
			'mn',
			'mr',
			'ms',
			'my',
			'ne',
			'nl',
			'no',
			'pa',
			'pl',
			'pt',
			'pt-BR',
			'pt-PT',
			'ro',
			'ru',
			'si',
			'sk',
			'sl',
			'sq',
			'sr',
			'sv',
			'sw',
			'ta',
			'te',
			'th',
			'tr',
			'uk',
			'ur',
			'uz',
			'vi',
			'zh',
			'zh-CN',
			'zh-HK',
			'zh-TW',
			'zu'
		);

		if (is_string(get_option('WPLANG')))
		{
			if (preg_match('/^[^_]+$/', get_option('WPLANG')) && in_array(get_option('WPLANG'), $google_api_languages))
			{
				$language = get_option('WPLANG');
			}
			elseif (preg_match('/^([^_]+)_([^_]+)$/', get_option('WPLANG'), $m) && in_array($m[1] . '-' . $m[2], $google_api_languages))
			{
				$language = $m[1] . '-' . $m[2];
			}
		}
		
		if (!is_string(get_option(__CLASS__ . '_time_format')))
		{
			$regular = array();

			for ($i = 0; $i < 7; $i++)
			{
				$regular[$i] = array(
					'closed' => TRUE,
					'hours' => array(),
					'hours_24' => FALSE,
					'modified' => NULL
				);
			}

			$plugin_data = (function_exists('get_file_data')) ? get_file_data(plugin_dir_path(__FILE__) . 'opening-hours.php', array('Version' => 'Version'), FALSE) : array();
			$version = (array_key_exists('Version', $plugin_data)) ? $plugin_data['Version'] : NULL;
			$week_start = get_option('start_of_week');
	
			update_option(__CLASS__ . '_24_hours_text', __('Open 24 Hours', 'opening-hours'), 'yes');
			update_option(__CLASS__ . '_address', NULL, 'no');
			update_option(__CLASS__ . '_api_key', NULL, 'no');
			update_option(__CLASS__ . '_business_type', NULL, 'no');
			update_option(__CLASS__ . '_closed_show', TRUE, 'yes');
			update_option(__CLASS__ . '_closed_text', __('Closed', 'opening-hours'), 'yes');
			update_option(__CLASS__ . '_closure', NULL, 'no');
			update_option(__CLASS__ . '_consolidation', NULL, 'yes');
			update_option(__CLASS__ . '_consolidation_labels', TRUE, 'yes');
			update_option(__CLASS__ . '_custom_styles', NULL, 'yes');
			update_option(__CLASS__ . '_day_format', 'full', 'yes');
			update_option(__CLASS__ . '_day_format_special', NULL, 'yes');
			update_option(__CLASS__ . '_everyday_text', __('Everyday', 'opening-hours'), 'yes');
			update_option(__CLASS__ . '_google_places_api', 1, 'no');
			update_option(__CLASS__ . '_google_result', NULL, 'no');
			update_option(__CLASS__ . '_google_sync', 0, 'no');
			update_option(__CLASS__ . '_google_sync_frequency', 24, 'no');
			update_option(__CLASS__ . '_javascript', 1, 'yes');
			update_option(__CLASS__ . '_initial_version', $version, 'no');
			update_option(__CLASS__ . '_language', $language, 'no');
			update_option(__CLASS__ . '_log', NULL, 'no');
			update_option(__CLASS__ . '_logo', NULL, 'no');
			update_option(__CLASS__ . '_midday_text', NULL, 'yes');
			update_option(__CLASS__ . '_midnight_text', NULL, 'yes');
			update_option(__CLASS__ . '_name', NULL, 'no');
			update_option(__CLASS__ . '_notifications', NULL, 'no');
			update_option(__CLASS__ . '_place_id', NULL, 'no');
			update_option(__CLASS__ . '_price_range', NULL, 'no');
			update_option(__CLASS__ . '_regular', $regular, 'no');
			update_option(__CLASS__ . '_retrieval', NULL, 'no');
			update_option(__CLASS__ . '_section', NULL, 'no');
			update_option(__CLASS__ . '_special', NULL, 'no');
			update_option(__CLASS__ . '_special_cut_off', 14, 'yes');
			update_option(__CLASS__ . '_structured_data', FALSE, 'yes');
			update_option(__CLASS__ . '_stylesheet', 1, 'yes');
			update_option(__CLASS__ . '_telephone', NULL, 'no');
			update_option(__CLASS__ . '_time_format', NULL, 'yes');
			update_option(__CLASS__ . '_time_group_separator', ', ', 'yes');
			update_option(__CLASS__ . '_time_separator', ' – ', 'yes');
			update_option(__CLASS__ . '_time_type', NULL, 'yes');
			update_option(__CLASS__ . '_day_separator', ', ', 'yes');
			update_option(__CLASS__ . '_day_range_separator', ' – ', 'yes');
			update_option(__CLASS__ . '_day_range_suffix', ':', 'yes');
			update_option(__CLASS__ . '_day_range_suffix_special', ':', 'yes');
			update_option(__CLASS__ . '_week_start', $week_start, 'yes');
			update_option(__CLASS__ . '_weekdays', (($week_start == 0) ? array('_', 1, 2, 3, 4) : (($week_start == 6) ? array('_', 1, 2, 3, 6) : array(1, 2, 3, 4, 5))), 'yes');
			update_option(__CLASS__ . '_weekdays_text', __('Weekdays', 'opening-hours'), 'yes');
			update_option(__CLASS__ . '_weekend', (($week_start == 0) ? array(5, 6) : (($week_start == 6) ? array(4, 5) : array('_', 6))), 'yes');
			update_option(__CLASS__ . '_weekend_text', __('Weekend', 'opening-hours'), 'yes');
			
			self::log('install', $version);
		}
		else
		{
			self::log('activate');
		}

		if ($language != 'en')
		{
			update_option(__CLASS__ . '_language', $language, 'no');
		}

		return TRUE;
	}
	
	public static function deactivate()
	{
		// Deactivate the plugin

		if (!current_user_can('activate_plugins', __CLASS__))
		{
			return;
		}
		
		wp_cache_delete('data', __CLASS__);
		wp_cache_delete('regular', __CLASS__);
		wp_cache_delete('special', __CLASS__);
		wp_cache_delete('closure', __CLASS__);
		wp_cache_delete('structured_data', __CLASS__);
		wp_cache_delete('google_result', __CLASS__);
		wp_cache_delete('consolidation', __CLASS__);
		delete_transient(__CLASS__ . '_offset_changes');
		update_option(__CLASS__ . '_google_result', NULL, 'no');

		self::log('deactivate');
		
		return TRUE;
	}
	
	public static function uninstall($check = NULL)
	{
		// Uninstall plugin

		if (!current_user_can('activate_plugins', __CLASS__))
		{
			return;
		}

		if ($check != NULL && $check != md5(__FILE__ . ':' . __CLASS__))
		{
			die();
		}

		delete_option(__CLASS__ . '_24_hours_text');
		delete_option(__CLASS__ . '_address');
		delete_option(__CLASS__ . '_api_key');
		delete_option(__CLASS__ . '_business_type');
		delete_option(__CLASS__ . '_closed_show');
		delete_option(__CLASS__ . '_closed_text');
		delete_option(__CLASS__ . '_closure');
		delete_option(__CLASS__ . '_consolidation');
		delete_option(__CLASS__ . '_consolidation_labels');
		delete_option(__CLASS__ . '_custom_styles');
		delete_option(__CLASS__ . '_day_format');
		delete_option(__CLASS__ . '_day_format_special');
		delete_option(__CLASS__ . '_day_range_separator');
		delete_option(__CLASS__ . '_day_range_suffix');
		delete_option(__CLASS__ . '_day_range_suffix_special');
		delete_option(__CLASS__ . '_day_separator');
		delete_option(__CLASS__ . '_everyday_text');
		delete_option(__CLASS__ . '_force');
		delete_option(__CLASS__ . '_google_places_api');
		delete_option(__CLASS__ . '_google_result');
		delete_option(__CLASS__ . '_google_sync');
		delete_option(__CLASS__ . '_google_sync_frequency');
		delete_option(__CLASS__ . '_initial_version');
		delete_option(__CLASS__ . '_javascript');
		delete_option(__CLASS__ . '_language');
		delete_option(__CLASS__ . '_log');
		delete_option(__CLASS__ . '_logo');
		delete_option(__CLASS__ . '_midday_text');
		delete_option(__CLASS__ . '_midnight_text');
		delete_option(__CLASS__ . '_name');
		delete_option(__CLASS__ . '_notifications');
		delete_option(__CLASS__ . '_place_id');
		delete_option(__CLASS__ . '_price_range');
		delete_option(__CLASS__ . '_regular');
		delete_option(__CLASS__ . '_result');
		delete_option(__CLASS__ . '_retrieval');
		delete_option(__CLASS__ . '_section');
		delete_option(__CLASS__ . '_special');
		delete_option(__CLASS__ . '_special_cut_off');
		delete_option(__CLASS__ . '_structured_data');
		delete_option(__CLASS__ . '_stylesheet');
		delete_option(__CLASS__ . '_telephone');
		delete_option(__CLASS__ . '_time_format');
		delete_option(__CLASS__ . '_time_group_separator');
		delete_option(__CLASS__ . '_time_separator');
		delete_option(__CLASS__ . '_time_type');
		delete_option(__CLASS__ . '_week_start');
		delete_option(__CLASS__ . '_weekdays');
		delete_option(__CLASS__ . '_weekdays_text');
		delete_option(__CLASS__ . '_weekend');
		delete_option(__CLASS__ . '_weekend_text');
		delete_option('widget_' . __CLASS__);

		return TRUE;
	}
	
	public static function upgrade($object, $options)
	{
		// Upgrade plugin
		
		if (!isset($options['action']) || isset($options['action']) && $options['action'] != 'update' || !isset($options['type']) || isset($options['type']) && $options['type'] != 'plugin' || !isset($options['plugins']) || isset($options['plugins']) && !is_array($options['plugins']))
		{
			return TRUE;
		}
		
		$plugin_directory_name = preg_replace('#^/?([^/]+)/.*$#', '$1', plugin_basename(__FILE__));
		
		foreach ($options['plugins'] as $path)
		{	
			if (!preg_match('#^/?' . preg_quote($plugin_directory_name, '#'). '/.*$#', $path))
			{
				continue;
			}

			wp_cache_delete('data', __CLASS__);
			wp_cache_delete('regular', __CLASS__);
			wp_cache_delete('special', __CLASS__);
			wp_cache_delete('closure', __CLASS__);
			wp_cache_delete('structured_data', __CLASS__);
			wp_cache_delete('google_result', __CLASS__);
			wp_cache_delete('consolidation', __CLASS__);
			
			$plugin_data = (function_exists('get_file_data')) ? get_file_data(plugin_dir_path(__FILE__) . 'opening-hours.php', array('Version' => 'Version'), FALSE) : array();
			$version = (array_key_exists('Version', $plugin_data)) ? $plugin_data['Version'] : 0;
			$initial_version = get_option(__CLASS__ . '_initial_version', 0);
			$custom_styles = get_option(__CLASS__ . '_custom_styles');

			if (!is_numeric(get_option(__CLASS__ . '_google_places_api', NULL)))
			{
				update_option(__CLASS__ . '_google_places_api', 0, 'no');
			}

			if (!version_compare($initial_version, '1.35'))
			{
				update_option(__CLASS__ . '_javascript', 1, 'yes');
				update_option(__CLASS__ . '_stylesheet', get_option(__CLASS__ . '_stylesheet', TRUE) ? 1 : 0, 'yes');
			}

			if (version_compare($version, '1.52', '<'))
			{
				wp_clear_scheduled_hook('we_are_open_run');
				wp_schedule_event(time(), 'hourly', 'we_are_open_run');
			}
			
			if ($custom_styles == NULL)
			{
				return TRUE;
			}
			
			$fp = FALSE;
			$custom_styles_file = plugin_dir_path(__FILE__) . 'wp/css/custom.css';

			if (!is_file($custom_styles_file))
			{
				if (!is_writable(plugin_dir_path(__FILE__) . 'wp/css/'))
				{
					return TRUE;
				}
				
				$fp = fopen($custom_styles_file, 'w');
				
				if (!$fp || !is_file($custom_styles_file))
				{
					if ($fp)
					{
						fclose($fp);
					}
					
					return TRUE;
				}
			}
			
			if (!is_writable($custom_styles_file))
			{
				return TRUE;
			}
			
			if (!$fp)
			{
				$fp = fopen($custom_styles_file, 'w');
			}
				
			if (!$fp || !fwrite($fp, ($custom_styles != NULL) ? $custom_styles : ''))
			{
				return TRUE;
			}
			
			fclose($fp);

			return TRUE;
		}
		
		return TRUE;
	}
	
	private function reset()
	{
		// Reset the plugin to a fresh installation
		
		$this->set(NULL, TRUE);
		
		if (!self::deactivate())
		{
			return FALSE;
		}
		
		$md5 = md5(__FILE__ . ':' . __CLASS__);

		if (!self::uninstall($md5))
		{
			return FALSE;
		}
		
		self::log('reset');
		
		return self::activate();
	}

	public function admin_init()
	{
		// Initiate the plugin in the dashboard
		
		$this->settings_updated = ($this->dashboard && isset($_REQUEST['settings-updated']) && (is_bool($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] || is_string($_REQUEST['settings-updated']) && preg_match('/^(?:true|1)$/i', $_REQUEST['settings-updated'])));
		$this->notes = ($this->dashboard && isset($_REQUEST['notes']) && (is_bool($_REQUEST['notes']) && $_REQUEST['notes'] || is_string($_REQUEST['notes']) && preg_match('/^(?:true|1)$/i', $_REQUEST['notes'])));

		register_setting($this->prefix . 'settings', $this->prefix . 'day_format', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_string')));
		register_setting($this->prefix . 'settings', $this->prefix . 'day_format_special', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_string')));
		register_setting($this->prefix . 'settings', $this->prefix . 'time_format', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_string')));
		register_setting($this->prefix . 'settings', $this->prefix . 'time_type', array('type' => 'integer'));
		register_setting($this->prefix . 'settings', $this->prefix . 'closed_show', array('type' => 'boolean'));
		register_setting($this->prefix . 'settings', $this->prefix . 'weekdays', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_array')));
		register_setting($this->prefix . 'settings', $this->prefix . 'weekend', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_array')));
		register_setting($this->prefix . 'settings', $this->prefix . 'consolidation', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_string')));
		register_setting($this->prefix . 'settings', $this->prefix . 'week_start', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_string')));
		register_setting($this->prefix . 'settings', $this->prefix . 'stylesheet', array('type' => 'number'));
		register_setting($this->prefix . 'settings', $this->prefix . 'javascript', array('type' => 'number'));
		
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_enqueue_scripts', array($this, 'admin_css_load'));
		add_action('admin_enqueue_scripts', array($this, 'admin_js_load'));
		add_action('wp_ajax_' . $this->class_name . '_admin_ajax', array($this, 'admin_ajax'));
		add_action('admin_notices', array($this, 'admin_notices'));
		add_action('widgets_init', array($this, 'widget'));
		add_action('init', array($this, 'loaded'));		
		
		add_filter('plugin_action_links', array($this->class_name, 'admin_add_action_links'), 10, 5);
		add_filter('plugin_row_meta', array($this->class_name, 'admin_add_plugin_meta'), 10, 2);
		
		if (!$this->set())
		{
			return TRUE;
		}
		
		$this->set_logo();
		$this->set_synchronization();
		
		return TRUE;
	}
	
	public function wp_init()
	{
		// Initiate the plugin in the front-end

		$stylesheet = get_option($this->prefix . 'stylesheet', TRUE);
		$javascript = get_option($this->prefix . 'javascript', TRUE);
		$structured_data = get_option($this->prefix . 'structured_data', 0);

		add_shortcode('closed_now', array($this, 'wp_display_closed_now'));
		add_shortcode('open', array($this, 'wp_display'));
		add_shortcode('open_not_special', array($this, 'wp_display_open_special'));
		add_shortcode('open_now', array($this, 'wp_display_open_now'));
		add_shortcode('open_special', array($this, 'wp_display_open_special'));
		add_shortcode('open_text', array($this, 'wp_display'));
		add_shortcode('opening_hours', array($this, 'wp_display'));
		add_shortcode('opening_hours_text', array($this, 'wp_display'));
		add_shortcode('we_are_open', array($this, 'wp_display'));
		
		if (is_bool($stylesheet) && $stylesheet || is_numeric($stylesheet) && $stylesheet > 0 || is_string($stylesheet) && $stylesheet != NULL)
		{
			add_action('wp_enqueue_scripts', array($this, 'wp_css_load'));
		}
		
		if (is_bool($javascript) && $javascript || is_numeric($javascript) && $javascript > 0 || is_string($javascript) && $javascript != NULL)
		{
			add_action('wp_enqueue_scripts', array($this, 'wp_js_load'));
			add_action('wp_ajax_' . $this->class_name . '_wp_ajax', array($this, 'wp_ajax'));
			add_action('wp_ajax_nopriv_' . $this->class_name . '_wp_ajax', array($this, 'wp_ajax'));
		}
		
		if (is_bool($structured_data) && $structured_data || is_numeric($structured_data) && ($structured_data >= 1 || $structured_data <= -1))
		{
			add_action('wp_head', array($this, 'structured_data'));
		}

		add_action('init', array($this, 'loaded'));		

		$this->weekdays = get_option($this->prefix . 'weekdays', array());
		$this->weekend = get_option($this->prefix . 'weekend', array());

		return TRUE;
	}

	public function sync()
	{
		// Handle synchronization from CRON job
		
		if (!defined('DOING_CRON') || defined('DOING_CRON') && !DOING_CRON)
		{
			return FALSE;
		}

		$this->set_synchronization();

		if ($this->synchronization != 'google_places')
		{
			return TRUE;
		}

		$frequency = get_option($this->prefix . 'google_sync_frequency', 24);

		if (!is_numeric($frequency) || $frequency < 1 || $frequency > 24)
		{
			$frequency = 24;
		}

		switch ($frequency)
		{
		case 24:
		case 12:
		case 8:
		case 6:
		case 4:
		case 3:
		case 2:
		case 1:
			if (intval(wp_date("H")) % $frequency > 0)
			{
				return TRUE;
			}
			break;
		default:
			return TRUE;
		}

		if (!$this->set(NULL, TRUE))
		{
			return FALSE;
		}
		
		return $this->set_google_data();
	}
	
	public function admin_menu()
	{
		// Set the menu item
		
		if (!current_user_can('edit_published_posts', $this->class_name))
		{
			return;
		}
		
		$icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2Ij4KICA8cGF0aCBkPSJNNy44IDcuNzVjLTEuMzIgMC4wNS0zLjEzIDEuNzItMy4xMyAzLjMyIDAgMS44NSAxLjggMy4zMyAzLjEzIDMuMzMgMS44NSAwIDMuMzMtMS40NyAzLjMzLTMuMzJDMTEuMTMgOS4yMiA5LjY1IDcuNjkgNy44IDcuNzV6TTguODcgMTMuNTdjLTAuMDYgMC4wNi0xLjMzLTEuNzUtMS4zMy0xLjc1cy0wLjIyLTAuMjMtMC4yOC0wLjRjLTAuMDYtMC4xNy0wLjA2LTAuNTEtMC4wNi0wLjU2IDAtMC4wNiAwLjMzLTIuNzEgMC40NS0yLjcxIDAuMDYgMCAwLjUgMi44OCAwLjUgM0M4LjIxIDExLjI1IDguOTMgMTMuNTEgOC44NyAxMy41N3oiIGZpbGw9IiNhMGE1YWEiLz4KICA8cGF0aCBkPSJNMTQuNjIgNS45MWMtMC4wMi0wLjA1LTAuMDQtMC4wNi0wLjA1LTAuMDcgMC4wMSAwIDAuMDMgMCAwIDAgLTAuMDEgMC0wLjAyIDAtMC4wMiAwIC0wLjAyIDAtMC4wNSAwLTAuMSAwIC0wLjAyLTAuMDItMC4wNC0wLjAxLTAuMDQtMC4wMXMtMC41NyAwLjAxLTEuNDcgMC4wM2MtMC45LTEuMzMtNS4zNi01LjI5LTUuNTEtNS40NiAtMC40Ny0wLjU0LTEuNzMtMC40Ni0yLjA5IDAgLTAuMjggMC4zNS0yLjQ2IDUuMzYtMi44MiA2LjA1QzEuNjMgNi40NyAxLjA3IDYuNDkgMS4wNiA2LjVjLTAuMDcgMC4wNC0wLjA1IDAuMDMtMC4wNiAwLjA3TDAuOTggOC40M2MwIDAgMC4wMyA2LjgxIDAuMDQgNi44NEMxLjAzIDE1LjI5IDEuMTIgMTUuMzIgMS4xMiAxNS4zMmwwLjI1IDAuMDFjMC43NiAwLjA1IDIuODggMC4yOSA1LjY4IDAuNDQgMi43NCAwLjE1IDQuODkgMC4yIDYuODUgMC4yMSAwLjA1IDAgMC4xNCAwIDAuMjEgMCAwLjEzIDAgMC4zMi0wLjAxIDAuMzQtMC4wMiAwLjA1LTAuMDIgMC4wNS0wLjExIDAuMDUtMC4xMVMxNC42NSA2IDE0LjYyIDUuOTF6TTUuODMgMS41MkM1Ljg3IDEuNDUgNS45MyAxLjQgNiAxLjM3YzAuMDggMC4xNSAwLjIgMC4yOSAwLjM4IDAuMjkgMC4xOSAwIDAuMzItMC4xMiAwLjQyLTAuMjVDNi44MyAxLjQzIDYuODYgMS40NiA2Ljg4IDEuNDljMC4xNyAwLjIzIDQuMTcgMy4zNiA0Ljg4IDQuNCAtMi4yNiAwLjEtNS42MSAwLjM0LTguMDUgMC41MkM0LjQ3IDUuMDYgNS43NCAxLjY1IDUuODMgMS41MnpNMi44NCA4LjJDMi43OCA4LjIgMi43MyA4LjE4IDIuNjkgOC4xNiAyLjUxIDguMDYgMi4zOCA3Ljg2IDIuMzcgNy42Yy0wLjAxLTAuMTkgMC4wOS0wLjM1IDAuMjItMC40OCAwLjIxIDAuMiAwLjQ3IDAuMjcgMC43OSAwLjAzIDAuMDcgMC4xMiAwLjE2IDAuMzQgMC4xNSAwLjQ3QzMuNSA3Ljk3IDMuMDggOC4yMSAyLjg0IDguMnpNNy44NSAxNS4zYy0xLjg4LTAuMDEtNC4yLTIuMDMtNC4xMS00LjIzQzMuODIgOC45MyA1Ljg4IDYuODggNy44NSA2Ljc2YzIuMzQtMC4xNCA0LjI3IDEuOTIgNC4yNyA0LjI3QzEyLjExIDEzLjM4IDEwLjE5IDE1LjMxIDcuODUgMTUuM3pNMTIuOTEgNy43MUMxMi44NiA3LjcxIDEyLjgxIDcuNjkgMTIuNzYgNy42N2MtMC4xOC0wLjEtMC4zLTAuMy0wLjMyLTAuNTcgLTAuMDEtMC4xMSAwLjAzLTAuMjIgMC4wOC0wLjMxIDAuNTEgMC4yOCAwLjczLTAuMiAwLjY4LTAuMzIgMC4yOCAwLjEyIDAuNDEgMC4zOSAwLjQgMC42NEMxMy41OCA3LjQ4IDEzLjE1IDcuNzIgMTIuOTEgNy43MXoiIGZpbGw9IiNhMGE1YWEiLz4KPC9zdmc+';
			
		$pages = array(
			array('add_menu_page', __('We’re Open!', 'opening-hours'), __('We’re Open!', 'opening-hours'), 'edit_published_posts', 'opening_hours', array($this, 'admin'), $icon, 51),
		);
		
		if (current_user_can('manage_options', $this->class_name))
		{
			$pages[] = array('add_options_page', __('We’re Open!', 'opening-hours'), __('We’re Open!', 'opening-hours'), 'manage_options', 'opening_hours_settings', array($this, 'admin_settings'));
		}
		
		foreach ($pages as $i => $p)
		{
			if ($p[0] == 'add_menu_page' || $p[0] == 'add_options_page')
			{
				$function = $p[0];
			}
			else
			{
				$function = 'add_submenu_page';
			}

			array_shift($p);
			call_user_func_array($function, $p);
			continue;
		}
		
		return TRUE;
	}
	
	private function admin_current()
	{
		// Check if the plugin is showing in the Dashboard

		if (!current_user_can('edit_published_posts', $this->class_name))
		{
			return FALSE;
		}
				
		return (isset($_GET['page']) && is_string($_GET['page']) && preg_match('/^(?:we[\s_-]?a?re[\s_-]?open|opening[\s_-]?hours)[\s_-]?.*$/i', $_GET['page']));
	}
	
	private function google_data_exists($valid = FALSE, $reset = FALSE)
	{
		// Check there is any existing data

		if ($reset || !isset($this->google_result_valid) || isset($this->google_result_valid) && !is_bool($this->google_result_valid))
		{
			if (isset($this->google_data['displayName']) && is_array($this->google_data['displayName']) || isset($this->google_data['error']) && is_array($this->google_data['error']))
			{
				$this->google_result_valid = (isset($this->google_data['displayName']) && isset($this->google_data['regularOpeningHours']) && is_array($this->google_data['displayName']) && is_array($this->google_data['regularOpeningHours']));
			}
			else
			{
				$this->google_result_valid = (!empty($this->google_data) && isset($this->google_data['status']) && preg_match('/^OK$/i', $this->google_data['status']) && isset($this->google_data['result']) && isset($this->google_data['result']['name']) && isset($this->google_data['result']['opening_hours']) && is_array($this->google_data['result']['opening_hours']));
			}
		}

		if ($valid)
		{
			return $this->google_result_valid;
		}
		
		return ($this->google_result_valid || !empty($this->google_data) && (isset($this->google_data['displayName']) && is_array($this->google_data['displayName']) || isset($this->google_data['status']) && preg_match('/^OK$/i', $this->google_data['status'])));
	}
	
	public function admin()
	{
		// Management page in the Dashboard
		
		if (!current_user_can('edit_published_posts', $this->class_name))
		{
			wp_die(__('You do not have sufficient permissions to access this page.', 'opening-hours'));
		}

		$this->set_localized_dates();
		
		if (!isset($this->regular) || !is_array($this->regular))
		{
			$this->regular = array();
		}
		
		if (!isset($this->special) || !is_array($this->special))
		{
			$this->special = array();
		}
		
		if (!isset($this->closure) || !is_array($this->closure))
		{
			$this->closure = array();
		}

		if (!$this->notes)
		{
			foreach ($this->special as $a)
			{
				if (!isset($a['note']) || $a['note'] == NULL)
				{
					continue;
				}

				$this->notes = TRUE;
				break;
			}
		}

		include(plugin_dir_path(__FILE__) . 'templates/index.php');
	}
	
	public function admin_settings()
	{
		// Set and process settings in the Dashboard
		
		if (!current_user_can('manage_options', $this->class_name))
		{
			wp_die(__('You do not have sufficient permissions to access this page.', 'opening-hours'));
		}
		
		$this->set_localized_dates();
		
		$this->business_types = array(
			'AnimalShelter' => __('Animal Shelter', 'opening-hours'),
			'ArchiveOrganization' => __('Archive Organization', 'opening-hours'),
			'AutomotiveBusiness' => __('Automotive Business', 'opening-hours'),
			'ChildCare' => __('Child Care', 'opening-hours'),
			'Dentist' => __('Dentist', 'opening-hours'),
			'DryCleaningOrLaundry' => __('Dry Cleaning or Laundry', 'opening-hours'),
			'EmergencyService' => __('Emergency Service', 'opening-hours'),
			'EmploymentAgency' => __('Employment Agency', 'opening-hours'),
			'EntertainmentBusiness' => __('Entertainment Business', 'opening-hours'),
			'FinancialService' => __('Financial Service', 'opening-hours'),
			'FoodEstablishment' => __('Food Establishment', 'opening-hours'),
			'GovernmentOffice' => __('Government Office', 'opening-hours'),
			'HealthAndBeautyBusiness' => __('Health and Beauty Business', 'opening-hours'),
			'HomeAndConstructionBusiness' => __('Home and Construction Business', 'opening-hours'),
			'InternetCafe' => __('Internet Café', 'opening-hours'),
			'LegalService' => __('Legal Service', 'opening-hours'),
			'Library' => __('Library', 'opening-hours'),
			'LodgingBusiness' => __('Lodging Business', 'opening-hours'),
			'MedicalBusiness' => __('Medical Business', 'opening-hours'),
			'ProfessionalService' => __('Professional Service', 'opening-hours'),
			'RadioStation' => __('Radio Station', 'opening-hours'),
			'RealEstateAgent' => __('Real Estate Agent', 'opening-hours'),
			'RecyclingCenter' => __('Recycling Center', 'opening-hours'),
			'SelfStorage' => __('Self Storage', 'opening-hours'),
			'ShoppingCenter' => __('Shopping Center', 'opening-hours'),
			'SportsActivityLocation' => __('Sports Activity Location', 'opening-hours'),
			'Store' => __('Store', 'opening-hours'),
			'TelevisionStation' => __('Television Station', 'opening-hours'),
			'TouristInformationCenter' => __('Tourist Information Center', 'opening-hours'),
			'TravelAgency' => __('Travel Agency', 'opening-hours')
		);
		$this->price_ranges = array(
			1 => array(
					'name' => __('Inexpensive $', 'opening-hours'),
					'symbol' => '$'
				),
			2 => array(
					'name' => __('Moderate $$', 'opening-hours'),
					'symbol' => str_repeat('$', 2)
				),
			3 => array(
					'name' => __('Expensive $$$', 'opening-hours'),
					'symbol' => str_repeat('$', 3)
				),
			4 => array(
					'name' => __('Very Expensive $$$$', 'opening-hours'),
					'symbol' => str_repeat('$', 4)
				)
		);
		
		$this->section = get_option($this->prefix . 'section');

		$placeholders = array(
			'hours_24' => $this->hours_string(array(
					0 => array(
						0 => '00:00',
						1 => '00:00'
					)
				), FALSE, FALSE, NULL, NULL,
				array(
					'hours_24' => NULL,
					'midnight' => NULL
				)
			),
			'midday' => $this->hours_string(array(
					0 => array(
						0 => '12:00',
						1 => '13:00'
					)
				), FALSE, FALSE, NULL, 'start',
				array(
					'midday' => NULL
				)
			),
			'midnight' => $this->hours_string(array(
					0 => array(
						0 => '00:00',
						1 => '01:00'
					)
				), FALSE, FALSE, NULL, 'start',
				array(
					'midnight' => NULL
				)
			)
		);

		include(plugin_dir_path(__FILE__) . 'templates/settings.php');
	}
	
	public function admin_notices()
	{
		// Handle Dashboard notices
		
		if (!current_user_can('edit_published_posts', $this->class_name) || !$this->admin_current())
		{
			return;
		}
		
		$html = '';
		
		if (is_string(get_option($this->prefix . 'api_key')) && is_string(get_option($this->prefix . 'place_id')))
		{
			$this->set();

			if (!current_user_can('manage_options', $this->class_name) || !isset($_GET['page']) || isset($_GET['page']) && !is_string($_GET['page']) || is_string($_GET['page']) && !preg_match('/^opening[\s_-]?hours[\s_-]?settings$/i', $_GET['page']))
			{
				return;
			}

			$status = (isset($this->google_data['error']) && is_array($this->google_data['error']) && isset($this->google_data['error']['status']) && is_string($this->google_data['error']['status'])) ? $this->google_data['error']['status'] : ((isset($this->google_data['status']) && is_string($this->google_data['status'])) ? $this->google_data['status'] : NULL);

			if ($status == NULL || preg_match('/^OK$/i', $status))
			{
				return;
			}
			
			if (isset($this->google_data['error']) && is_array($this->google_data['error']) && preg_match('/^[0-9A-Z _-]{4,127}$/i', $status) && isset($this->google_data['error']['message']) && is_string($this->google_data['error']['message']) && $this->google_data['error']['message'] != NULL)
			{
				if (preg_match('/^PERMISSION|[\s_-]?DENIED$/i', $status))
				{
					$html = '<div class="notice notice-error visible is-dismissible">
	<p>'
				/* translators: %s refers to a URL to resolve errors and should remain untouched */
				. sprintf(__('<strong>Google API Error:</strong> Please enable <a href="%s" target="_blank">Places API (New)</a> and add this to the API Key Restrictions.', 'opening-hours'), 'https://console.cloud.google.com/apis/library/places-backend.googleapis.com?q=places+api+(new)') . '</p>
</div>
';
				}
				else
				{
					$html = '<div class="notice notice-error visible is-dismissible">
	<p>' . $this->google_data['error']['message'] . '</p>
</div>
';
				}
			}
			elseif ($status != NULL)
			{
				if (preg_match('/^(?:PERMISSION|REQUEST)[\s_-]?DENIED$/i', $status))
				{
					$html = '<div class="notice notice-error visible is-dismissible">
	<p>'
				/* translators: %s refers to a URL to resolve errors and should remain untouched */
				. sprintf(__('<strong>Google API Error:</strong> Your Google API Key is not valid for this request and permission is denied. Please check your Google <a href="%s" target="_blank">API Key</a>.', 'opening-hours'), 'https://developers.google.com/maps/documentation/javascript/get-api-key') . '</p>
</div>
';
				}
				elseif (preg_match('/^INVALID[\s_-]?REQUEST$/i', $status))
				{
					$html = '<div class="notice notice-error visible is-dismissible">
	<p>'
				/* translators: %s refers to a URL to resolve errors and should remain untouched */
				. sprintf(__('<strong>Google API Error:</strong> Google has returned an invalid request error. Please check your <a href="%s" target="_blank">Place ID</a>.', 'opening-hours'), 'https://developers.google.com/places/place-id') . '</p>
</div>
';
				}
				elseif (preg_match('/^NOT[\s_-]?FOUND$/i', $status))
				{
					$html = '<div class="notice notice-error visible is-dismissible">
	<p>'
				/* translators: %s refers to a URL to resolve errors and should remain untouched */
				. sprintf(__('<strong>Google API Error:</strong> Google has not found data for the current Place ID. Please ensure you search for a specific business location; not a region or coordinates using the <a href="%s" target="_blank">Place ID Finder</a>.', 'opening-hours'), 'https://developers.google.com/places/place-id') . '</p>
</div>
';
				}
				else
				{
					$html = '<div class="notice notice-error visible is-dismissible">
	<p>' . ((isset($this->google_data['error_message'])) ? preg_replace('/\s+rel="nofollow"/i', ' target="_blank"', '<strong>' . __('Google API Error:', 'opening-hours') . '</strong> ' . $this->google_data['error_message']) : __('<strong>Google API Error:</strong> Unknown error returned by the Google Places API.', 'opening-hours')) . '</p>
</div>
';
				}
			}
		}
		
		if ($html == '')
		{
			return;
		}
		
		echo wp_kses($html, array('div' => array('id' => array(), 'class' => array()), 'span' => array('id' => array(), 'class' => array()), 'p' => array('id' => array(), 'class' => array()), 'a' => array('href' => array(), 'target' => array(), 'class' => array()), 'code' => array(), 'strong' => array(), 'em' => array()));
	}
	
	public function admin_ajax()
	{
		// Handle AJAX requests from Dashboard

		$ret = array();

		if (!$this->dashboard || !current_user_can('edit_published_posts', $this->class_name))
		{
			echo json_encode($ret);
			wp_die();
		}

		$id = (isset($_POST['id']) && is_numeric($_POST['id'])) ? intval($_POST['id']) : NULL;
		$type = (isset($_POST['type']) && is_string($_POST['type'])) ? preg_replace('/[^\w_]/', '', strtolower(wp_kses_stripslashes(sanitize_text_field($_POST['type'])))) : NULL;
		$section = (isset($_POST['section']) && is_string($_POST['type']) && !preg_match('/^(?:general|setup)$/i', $_POST['section'])) ? preg_replace('/[^\w_-]/', '', strtolower(wp_kses_stripslashes(sanitize_text_field($_POST['section'])))) : NULL;
		$regular = (isset($_POST['regular']) && is_array($_POST['regular'])) ? $this->sanitize_input($_POST['regular']) : array();
		$special = (isset($_POST['special']) && is_array($_POST['special'])) ? $this->sanitize_input($_POST['special']) : array();
		$closure = (isset($_POST['closure']) && is_array($_POST['closure'])) ? $this->sanitize_input($_POST['closure']) : array();
		$notification_action = (isset($_POST['notification_action']) && is_string($_POST['notification_action']) && strlen($_POST['notification_action']) >= 2 && strlen($_POST['notification_action']) <= 255) ? mb_strtolower($this->sanitize_input($_POST['notification_action'])) : NULL;
		$structured_data = (isset($_POST['structured_data']) && is_numeric($_POST['structured_data'])) ? intval($_POST['structured_data']) : 0;
		$google_sync = (isset($_POST['google_sync']) && is_numeric($_POST['google_sync']) && intval($_POST['google_sync']) >= 1 && intval($_POST['google_sync']) <= 3) ? intval($_POST['google_sync']) : 0;
		$name = ($structured_data != 0 && isset($_POST['name']) && is_string($_POST['name']) && mb_strlen($_POST['name']) >= 1 && mb_strlen($_POST['name']) <= 100) ? $this->sanitize_input($_POST['name']) : NULL;
		$address = ($structured_data != 0 && isset($_POST['address']) && is_string($_POST['address']) && strlen($_POST['address']) > 2) ? $this->sanitize_multiline($_POST['address']) : NULL;
		$telephone = ($structured_data != 0 && isset($_POST['telephone']) && is_string($_POST['telephone']) && mb_strlen($_POST['telephone']) >= 5 && mb_strlen($_POST['telephone']) <= 100) ? $this->sanitize_input($_POST['telephone']) : NULL;
		$business_type = ($structured_data != 0 && isset($_POST['business_type']) && is_string($_POST['business_type']) && preg_match('/^[a-z]+$/i', $this->sanitize_input($_POST['business_type']))) ? $this->sanitize_input($_POST['business_type']) : NULL;
		$price_range = ($structured_data != 0 && isset($_POST['price_range']) && is_numeric($_POST['price_range']) && intval($_POST['price_range']) >= 1 && intval($_POST['price_range']) <= 4) ? intval($_POST['price_range']) : NULL;
		$logo = ($structured_data != 0 && isset($_POST['logo']) && is_numeric($_POST['logo'])) ? intval($_POST['logo']) : NULL;
		$api_key = (isset($_POST['api_key']) && is_string($_POST['api_key'])) ? $this->sanitize_input($_POST['api_key']) : NULL;
		$place_id = (isset($_POST['place_id']) && is_string($_POST['place_id'])) ? $this->sanitize_input($_POST['place_id']) : NULL;
		$custom_styles = (isset($_POST['custom_styles']) && is_string($_POST['custom_styles']) && strlen($_POST['custom_styles']) > 2 && !preg_match('/<\?(?:php|=)/i', $_POST['custom_styles'])) ? wp_kses_stripslashes(sanitize_text_field($_POST['custom_styles'])) : NULL;
		$reset = (isset($_POST['reset']) && is_array($_POST['reset'])) ? $this->sanitize_array($_POST['reset']) : NULL;
		$link = (isset($_POST['link']) && is_string($_POST['link']) && strlen($_POST['link']) < 255) ? $this->sanitize_input($_POST['link']) : NULL;
		$nonce = (isset($_POST['nonce']) && is_string($_POST['nonce']) && preg_match('/^[0-9a-f]{8,128}$/i', $_POST['nonce'])) ? $this->sanitize_input($_POST['nonce']) : NULL;
		
		switch($type)
		{
		case 'section':
			$this->section = $section;
			update_option($this->prefix . 'section', $this->section, 'no');
			$ret = array(
				'success' => TRUE
			);
			break;
		case 'notification_action':
			if (preg_match('/^notification rate [a-z]{2,25}$/', $notification_action))
			{
				self::log($notification_action);
				$logged = TRUE;
			}

			$ret = array(
				'notification_action' => mb_strtolower($notification_action),
				'link' => $link,
				'success' => $logged
			);
			break;
		case 'update':
			if (!wp_verify_nonce($nonce, $this->class_name . '_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}

			$this->update($regular, $special, $closure);
			$ret = array(
				'google_result' => $this->google_data,
				'regular' => $this->regular,
				'special' => $this->special,
				'closure' => $this->closure,
				'message' => __('Successfully saved opening hours', 'opening-hours'),
				'date' => wp_date("Y/m/d"),
				'success' => TRUE
			);
			break;
		case 'delete':
			if (!wp_verify_nonce($nonce, $this->class_name . '_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}

			$this->delete($special);
			
			if (count($special) <= count($this->special))
			{
				$ret = array(
					'special' => $this->special,
					'date' => wp_date("Y/m/d"),
					'success' => TRUE
				);
				break;
			}
			
			$ret = array(
				'special' => $this->special,
				'message' => __('Successfully removed special opening hours', 'opening-hours'),
				'date' => wp_date("Y/m/d"),
				'success' => TRUE
			);
			break;
		case 'sync':
			if (!current_user_can('manage_options', __CLASS__))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}

			if ($google_sync > 0 && !$this->google_data_exists())
			{
				$google_sync = 0;
			}

			update_option($this->prefix . 'structured_data', $structured_data, 'yes');
			update_option($this->prefix . 'google_sync', $google_sync, 'yes');
			update_option($this->prefix . 'name', $name, 'yes');
			update_option($this->prefix . 'address', $address, 'yes');
			update_option($this->prefix . 'telephone', $telephone, 'yes');
			update_option($this->prefix . 'business_type', $business_type, 'yes');
			update_option($this->prefix . 'logo', $logo, 'yes');
			update_option($this->prefix . 'price_range', $price_range, 'yes');

			$ret = array(
				'structured_data' => $structured_data,
				'google_sync' => $google_sync,
				'message' => __('Successfully set synchronization preference', 'opening-hours'),
				'success' => TRUE
			);
			break;
		case 'google_business_credentials':
			if (!current_user_can('manage_options', __CLASS__))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}

			$current_api_key = get_option($this->prefix . 'api_key');
			$current_place_id = get_option($this->prefix . 'place_id');
			
			$api_key = $this->set_api_key($api_key, $current_api_key);
			$place_id = $this->set_place_id($place_id, $current_place_id, $current_api_key);
			$set_data = array(
				'api_key' => $api_key,
				'place_id' => $place_id
			);
			
			$this->set($set_data);
			
			$business_name = ($this->google_data_exists(TRUE)) ? ((isset($this->google_data['displayName']) && is_array($this->google_data['displayName']) && isset($this->google_data['displayName']['text'])) ? $this->google_data['displayName']['text'] : $this->google_data['result']['name']) : NULL;

			if (($current_api_key != NULL || $current_place_id != NULL) && $api_key == NULL && $place_id == NULL)
			{
				$ret = array(
					'message' => __('Successfully cleared Google My Business credentials', 'opening-hours'),
					'business_name' => $business_name,
					'google_data_exists' => $this->google_data_exists(),
					'success' => TRUE
				);
				break;
			}
			
			if ($api_key != NULL && $place_id == NULL)
			{
				$ret = array(
					'message' => __('Successfully set API Key for Google My Business', 'opening-hours'),
					'business_name' => $business_name,
					'google_data_exists' => $this->google_data_exists(),
					'success' => TRUE
				);
				break;
			}
			
			if ($api_key == NULL && $place_id != NULL)
			{
				$ret = array(
					'message' => __('Successfully set Place ID for Google My Business', 'opening-hours'),
					'business_name' => $business_name,
					'google_data_exists' => $this->google_data_exists(),
					'success' => TRUE
				);
				break;
			}

			$ret = array(
				'message' => __('Successfully set Google My Business credentials', 'opening-hours'),
				'business_name' => $business_name,
				'google_data_exists' => $this->google_data_exists(),
				'success' => TRUE
			);
			break;
		case 'google_data':
		case 'google_business':
			if (!wp_verify_nonce($nonce, $this->class_name . '_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}

			if (!$this->set_google_data())
			{
				if (isset($this->google_data['displayName']) && is_array($this->google_data['displayName']) || isset($this->google_data['error']) && is_array($this->google_data['error']))
				{
					$this->google_result_valid = (isset($this->google_data['displayName']) && isset($this->google_data['regularOpeningHours']) && is_array($this->google_data['displayName']) && is_array($this->google_data['regularOpeningHours']));
				}
				else
				{
					$this->google_result_valid = (!empty($this->google_data) && isset($this->google_data['status']) && preg_match('/^OK$/i', $this->google_data['status']) && isset($this->google_data['result']) && isset($this->google_data['result']['name']) && isset($this->google_data['result']['opening_hours']) && is_array($this->google_data['result']['opening_hours']));
				}

				if (!isset($this->google_data['result']['opening_hours']) || isset($this->google_data['result']['opening_hours']) && !is_array($this->google_data['result']['opening_hours']) || isset($this->google_data['result']['opening_hours']) && is_array($this->google_data['result']['opening_hours']) && empty($this->google_data['result']['opening_hours']))
				{
					$ret = array(
						'valid' => $this->google_result_valid,
						'regular' => $this->regular,
						'special' => $this->special,
						'message' => __('Failed to set data from Google My Business because opening hours do not exist for this place', 'opening-hours'),
						'success' => FALSE
					);
					break;
				}
				
				$ret = array(
					'valid' => $this->google_result_valid,
					'regular' => $this->regular,
					'special' => $this->special,
					'message' => __('Failed to set data from Google My Business', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}

			$places_api = (isset($this->google_data['displayName']) && isset($this->google_data['regularOpeningHours']) && is_array($this->google_data['displayName']) && is_array($this->google_data['regularOpeningHours'])) ? 1 : 0;

			if ($places_api == 1)
			{
				foreach (array_keys($this->special) as $timestamp)
				{
					$this->special[$timestamp]['date_display'] = wp_date("Y-m-d", $timestamp);
					$this->special[$timestamp]['modified_display'] = (is_numeric($this->special[$timestamp]['modified'])) ? wp_date("Y/m/d", $this->special[$timestamp]['modified']) : NULL;
				}
			}

			$ret = array(
				'valid' => $this->google_result_valid,
				'regular' => $this->regular,
				'special' => ($places_api == 1) ? $this->special : NULL,
				'message' => __('Successfully set data from Google My Business', 'opening-hours'),
				'success' => TRUE
			);
			break;
		case 'separators':
			if (!current_user_can('manage_options', __CLASS__))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}

			$time_separator = (isset($_POST['time_separator']) && is_string($_POST['time_separator'])) ? $this->sanitize_separator($_POST['time_separator']) : NULL;
			$time_group_separator = (isset($_POST['time_group_separator']) && is_string($_POST['time_group_separator'])) ? $this->sanitize_separator($_POST['time_group_separator']) : NULL;
			$day_separator = (isset($_POST['day_separator']) && is_string($_POST['day_separator'])) ? $this->sanitize_separator($_POST['day_separator']) : NULL;
			$day_range_separator = (isset($_POST['day_range_separator']) && is_string($_POST['day_range_separator'])) ? $this->sanitize_separator($_POST['day_range_separator']) : NULL;
			$day_range_suffix = (isset($_POST['day_range_suffix']) && is_string($_POST['day_range_suffix'])) ? $this->sanitize_separator($_POST['day_range_suffix'], 'right') : NULL;
			$day_range_suffix_special = (isset($_POST['day_range_suffix_special']) && is_string($_POST['day_range_suffix_special'])) ? $this->sanitize_separator($_POST['day_range_suffix_special'], 'right') : NULL;
			$closed_text = (isset($_POST['closed_text']) && is_string($_POST['closed_text'])) ? $this->sanitize_string($_POST['closed_text']) : NULL;
			$midday_text = (isset($_POST['midday_text']) && is_string($_POST['midday_text'])) ? $this->sanitize_string($_POST['midday_text']) : NULL;
			$midnight_text = (isset($_POST['midnight_text']) && is_string($_POST['midnight_text'])) ? $this->sanitize_string($_POST['midnight_text']) : NULL;
			$hours_24_text = (isset($_POST['hours_24_text']) && is_string($_POST['hours_24_text'])) ? $this->sanitize_string($_POST['hours_24_text']) : NULL;
			$weekdays_text = (isset($_POST['weekdays_text']) && is_string($_POST['weekdays_text'])) ? $this->sanitize_string($_POST['weekdays_text']) : NULL;
			$weekend_text = (isset($_POST['weekend_text']) && is_string($_POST['weekend_text'])) ? $this->sanitize_string($_POST['weekend_text']) : NULL;
			$everyday_text = (isset($_POST['everyday_text']) && is_string($_POST['everyday_text'])) ? $this->sanitize_string($_POST['everyday_text']) : NULL;
			
			if ($closed_text == NULL)
			{
				$ret = array(
					'message' => __('Failed to update — text for “closed” is required.', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}
			
			if ($time_separator == NULL || $time_group_separator == NULL || $day_separator == NULL || $day_range_separator == NULL)
			{
				$ret = array(
					'message' => __('Failed to update — separators cannot be empty.', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}
			
			update_option($this->prefix . 'time_separator', $time_separator, 'yes');
			update_option($this->prefix . 'time_group_separator', $time_group_separator, 'yes');
			update_option($this->prefix . 'day_separator', $day_separator, 'yes');
			update_option($this->prefix . 'day_range_separator', $day_range_separator, 'yes');
			update_option($this->prefix . 'day_range_suffix', $day_range_suffix, 'yes');
			update_option($this->prefix . 'day_range_suffix_special', $day_range_suffix_special, 'yes');
			update_option($this->prefix . 'closed_text', $closed_text, 'yes');
			update_option($this->prefix . 'midday_text', $midday_text, 'yes');
			update_option($this->prefix . 'midnight_text', $midnight_text, 'yes');
			update_option($this->prefix . '24_hours_text', $hours_24_text, 'yes');
			update_option($this->prefix . 'weekdays_text', $weekdays_text, 'yes');
			update_option($this->prefix . 'weekend_text', $weekend_text, 'yes');
			update_option($this->prefix . 'everyday_text', $everyday_text, 'yes');

			$ret = array(
				'message' => __('Settings Saved.', 'opening-hours'),
				'success' => TRUE
			);

			break;
		case 'logo-delete':
		case 'logo_delete':
		case 'logo-remove':
		case 'logo_remove':
			if (!current_user_can('manage_options', __CLASS__))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}
			
			$this->delete_logo();
			
			$ret = array(
				'id' => NULL,
				'image' => NULL,
				'success' => TRUE
			);
			break;	
		case 'logo':
			if (!current_user_can('manage_options', __CLASS__))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}
			
			if (!is_numeric($id))
			{
				$this->delete_logo();
				
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				);
				break;	
			}
			
			$this->set_logo($id);
			
			if (!is_string($this->logo_image_url) || is_string($this->logo_image_url) && strlen($this->logo_image_url) < 5)
			{
				$this->delete_logo();
				
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				);
				
				break;	
			}
			
			$ret = array(
				'id' => $this->logo_image_id,
				'image' => preg_replace('/\s+(?:width|height)="\d*"/i', '', wp_get_attachment_image($this->logo_image_id, 'large', FALSE, array('id' => 'logo-image-preview-image'))),
				'success' => TRUE
			);
			break;
		case 'structured-data':
		case 'structured_data':
			if (!current_user_can('manage_options', __CLASS__))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}
			
			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}
			
			$data = array(
				'name' => $name,
				'address' => $address,
				'telephone' => $telephone,
				'business_type' => $business_type,
				'price_range' => $price_range,
				'logo' => (preg_match('/.+\.(?:jpe?g|png|svg|gif|webp)$/i', $this->logo_image_url)) ? $this->logo_image_url : NULL
			);
			
			$ret = array(
				'data' => $this->structured_data('json', $data),
				'success' => TRUE
			);
			break;
		case 'google_data_preview':
			if (!current_user_can('manage_options', __CLASS__))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}
			
			$this->set();
			
			if (!$this->google_data_exists())
			{
				$ret = array(
					'data' => NULL,
					'success' => FALSE
				);
				break;
			}
			
			$ret = array(
				'data' => $this->get_google_data('json'),
				'success' => TRUE
			);
			break;
		case 'custom-styles':
		case 'custom_styles':
			if (!current_user_can('manage_options', __CLASS__))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}
			
			if ($custom_styles == get_option($this->prefix . 'custom_styles'))
			{
				$ret = array(
					'success' => TRUE
				);
				break;
			}
			
			update_option($this->prefix . 'custom_styles', $custom_styles, 'yes');

			$fp = FALSE;
			$file = plugin_dir_path(__FILE__) . 'wp/css/custom.css';

			if (!is_file($file))
			{
				if (!is_writable(plugin_dir_path(__FILE__) . 'wp/css/'))
				{
					$ret = array(
						/* translators: %s: file directory, this should remain untouched */
						'message' => sprintf(__('Cannot create a new file in plugin directory: %s', 'opening-hours'), './wp/css/'),
						'success' => FALSE
					);
					break;
				}
				
				$fp = fopen($file, 'w');
				
				if (!$fp || !is_file($file))
				{
					if ($fp)
					{
						fclose($fp);
					}
					
					$ret = array(
						/* translators: %s: file name, this should remain untouched */
						'message' => sprintf(__('Cannot create a new file: %s', 'opening-hours'), './wp/css/custom.css'),
						'success' => FALSE
					);
					break;
				}
			}
			
			if (!is_writable($file))
			{
				$ret = array(
					/* translators: %s: file name, this should remain untouched */
					'message' => sprintf(__('File at: %s is not writable.', 'opening-hours'), './wp/css/custom.css'),
					'success' => FALSE
				);
				break;
			}
			
			if (!$fp)
			{
				$fp = fopen($file, 'w');
			}
				
			if (!$fp)
			{
				$ret = array(
					/* translators: %s: file name, this should remain untouched */
					'message' => sprintf(__('Cannot write new data to file at: %s', 'opening-hours'), './wp/css/custom.css'),
					'success' => FALSE
				);
				break;
			}
			
			if ($custom_styles != NULL && !fwrite($fp, $custom_styles))
			{
				fclose($fp);
				$ret = array(
					'success' => FALSE
				);
				break;
			}
			
			fclose($fp);
			
			$ret = array(
				'success' => TRUE
			);
			break;
		case 'clear':
		case 'cache':
		case 'clear-cache':
		case 'clear_cache':
			if (!current_user_can('manage_options', __CLASS__))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}
			
			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}
			
			$plugin_data = (function_exists('get_file_data')) ? get_file_data(plugin_dir_path(__FILE__) . 'opening-hours.php', array('Version' => 'Version'), FALSE) : array();
			$version = (array_key_exists('Version', $plugin_data)) ? $plugin_data['Version'] : NULL;

			wp_cache_delete('structured_data', $this->class_name);
			wp_cache_delete('google_result', $this->class_name);
			delete_transient($this->prefix . 'offset_changes');
			update_option($this->prefix . 'google_result', NULL, 'no');

			if (version_compare($version, '1.52', '<'))
			{
				wp_clear_scheduled_hook('we_are_open_run');
				wp_schedule_event(time(), 'hourly', 'we_are_open_run');
			}

			$this->google_data = array();
			$this->google_result = array();

			if (!$this->set(NULL, TRUE))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}
			
			$this->section = NULL;
			update_option($this->prefix . 'section', $this->section, 'no');

			$ret = array(
				'success' => TRUE
			);
			break;
		case 'reset':
			if (!current_user_can('activate_plugins', $this->class_name) || !is_array($reset))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, $this->class_name . '_settings_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page', 'opening-hours'),
					'success' => FALSE
				);
				break;
			}

			$successes = array();

			if (in_array('notifications', $reset) && (is_bool($reset['notifications']) && $reset['notifications'] || is_string($reset['notifications']) && preg_match('/^(?:true|[1-9])$/i', $this->sanitize_input($reset['notifications']))))
			{
				$this->notification_reset();
				$successes[] = TRUE;
			}

			if (in_array('opening_hours', $reset) && (is_bool($reset['opening_hours']) && $reset['opening_hours'] || is_string($reset['opening_hours']) && preg_match('/^(?:true|[1-9])$/i', $this->sanitize_input($reset['opening_hours']))))
			{
				$this->regular = array();
				$this->special = array();
				$this->closure = array();
				update_option(__CLASS__ . '_closure', NULL, 'no');
				update_option(__CLASS__ . '_special', NULL, 'no');
				update_option(__CLASS__ . '_regular', $regular, 'no');
				$successes[] = TRUE;
			}

			if (in_array('everything', $reset) && (is_bool($reset['everything']) && $reset['everything'] || is_string($reset['everything']) && preg_match('/^(?:true|[1-9])$/i', $this->sanitize_input($reset['everything']))))
			{
				$successes[] = $this->reset();
			}

			$ret = array(
				'success' => (!(in_array(FALSE, $successes, TRUE)))
			);
			break;
		default:
			break;
		}

		echo json_encode($ret);
		wp_die();
	}
	
	public static function admin_add_action_links($links, $file)
	{
		// Add action link in Dashboard Plugin list
		
		if (!preg_match('#^([^/]+).*$#', $file, $m1) || !preg_match('#^([^/]+).*$#', plugin_basename(__FILE__), $m2) || $m1[1] != $m2[1])
		{
			return $links;
		}
		
		$new_links = array('settings' => '<a href="' . admin_url('options-general.php?page=opening_hours_settings') . '">' . esc_html(__('Settings', 'opening-hours')) . '</a>');
		$links = array_merge($new_links, $links);

		return $links;
	}
	
	public static function admin_add_plugin_meta($links, $file)
	{
		// Add support link in Dashboard Plugin list
		
		if (!preg_match('#^([^/]+).*$#', $file, $m1) || !preg_match('#^([^/]+).*$#', plugin_basename(__FILE__), $m2) || $m1[1] != $m2[1])
		{
			return $links;
		}
		
		$new_links = array(
			'reviews' => '<a href="https://wordpress.org/support/plugin/opening-hours/reviews/#new-post" title="' . esc_attr__('Like our plugin? Please leave a review!', 'opening-hours') . '" style="color: #ffb900; line-height: 90%; font-size: 1.3em; letter-spacing: -0.12em; position: relative; top: 0.08em;">★★★★★</a>',
			'support' => '<a href="https://designextreme.com/wordpress/we-are-open/" target="_blank" title="' . esc_attr__('Support', 'opening-hours') . '">' . esc_html__('Support', 'opening-hours') . '</a>'
		);
		$links = array_merge($links, $new_links);
				
		return $links;
	}

	public function admin_css_load()
	{
		// Load style sheet in the Dashboard
		
		if (!current_user_can('edit_published_posts', $this->class_name))
		{
			return;
		}

		wp_register_style('open_admin_css', plugins_url('opening-hours/admin/css/css.css'));
		wp_enqueue_style('open_admin_css');
		
		if (!$this->admin_current())
		{
			return;
		}
		
		wp_register_style('open_wp_css', plugins_url('opening-hours/wp/css/css.css'));
		wp_enqueue_style('open_wp_css');
		wp_enqueue_media();
	}
	
	public function admin_js_load()
	{
		// Load Javascript in the Dashboard
		
		if (!$this->admin_current() || !current_user_can('edit_published_posts', $this->class_name))
		{
			return;
		}

		wp_register_script('open_admin_js', plugins_url('opening-hours/admin/js/js.js'));
		wp_localize_script('open_admin_js', 'we_are_open_admin_ajax', array('url' => admin_url('admin-ajax.php'), 'action' => 'we_are_open_admin_ajax'));
		wp_register_script('open_wp_js', plugins_url('opening-hours/wp/js/js.js'), array('jquery'));
		wp_enqueue_script('open_admin_js');
		wp_enqueue_script('open_wp_js');
	}
	
	public function wp_css_load()
	{
		// Load style sheet in the front-end
		
		$mode = get_option(__CLASS__ . '_stylesheet', TRUE);
		$compressed = (is_numeric($mode) && $mode == 2 || is_string($mode) && ($mode == 'compress' || $mode == 'compressed' || $mode == 'min'));
		
		wp_register_style('open_wp_css', ($compressed && is_file(plugins_url('opening-hours/wp/css/css.min.css'))) ? plugins_url('opening-hours/wp/css/css.min.css') : plugins_url('opening-hours/wp/css/css.css'));
		wp_enqueue_style('open_wp_css');
		
		if (is_file(plugin_dir_path(__FILE__) . 'wp/css/custom.css') && filesize(plugin_dir_path(__FILE__) . 'wp/css/custom.css') > 20)
		{
			wp_register_style('open_wp_custom_css', plugins_url('opening-hours/wp/css/custom.css'));
			wp_enqueue_style('open_wp_custom_css');
		}
	}
	
	public function wp_js_load()
	{
		// Load Javascript in the front-end
		
		$mode = get_option(__CLASS__ . '_javascript', TRUE);
		$compressed = (is_numeric($mode) && $mode == 2 || is_string($mode) && ($mode == 'compress' || $mode == 'compressed' || $mode == 'min'));

		wp_register_script('open_wp_js', ($compressed && is_file(plugins_url('opening-hours/wp/js/js.min.js'))) ? plugins_url('opening-hours/wp/js/js.min.js') : plugins_url('opening-hours/wp/js/js.js'), array('jquery'));
		wp_localize_script('open_wp_js', 'we_are_open_wp_ajax', array('url' => admin_url('admin-ajax.php'), 'action' => 'we_are_open_wp_ajax'));
		wp_enqueue_script('open_wp_js');
	}
	
	public function get_day_timestamp($day_offset = NULL, $month_offset = NULL, $year_offset = NULL)
	{
		// Get the timestamp from the start of a local day relative to today
		
		if (!is_numeric($day_offset))
		{
			$day_offset = 0;
		}
		
		if (!is_numeric($month_offset))
		{
			$month_offset = 0;
		}
		
		if (!is_numeric($year_offset))
		{
			$year_offset = 0;
		}
		
		if (!is_numeric($this->current_timestamp))
		{
			$this->current_timestamp = time();
		}

		if (!is_numeric($this->offset))
		{
			$this->offset = round(floatval(get_option('gmt_offset')) * HOUR_IN_SECONDS);
		}
				
		if (!is_array($this->offset_changes))
		{
			$this->offset_changes = get_transient($this->prefix . 'offset_changes');
		}
		
		if (!is_array($this->offset_changes))
		{
			$timezone = FALSE;
			
			if (class_exists('DateTimeZone') && get_option('timezone_string') != NULL)
			{
				$timezone = new DateTimeZone(get_option('timezone_string'));
			}
			
			if (is_object($timezone))
			{
				$this->offset_changes = $timezone->getTransitions(mktime(0, 0, 0, gmdate("m")-6, 1, gmdate("Y")), mktime(0, 0, 0, 12, 31, gmdate("Y")+2));
				set_transient($this->prefix . 'offset_changes', $this->offset_changes, MONTH_IN_SECONDS);
			}
		}
		
		$offset = $this->offset;
		$timestamp = mktime(0, 0, $this->offset * -1, wp_date("m", $this->current_timestamp) + $month_offset, wp_date("j", $this->current_timestamp) + $day_offset, wp_date("Y", $this->current_timestamp) + $year_offset);

		if (is_array($this->offset_changes))
		{
			foreach ($this->offset_changes as $i => $a)
			{
				if ($a['ts'] > $timestamp || $a['ts'] <= $timestamp && array_key_exists($i + 1, $this->offset_changes) && isset($this->offset_changes[$i + 1]['ts']) && $this->offset_changes[$i + 1]['ts'] < $timestamp)
				{
					continue;
				}
				
				$offset = $a['offset'];
				$timestamp = mktime(0, 0, $offset * -1, wp_date("m", $this->current_timestamp) + $month_offset, wp_date("j", $this->current_timestamp) + $day_offset, wp_date("Y", $this->current_timestamp) + $year_offset);
				break;
			}
		}
		
		return mktime(0, 0, -1 * $offset, wp_date("m", $timestamp), wp_date("j", $timestamp), wp_date("Y", $timestamp));
	}
	
	public function get_google_data($format = 'array', $force = FALSE)
	{
		// Return data from either Google Places or option value
		
		$ret = ($format == 'array') ? array() : '';
		
		if (!$this->dashboard)
		{
			return $ret;
		}
	
		$this->api_key = ($this->api_key != NULL) ? $this->api_key : get_option($this->prefix . 'api_key');
		$this->place_id = ($this->place_id != NULL) ? $this->place_id : get_option($this->prefix . 'place_id');
				
		return $this->retrieve_google_data($format, $force);
	}
	
	private function set_google_data()
	{
		// Set opening hours using data retrieved from Google My Business
		
		$this->set();

		$google_sync = (defined('DOING_CRON') && DOING_CRON) ? ((is_numeric(get_option($this->prefix . 'google_sync', FALSE))) ? intval(get_option($this->prefix . 'google_sync')) : 0) : ((isset($this->google_data['currentOpeningHours']) && is_array($this->google_data['currentOpeningHours']) && isset($this->google_data['currentOpeningHours']['periods']) && is_array($this->google_data['currentOpeningHours']['periods'])) ? 3 : 1);
		
		if ($google_sync < 1)
		{
			return FALSE;
		}

		$periods = array(
			'regular' => (isset($this->google_data['regularOpeningHours']) && is_array($this->google_data['regularOpeningHours']) && isset($this->google_data['regularOpeningHours']['periods']) && is_array($this->google_data['regularOpeningHours']['periods'])) ? $this->google_data['regularOpeningHours']['periods'] : ((isset($this->google_data['result']['opening_hours']) && is_array($this->google_data['result']['opening_hours']) && isset($this->google_data['result']['opening_hours']['periods']) && is_array($this->google_data['result']['opening_hours']['periods'])) ? $this->google_data['result']['opening_hours']['periods'] : NULL),
			'current' => ($google_sync >= 2 && isset($this->google_data['currentOpeningHours']) && is_array($this->google_data['currentOpeningHours']) && isset($this->google_data['currentOpeningHours']['periods']) && is_array($this->google_data['currentOpeningHours']['periods'])) ? $this->google_data['currentOpeningHours']['periods'] : NULL,
			'special' => NULL
		);

		if (!is_array($periods['regular']))
		{
			return FALSE;
		}

		if (is_array($periods['current']))
		{
			$periods['special'] = (isset($this->google_data['currentOpeningHours']['specialDays']) && is_array($this->google_data['currentOpeningHours']['specialDays']) && isset($this->google_data['currentOpeningHours']['specialDays']) && is_array($this->google_data['currentOpeningHours']['specialDays'])) ? $this->google_data['currentOpeningHours']['specialDays'] : NULL;
		}

		$opening_hours = array(
			'regular' => array(),
			'special' => array()
		);
		$open_always = (count($periods['regular']) == 1 && isset($periods['regular'][0]['periods']['open']) && !isset($periods['regular'][0]['periods']['close']));
		$unix_days = (is_array($periods['special']) && !empty($periods['special'])) ? round(mktime(0, 0, $this->offset * -1, wp_date("m"), wp_date("j"), wp_date("Y")) / DAY_IN_SECONDS) : NULL;

		if ((!$open_always || $open_always && (is_array($periods['special']) && !empty($periods['special']))) && !empty($periods['regular']))
		{
			foreach ($periods['regular'] as $a)
			{
				if (!array_key_exists('open', $a) || !array_key_exists('day', $a['open']))
				{
					continue;
				}

				$weekday = intval($a['open']['day']);
				
				if (!array_key_exists($weekday, $opening_hours['regular']))
				{
					if (!array_key_exists('close', $a) || !array_key_exists('day', $a['close']) || (!array_key_exists('hour', $a['close']) || isset($a['close']['hour']) && !is_numeric($a['close']['hour']) || is_numeric($a['close']['hour']) && $a['close']['hour'] < 0 || $a['close']['hour'] > 23) && (!array_key_exists('time', $a['close']) || isset($a['close']['time']) && !preg_match('/^(\d{2})[^\d]*(\d{2})$/', $a['close']['time'])))
					{
						$opening_hours['regular'][$weekday] = array(
							'closed' => FALSE,
							'hours_24' => TRUE,
							'hours' => NULL
						);

						continue;
					}
					
					$opening_hours['regular'][$weekday] = array(
						'closed' => FALSE,
						'hours_24' => FALSE,
						'hours' => array()
					);
				}

				if (isset($a['close']['hour']))
				{
					$opening_hours['regular'][$weekday]['hours'][] = array(str_pad(strval($a['open']['hour']), 2, '0', STR_PAD_LEFT) . ':' . str_pad(strval($a['open']['minute']), 2, '0', STR_PAD_LEFT), str_pad(strval($a['close']['hour']), 2, '0', STR_PAD_LEFT) . ':' . str_pad(strval($a['close']['minute']), 2, '0', STR_PAD_LEFT));

					continue;
				}
				
				$opening_hours['regular'][$weekday]['hours'][] = array(((preg_match('/^(\d{2})[^\d]*(\d{2})$/', $a['open']['time'], $m)) ? $m[1] . ':'. $m[2] : NULL), ((preg_match('/^(\d{2})[^\d]*(\d{2})$/', $a['close']['time'], $m)) ? $m[1] . ':'. $m[2] : NULL));
			}

			if ($google_sync > 1 && is_array($periods['current']) && is_array($periods['special']) && !empty($periods['special']))
			{
				foreach ($periods['special'] as $s)
				{
					if (!isset($s['date']['year']) || !isset($s['date']['month']) || !isset($s['date']['day']) || !is_numeric($s['date']['year']) || !is_numeric($s['date']['month']) || !is_numeric($s['date']['day']) || $s['date']['year'] < 2024 || $s['date']['month'] < 1 || $s['date']['month'] > 12 || $s['date']['day'] < 1 || $s['date']['day'] > 31)
					{
						continue;
					}
					
					$timestamp = $this->get_day_timestamp(round(mktime(0, 0, $this->offset * -1, $s['date']['month'], $s['date']['day'], $s['date']['year']) / DAY_IN_SECONDS) - $unix_days, 0, 0);
					$date = implode('-', array($s['date']['year'], str_pad($s['date']['month'], 2, '0', STR_PAD_LEFT), str_pad($s['date']['day'], 2, '0', STR_PAD_LEFT)));

					foreach ($periods['current'] as $c)
					{
						if (!isset($c['open']['date']['year']) || !isset($c['open']['date']['month']) || !isset($c['open']['date']['day']) || !is_numeric($c['open']['date']['year']) || !is_numeric($c['open']['date']['month']) || !is_numeric($c['open']['date']['day']) || $c['open']['date']['year'] != $s['date']['year'] || $c['open']['date']['month'] != $s['date']['month'] || $c['open']['date']['day'] != $s['date']['day'])
						{
							continue;
						}

						if (!array_key_exists($timestamp, $opening_hours['special']))
						{
							$opening_hours['special'][$timestamp] = array(
								'timestamp' => $timestamp,
								'date' => $date,
								'closed' => FALSE,
								'hours_24' => FALSE,
								'hours' => array(),
								'label' => (is_array($this->special) && isset($this->special[$timestamp]) && isset($this->special[$timestamp]['label'])) ? $this->special[$timestamp]['label'] : NULL,
								'note' => (is_array($this->special) && isset($this->special[$timestamp]) && isset($this->special[$timestamp]['note'])) ? $this->special[$timestamp]['note'] : NULL
							);
						}

						if (!array_key_exists('hour', $c['close']) || isset($c['close']['hour']) && !is_numeric($c['close']['hour']) || is_numeric($c['close']['hour']) && $c['close']['hour'] < 0 || $c['close']['hour'] > 23)
						{
							$opening_hours['special'][$timestamp]['closed'] = !$open_always;
							$opening_hours['special'][$timestamp]['hours_24'] = $open_always;
							$opening_hours['special'][$timestamp]['hours'] = NULL;
							continue(2);
						}

						$opening_hours['special'][$timestamp]['hours'][] = array(str_pad(strval($c['open']['hour']), 2, '0', STR_PAD_LEFT) . ':' . str_pad(strval($c['open']['minute']), 2, '0', STR_PAD_LEFT), str_pad(strval($c['close']['hour']), 2, '0', STR_PAD_LEFT) . ':' . str_pad(strval($c['close']['minute']), 2, '0', STR_PAD_LEFT));
						continue(2);
					}

					$opening_hours['special'][$timestamp] = array(
						'timestamp' => $timestamp,
						'date' => $date,
						'closed' => !$open_always,
						'hours_24' => $open_always,
						'hours' => NULL,
						'label' => (is_array($this->special) && isset($this->special[$timestamp]) && isset($this->special[$timestamp]['label'])) ? $this->special[$timestamp]['label'] : NULL,
						'note' => (is_array($this->special) && isset($this->special[$timestamp]) && isset($this->special[$timestamp]['note'])) ? $this->special[$timestamp]['note'] : NULL
					);
				}
			}
		}
		
		if ($google_sync % 2 == 1)
		{
			foreach (array_keys($this->days) as $weekday)
			{
				if (!array_key_exists($weekday, $opening_hours['regular']))
				{
					$opening_hours['regular'][$weekday] = array(
						'closed' => !$open_always,
						'hours_24' => $open_always,
						'hours' => NULL
					);
				}
			}
		}
		
		ksort($opening_hours['regular']);

		if (is_numeric($google_sync) && $google_sync == 2)
		{
			$opening_hours['regular'] = $this->regular;
		}

		if ($google_sync > 1 && is_array($opening_hours['special']) && !empty($opening_hours['special']))
		{
			if (is_array($this->special))
			{
				foreach ($this->special as $timestamp => $a)
				{
					if (array_key_exists($timestamp, $opening_hours['special']))
					{
						continue;
					}
	
					$opening_hours['special'][$timestamp] = $this->special[$timestamp];
					$opening_hours['special'][$timestamp]['timestamp'] = $timestamp;
				}
			}

			ksort($opening_hours['special']);

			return $this->update($opening_hours['regular'], $opening_hours['special']);
		}

		return $this->update($opening_hours['regular']);
	}
	
	private function get_closure()
	{
		// Get relevant details of closure for Dashboard
		
		if (empty($this->closure) || !isset($this->closure['start']) || !isset($this->closure['end']) || isset($this->closure['start']) && !is_numeric($this->closure['start']) || isset($this->closure['end']) && !is_numeric($this->closure['end']))
		{
			return array(NULL, NULL, NULL, NULL, NULL, NULL);
		}
		
		$closure_date_start = wp_date("Y-m-d", $this->closure['start_display']);
		$closure_date_end = wp_date("Y-m-d", $this->closure['end_display']);
		$closure_count = (isset($this->closure['count']) && is_numeric($this->closure['count'])) ? $this->closure['count'] : NULL;
		$closure_modified = (isset($this->closure['modified']) && is_numeric($this->closure['modified'])) ? $this->closure['modified'] : NULL;
		
		return array($this->closure['start'], $this->closure['end'], $closure_date_start, $closure_date_end, $closure_count, $closure_modified);
	}

	private function update($regular = NULL, $special = NULL, $closure = NULL)
	{
		// Update opening hours from form data array
		
		$this->data = array();
		$this->consolidation = array();
		
		wp_cache_delete('data', $this->class_name);
		wp_cache_delete('special', $this->class_name);
		wp_cache_delete('closure', $this->class_name);
		wp_cache_delete('consolidation', $this->class_name);
		
		if (is_array($regular))
		{
			if (!is_array($this->regular))
			{
				$this->regular = array();
			}
			
			wp_cache_delete('regular', $this->class_name);
			
			foreach (array_keys($this->days) as $weekday)
			{
				$a = (array_key_exists($weekday, $regular)) ? $regular[$weekday] : array();
				$modified = (!empty($a) && array_key_exists($weekday, $this->regular) && array_key_exists('modified', $this->regular[$weekday])) ? $this->regular[$weekday]['modified'] : NULL;
				$checksum = ($modified != NULL) ? md5(serialize(array($this->regular[$weekday]['closed'], $this->regular[$weekday]['hours_24'], $this->regular[$weekday]['hours']))) : NULL;
				
				if (!array_key_exists($weekday, $regular) || array_key_exists($weekday, $regular)
					&& (is_bool($a['closed']) && $a['closed']
					|| is_string($a['closed']) && $a['closed'] == 'true'
					|| !isset($a['hours'])
					|| isset($a['hours']) && (empty($a['hours'])
					|| !isset($a['hours'][0][0])
					|| isset($a['hours'][0][0]) && !preg_match('/^\d{2}:\d{2}$/', $a['hours'][0][0])
					|| !isset($a['hours'][0][1])
					|| isset($a['hours'][0][1]) && !preg_match('/^\d{2}:\d{2}$/', $a['hours'][0][1])
					|| (isset($a['hours_24']) && (is_bool($a['hours_24']) && $a['hours_24'] || is_string($a['hours_24']) && $a['hours_24'] == 'true'))
					|| isset($a['hours'][0][0]) && isset($a['hours'][0][1]) && preg_match('/^00:00$/', $a['hours'][0][0]) && preg_match('/^(?:00:00|23:5[5-9])$/', $a['hours'][0][1]))))
				{
					$hours_24 = (isset($a['hours_24']) && (is_bool($a['hours_24']) && $a['hours_24'] || is_string($a['hours_24']) && $a['hours_24'] == 'true'));
					
					$this->regular[$weekday] = array(
						'closed' => !$hours_24,
						'hours' => array(),
						'hours_24' => $hours_24
					);
					$this->regular[$weekday]['modified'] = ($checksum == NULL || $checksum != md5(serialize(array($this->regular[$weekday]['closed'], $this->regular[$weekday]['hours_24'], $this->regular[$weekday]['hours'])))) ? time() : $modified;
					
					continue;
				}
				
				$this->regular[$weekday] = array(
					'closed' => FALSE,
					'hours' => $this->hours_filter($a['hours']),
					'hours_24' => FALSE
				);
				$this->regular[$weekday]['modified'] = ($checksum == NULL || $checksum != md5(serialize(array($this->regular[$weekday]['closed'], $this->regular[$weekday]['hours_24'], $this->regular[$weekday]['hours'])))) ? time() : $modified;
			}
			
			ksort($this->regular);
			update_option($this->prefix . 'regular', $this->regular, 'yes');
			wp_cache_add('regular', $this->regular, $this->class_name, HOUR_IN_SECONDS);
		}
		
		if (is_array($closure) && count($closure) != 2)
		{
			$this->closure = array();
			update_option($this->prefix . 'closure', $this->closure, 'yes');
			wp_cache_add('closure', $this->closure, $this->class_name, HOUR_IN_SECONDS);
		}
	
		if (!is_array($special) || is_array($special) && empty($special))
		{
			if (is_array($special))
			{
				$this->special = array();
				update_option($this->prefix . 'special', $this->special, 'yes');
				wp_cache_add('special', $this->special, $this->class_name, HOUR_IN_SECONDS);
			}
			
			$this->set(NULL);
		}
		else
		{
			if (!is_array($this->special))
			{
				$this->special = array();
			}
			
			$set_dates = array();
			$special_cut_off = intval((is_numeric(get_option($this->prefix . 'special_cut_off', NULL)) && get_option($this->prefix . 'special_cut_off') >= 1) ? get_option($this->prefix . 'special_cut_off') : 14);
			$current_date = $this->get_day_timestamp();
			$remove_date = $this->get_day_timestamp($special_cut_off * -1);

			foreach ($special as $a)
			{
				$timestamp = (isset($a['timestamp']) && is_numeric($a['timestamp']) && $a['timestamp'] > 1714521600) ? intval($a['timestamp']) : ((isset($a['date']) && is_numeric($a['date']) && $a['date'] > 1714521600) ? intval($a['date']) : NULL);
				$date = ($timestamp != NULL) ? wp_date("Y-m-d", $timestamp) : ((isset($a['date']) && is_string($a['date']) && preg_match('#^\d{4}[/-]\d{1,2}[/-]\d{1,2}$#', $a['date'])) ? $a['date'] : NULL);

				if ($timestamp == NULL && $date == NULL)
				{
					continue;
				}
				
				if ($timestamp == NULL)
				{
					$day_offset = round((strtotime($date) - $this->offset - $this->today_timestamp) / DAY_IN_SECONDS);
					$timestamp = $this->get_day_timestamp($day_offset);
				}
				
				$label = (isset($a['label']) && (is_string($a['label']) && $a['label'] != NULL)) ? $a['label'] : NULL;
				$note = (isset($a['note']) && (is_string($a['note']) && $a['note'] != NULL)) ? $a['note'] : NULL;
				
				if ($current_date - DAY_IN_SECONDS > $timestamp)
				{
					if ($timestamp > $remove_date)
					{
						$set_dates[] = $timestamp;
					}

					if (array_key_exists($timestamp, $this->special))
					{
						if (isset($this->special[$timestamp]['label']) && $this->special[$timestamp]['label'] != $label || !isset($this->special[$timestamp]['note']) && $note != NULL || isset($this->special[$timestamp]['note']) && $this->special[$timestamp]['note'] != $note)
						{
							$this->special[$timestamp]['label'] = (isset($a['label']) && (is_string($a['label']) && $a['label'] != NULL)) ? $a['label'] : NULL;
							$this->special[$timestamp]['note'] = (isset($a['note']) && (is_string($a['note']) && $a['note'] != NULL)) ? $a['note'] : NULL;
							$this->special[$timestamp]['modified'] = time();
						}
					}

					continue;
				}
				
				$a['date'] = $timestamp;
				$modified = (array_key_exists($timestamp, $this->special) && array_key_exists('modified', $this->special[$timestamp])) ? $this->special[$timestamp]['modified'] : NULL;
				$checksum = ($modified != NULL) ? md5(serialize(array($this->special[$timestamp]['closed'], $this->special[$timestamp]['hours_24'], $this->special[$timestamp]['hours'], (isset($this->special[$timestamp]['label'])) ? $this->special[$timestamp]['label'] : NULL, (isset($this->special[$timestamp]['note'])) ? $this->special[$timestamp]['note'] : NULL))) : NULL;
				$set_dates[] = $timestamp;
	
				if (is_bool($a['closed']) && $a['closed']
					|| is_string($a['closed']) && mb_strtolower($a['closed']) == 'true'
					|| !isset($a['hours'])
					|| isset($a['hours']) && (empty($a['hours'])
					|| !isset($a['hours'][0][0])
					|| isset($a['hours'][0][0]) && !preg_match('/^\d{2}:\d{2}$/', $a['hours'][0][0])
					|| !isset($a['hours'][0][1])
					|| isset($a['hours'][0][1]) && !preg_match('/^\d{2}:\d{2}$/', $a['hours'][0][1]))
					|| (isset($a['hours_24']) && (is_bool($a['hours_24']) && $a['hours_24'] || is_string($a['hours_24']) && mb_strtolower($a['hours_24']) == 'true'))
					|| isset($a['hours'][0][0]) && isset($a['hours'][0][1]) && preg_match('/^00:00$/', $a['hours'][0][0]) && preg_match('/^(?:00:00|23:5[5-9])$/', $a['hours'][0][1]))
				{
					$hours_24 = (isset($a['hours_24']) && (is_bool($a['hours_24']) && $a['hours_24'] || is_string($a['hours_24']) && mb_strtolower($a['hours_24']) == 'true'));
					$this->special[$timestamp] = array(
						'closed' => !$hours_24,
						'date' => $timestamp,
						'label' => $label,
						'note' => $note,
						'hours' => array(),
						'hours_24' => $hours_24
					);
					$this->special[$timestamp]['modified'] = ($modified == NULL || $checksum == NULL || $checksum != md5(serialize(array($this->special[$timestamp]['closed'], $this->special[$timestamp]['hours_24'], $this->special[$timestamp]['hours'], $label, $note)))) ? time() : $modified;

					continue;
				}
				
				$this->special[$timestamp] = array(
					'closed' => FALSE,
					'date' => $timestamp,
					'label' => $label,
					'note' => $note,
					'hours' => $this->hours_filter($a['hours']),
					'hours_24' => FALSE
				);
				$this->special[$timestamp]['modified'] = ($modified == NULL || $checksum == NULL || $checksum != md5(serialize(array($this->special[$timestamp]['closed'], $this->special[$timestamp]['hours_24'], $this->special[$timestamp]['hours'], $label, $note)))) ? time() : $modified;
			}
			
			foreach (array_keys($this->special) as $timestamp)
			{
				if (!in_array($timestamp, $set_dates))
				{
					unset($this->special[$timestamp]);
				}
			}
			
			ksort($this->special);
			update_option($this->prefix . 'special', $this->special, 'yes');
			wp_cache_add('special', $this->special, $this->class_name, HOUR_IN_SECONDS);
		}
		
		if (is_array($closure) && count($closure) == 2)
		{
			if (!is_string($closure[0]) || !is_string($closure[1]) || !preg_match('#^\d{4}[/-]\d{1,2}[/-]\d{1,2}$#', $closure[0]) || !preg_match('#^\d{4}[/-]\d{1,2}[/-]\d{1,2}$#', $closure[1]))
			{
				$this->closure = array();
			}
			else
			{
				$closure_timestrings = array(
					strtotime($closure[0]),
					strtotime($closure[1])
				);
				sort($closure_timestrings);
				$day_start_offset = round(($closure_timestrings[0] - $this->offset - $this->today_timestamp)/DAY_IN_SECONDS);
				$day_end_display_offset = round(($closure_timestrings[1] - $this->offset - $this->today_timestamp)/DAY_IN_SECONDS);
				$day_end_offset = $day_end_display_offset + 1;
				$closure_date_start = $closure_date_start_display = $this->get_day_timestamp($day_start_offset);
				$closure_date_end = $this->get_day_timestamp($day_end_offset);
				$closure_date_end_display = $this->get_day_timestamp($day_end_display_offset);
				$this->closure = array(
					'start' => $closure_date_start,
					'start_display' => $closure_date_start,
					'end' => $closure_date_end,
					'end_display' => $closure_date_end_display,
					'count' => round($closure_date_end/DAY_IN_SECONDS) - round($closure_date_start/DAY_IN_SECONDS),
					'modified' => (isset($this->closure['modified']) && $this->closure['modified'] != NULL && $this->closure['start'] == $closure_date_start && $this->closure['end'] == $closure_date_end) ? $this->closure['modified'] : time()
				);
			}
			
			update_option($this->prefix . 'closure', $this->closure, 'yes');
			wp_cache_add('closure', $this->closure, $this->class_name, HOUR_IN_SECONDS);
		}
		
		$this->set(NULL);

		return TRUE;
	}
	
	private function delete($special = NULL)
	{
		// Update (delete) special opening hours from form data array
		
		if ($special == NULL || is_array($special) && empty($special))
		{
			return TRUE;
		}
		
		return $this->update(NULL, $special);
		
	}
	
	public function retrieve_google_data($format = 'array', $force = FALSE)
	{
		// Collect data from Google Places as JSON string
		
		$ret = ($format == 'array') ? array() : '';
		
		if ($this->request_count > 2)
		{
			return $ret;
		}

		$google_sync = get_option($this->prefix . 'google_sync', FALSE);
		$places_api = (get_option($this->prefix . 'google_places_api', 0) == 1 || is_bool($google_sync) && $google_sync || is_numeric($google_sync) && $google_sync >= 2) ? 1 : 0;
		$fields = ($places_api == 1) ? array('regularOpeningHours', 'currentOpeningHours', 'displayName', 'googleMapsUri', 'businessStatus') : array('opening_hours', 'name', 'url', 'business_status');
		$language = get_option($this->prefix . 'language');
		$recheck = FALSE;
		$retrieval = NULL;
		$last_retrieval = NULL;
		$data_array = array();
		$data_string = '';

		if ($this->place_id == NULL || $this->api_key == NULL)
		{
			return $ret;
		}
		
		if ($force)
		{
			$retrieval = get_option($this->prefix . 'retrieval');
			
			if (is_array($retrieval) && isset($retrieval['requests']) && is_array($retrieval['requests']) && count($retrieval) > 1)
			{
				$last_retrieval = end($retrieval['requests']);
				$force = (!isset($last_retrieval['place_id']) || isset($last_retrieval['place_id']) && $last_retrieval['place_id'] != $this->place_id || (!isset($last_retrieval['time']) || isset($last_retrieval['time']) && (time() - $last_retrieval['time']) > 10));
			}
		}
		
		if (!$force && (!is_array($this->google_result) || is_array($this->google_result) && empty($this->google_result)))
		{
			$this->google_result = get_option($this->prefix . 'google_result', array());
		}
		
		if (!$force && is_array($this->google_result) && !empty($this->google_result))
		{
			$data_string = json_encode($this->google_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			$data_array = $this->google_result;
		}
		
		if (!$force && !is_array($retrieval) && (!is_array($this->google_result) || is_array($this->google_result) && (empty($this->google_result) || !empty($this->google_result) && (!isset($this->google_result['status']) && !isset($this->google_result['displayName']) || $this->settings_updated && (isset($this->google_data['error']) && is_array($this->google_data['error']) || isset($this->google_result['status']) && !preg_match('/^OK$/i', $this->google_result['status']))))))
		{
			$retrieval = get_option($this->prefix . 'retrieval');
			
			if ($this->settings_updated && (!is_array($retrieval) || !isset($retrieval['requests']) || isset($retrieval['requests']) && count($retrieval['requests']) < 5))
			{
				$recheck = TRUE;
			}
			elseif (is_array($retrieval) && isset($retrieval['requests']) && is_array($retrieval['requests']))
			{
				$last_retrieval = end($retrieval['requests']);
				$recheck = ((!isset($last_retrieval['place_id']) || isset($last_retrieval['place_id']) && $last_retrieval['place_id'] == $this->place_id) && (!isset($last_retrieval['time']) || isset($last_retrieval['time']) && (time() - $last_retrieval['time']) > 10));
			}
		}
		
		if ($recheck)
		{
			$this->request_count++;
			
			if (!$force && $format != 'array')
			{
				return $ret;
			}			
		}
		
		if ($force || $recheck)
		{
			$url = 'https://'
				. (($places_api == 1) ? 'places.googleapis.com/v1/places/' . rawurlencode($this->place_id) . '?' : 'maps.googleapis.com/maps/api/place/details/json?placeid=' . rawurlencode($this->place_id) . '&')
				. 'fields=' . rawurlencode(implode(',', $fields))
				. '&key=' . rawurlencode($this->api_key)
				. (($language != NULL) ? '&language' . (($places_api == 1) ? 'Code' : '') . '=' . rawurlencode($language) : '');

			if (function_exists('wp_remote_get') && function_exists('wp_remote_retrieve_body'))
			{
				$data_string = wp_remote_retrieve_body(wp_remote_get($url));
			}
			
			if (!is_string($data_string))
			{
				if ($ret == 'html')
				{
					$ret = '<p class="error">'
					/* translators: %s: URL of remote data, this should remain untouched */
					. sprintf(__('Error: Unable to collect remote data from URL: <em>%s</em>', 'opening-hours'), $url) . '</p>';
				}

				return $ret;
			}
			
			$data_array = ($data_string != NULL) ? json_decode($data_string, TRUE) : array();
			$this->google_result = $data_array;
			$retrieval = ($retrieval == NULL) ? get_option($this->prefix . 'retrieval') : $retrieval;
			
			if (!is_array($retrieval))
			{
				$retrieval = array(
					'count' => 0,
					'initial' => time(),
					'requests' => array()
				);
			}
			elseif (!is_array($retrieval['requests']))
			{
				$retrieval['requests'] = array();
			}
			elseif (count($retrieval['requests']) > 10)
			{
				$retrieval['requests'] = array_slice($retrieval['requests'], -10);
			}
			
			$this->request_count++;
			$retrieval['requests'][] = array(
				'time' => time(),
				'place_id' => $this->place_id,
				'name' => (isset($this->google_result['displayName']) && isset($this->google_result['displayName']['text'])) ? $this->google_result['displayName']['text'] : ((isset($this->google_result['result']['name'])) ? $this->google_result['result']['name'] : NULL),
				'status' => (isset($this->google_data['error']) && is_array($this->google_data['error']) && isset($this->google_data['error']['status']) && is_string($this->google_data['error']['status'])) ? $this->google_data['error']['status'] : ((isset($this->google_result['status'])) ? $this->google_result['status'] : NULL),
				'opening_hours' => (isset($this->google_data['regularOpeningHours']) && is_array($this->google_data['regularOpeningHours'])) ? count($this->google_data['regularOpeningHours']) : ((isset($this->google_result['result']['opening_hours']) && is_array($this->google_result['result']['opening_hours'])) ? count($this->google_result['result']['opening_hours']) : NULL),
				'count' => $this->request_count
			);
			$retrieval['count'] = intval($retrieval['count']) + 1;

			update_option($this->prefix . 'retrieval', $retrieval, 'no');
		}
		
		switch ($format)
		{
		case 'html':
			if ($this->place_id == NULL && $this->api_key == NULL)
			{
				$ret = '<p class="error">' . __('Error: Place ID and Google API Key are required.', 'opening-hours') . '</p>';
			}
			elseif ($this->place_id == NULL)
			{
				$ret = '<p class="error">' . __('Error: Place ID is required.', 'opening-hours') . '</p>';
			}
			elseif ($this->api_key == NULL)
			{
				$ret = '<p class="error">' . __('Error: Google API Key is required.', 'opening-hours') . '</p>';
			}
			
			if ($ret != '')
			{
				break;
			}
			
			$ret = '	<pre id="open-google-data">' . esc_html($data_string) . '</pre>
';
			break;
		case 'array':
			$ret = $data_array;
			break;
		case 'json':
		default:
			if ($this->place_id == NULL && $this->api_key == NULL)
			{
				$ret = json_encode(array(
					'error' => __('Place ID and Google API Key are required.', 'opening-hours')
				));
			}
			elseif ($this->place_id == NULL)
			{
				$ret = json_encode(array(
					'error' => __('Error: Place ID is required.', 'opening-hours')
				));
			}
			elseif ($this->api_key == NULL)
			{
				$ret = json_encode(array(
					'error' => __('Error: Google API Key is required.', 'opening-hours')
				));
			}
			
			if ($ret != '')
			{
				return $ret;
			}
			
			$ret = $data_string;
			break;
		}
		
		return $ret;
	}
	
	private function hours_filter($a, $return = NULL)
	{
		// Checks and filtering of groups of start and end hours for a day
		
		$strings = array();
		$seconds = array();
		$l = 0;
		$next_day = 0;
		
		foreach (array_values($a) as $h)
		{
			if (!is_array($h) || is_array($h) && count($h) != 2)
			{
				continue;
			}
			
			$h = array_values($h);
			
			if (!preg_match('/^(\d{2}):(\d{2})$/', $h[0], $m) || !preg_match('/^(\d{2}):(\d{2})$/', $h[1], $n))
			{
				continue;
			}
			
			$strings[] = $h[0];
			$strings[] = $h[1];
			$seconds[] = (intval($m[1]) * HOUR_IN_SECONDS) + (intval($m[2]) * MINUTE_IN_SECONDS);
			$seconds[] = (intval($n[1]) * HOUR_IN_SECONDS) + (intval($n[2]) * MINUTE_IN_SECONDS);
		}
		
		$a = array();
		
		foreach (array_keys($seconds) as $i)
		{
			if ($i%2 == 1)
			{
				continue;
			}
			
			$j = $i + 1;
			$k = (array_key_exists(($j + 1), $seconds)) ? $j + 1 : NULL;
			
			if ($seconds[$i] < $seconds[$j])
			{
				if ($k == NULL)
				{
					break;
				}
				
				if ($seconds[$j] < $seconds[$k])
				{
					continue;
				}
				
				$seconds[$k] += DAY_IN_SECONDS;
				
				break;
			}
			
			$seconds[$j] += DAY_IN_SECONDS;
			
			if ($k == NULL)
			{
				break;
			}
			
			if ($seconds[$j] < $seconds[$k])
			{
				continue;
			}
			
			$seconds[$k] += DAY_IN_SECONDS;
			
			break;
		}
		
		$next_day = FALSE;
		
		foreach ($seconds as $i => $x)
		{
			$y = $seconds[($i + 1)];
			
			if ($x >= 129600 || $y >= 129600)
			{
				break;
			}
			
			$j = $i + 1;
			$z = array_key_exists(($j + 1), $seconds) ? $seconds[($j + 1)] : NULL;
			$k = ($z != NULL) ? $j + 1 : NULL;
			
			if ($i%2 == 1)
			{
				continue;
			}
			
			if ($next_day && $x > $y)
			{
				continue;
			}
			
			if ($next_day && $z != NULL && $y > $z)
			{
				$a[$l] = array($strings[$i], $strings[$k]);
				$l++;
				continue;
			}
			
			$a[$l] = array($strings[$i], $strings[$j]);
			
			if (!$next_day && ($x > $y || $z != NULL && $y > $z))
			{
				$next_day = TRUE;
			}
			
			$l++;
			
			if ($z == NULL)
			{
				break;
			}
		}
		
		if (is_string($return))
		{
			if (count($seconds) != count($strings))
			{
				$seconds = array_slice($seconds, 0, count($strings));
			}
			
			$seconds = array_map( function($v) { return $v; }, $seconds);
			
			switch ($return)
			{
			case 'days':
			case 'day':
				return array_map( function($v) { return $v / DAY_IN_SECONDS; }, $seconds);
			case 'hours':
			case 'hour':
				return array_map( function($v) { return $v / HOUR_IN_SECONDS; }, $seconds);
			case 'minutes':
			case 'minute':
				return array_map( function($v) { return $v / MINUTE_IN_SECONDS; }, $seconds);
			case 'seconds':
			case 'second':
				return $seconds;
			}
		}
		
		return $a;
	}
	
	private function set_localized_dates()
	{
		// Set days and dates in the local language
		
		if (is_array($this->day_formats) && !empty($this->day_formats))
		{
			return TRUE;
		}
		
		for ($i = 0; $i < 7; $i++)
		{
			$this->days[$i] = $this->sentence_case(wp_date("l", 1590883200 + $i * DAY_IN_SECONDS + ($this->offset * -1) + HOUR_IN_SECONDS));
		}
		
		$this->day_formats = array(
			'full' => array($this->sentence_case(wp_date("l", $this->next_week_start_timestamp)), 'l', NULL),
			'short' => array($this->sentence_case(wp_date("D", $this->next_week_start_timestamp)), 'D', NULL),
			'initial' => array(mb_substr(ucfirst(wp_date("D", $this->next_week_start_timestamp)), 0, 1), 'D', 1),
			'short_date_short_month' => array($this->sentence_case(wp_date("D jS M", $this->next_week_start_timestamp)), 'D jS M', NULL),
			'short_date_short_month_comma' => array($this->sentence_case(wp_date("D, jS M", $this->next_week_start_timestamp)), 'D, jS M', NULL),
			'short_date_short_month_first' => array($this->sentence_case(wp_date("D M jS", $this->next_week_start_timestamp)), 'D M jS', NULL),
			'short_date_short_month_first_comma' => array($this->sentence_case(wp_date("D, M jS", $this->next_week_start_timestamp)), 'D, M jS', NULL),
			'short_date_short_month_nos' => array($this->sentence_case(wp_date("D j M", $this->next_week_start_timestamp)), 'D j M', NULL),
			'short_date_short_month_date_dot_nos' => array($this->sentence_case(wp_date("D d. M", $this->next_week_start_timestamp)), 'D d. M', NULL),
			'short_date_short_month_comma_nos' => array($this->sentence_case(wp_date("D, j M", $this->next_week_start_timestamp)), 'D, j M', NULL),
			'short_date_short_month_first_nos' => array($this->sentence_case(wp_date("D M j", $this->next_week_start_timestamp)), 'D M j', NULL),
			'short_date_short_month_first_comma_nos' => array($this->sentence_case(wp_date("D, M j", $this->next_week_start_timestamp)), 'D, M j', NULL),
			'full_date' => array($this->sentence_case(wp_date("l jS", $this->next_week_start_timestamp)), 'l jS', NULL),
			'full_date_comma' => array($this->sentence_case(wp_date("l, jS", $this->next_week_start_timestamp)), 'l, jS', NULL),
			'full_date_nos' => array($this->sentence_case(wp_date("l j", $this->next_week_start_timestamp)), 'l j', NULL),
			'full_date_date_dot' => array($this->sentence_case(wp_date("l d.", $this->next_week_start_timestamp)), 'l d.', NULL),
			'full_date_comma_nos' => array($this->sentence_case(wp_date("l, j", $this->next_week_start_timestamp)), 'l, j', NULL),
			'full_date_month' => array($this->sentence_case(wp_date("l jS F", $this->next_week_start_timestamp)), 'l jS F', NULL),
			'full_date_month_comma' => array($this->sentence_case(wp_date("l, jS F", $this->next_week_start_timestamp)), 'l, jS F', NULL),
			'full_date_month_first' => array($this->sentence_case(wp_date("l F jS", $this->next_week_start_timestamp)), 'l F jS', NULL),
			'full_date_month_first_comma' => array($this->sentence_case(wp_date("l, F jS", $this->next_week_start_timestamp)), 'l, F jS', NULL),
			'full_date_month_nos' => array($this->sentence_case(wp_date("l j F", $this->next_week_start_timestamp)), 'l j F', NULL),
			'full_date_month_date_dot_nos' => array($this->sentence_case(wp_date("l d. F", $this->next_week_start_timestamp)), 'l d. F', NULL),
			'full_date_month_comma_nos' => array($this->sentence_case(wp_date("l, j F", $this->next_week_start_timestamp)), 'l, j F', NULL),
			'full_date_month_first_nos' => array($this->sentence_case(wp_date("l F j", $this->next_week_start_timestamp)), 'l F j', NULL),
			'full_date_month_first_comma_nos' => array($this->sentence_case(wp_date("l, F j", $this->next_week_start_timestamp)), 'l, F j', NULL),
			'full_date_short_month' => array($this->sentence_case(wp_date("l jS M", $this->next_week_start_timestamp)), 'l jS M', NULL),
			'full_date_short_month_comma' => array($this->sentence_case(wp_date("l, jS M", $this->next_week_start_timestamp)), 'l, jS M', NULL),
			'full_date_short_month_first' => array($this->sentence_case(wp_date("l M jS", $this->next_week_start_timestamp)), 'l M jS', NULL),
			'full_date_short_month_first_comma' => array($this->sentence_case(wp_date("l, M jS", $this->next_week_start_timestamp)), 'l, M jS', NULL),
			'full_date_short_month_nos' => array($this->sentence_case(wp_date("l j M", $this->next_week_start_timestamp)), 'l j M', NULL),
			'full_date_short_month_date_dot_nos' => array($this->sentence_case(wp_date("l d. M", $this->next_week_start_timestamp)), 'l d. M', NULL),
			'full_date_short_month_comma_nos' => array($this->sentence_case(wp_date("l, j M", $this->next_week_start_timestamp)), 'l, j M', NULL),
			'full_date_short_month_first_nos' => array($this->sentence_case(wp_date("l M j", $this->next_week_start_timestamp)), 'l M j', NULL),
			'full_date_short_month_first_comma_nos' => array($this->sentence_case(wp_date("l, M j", $this->next_week_start_timestamp)), 'l, M j', NULL)
		);

		return TRUE;
	}
	
	public function set($data = NULL, $force = NULL)
	{
		// Set data with cache check
		
		if (is_array($data) && !empty($data))
		{
			ksort($data);
			$hash_key = md5(implode('|', array_keys($data)) . '|' . implode('|', array_values($data)));
			extract($data, EXTR_SKIP);
		}
		else
		{
			$data = NULL;
			$hash_key = 'data';
		}

		$cache = FALSE;
		$cache_retrieved = FALSE;
		$consolidation_cache = FALSE;
		$consolidation_cache_retrieved = FALSE;
		
		if (!is_bool($force) || !$force)
		{
			$force_check = get_option($this->prefix . 'force', NULL);
			
			if (is_string($force_check) && preg_match('#^(\d+(?:\.\d+)?)/0$#', $force_check, $m))
			{
				$force = ((time() - intval($m[1])) < 10);
				update_option($this->prefix . 'force', $m[1] . '/1', 'yes');
			}
			
			$force = ($force || ((is_bool($force) && !$force || !is_bool($force)) && $this->settings_updated && !is_array(get_option($this->prefix . 'regular'))));
		}

		$this->regular = (isset($regular) && is_array($regular)) ? $regular : ((is_array($this->regular) && !empty($this->regular)) ? $this->regular : get_option($this->prefix . 'regular'));
		$this->special = (isset($special) && is_array($special)) ? $special : ((is_array($this->special) && !empty($this->special)) ? $this->special : get_option($this->prefix . 'special'));
		$this->closure = (isset($closure) && is_array($closure)) ? $closure : ((is_array($this->closure) && !empty($this->closure)) ? $this->closure : get_option($this->prefix . 'closure'));
		$this->api_key = (isset($api_key) && is_string($api_key) && $api_key != NULL) ? $api_key : get_option($this->prefix . 'api_key');
		$this->place_id = (isset($place_id) && is_string($place_id) && $place_id != NULL) ? $place_id : get_option($this->prefix . 'place_id');
		$this->consolidation_labels = (isset($consolidation_labels) && is_bool($consolidation_labels)) ? $consolidation_labels : get_option($this->prefix . 'consolidation_labels', TRUE);
		$consolidation = (is_array($data) && array_key_exists('consolidation', $data)) ? ((isset($consolidation)) ? $consolidation : NULL) : get_option($this->prefix . 'consolidation');

		
		if (is_array($data) && (array_key_exists('regular', $data) || array_key_exists('special', $data) || array_key_exists('closure', $data)))
		{
			$regular = (array_key_exists('regular', $data) && is_bool($data['regular'])) ? $data['regular'] : TRUE;
			$special = (array_key_exists('special', $data) && is_bool($data['special']) && ($regular || $data['special'] || array_key_exists('closure', $data) && is_bool($data['closure']) && $data['closure'])) ? $data['special'] : TRUE;
			$closure = (array_key_exists('closure', $data) && is_bool($data['closure']) && ($regular || $special || $data['closure'])) ? $data['closure'] : TRUE;
		}
		else
		{
			$regular = TRUE;
			$special = TRUE;
			$closure = TRUE;
		}
		
		if (!is_array($this->data) || is_array($this->data) && empty($this->data))
		{
			$cache = wp_cache_get('data', $this->class_name);
			
			if (is_array($cache) && array_key_exists($hash_key, $cache))
			{
				$this->data = $cache[$hash_key];
				$cache_retrieved = TRUE;
			}
		}
		
		if ($consolidation == NULL)
		{
			$consolidation_cache_retrieved = TRUE;
		}
		elseif (!is_array($this->consolidation) || is_array($this->consolidation) && empty($this->consolidation) && $consolidation != NULL)
		{
			$consolidation_cache = wp_cache_get('consolidation', $this->class_name);
			
			if (is_array($consolidation_cache) && array_key_exists($hash_key, $consolidation_cache))
			{
				$this->consolidation = $consolidation_cache[$hash_key];
				$consolidation_cache_retrieved = TRUE;
			}
		}
		
		if (!$force && $cache_retrieved && $consolidation_cache_retrieved && (is_array($this->data) && !empty($this->data) && is_array($this->consolidation) && !empty($this->consolidation)))
		{
			return TRUE;
		}
		
		$this->data = array();
		$this->consolidation = array();
		
		if (is_array($cache) || is_array($consolidation_cache))
		{
			wp_cache_delete('data', $this->class_name);
			wp_cache_delete('consolidation', $this->class_name);
		}
		
		if (isset($start) && is_numeric($start))
		{
			if ($start >= -91 && $start <= 724)
			{
				$start = $this->get_day_timestamp($start);
			}
			
			if ($start >= 946684800)
			{
				$week_start = wp_date("w", $start);
			}
			else
			{
				$start = NULL;
			}
		}
		else
		{
			$start = NULL;
		}
		
		if (isset($end) && is_numeric($end) && $end >= -7 && $end <= 731)
		{
			$end = $this->get_day_timestamp($end + 1);
		}

		if (isset($end) && is_numeric($end) && $end >= 946684800 && (is_numeric($start) && $start < $end || !is_numeric($start) && $this->today_timestamp < $end))
		{
			if (!$regular && ($special || $closure))
			{
				if (is_numeric($start))
				{
					$end = ($end - WEEK_IN_SECONDS - $start > YEAR_IN_SECONDS) ? $start + YEAR_IN_SECONDS + WEEK_IN_SECONDS : $end;
					$count = ceil(($end - $start)/DAY_IN_SECONDS);
				}
				else
				{
					$end = ($end - WEEK_IN_SECONDS - $this->today_timestamp > YEAR_IN_SECONDS) ? $this->today_timestamp + YEAR_IN_SECONDS + WEEK_IN_SECONDS : $end;
					$count = ceil(($end - $this->today_timestamp)/DAY_IN_SECONDS);
				}
			}
			else
			{
				if (is_numeric($start))
				{
					$end = ($end - $start > 31 * DAY_IN_SECONDS) ? $this->today_timestamp + 31 * DAY_IN_SECONDS : $end;
					$count = ceil(($end - $start)/DAY_IN_SECONDS);
				}
				else
				{
					$end = ($end - $this->today_timestamp > 31 * DAY_IN_SECONDS) ? $this->today_timestamp + 31 * DAY_IN_SECONDS : $end;
					$count = ceil(($end - $this->today_timestamp)/DAY_IN_SECONDS);
				}
			}
		}
		else
		{
			$end = NULL;
			$count = (isset($count) && is_numeric($count) && $count >= 1 && $count <= ((!$regular && ($special || $closure)) ? 366 : 31)) ? intval($count) : 7;
		}
		
		$days = array();
		$closed_show = (!isset($closed_show) || isset($closed_show) && $closed_show);
		$week_start = (isset($week_start) && is_numeric($week_start)) ? (($week_start < 0) ? (($week_start == -2) ? $this->yesterday : $this->today) : $week_start) : $this->week_start;
		$start_modifier = (is_numeric($start) && abs(round(($start - $this->today_timestamp)/DAY_IN_SECONDS)) <= 731) ? round(($start - $this->today_timestamp)/DAY_IN_SECONDS) : 0;
		
		for ($i = (($start_modifier != 0 || $this->today == $week_start) ? 0 : -7); $i <= ((!$regular && ($special || $closure)) ? 372 : 31); $i++)
		{
			if (count($days) == $count)
			{
				break;
			}
			
			$timestamp = $this->get_day_timestamp($i + $start_modifier);
			
			if ($start == NULL)
			{
				if ($week_start == wp_date("w", $timestamp))
				{
					$start = $timestamp;
					$days[] = $timestamp;
				}
				continue;
			}
			
			$days[] = $timestamp;
		}
		
		$end = $timestamp;
		$start = (isset($start) && is_numeric($start)) ? $start : $week_start;
		$end = (isset($end) && is_numeric($end)) ? $end : mktime(0, 0, 0, wp_date("m"), wp_date("j") + ($count - 1), wp_date("Y"));
		$consecutive = array();
		$consecutive_replacement = array();
		
		foreach ($days as $i => $timestamp)
		{
			$day = wp_date("w", $timestamp);
			$a = ($special && !empty($this->closure) && $timestamp >= $this->closure['start'] && $timestamp < $this->closure['end']) ? array('closed' => TRUE) : (($special && is_array($this->special) && array_key_exists($timestamp, $this->special)) ? $this->special[$timestamp] : ((isset($this->regular[$day])) ? $this->regular[$day] : array()));
			$closed = (empty($a) || !empty($a) && isset($a['closed']) && $a['closed']);
			$label = ($special && !empty($a) && isset($a['label']) && (is_string($a['label']) && $a['label'] != NULL)) ? $a['label'] : NULL;
			$note = ($special && !empty($a) && isset($a['note']) && (is_string($a['note']) && $a['note'] != NULL)) ? $a['note'] : NULL;
			$day_weekday = (isset($weekdays) && is_array($weekdays) && in_array($day, $weekdays) || (!isset($weekdays) || isset($weekdays) && !is_array($weekdays)) && isset($this->weekdays) && is_array($this->weekdays) && in_array($day, $this->weekdays));
			$day_weekend = (isset($weekend) && is_array($weekend) && in_array($day, $weekend) || (!isset($weekend) || isset($weekend) && !is_array($weekend)) && isset($this->weekend) && is_array($this->weekend) && in_array($day, $this->weekend));
			
			if (!$regular && (!is_array($this->special) || !array_key_exists($timestamp, $this->special)) && (!is_array($this->closure) || is_array($this->closure) && (empty($this->closure) || isset($this->closure['start']) && isset($this->closure['end']) && ($timestamp < $this->closure['start'] || $timestamp >= $this->closure['end']))))
			{
				continue;
			}
			
			if ($consolidation == 'all' || $consolidation == 'separate' || $consolidation == 'weekdays' && $day_weekday || $consolidation == 'weekend' && $day_weekend)
			{
				if ($closed || isset($a['hours_24']) && $a['hours_24'])
				{
					$check_key = (isset($a['hours_24']) && $a['hours_24']) ? (($this->consolidation_labels && $label != NULL) ? 'hours_24_' . bin2hex(mb_strtolower($label)) : 'hours_24') : (($this->consolidation_labels && $label != NULL) ? 'closed' . bin2hex(mb_strtolower($label)) : 'closed');

					if (!array_key_exists($check_key, $this->consolidation))
					{
						$this->consolidation[$check_key] = array();
					}
					
					$this->consolidation[$check_key][$i] = array(
						'timestamp' => $timestamp,
						'weekday' => $day_weekday,
						'weekend' => $day_weekend,
						'label' => $label
					);
				}
				else
				{
					$hours_key = ($this->consolidation_labels && $label != NULL) ? md5(serialize(array_values($a['hours'] + array(bin2hex(mb_strtolower($label)))))) : md5(serialize(array_values($a['hours'])));
					
					if (!array_key_exists('hours_' . $hours_key, $this->consolidation))
					{
						$this->consolidation['hours_' . $hours_key] = array();
						$consecutive[$i] = 'hours_' . $hours_key;
					}
					else
					{
						while (in_array($hours_key, $consecutive_replacement))
						{
							$hours_key = $consecutive_replacement[$hours_key];
						}
												
						if (count($consecutive) > 2 && in_array('hours_' . $hours_key, $consecutive) && array_key_exists($i - 1, $consecutive) && $consecutive[$i - 1] != 'hours_' . $hours_key)
						{
							for ($j = count($consecutive) - 2; $j >= 0; $j--)
							{
								if (array_key_exists($j, $consecutive) && $consecutive[$j] == 'hours_' . $hours_key)
								{
									$c = 1;
									$previous_hours_key = $hours_key;
									$hours_key = md5(serialize(array(array_values($a['hours']), $c)));
									
									while (array_key_exists($hours_key, $consecutive_replacement))
									{
										if ($c >= 31)
										{
											break;
										}
										
										$c++;
										$previous_hours_key = $hours_key;
										$hours_key = md5(serialize(array(array_values($a['hours']), $c)));
									}
									
									$consecutive_replacement[$previous_hours_key] = $hours_key;
									break;
								}
							}
						}
						
						$consecutive[$i] = 'hours_' . $hours_key;
					}
					
					$this->consolidation['hours_' . $hours_key][$i] = array(
						'timestamp' => $timestamp,
						'weekday' => $day_weekday,
						'weekend' => $day_weekend,
						'label' => $label
					);
				}
			}
			elseif ($consolidation != NULL)
			{
				if (!array_key_exists('ignore', $this->consolidation))
				{
					$this->consolidation['ignore'] = array();
				}
				
				$this->consolidation['ignore'][$i] = array(
					'timestamp' => $timestamp,
					'weekday' => $day_weekday,
					'weekend' => $day_weekend,
					'label' => $label
				);
			}
			
			if ($closed && !$closed_show)
			{
				continue;
			}
			
			$this->data[$timestamp] = array(
				'date' => $timestamp,
				'regular' => ((!is_array($this->special) || !array_key_exists($timestamp, $this->special)) && (empty($this->closure) || !empty($this->closure) && ($timestamp < $this->closure['start'] || $timestamp >= $this->closure['end']))),
				'special' => (is_array($this->special) && array_key_exists($timestamp, $this->special) || is_array($this->closure) && !empty($this->closure) && $timestamp >= $this->closure['start'] && $timestamp < $this->closure['end']),
				'closure' => (is_array($this->closure) && !empty($this->closure) && $timestamp >= $this->closure['start'] && $timestamp < $this->closure['end']),
				'day' => $day,
				'count' => $i,
				'today' => ($timestamp == $this->today_timestamp),
				'tomorrow' => ($timestamp == $this->tomorrow_timestamp),
				'past' => ($timestamp < $this->today_timestamp),
				'future' => ($timestamp > $this->today_timestamp),
				'weekday' => $day_weekday,
				'weekend' => $day_weekend,
				'closed' => $closed,
				'hours_24' => (!$closed && isset($a['hours_24']) && $a['hours_24']),
				'hours' => (!$closed && isset($a['hours']) && is_array($a['hours'])) ? $a['hours'] : array(),
				'label' => $label,
				'note' => $note,
				'consolidated' => FALSE,
				'consolidated_first' => FALSE
			);
		}

		if ($consolidation != NULL && (count($this->consolidation) + ((array_key_exists('ignore', $this->consolidation)) ? count($this->consolidation['ignore']) - 1 : 0)) < count($this->data))
		{
			foreach ($this->consolidation as $k => $days)
			{
				if ($k == 'ignore' || count($days) < 2)
				{
					continue;
				}
				
				ksort($days);
				
				foreach ($days as $count => $a)
				{
					$i = 0;
					$consolidated = array(
						'weekdays' => array(),
						'weekend' => array(),
						'days' => array()
					);
										
					while (array_key_exists(($count + $i), $days) && $i <= 31)
					{
						$next = $count + $i;
						$weekday = $days[$next]['weekday'];
						$weekend = $days[$next]['weekend'];
						
						if ($consolidation == 'separate')
						{
							if ($weekday)
							{
								$consolidated['weekdays'][] = $days[$next]['timestamp'];
							}
							elseif ($weekend)
							{
								$consolidated['weekend'][] = $days[$next]['timestamp'];
							}
						}
						
						$consolidated['days'][] = $days[$next]['timestamp'];
						
						$i++;
					}
					
					if (count($consolidated['days']) < 2)
					{
						continue(2);
					}
				
					break;
				}
				
				foreach (array_keys($this->data) as $timestamp)
				{
					if (is_array($this->data[$timestamp]['consolidated']) || in_array($timestamp, $consolidated['days']) === FALSE)
					{
						continue;
					}
					
					if ($consolidation == 'separate')
					{
						if (in_array($timestamp, $consolidated['weekdays']) !== FALSE)
						{
							$this->data[$timestamp]['consolidated'] = $consolidated['weekdays'];
							$this->data[$timestamp]['consolidated_first'] = ($consolidated['weekdays'][0] == $timestamp);
						}
						elseif (in_array($timestamp, $consolidated['weekend']) !== FALSE)
						{
							$this->data[$timestamp]['consolidated'] = $consolidated['weekend'];
							$this->data[$timestamp]['consolidated_first'] = ($consolidated['weekend'][0] == $timestamp);
						}
						
						continue;
					}
										
					$this->data[$timestamp]['consolidated'] = $consolidated['days'];
					$this->data[$timestamp]['consolidated_first'] = ($consolidated['days'][0] == $timestamp);
				}
			}
		}

		$cache_refresh_time = (mktime(0, 0, 0, gmdate("m"), gmdate("j") + 1, gmdate("Y")) - time());
		$cache_refresh_time = ($cache_refresh_time > HOUR_IN_SECONDS) ? HOUR_IN_SECONDS : $cache_refresh_time;
		
		if ($cache_refresh_time > 15)
		{
			if (!is_array($cache))
			{
				$cache = array();
			}

			if (!is_array($consolidation_cache))
			{
				$consolidation_cache = array();
			}

			$cache[$hash_key] = $this->data;
			wp_cache_add('data', $cache, $this->class_name, $cache_refresh_time);
			
			if ($consolidation != NULL)
			{
				$consolidation_cache[$hash_key] = $this->consolidation;
				wp_cache_add('consolidation', $consolidation_cache, $this->class_name, $cache_refresh_time);
			}
		}
		
		if (!$this->dashboard || $this->api_key == NULL || $this->place_id == NULL || defined('XMLRPC_REQUEST') && XMLRPC_REQUEST || ((!is_bool($force) || !$force) && defined('DOING_CRON') && DOING_CRON) || isset($_POST['action']) && is_string($_POST['action']) && preg_match('/^heartbeat$/i', $_POST['action']) || isset($_POST['log']) && $_POST['log'] != NULL)
		{
			return TRUE;
		}

		if (!$force)
		{
			if (!isset($this->google_data) || isset($this->google_data) && !is_array($this->google_data) || isset($this->google_data) && is_array($this->google_data) && empty($this->google_data))
			{
				$this->google_data = get_option($this->prefix . 'google_result', NULL);
			}
			
			if ($this->google_data_exists(TRUE))
			{
				return TRUE;
			}

			if ((!is_array($this->google_data) || is_array($this->google_data) && empty($this->google_data)) && $this->request_count == 0)
			{
				$this->request_count++;
				$this->google_data = $this->get_google_data();
				$this->google_data_exists(TRUE, TRUE);
				update_option($this->prefix . 'google_result', $this->google_data, 'no');
				wp_cache_add('google_result', $this->google_data, $this->class_name, HOUR_IN_SECONDS);
				
				return (is_array($this->google_data) && !empty($this->google_data));
			}
			
			return TRUE;
		}
		
		delete_transient($this->prefix . 'offset_changes');
		wp_cache_delete('structured_data', $this->class_name);
		wp_cache_delete('google_result', $this->class_name);

		if ($this->request_count > 2)
		{
			return FALSE;
		}
		
		$this->google_data = $this->get_google_data('array', TRUE);
		$this->google_data_exists(TRUE, TRUE);
		update_option($this->prefix . 'google_result', $this->google_data, 'no');
		wp_cache_add('google_result', $this->google_data, $this->class_name, HOUR_IN_SECONDS);

		return TRUE;
	}
	
	public function structured_data($return = FALSE, $data = array())
	{
		// Collect Structured Data to display on the home page
				
		$test = (is_bool($return) && $return);
		$string = (is_string($return) && $return == 'json');
		$html = (is_string($return) && $return == 'html');
		$show_in_page = get_option($this->prefix . 'structured_data', 0);
		$show_in_page = (!$this->dashboard && (is_numeric($show_in_page) && $show_in_page > 1 && function_exists('get_the_ID') && get_the_ID() == intval($show_in_page) || (is_bool($show_in_page) && $show_in_page || is_numeric($show_in_page) && intval($show_in_page) == 1) && is_front_page()));
		
		if (!$return && !$string && empty($data) && !$show_in_page)
		{
			return;
		}
		
		$this->set(array('consolidation' => 'all', 'regular' => TRUE, 'special' => FALSE, 'week_start' => 0));
	
		if ($test)
		{
			return TRUE;
		}
		
		if (!$string && !$html)
		{
			$structured_data = wp_cache_get('structured_data', $this->class_name);
			if (is_string($structured_data) && strlen($structured_data) > 20)
			{
				echo wp_kses($structured_data, array('script' => array('type' => 'application/ld+json')));
				return;
			}
		}
		
		$logo = FALSE;
		
		$this->set_logo();

		if (is_string($this->logo_image_url))
		{
			$logo = $this->logo_image_url;
		}
		
		if (!is_string($logo) || is_string($logo) && !preg_match('/.+\.(?:jpe?g|png|svg|gif|webp)$/i', $logo))
		{
			$a = get_option('wpseo_titles');
			
			if (is_array($a) && isset($a['company_logo']) && is_string($a['company_logo']))
			{
				$logo = $a['company_logo'];
			}
			elseif (is_string($logo))
			{
				$logo = (!$string && isset($this->google_data['result']['icon'])) ? $this->google_data['result']['icon'] : FALSE;
			}
			
			if (is_null($logo))
			{
				$logo = FALSE;
			}
		}

		$name = (is_string(get_option($this->prefix . 'name'))) ? get_option($this->prefix . 'name') : ((isset($this->google_data['result']['name']) && is_string($this->google_data['result']['name'])) ? sanitize_text_field($this->google_data['result']['name']) : FALSE);
		$address = (is_string(get_option($this->prefix . 'address'))) ? get_option($this->prefix . 'address') : ((isset($this->google_data['result']['formatted_address']) && is_string($this->google_data['result']['formatted_address'])) ? sanitize_text_field($this->google_data['result']['formatted_address']) : FALSE);
		$telephone = get_option($this->prefix . 'telephone', FALSE);
		$business_type = (is_string(get_option($this->prefix . 'business_type'))) ? get_option($this->prefix . 'business_type') : FALSE;
		$price_range = (is_numeric(get_option($this->prefix . 'price_range', NULL))) ? str_repeat('$', get_option($this->prefix . 'price_range')) : FALSE;
		
		extract($data, EXTR_OVERWRITE);
		
		if (!is_string($name))
		{
			if ($test)
			{
				return FALSE;
			}
			
			if (!$string && !$html)
			{
				echo '';
				
				return;
			}
		}

		$data = array(
			'@context' => 'http://schema.org',
			'@type' => 'LocalBusiness',
			'name' => ($name != NULL) ? $name : FALSE,
			'address' => ($address != NULL) ? $address : FALSE,
			'image' => ($logo != NULL) ? $logo : FALSE,
			'url' => get_site_url(),
			'telephone' => ($telephone != NULL) ? $telephone : FALSE,
			'additionalType' => ($business_type != NULL) ? $business_type : FALSE,
			'priceRange' => ($price_range != NULL) ? $price_range : FALSE,
			'openingHoursSpecification' => array()
		);
		
		if (preg_match('/^\s*([^\r\n]+[^, \r\n])(?:,\s*|[ \t]*[\r\n]+[ \t]*)([^\r\n,]+)(?:,\s*|[ \t]*[\r\n]+[ \t]*)(?:([^\r\n,]+)(?:,\s*|[ \t]*[\r\n]+[ \t]*))?([^\r\n,]+)(?:,\s*|[ \t]*[\r\n]+[ \t]*)([a-z]{2})\s*$/si', $address, $m))
		{
			$data['address'] = array(
				'@type' => 'PostalAddress',
				'streetAddress' => $m[1],
				'addressLocality' => $m[2],
				'addressRegion' => $m[3],
				'postalCode' => $m[4],
				'addressCountry' => $m[5]
			);
		}
		
		$day_names_english = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

		foreach ($this->consolidation as $k => $day)
		{
			$d = array();
			$hours = NULL;
			
			if ($k == 'closed')
			{
				$hours = array(array('00:00', '00:00'));
			}
			elseif ($k == 'hours_24')
			{
				$hours = array(array('00:00', '23:59'));
			}
			
			foreach ($this->data as $timestamp => $a)
			{
				$match = FALSE;
				
				foreach ($day as $count => $t)
				{
					if ($t['timestamp'] == $timestamp)
					{
						$match = TRUE;
						break;
					}
				}
				
				if (!$match)
				{
					continue;
				}

				if ($hours == NULL)
				{
					$hours = $a['hours'];
				}
				
				$day_number = wp_date("w", $timestamp);
				$d[] = $day_names_english[$day_number];
			}
			
			$data['openingHoursSpecification'][] = array(
				'@type' => 'OpeningHoursSpecification',
				'dayOfWeek' => (count($d) == 1) ? $d[0] : $d,
				'opens' => (count($hours) == 3) ? array($hours[0][0], $hours[1][0], $hours[2][0]) : ((count($hours) == 2) ? array($hours[0][0], $hours[1][0]) : $hours[0][0]),
				'closes' => (count($hours) == 3) ? array($hours[0][1], $hours[1][1], $hours[2][1]) : ((count($hours) == 2) ? array($hours[0][1], $hours[1][1]) : $hours[0][1]),
			);			
		}
		
		if (isset($this->closure) && is_array($this->closure) && isset($this->closure['start']) && $this->closure['start'] != NULL && isset($this->closure['end']) && $this->closure['end'] != NULL)
		{
			$data['openingHoursSpecification'][] = array(
				'@type' => 'OpeningHoursSpecification',
				'opens' => '00:00',
				'closes' => '00:00',
				'validFrom' => wp_date("Y-m-d", mktime(gmdate("H", $this->closure['start']), gmdate("i", $this->closure['start']), 0, gmdate("m", $this->closure['start']), gmdate("j", $this->closure['start']), gmdate("Y", $this->closure['start']))),
				'validThrough' => wp_date("Y-m-d", mktime(gmdate("H", $this->closure['end']), gmdate("i", $this->closure['end']), 0, gmdate("m", $this->closure['end']), gmdate("j", $this->closure['end']) - 1, gmdate("Y", $this->closure['end'])))
			);
		}
		
		if (isset($this->special) && is_array($this->special))
		{
			$included = array();
			
			foreach ($this->special as $timestamp => $a)
			{
				if (!empty($included) && in_array($timestamp, $included) !== FALSE || (isset($this->closure) && is_array($this->closure) && isset($this->closure['start']) && $this->closure['start'] != NULL && isset($this->closure['end']) && $this->closure['end'] != NULL && $timestamp >= $this->closure['start'] && $timestamp <= $this->closure['end']))
				{
					continue;
				}
				
				$range = FALSE;
				
				if ($a['closed'])
				{
					$hours = array(array('00:00', '00:00'));
				}
				elseif ($a['hours_24'])
				{
					$hours = array(array('00:00', '23:59'));
				}
				else
				{
					$hours = $a['hours'];
				}
				
				foreach ($this->special as $timestamp_check => $a_check)
				{
					if ($timestamp == $timestamp_check)
					{
						$range = 0;
						continue;
					}
					
					if (!is_numeric($range))
					{
						continue;
					}
					
					if (round($timestamp_check/DAY_IN_SECONDS) == round($timestamp/DAY_IN_SECONDS) + $range + 1 && $a['closed'] == $a_check['closed'] && $a['hours_24'] == $a_check['hours_24'] && $a['hours'] == $a_check['hours'])
					{
						$included[] = $timestamp_check;
						$range++;
					}
				}
				
				if (!is_numeric($range))
				{
					$range = 0;
				}
				
				$data['openingHoursSpecification'][] = array(
					'@type' => 'OpeningHoursSpecification',
					'opens' => (count($hours) == 3) ? array($hours[0][0], $hours[1][0], $hours[2][0]) : ((count($hours) == 2) ? array($hours[0][0], $hours[1][0]) : $hours[0][0]),
					'closes' => (count($hours) == 3) ? array($hours[0][1], $hours[1][1], $hours[2][1]) : ((count($hours) == 2) ? array($hours[0][1], $hours[1][1]) : $hours[0][1]),
					'validFrom' => wp_date("Y-m-d", $timestamp),
					'validThrough' => wp_date("Y-m-d", mktime(gmdate("H", $timestamp), gmdate("i", $timestamp), 0, gmdate("m", $timestamp), gmdate("j", $timestamp) + $range, gmdate("Y", $timestamp)))
				);
			}
		}
		
		$data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		$structured_data = '<script type="application/ld+json">' . PHP_EOL . '[ ' . $data . ' ]' . PHP_EOL . '</script>';
		wp_cache_add('structured_data', $structured_data, $this->class_name, HOUR_IN_SECONDS);
		
		if ($html)
		{
			return esc_html($data);
		}
		
		if ($string)
		{
			return $data;
		}
		
		echo wp_kses($structured_data, array('script' => array('type' => 'application/ld+json')));
		return;
	}
		
	private function delete_logo()
	{
		// Delete the logo image for Structured Data
		
		$this->logo_image_id = NULL;
		$this->logo_image_url = NULL;
		update_option($this->prefix . 'logo', $this->logo_image_id);

		return TRUE;
	}
	
	private function set_synchronization()
	{
		// Set the synchronization method
		
		$this->synchronization = NULL;

		$google_sync = get_option($this->prefix . 'google_sync', FALSE);
		$structured_data = get_option($this->prefix . 'structured_data', FALSE);
		
		if ($structured_data != NULL && (is_bool($structured_data) && $structured_data || is_numeric($structured_data) && $structured_data >= 1))
		{
			$this->synchronization = 'structured_data';
		}
		elseif (is_bool($google_sync) && $google_sync || is_numeric($google_sync) && $google_sync >= 1)
		{
			$this->synchronization = 'google_places';
		}

		return TRUE;
	}
	
	private function set_logo($id = NULL)
	{
		// Set the logo image for Structured Data
		
		if (is_numeric($id))
		{
			update_option($this->prefix . 'logo', $id);
			$this->logo_image_id = $id;
		}
		else
		{
			$this->logo_image_id = get_option($this->prefix . 'logo');
		}
		
		if (is_numeric($this->logo_image_id))
		{
			$a = wp_get_attachment_image_src($this->logo_image_id, 'full');
			$this->logo_image_url = (is_array($a) && isset($a[0])) ? $a[0] : NULL;
		}
		
		return TRUE;
	}
	
	public function server_ip()
	{
		// Retrieve an accurate IP Address for the web server
		
		if (is_string(wp_cache_get('server_ip', $this->class_name)))
		{
			return trim(wp_cache_get('server_ip', $this->class_name));
		}

		$ip_regex = '/(?:^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$)|(?:^(?:(?:[a-fA-F\d]{1,4}:){7}(?:[a-fA-F\d]{1,4}|:)|(?:[a-fA-F\d]{1,4}:){6}(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|:[a-fA-F\d]{1,4}|:)|(?:[a-fA-F\d]{1,4}:){5}(?::(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,2}|:)|(?:[a-fA-F\d]{1,4}:){4}(?:(?::[a-fA-F\d]{1,4}){0,1}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,3}|:)|(?:[a-fA-F\d]{1,4}:){3}(?:(?::[a-fA-F\d]{1,4}){0,2}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,4}|:)|(?:[a-fA-F\d]{1,4}:){2}(?:(?::[a-fA-F\d]{1,4}){0,3}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,5}|:)|(?:[a-fA-F\d]{1,4}:){1}(?:(?::[a-fA-F\d]{1,4}){0,4}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,6}|:)|(?::(?:(?::[a-fA-F\d]{1,4}){0,5}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,7}|:)))(?:%[0-9a-zA-Z]{1,})?$)/mi';
		
		if (function_exists('wp_remote_get') && function_exists('wp_remote_retrieve_body'))
		{
			if (version_compare(PHP_VERSION, '8.1') >= 0)
			{
				$response = @wp_remote_get('http://ip6.me/api/');
			}
			else
			{
				$response = wp_remote_get('http://ip6.me/api/');
			}
			
			if (is_array($response) && !is_wp_error($response))
			{
				$string = wp_remote_retrieve_body($response);
				$a = (is_string($string)) ? preg_split('/,/i', $string, 2) : array('', '');
				
				if (preg_match($ip_regex, $a[1]))
				{
					$string = trim(strtolower($a[1]));
					wp_cache_set('server_ip', $string, $this->class_name, HOUR_IN_SECONDS);
					return $string;
				}
			}

			if (version_compare(PHP_VERSION, '8.1') >= 0)
			{
				$response = @wp_remote_get('http://checkip.dyndns.com/');
			}
			else
			{
				$response = wp_remote_get('http://checkip.dyndns.com/');
			}
			
			if (is_array($response) && !is_wp_error($response))
			{
				$string = wp_remote_retrieve_body($response);
				$string = (is_string($string)) ? preg_replace('/^.+ip\s+address[:\s]+\[?([^<>\s\b\]]+)\]?.*$/i', '$1', $string) : '';
			
				if (preg_match($ip_regex, $string))
				{
					$string = trim(strtolower($string));
					wp_cache_set('server_ip', $string, $this->class_name, HOUR_IN_SECONDS);
					return $string;
				}
			}
		}

		if (function_exists('gethostname') && function_exists('gethostbyname'))
		{
			$string = gethostbyname(gethostname());

			if (is_string($string) && preg_match($ip_regex, $string))
			{
				$string = trim(strtolower($string));
				wp_cache_set('server_ip', $string, $this->class_name, HOUR_IN_SECONDS);
				return $string;
			}
		}
		
		if (isset($_SERVER['SERVER_ADDR']) && is_string($_SERVER['SERVER_ADDR']) && preg_match($ip_regex, $_SERVER['SERVER_ADDR']))
		{
			wp_cache_set('server_ip', trim($_SERVER['SERVER_ADDR']), $this->class_name, HOUR_IN_SECONDS);
			return trim($_SERVER['SERVER_ADDR']);
		}
		
		return NULL;
	}

	public function data_hunter($format = 'array', $force = FALSE)
	{
		// Find all references to existing Google Reviews, API Key and Place ID
		
		if (!$force && is_string(get_option($this->prefix . 'time_format')) && get_option($this->prefix . 'time_format') != NULL)
		{
			switch ($format)
			{
			case 'boolean':
			case 'test':
				return FALSE;
			case 'json':
				return json_encode(NULL);
			default:
				break;
			}
			return array();
		}
		
		global $wpdb;
		
		$ret = array();
		
		if (get_option('google_business_reviews_rating_api_key') != NULL && get_option('google_business_reviews_rating_place_id') != NULL)
		{
			$ret = array(
				'api_key' => get_option('google_business_reviews_rating_api_key'),
				'place_id' => get_option('google_business_reviews_rating_place_id')
			);
		}

		if (empty($ret) && is_string(get_option('grw_google_api_key')) && $wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "grp_google_place'") == $wpdb->prefix . 'grp_google_place')
		{
			$id = $wpdb->get_var("SELECT `id` FROM `" . $wpdb->prefix . "grp_google_place` ORDER BY `id` DESC LIMIT 1");
			$place_id = $wpdb->get_var("SELECT `place_id` FROM `" . $wpdb->prefix . "grp_google_place` WHERE `id` = '" . esc_sql($id) . "' LIMIT 1");
			$ret = array(
				'api_key' => get_option('grw_google_api_key'),
				'place_id' => $place_id
			);
		}
		
		if (empty($ret) && is_array(get_option('wpfbr_google_options')))
		{
			$d = get_option('wpfbr_google_options');
			if ($d['select_google_api'] != 'default' && is_string($d['google_api_key']))
			{				
				$ret = array(
					'api_key' => $d['google_api_key'],
					'place_id' => (isset($d['google_location_set']['place_id'])) ? $d['google_location_set']['place_id'] : NULL
				);
			}
		}
		
		if (empty($ret) && is_array(get_option('googleplacesreviews_options')))
		{
			$d = get_option('googleplacesreviews_options');
			$w = array('place_id' => NULL);
			
			if (array_key_exists('google_places_api_key', $d))
			{
				$w = get_option('googleplacesreviews_options');
				if (is_array($w) && array_key_exists('place_id', $w))
				{
					$place_id = $w['place_id'];
				}
				
				$ret = array(
					'api_key' => $d['google_places_api_key'],
					'place_id' => $place_id
				);
			}
		}
		
		if (empty($ret) && is_string(get_option('google_places_api_key')))
		{
			$ret = array(
				'api_key' => get_option('google_places_api_key')
			);
		}
		
		switch ($format)
		{
		case 'boolean':
		case 'test':
			$ret = (!empty($ret) || !is_numeric(get_option($this->prefix . 'week_start')));
			break;
		case 'json':
			$ret['day_format'] = get_option('day_format');			
			$ret['time_format'] = get_option('time_format');			
			$ret['week_start'] = get_option('start_of_week');
			$ret = json_encode($ret);
			break;
		default:
			break;
		}
		
		return $ret;
	}
		
	public function open_change($range = NULL, $timestamp_return = FALSE, $set = TRUE)
	{
		// Find out current open status and time to change in seconds
		
		$range = (is_int($range) && $range >= 1 && $range <= 31) ? $range : ((count($this->days) != 7 && count($this->days) >= 1) ? count($this->days) : 31);
		$seconds_to_change = NULL;
		$open_now = FALSE;
		
		if ($set)
		{
			$this->set();
		}
		
		for ($i = 0; $i < $range; $i++)
		{
			if ($seconds_to_change > $range * DAY_IN_SECONDS)
			{
				break;
			}
			
			$d = ($this->today + $i) % 7;
			$timestamp = ($i == 0) ? $this->today_timestamp : $this->get_day_timestamp($i);
			$a = (!empty($this->closure) && $timestamp >= $this->closure['start'] && $timestamp < $this->closure['end']) ? array('closed' => TRUE) : ((is_array($this->special) && array_key_exists($timestamp, $this->special)) ? $this->special[$timestamp] : ((isset($this->regular[$d])) ? $this->regular[$d] : array()));
			$day_closed = (empty($a) || !empty($a) && isset($a['closed']) && $a['closed']);
			$day_hours_24 = (!$day_closed && isset($a['hours_24']) && $a['hours_24']);
			$day_hours = (!$day_closed && isset($a['hours']) && is_array($a['hours'])) ? $a['hours'] : array();
			$day_seconds = (!empty($day_hours)) ? $this->hours_filter($day_hours, 'seconds') : array();

			if ($i == 0)
			{
				if ($day_closed || $day_hours_24)
				{
					$seconds_to_change = ($timestamp + DAY_IN_SECONDS - $this->current_timestamp);
					$open_now = $day_hours_24;
					continue;
				}
				
				foreach ($day_seconds as $j => $h)
				{
					if ($j%2 == 1)
					{
						continue;
					}
					
					$start = $h - $this->offset;
					$end = $day_seconds[($j + 1)] - $this->offset;
					$timestamp_hours_start = mktime(0, 0, $start, wp_date("m", $timestamp), wp_date("j", $timestamp), wp_date("Y", $timestamp));
					$timestamp_hours_end = mktime(0, 0, $end, wp_date("m", $timestamp), wp_date("j", $timestamp), wp_date("Y", $timestamp));
					
					if ($timestamp_hours_start > $this->current_timestamp)
					{
						$seconds_to_change = ($timestamp_hours_start - $this->current_timestamp);
						break(2);
					}
					
					if ($this->current_timestamp >= $timestamp_hours_start && $this->current_timestamp < $timestamp_hours_end)
					{
						$seconds_to_change = ($timestamp_hours_end - $this->current_timestamp);
						$open_now = TRUE;
						break(2);
					}
					
					$next = (count($day_seconds) >= $j + 3) ? $day_seconds[($j + 2)] - $this->offset : NULL;
					
					if ($next == NULL)
					{
						$seconds_to_change = ($timestamp + DAY_IN_SECONDS - $this->current_timestamp);
						break;
					}
					
					$timestamp_hours_next = mktime(0, 0, $next, wp_date("m", $timestamp), wp_date("j", $timestamp), wp_date("Y", $timestamp));

					if ($this->current_timestamp >= $timestamp_hours_end && $this->current_timestamp < $timestamp_hours_next)
					{
						$seconds_to_change = ($timestamp_hours_next - $this->current_timestamp);
						break(2);
					}
				}
				continue;
			}
			
			if ($day_closed || $day_hours_24)
			{
				if ($open_now == $day_closed)
				{
					break;
				}
				
				$seconds_to_change += DAY_IN_SECONDS;
				continue;
			}
			
			if (!is_array($day_seconds))
			{
				break;
			}

			$start = $day_seconds[0] - $this->offset;
			$end = $day_seconds[1] - $this->offset;
			$timestamp_hours_start = mktime(0, 0, $start, wp_date("m", $timestamp), wp_date("j", $timestamp), wp_date("Y", $timestamp));
			$timestamp_hours_end = mktime(0, 0, $end, wp_date("m", $timestamp), wp_date("j", $timestamp), wp_date("Y", $timestamp));
			
			if (!$open_now && $timestamp_hours_start > $this->current_timestamp)
			{
				$seconds_to_change += ($timestamp_hours_start - $timestamp);
			}
			elseif ($open_now && $this->current_timestamp >= $timestamp_hours_start && $this->current_timestamp < $timestamp_hours_end)
			{
				$seconds_to_change += ($timestamp_hours_end - $timestamp);
			}
			
			break;
		}
		
		if ($seconds_to_change > WEEK_IN_SECONDS * 2)
		{
			$seconds_to_change = WEEK_IN_SECONDS * 2;
		}
		
		if ($timestamp_return)
		{
			return array($open_now, $this->current_timestamp + $seconds_to_change);
		}

		return array($open_now, $seconds_to_change);
	}
	
	private function day_string($data, $day_format, $day_range_suffix = NULL, $day_format_length = NULL, $format = 'html', $preferences = NULL)
	{
		// Create a text string of day or day range from arguments
	
		if (is_array($preferences))
		{
			extract($preferences, EXTR_OVERWRITE);
		}

		$day = $data['day'];
		$replace_day_name = (isset($days) && is_array($days) && count($days) == 7);
		$labels = ($data['special'] && $data['label'] != NULL && (!isset($labels) || (isset($labels) && $labels)));
		$today_tomorrow_text = (isset($today) && $today != NULL && $data['date'] == $this->today_timestamp) ? $today : ((isset($tomorrow) && $tomorrow != NULL && $data['date'] == $this->tomorrow_timestamp) ? $tomorrow : NULL);
		$day_name = ($today_tomorrow_text != NULL && (!$labels || !isset($labels_precedence) || !$labels_precedence)) ? $today_tomorrow_text : (($labels) ? $data['label'] : (($replace_day_name) ? $days[$day] : $this->days[$day]));
		$day_replacement_word = NULL;
		$consolidated = $data['consolidated'];
		$consolidated_first = $data['consolidated_first'];
		$consolidated_range = FALSE;
		
		if (array_key_exists('weekdays_text', $preferences) && $preferences['weekdays_text'] == NULL)
		{
			$weekdays_text = NULL;
		}
		
		if (array_key_exists('weekend_text', $preferences) && $preferences['weekend_text'] == NULL)
		{
			$weekend_text = NULL;
		}
		
		if (array_key_exists('everyday_text', $preferences) && $preferences['everyday_text'] == NULL)
		{
			$everyday_text = NULL;
		}
		
		if (!$labels && preg_match($this->accepted_day_format, $day_format))
		{
			if (preg_match('/^(.+)\^(S)(.+)$/', $day_format, $m))
			{
				$day_format = $m[1] . '<\\s\\u\\p>' . $m[2] . '</\\s\\u\\p>' . $m[3];
			}
			
			$timestamp = (isset($data['date']) && is_numeric($data['date'])) ? $data['date'] : NULL;
			
			if ($timestamp == NULL)
			{
				for ($i = $this->week_start; $i < ($this->week_start + 7); $i++)
				{
					$timestamp = mktime(0, 0, 0, wp_date("m", $this->week_start_timestamp), wp_date("j", $this->week_start_timestamp) - (($day == $this->week_start) ? 0 : 7) + $i, wp_date("Y", $this->week_start_timestamp));

					if (wp_date("w", $timestamp) == $data['day'])
					{
						break;
					}
				}
			}
			
			if (!$replace_day_name)
			{
				$day_name = ($today_tomorrow_text != NULL && (!$labels || !isset($labels_precedence) || !$labels_precedence)) ? $today_tomorrow_text : wp_date($day_format, $timestamp);
			}
		}

		if (is_array($this->data) && is_array($consolidated) && $consolidated_first)
		{
			if (count($this->data) == count($consolidated))
			{
				$day_replacement_word = (array_key_exists('everyday_text', $preferences)) ? $everyday_text : get_option($this->prefix . 'everyday_text');
			}
			elseif (count($this->weekdays) == count($consolidated) || count($this->weekend) == count($consolidated) && ((array_key_exists('weekdays_text', $preferences) && $weekdays_text != NULL || get_option($this->prefix . 'weekdays_text') != NULL) || (array_key_exists('weekend_text', $preferences) && $weekend_text != NULL || get_option($this->prefix . 'weekend_text') != NULL)))
			{
				$weekdays_check = 0;
				$weekend_check = 0;
				
				foreach ($consolidated as $timestamp)
				{
					$day_value = wp_date("w", $timestamp);
					
					if (in_array($day_value, $this->weekdays) !== FALSE)
					{
						$weekdays_check++;
					}
					elseif (in_array($day_value, $this->weekend) !== FALSE)
					{
						$weekend_check++;
					}
				}

				if ($weekend_check == 0 && count($this->weekdays) == $weekdays_check || $weekdays_check == 0 && count($this->weekend) == $weekend_check)
				{
					if ($labels)
					{
						$day_replacement_word = $data['label'];
					}
					elseif ($weekend_check == 0)
					{
						$day_replacement_word = (array_key_exists('weekdays_text', $preferences) && ($weekdays_text == NULL || is_string($weekdays_text))) ? $weekdays_text : get_option($this->prefix . 'weekdays_text');
					}
					else
					{
						$day_replacement_word = (array_key_exists('weekend_text', $preferences) && ($weekend_text == NULL || is_string($weekend_text))) ? $weekend_text : get_option($this->prefix . 'weekend_text');
					}
				}
			}
		}
		
		$html = ($day_replacement_word == NULL && (is_numeric($day_format_length)) ? mb_substr($day_name, 0, $day_format_length) : (($day_replacement_word != NULL) ? $day_replacement_word : $day_name));
		
		if ($day_replacement_word == NULL && is_array($consolidated) && $consolidated_first)
		{
			if (count($consolidated) >= ((isset($day_range_min)) ? $day_range_min : $this->day_range_min))
			{
				$day = $this->data[max($consolidated)]['day'];
				$timestamp = $this->data[max($consolidated)]['date'];
				$day_name = ($today_tomorrow_text != NULL && (!$labels || !isset($labels_precedence) || !$labels_precedence)) ? $today_tomorrow_text : (($labels) ? $data['label'] : (($replace_day_name) ? $days[$day] : $this->days[$day]));
				
				if (!$labels && preg_match($this->accepted_day_format, $day_format))
				{
					if (preg_match('/^(.+)\^(S)(.+)$/', $day_format, $m))
					{
						$day_format = $m[1] . '<\\s\\u\\p>' . $m[2] . '</\\s\\u\\p>' . $m[3];
					}
					
					if ($timestamp == NULL)
					{
						for ($i = $this->week_start; $i < ($this->week_start + 7); $i++)
						{
							$timestamp = mktime(0, 0, 0, wp_date("m", $this->week_start_timestamp), wp_date("j", $this->week_start_timestamp) - (($day == $this->week_start) ? 0 : 7) + $i, wp_date("Y", $this->week_start_timestamp));

							if (wp_date("w", $timestamp) == $day)
							{
								if (!$replace_day_name)
								{
									$day_name = ($today_tomorrow_text != NULL && (!$labels || !isset($labels_precedence) || !$labels_precedence)) ? $today_tomorrow_text : wp_date($day_format, $timestamp);
								}

								break;
							}
						}
					}
					elseif (!$replace_day_name)
					{
						$day_name = ($today_tomorrow_text != NULL && (!$labels || !isset($labels_precedence) || !$labels_precedence)) ? $today_tomorrow_text : wp_date($day_format, $timestamp);
					}
				}

				$day_range_separator = (isset($day_range_separator)) ? $day_range_separator : get_option($this->prefix . 'day_range_separator');
				
				if (preg_match('/^["]([^"]+)["]$/', $day_range_separator, $m))
				{
					$day_range_separator = $m[1];
				}
				
				$html .= $day_range_separator . ((is_numeric($day_format_length)) ? mb_substr($day_name, 0, $day_format_length) : $day_name);
			}
			else
			{
				$day_separator = (isset($day_separator)) ? $day_separator : get_option($this->prefix . 'day_separator');
				
				if (preg_match('/^["]?([^|]+)\|([^|"]+)["]?$/', $day_separator, $m))
				{
					$day_separator_first = $m[1];
					$day_separator_last = $m[2];
				}
				elseif (preg_match('/^["]([^"]+)["]$/', $day_separator, $m))
				{
					$day_separator_first = $day_separator_last = $m[1];
				}
				else
				{
					$day_separator_first = $day_separator_last = $day_separator;
				}
				
				$i = 0;
				array_shift($consolidated);
				
				foreach ($consolidated as $timestamp)
				{
					$day = $this->data[$timestamp]['day'];
					$day_name = ($labels) ? $data['label'] : (($replace_day_name) ? $days[$day] : $this->days[$day]);
					
					if (!$labels && !$replace_day_name && preg_match($this->accepted_day_format, $day_format))
					{
						if (is_string($day_format) && preg_match('/^(.+)\^(S)(.+)$/', $day_format, $m))
						{
							$day_format = $m[1] . '<\\s\\u\\p>' . $m[2] . '</\\s\\u\\p>' . $m[3];
						}
						
						$day_name = wp_date($day_format, $timestamp);
					}
					
					$html .= (($i == count($consolidated) - 1) ? $day_separator_last : $day_separator_first) . ((is_numeric($day_format_length)) ? mb_substr($day_name, 0, $day_format_length) : $day_name);
					$i++;
				}
			}
			
			$consolidated_range = TRUE;
		}
		
		$html .= ((!$consolidated_range && isset($day_suffix) && $day_suffix != NULL) ? $day_suffix : '')	
			. ((($labels || $day_replacement_word == NULL) && isset($day_range_suffix) && $day_range_suffix != NULL && (!isset($day_suffix) || isset($day_suffix) && $day_range_suffix != $day_suffix)) ? $day_range_suffix : '')
			. ((!$labels && $day_replacement_word != NULL && isset($day_suffix_consolidated) && $day_suffix_consolidated != NULL) ? $day_suffix_consolidated : '');
		
		switch ($format)
		{
		case 'text':
			$html = wp_strip_all_tags($html);
			break;
		case 'html':
		default:
			$html = esc_html($html);
			
			if (is_string($day_format) && preg_match('#' . preg_quote('<\\s\\u\\p>S</\\s\\u\\p>', '#') . '#', $day_format))
			{
				$html = preg_replace('#&lt;(sup)&gt;([^&]{1,10})&lt;(/sup)&gt;#i', '<$1>$2<$3>', $html);
			}
			break;
		}
		
		return $html;
	}
	
	private function hours_string($hours, $closed, $hours_24, $note = NULL, $format = NULL, $preferences = NULL)
	{
		// Create a text string of opening hours from arguments

		$html = '';

		if ($closed && ($format == 'start' || $format == 'end' || $format == 'next'))
		{
			return NULL;
		}

		if ($format == 'start' || $format == 'end' || $format == 'next' || isset($preferences['notes']) && is_bool($preferences['notes']) && !$preferences['notes'])
		{
			$note = NULL;
		}
		elseif ($note != NULL && (!isset($preferences['notes']) || isset($preferences['notes']) && (is_bool($preferences['notes']) && $preferences['notes'] || is_string($preferences['notes']))) && isset($preferences['note_affixes']) && is_array($preferences['note_affixes']))
		{
			$note = $preferences['note_affixes'][0] . $note . $preferences['note_affixes'][1];
		}

		if ($note != NULL && isset($preferences['notes']) && ($preferences['notes'] === 'replace' || $closed && $preferences['notes'] === 'replace closed' || $hours_24 && $preferences['notes'] === 'replace 24 hours'))
		{
			return $note;
		}

		if ($closed)
		{
			switch ($format)
			{
			case 'text':
				$html = (($note != NULL && isset($preferences['notes']) && $preferences['notes'] === 'prefix') ? $note . ' ' : '')
					. ((is_array($preferences) && isset($preferences['closed'])) ? $preferences['closed'] : get_option($this->prefix . 'closed_text'))
					. (($note != NULL && (!isset($preferences['notes']) || isset($preferences['notes']) && (is_bool($preferences['notes']) && $preferences['notes'] || $preferences['notes'] === 'suffix'))) ? ' ' . $note : '');
				$html = wp_strip_all_tags($html);
				break;
			case 'html':
			default:
				$html = (($note != NULL && isset($preferences['notes']) && $preferences['notes'] === 'prefix') ? '<span class="note prefix">' . esc_html($note) . '</span> ' : '')
					. esc_html((is_array($preferences) && isset($preferences['closed'])) ? $preferences['closed'] : get_option($this->prefix . 'closed_text'))
					. (($note != NULL && (!isset($preferences['notes']) || isset($preferences['notes']) && (is_bool($preferences['notes']) && $preferences['notes'] || $preferences['notes'] === 'suffix'))) ? ' <span class="note">' . esc_html($note) . '</span>' : '');
				break;
			}
			
			return $html;
		}
				
		if ($hours_24 && (is_array($preferences) && array_key_exists('hours_24', $preferences) && $preferences['hours_24'] != NULL || !is_array($preferences) && get_option($this->prefix . '24_hours_text') != NULL || is_array($preferences) && !array_key_exists('hours_24', $preferences) && get_option($this->prefix . '24_hours_text') != NULL))
		{
			switch ($format)
			{
			case 'text':
				$html = (($note != NULL && isset($preferences['notes']) && $preferences['notes'] === 'prefix') ? $note . ' ' : '')
					. ((is_array($preferences) && array_key_exists('hours_24', $preferences) && $preferences['hours_24'] != NULL) ? $preferences['hours_24'] : get_option($this->prefix . '24_hours_text'))
					. (($note != NULL && (!isset($preferences['notes']) || isset($preferences['notes']) && (is_bool($preferences['notes']) && $preferences['notes'] || $preferences['notes'] === 'suffix'))) ? ' ' . $note : '');
				$html = wp_strip_all_tags($html);
				break;
			case 'html':
			default:
				$html = (($note != NULL && isset($preferences['notes']) && $preferences['notes'] === 'prefix') ? '<span class="note prefix">' . esc_html($note) . '</span> ' : '')
					. esc_html((is_array($preferences) && array_key_exists('hours_24', $preferences) && $preferences['hours_24'] != NULL) ? $preferences['hours_24'] : get_option($this->prefix . '24_hours_text'))
					. (($note != NULL && (!isset($preferences['notes']) || isset($preferences['notes']) && (is_bool($preferences['notes']) && $preferences['notes'] || $preferences['notes'] === 'suffix'))) ? ' <span class="note">' . esc_html($note) . '</span>' : '');
				break;
			}
			
			return $html;
		}
		
		if ($hours_24 && !is_array($hours) || is_array($hours) && empty($hours))
		{
			if ($format == 'next')
			{
				return NULL;
			}
			
			$hours = array(
				0 => array(
					0 => '00:00',
					1 => '00:00'
				)
			);
		}
		
		$html = array();
		$time_group_separator_first = NULL;
		$time_group_separator_last = NULL;
		$time_format_key = (is_array($preferences) && isset($preferences['time_format'])) ? $preferences['time_format'] : ((get_option($this->prefix . 'time_format') != NULL) ? get_option($this->prefix . 'time_format') : '24_colon');
		$time_format = $this->time_formats[$time_format_key][1];
		$time_format_space = (preg_match('/^.*[^ ]+ [^ ]+.*$/', $time_format));
		$time_trim = (is_bool($this->time_formats[$time_format_key][2]) && $this->time_formats[$time_format_key][2]);
		$time_minute_replacement = (is_string($this->time_formats[$time_format_key][2])) ? $this->time_formats[$time_format_key][2] : NULL;
		$time_separator = (is_array($preferences) && isset($preferences['time_separator'])) ? $preferences['time_separator'] : get_option($this->prefix . 'time_separator');
		$time_group_separator = (is_array($preferences) && isset($preferences['time_group_separator'])) ? $preferences['time_group_separator'] : get_option($this->prefix . 'time_group_separator');
		$time_group_prefix = (is_array($preferences) && isset($preferences['time_group_prefix'])) ? $preferences['time_group_prefix'] : NULL;
		$time_group_suffix = (is_array($preferences) && isset($preferences['time_group_suffix'])) ? $preferences['time_group_suffix'] : NULL;
		$midday_text = (is_array($preferences) && array_key_exists('midday', $preferences)) ? $preferences['midday'] : get_option($this->prefix . 'midday_text');
		$midnight_text = (is_array($preferences) && array_key_exists('midnight', $preferences)) ? $preferences['midnight'] : get_option($this->prefix . 'midnight_text');

		if ($time_format_space)
		{
			$time_format = preg_replace('/ /', '&\nb\s\p;', $time_format);
		}
		
		if (preg_match('/^([^|]+)\|([^|]+)$/', $time_group_separator, $m))
		{
			$time_group_separator_first = $m[1];
			$time_group_separator_last = $m[2];
		}
		
		$hours = (is_array($hours)) ? array_values($hours) : array();
		
		if ($format == 'next')
		{
			list($open_now, $change_timestamp) = $this->open_change(1, TRUE);
			
			if ($open_now && count($hours) == 1 || count($hours) > 1)
			{
				$hours = array(
					0 => array(
						0 => (count($hours) == 1) ? $hours[0][1] : wp_date("H:i", $change_timestamp),
						1 => '00:00'
					)
				);
			}
		}
		
		foreach ($hours as $i => $a)
		{
			$a = array_values($a);
			
			if (count($a) != 2)
			{
				break;
			}
			
			if ($format == 'end' && $i < (count($hours) - 1))
			{
				continue;
			}

			$time_first_text = $time_last_text = NULL;
			list($hour_first, $minute_first, $hour_last, $minute_last) = preg_split('/[:-]/', implode('-', $a), 4);
			
			if ($format == 'end')
			{
				$hour_first = $hour_last;
				$minute_first = $minute_last;
			}

			if ($midday_text != NULL)
			{
				if (intval($hour_first) == 12 && intval($minute_first) == 0)
				{
					$time_first_text = $midday_text;
				}
	
				if (intval($hour_last) == 12 && intval($minute_last) == 0)
				{
					$time_last_text = $midday_text;
				}
			}

			if ($midnight_text != NULL)
			{
				if (intval($hour_first) == 0 && intval($minute_first) == 0)
				{
					$time_first_text = $midnight_text;
				}
	
				if (intval($hour_last) == 0 && intval($minute_last) == 0)
				{
					$time_last_text = $midnight_text;
				}
			}
			
			if ($time_trim)
			{
				$html[] = (($time_group_prefix != NULL) ? $time_group_prefix : '')
				. (($time_first_text != NULL) ? $time_first_text : ((intval($minute_first) == 0) ? preg_replace('/^(\d{1,2})[^\d]*[0]{2}(.*)$/', '$1$2', gmdate($time_format, mktime($hour_first, $minute_first, 0, 1, 1, 2020))) : gmdate($time_format, mktime($hour_first, $minute_first, 0, 1, 1, 2020))))
				. (($format != 'start' && $format != 'end' && $format != 'next') ? $time_separator
				. (($time_last_text != NULL) ? $time_last_text : ((intval($minute_last) == 0) ? preg_replace('/^(\d{1,2})[^\d]*[0]{2}(.*)$/', '$1$2', gmdate($time_format, mktime($hour_last, $minute_last, 0, 1, 1, 2020))) : gmdate($time_format, mktime($hour_last, $minute_last, 0, 1, 1, 2020)))) : '')
				. (($time_group_suffix != NULL && $time_last_text == NULL) ? $time_group_suffix : '');
			}
			elseif ($time_minute_replacement != NULL)
			{
				$html[] = (($time_group_prefix != NULL) ? $time_group_prefix : '')
				. (($time_first_text != NULL) ? $time_first_text : ((intval($minute_first) == 0) ? preg_replace('/^(\d{1,2}[^\d]*)[0]{2}(.*)$/', '$1' . $time_minute_replacement . '$2', gmdate($time_format, mktime($hour_first, $minute_first, 0, 1, 1, 2020))) : gmdate($time_format, mktime($hour_first, $minute_first, 0, 1, 1, 2020))))
				. (($format != 'start' && $format != 'end' && $format != 'next') ? $time_separator
				. (($time_last_text != NULL) ? $time_last_text : ((intval($minute_last) == 0) ? preg_replace('/^(\d{1,2}[^\d]*)[0]{2}(.*)$/', '$1' . $time_minute_replacement . '$2', gmdate($time_format, mktime($hour_last, $minute_last, 0, 1, 1, 2020))) : gmdate($time_format, mktime($hour_last, $minute_last, 0, 1, 1, 2020)))) : '')
				. (($time_group_suffix != NULL && $time_last_text == NULL) ? $time_group_suffix : '');
			}
			else
			{
				$html[] = (($time_group_prefix != NULL) ? $time_group_prefix : '')
				. (($time_first_text != NULL) ? $time_first_text : gmdate($time_format, mktime($hour_first, $minute_first, 0, 1, 1, 2020)))
				. (($format != 'start' && $format != 'end' && $format != 'next') ? $time_separator
				. (($time_last_text != NULL) ? $time_last_text : gmdate($time_format, mktime($hour_last, $minute_last, 0, 1, 1, 2020))) : '')
				. (($time_group_suffix != NULL && $time_last_text == NULL) ? $time_group_suffix : '');
			}
			
			if ($format == 'start' || $format == 'next')
			{
				break;
			}
		}
		
		if (count($html) == 3 && $time_group_separator_first != NULL && $time_group_separator_last != NULL)
		{
			$html = $html[0] . $time_group_separator_first . $html[1] . $time_group_separator_last . $html[2];
		}
		else
		{
			$html = ($time_group_separator_last != NULL) ? implode($time_group_separator_last, $html) : implode($time_group_separator, $html);
		}

		if ($time_format_space)
		{
			$html = preg_replace('/&nbsp;/', "\xc2\xa0", $html);
		}

		switch ($format)
		{
		case 'text':
			$html = (($note != NULL && isset($preferences['notes']) && $preferences['notes'] === 'prefix') ? $note . ' ' : '')
				. $html
				. (($note != NULL && (!isset($preferences['notes']) || isset($preferences['notes']) && (is_bool($preferences['notes']) && $preferences['notes'] || $preferences['notes'] === 'suffix'))) ? ' ' . $note : '');
			$html = wp_strip_all_tags($html);
			break;
		case 'html':
		default:
			$html = (($note != NULL && isset($preferences['notes']) && $preferences['notes'] === 'prefix') ? '<span class="note prefix">' . esc_html($note) . '</span> ' : '')
				. (($time_format_space) ? preg_replace('/\xc2\xa0/', '&nbsp;', esc_html($html)) : esc_html($html))
				. (($note != NULL && (!isset($preferences['notes']) || isset($preferences['notes']) && (is_bool($preferences['notes']) && $preferences['notes'] || $preferences['notes'] === 'suffix'))) ? ' <span class="note">' . esc_html($note) . '</span>' : '');
			break;
		}

		return $html;
	}

	public function set_api_key($api_key, $current_api_key = NULL)
	{
		// Sanitize data from API Key setting input
		
		if (!is_string($api_key) || strlen($api_key) < 10 || strlen($api_key) > 128)
		{
			$api_key = NULL;
		}

		$api_key = preg_replace('/[^0-9a-z_.-]/i', '', $api_key);
		
		if ($current_api_key === NULL)
		{
			$current_api_key = get_option($this->prefix . 'api_key');
		}
		
		if ($current_api_key != $api_key)
		{
			wp_cache_delete('structured_data', $this->class_name);
			wp_cache_delete('google_result', $this->class_name);
			$this->api_key = $api_key;
			
			if ($api_key != NULL)
			{
				update_option($this->prefix . 'force', time() . '/0', 'yes');
			}

			self::log('api_key', $api_key);
		}
		
		update_option($this->prefix . 'api_key', $api_key, 'no');
		
		return $api_key;
	}
	
	public function set_place_id($place_id, $current_place_id = NULL, $current_api_key = NULL)
	{
		// Sanitize data from Place ID setting input

		if (!is_string($place_id) || strlen($place_id) < 10 || strlen($place_id) > 128)
		{
			$place_id = NULL;
		}

		$place_id = preg_replace('/[^0-9a-z_.-]/i', '', $place_id);
		
		if ($current_api_key === NULL)
		{
			$current_api_key = get_option($this->prefix . 'api_key');
		}
		
		if ($current_place_id === NULL)
		{
			$current_place_id = get_option($this->prefix . 'place_id');
		}
		
		if ($current_place_id != $place_id)
		{
			wp_cache_delete('structured_data', $this->class_name);
			wp_cache_delete('google_result', $this->class_name);
			update_option($this->prefix . 'google_result', NULL, 'no');
			update_option($this->prefix . 'structured_data', FALSE, 'yes');
			$this->place_id = $place_id;
			$this->google_data = array();
			$this->google_result = array();
			$this->google_result_valid = FALSE;
			
			if ($place_id != NULL)
			{
				update_option($this->prefix . 'force', time() . '/0', 'yes');
			}
			else
			{
				wp_cache_delete('structured_data', $this->class_name);
				wp_cache_delete('regular', $this->class_name);
				wp_cache_delete('special', $this->class_name);
			}
			
			self::log('place_id', $place_id);
		}
		
		update_option($this->prefix . 'place_id', $place_id, 'no');

		return $place_id;
	}
	
	public function sanitize_separator($text, $trim = NULL)
	{
		// Sanitize data for time separator

		if (!is_string($text) && !is_numeric($text))
		{
			return '';
		}

		$text = wp_strip_all_tags(wp_kses_stripslashes(sanitize_text_field($text)), TRUE);
		
		if (preg_match('/^"(.*)"$/', $text, $m))
		{
			$text = $m[1];
		}

		if (preg_match('/&[0-9a-z]+;/', $text))
		{
			$text = html_entity_decode($text);
		}
		
		if ($trim == 'left')
		{
			$text = ltrim($text);
		}
		elseif ($trim == 'right')
		{
			$text = rtrim($text);
		}
		
		return $text;
	}

	public function sanitize_string($data, $multiline = FALSE)
	{
		// Sanitize string

		if (is_object($data) || is_array($data) || is_null($data))
		{
			return '';
		}
		
		if (is_bool($data))
		{
			return ($data) ? 1 : 0;
		}

		if ($multiline)
		{
			return wp_strip_all_tags(wp_kses_stripslashes(sanitize_textarea_field($data)), FALSE);
		}
		
		return wp_strip_all_tags(wp_kses_stripslashes(sanitize_text_field($data)), TRUE);
	}

	public function sanitize_multiline($data)
	{
		// Sanitize multiline string

		return $this->sanitize_string($data, TRUE);
	}

	public function sanitize_array($data)
	{
		// Sanitize string or array data for weekdays and weekend
		
		if (is_array($data))
		{
			return $this->sanitize_input(array_filter($data));
		}
		
		if (is_string($data))
		{
			$a = preg_split('/,+\s*/i', wp_strip_all_tags(wp_kses_stripslashes(sanitize_text_field($data))));
			$data = array();
			
			foreach ($a as $d)
			{
				$data[] = (preg_match('/^\d+$/', $d)) ? intval($d) : (($d != NULL) ? $d : NULL);
			}
			
			return $data;
		}
		
		return array();
	}

	public function wp_ajax()
	{
		// Handle AJAX requests from Frontend
		
		$type = (isset($_POST['type']) && is_string($_POST['type'])) ? preg_replace('/[^\w_]/', '', strtolower(sanitize_text_field($_POST['type']))) : NULL;
		$seconds_to_change = FALSE;
		$seconds_to_change_day = FALSE;
		$open_now = FALSE;
		$ret = array();

		switch($type)
		{
		case 'update':
			if (!is_array($_POST['elements']) || is_array($_POST['elements']) && empty($_POST['elements']))
			{
				$ret = array(
					'elements' => array(),
					'open_now' => $open_now,
					'closed_now' => !$open_now,
					'success' => FALSE
				);
				break;
			}
			
			$ret = array(
				'elements' => array(),
				'open_now' => $open_now,
				'closed_now' => !$open_now,
				'success' => TRUE
			);
			
			foreach ($_POST['elements'] as $index => $a)
			{
				if (!is_numeric($index) || !is_array($a) || !isset($a['action']) || isset($a['action']) && !is_string($a['action']))
				{
					continue;
				}

				$index = intval($index);
				$parameters = array();
				$content = NULL;
				
				if (strtolower($a['action']) == 'refresh')
				{
					if (is_bool($seconds_to_change_day) && !$seconds_to_change_day)
					{
						list($open_now, $seconds_to_change_day) = $this->open_change(1);
					}
					
					if (isset($a['parameters']) && is_array($a['parameters']) && !empty($a['parameters']))
					{
						foreach ($a['parameters'] as $pk => $p)
						{
							if (!is_string($pk) && !is_numeric($pk) && !is_bool($p) && !is_string($p) && !is_numeric($p))
							{
								continue;
							}
	
							if (preg_match('/^\s.+\s$/', $p))
							{
								$parameters[sanitize_key($pk)] = ' ' . trim(wp_kses_stripslashes(sanitize_text_field($p))) . ' ';
								continue;
							}
							
							if (preg_match('/^\s.+$/', $p))
							{
								$parameters[sanitize_key($pk)] = ' ' . trim(wp_kses_stripslashes(sanitize_text_field($p)));
								continue;
							}
							
							if (preg_match('/^.*\s$/', $p))
							{
								$parameters[sanitize_key($pk)] = trim(wp_kses_stripslashes(sanitize_text_field($p))) . ' ';
								continue;
							}

							$parameters[sanitize_key($pk)] = wp_kses_stripslashes(sanitize_text_field($p));
						}
					}

					$parameters['update'] = TRUE;
					$parameters['outer_tag'] = FALSE;
					
					if (isset($a['content']) && is_string($a['content']) && strlen($a['content']) > 1)
					{
						$content = wp_kses_stripslashes($a['content'], array('div' => array('id' => array(), 'class' => array(), 'style' => array()), 'section' => array('id' => array(), 'class' => array(), 'style' => array()), 'aside' => array('id' => array(), 'class' => array(), 'style' => array()), 'header' => array('id' => array(), 'class' => array(), 'style' => array()), 'footer' => array('id' => array(), 'class' => array(), 'style' => array()), 'span' => array('id' => array(), 'class' => array(), 'style' => array()), 'ul' => array('id' => array(), 'class' => array(), 'style' => array()), 'ol' => array('id' => array(), 'class' => array(), 'style' => array()), 'li' => array('id' => array(), 'class' => array(), 'style' => array()), 'h1' => array('id' => array(), 'class' => array(), 'style' => array()), 'h2' => array('id' => array(), 'class' => array(), 'style' => array()), 'h3' => array('id' => array(), 'class' => array(), 'style' => array()), 'h4' => array('id' => array(), 'class' => array(), 'style' => array()), 'h5' => array('id' => array(), 'class' => array(), 'style' => array()), 'h6' => array('id' => array(), 'class' => array(), 'style' => array()), 'p' => array('id' => array(), 'class' => array(), 'style' => array()), 'br' => array(), 'a' => array('id' => array(), 'class' => array(), 'href' => array(), 'target' => array(), 'rel' => array(), 'title' => array()), 'img' => array('id' => array(), 'class' => array(), 'src' => array(), 'alt' => array(), 'style' => array(), 'srcset' => array()), 'table' => array('id' => array(), 'class' => array(), 'style' => array()), 'tr' => array('id' => array(), 'class' => array(), 'rowspan' => array(), 'style' => array()), 'th' => array('id' => array(), 'class' => array(), 'colspan' => array(), 'style' => array()), 'td' => array('id' => array(), 'class' => array(), 'colspan' => array(), 'style' => array()), 'thead' => array(), 'tbody' => array(), 'tfoot' => array(), 'code' => array(), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'abbr' => array('title' => array())));
						$parameters['shortcodes'] = FALSE;

						if (!isset($a['shortcodes']) && isset($parameters['shortcodes']))
						{
							unset($parameters['shortcodes']);
						}
					}
					
					$ret['elements'][$index] = array(
						'action' => 'refresh',
						'parameters' => $parameters,
						'content' => $content,
						'html' => $this->wp_display($parameters, $content),
						'reload' => (array_key_exists('reload', $parameters) && isset($reload) && (is_bool($reload) && $reload || is_string($reload) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $reload))),
						'seconds_to_change' => $seconds_to_change_day
					);
					
					continue;
				}
				
				if (is_bool($seconds_to_change) && !$seconds_to_change)
				{
					list($open_now, $seconds_to_change) = $this->open_change();
				}
				
				$ret['elements'][$index] = array(
					'action' => 'update',
					'seconds_to_change' => $seconds_to_change
				);
			}
			
			if (is_bool($seconds_to_change) && is_bool($seconds_to_change_day) && !$seconds_to_change && !$seconds_to_change_day)
			{
				list($open_now, $seconds_to_change) = $this->open_change();
			}
			
			$ret['open_now'] = $open_now;
			$ret['closed_now'] = !$open_now;
			
			break;
		default:
			break;
		}
		
		echo json_encode($ret);
		wp_die();

		return;
	}

	public function wp_display($atts = NULL, $content = NULL, $shortcode = NULL)
	{
		// Display HTML from shortcodes
		
		$this->set_localized_dates();
		
		$type_check = 'table';
		$shortcode_defaults = array(
			'class' => NULL,
			'class_strip' => NULL,
			'closed' => NULL,
			'closed_show' => NULL,
			'closure' => NULL,
			'consolidation' => NULL,
			'count' => NULL,
			'day_end' => NULL,
			'day_format' => NULL,
			'day_format_special' => NULL,
			'day_range_min' => NULL,
			'day_range_separator' => NULL,
			'day_range_suffix' => NULL,
			'day_range_suffix_special' => NULL,
			'day_separator' => NULL,
			'day_separator' => NULL,
			'day_separator_last' => NULL,
			'day_suffix' => NULL,
			'day_suffix_special' => NULL,
			'day_suffix_consolidated' => NULL,
			'days' => NULL,
			'end' => NULL,
			'everyday_text' => NULL,
			'errors' => NULL,
			'hours_24' => NULL,
			'id' => NULL,
			'labels' => NULL,
			'labels_precedence' => NULL,
			'midday' => NULL,
			'midnight' => NULL,
			'note_affixes' => NULL,
			'notes' => NULL,
			'outer_tag' => NULL,
			'regular' => NULL,
			'reload' => NULL,
			'shortcodes' => NULL,
			'span_strip' => NULL,
			'special' => NULL,
			'start' => NULL,
			'stylesheet' => NULL,
			'time_format' => NULL,
			'time_group_prefix' => NULL,
			'time_group_separator' => NULL,
			'time_group_suffix' => NULL,
			'time_separator' => NULL,
			'tag' => NULL,
			'today' => NULL,
			'tomorrow' => NULL,
			'type' => NULL,
			'update' => NULL,
			'week_start' => NULL,
			'weekdays_text' => NULL,
			'weekend_text' => NULL
		);
		
		$types = array(
			'br',
			'closed_now',
			'closednow',
			'closed-now',
			'line',
			'lines',
			'list',
			'new_line',
			'new_lines',
			'newline',
			'new-line',
			'newlines',
			'new-lines',
			'now',
			'ol',
			'ol_ol',
			'ol-ol',
			'olol',
			'open_now',
			'opennow',
			'open-now',
			'ordered_list',
			'orderedlist',
			'ordered-list',
			'p',
			'paragraph',
			'paragraphs',
			'sentence',
			'structured',
			'structured_list',
			'structured-list',
			'structuredlist',
			'structured_data',
			'structured-data',
			'structureddata',
			'table',
			'text',
			'ul',
			'ul_ul',
			'ul-ul',
			'ulul',
			'unordered_list',
			'unordered-list',
			'unorderedlist'
		);

		foreach ($types as $t)
		{
			$shortcode_defaults[$t] = 0;
		}
		
		$args = shortcode_atts($shortcode_defaults, $atts);
		
		if (!is_array($atts))
		{
			$atts = array();
		}
	
		if (array_key_exists(0, $atts) && in_array($atts[0], $types))
		{
			$type_check = $atts[0];
		}
		
		foreach ($args as $k => $v)
		{
			if (is_string($v) && (strlen($v) == 0 || $v == 'NULL' || $v == 'null'))
			{
				$args[$k] = NULL;
			}
		}

		extract($args, EXTR_SKIP);
		
		$html = '';		
		$day_preferences = array();
		$time_preferences = array();
		$update_data = array();

		$type = (is_string($type)) ? preg_replace('/[^\w_]/', '_', trim(strtolower($type))) : $type_check;
		$regular = (!array_key_exists('regular', $atts) || array_key_exists('regular', $atts) && (is_bool($regular) && $regular || is_string($regular) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $regular)));
		$special = (!array_key_exists('special', $atts) || array_key_exists('special', $atts) && (is_bool($special) && $special || is_string($special) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $special)));
		$closure = (!array_key_exists('closure', $atts) || array_key_exists('closure', $atts) && (is_bool($closure) && $closure || is_string($closure) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $closure)));
		$id = (is_string($id)) ? preg_replace('/[^\w_-]/', '-', trim($id)) : NULL;
		$class = (is_string($class)) ? preg_replace('/[^\w _-]/', '-', trim(strtolower($class))) : NULL;
		$day_format_key = (is_string($day_format) && array_key_exists($day_format, $this->day_formats)) ? $day_format : NULL;
		$day_format_special_key = (is_string($day_format_special) && array_key_exists($day_format_special, $this->day_formats)) ? $day_format_special : NULL;
		$time_format_key = (is_string($time_format) && array_key_exists($time_format, $this->time_formats)) ? $time_format : ((get_option($this->prefix . 'time_format') != NULL) ? get_option($this->prefix . 'time_format') : '24_colon');
		$time_separator = (is_string($time_separator) && $time_separator != '') ? $time_separator : NULL;
		$time_group_separator = (is_string($time_group_separator) && $time_group_separator != '') ? $time_group_separator : NULL;
		$time_group_prefix = (is_string($time_group_prefix) && $time_group_prefix != '') ? ltrim($time_group_prefix) : NULL;
		$time_group_suffix = (is_string($time_group_suffix) && $time_group_suffix != '') ? rtrim($time_group_suffix) : NULL;
		$consolidation = (array_key_exists('consolidation', $atts) && ($consolidation == NULL || is_string($consolidation) && array_key_exists($consolidation, $this->consolidation_types))) ? (($consolidation == NULL) ? NULL : $consolidation) : get_option($this->prefix . 'consolidation');
		$days = (is_string($days) && preg_match('/^(?:[^,]+,\s*){6}[^,]+$/', $days)) ? preg_split('/,\s*/', $days, 7) : NULL;
		$labels = (!array_key_exists('labels', $atts) || array_key_exists('labels', $atts) && (is_bool($labels) && $labels || is_string($labels) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $labels)));
		$labels_precedence = (array_key_exists('labels_precedence', $atts) && (is_bool($labels_precedence) && $labels_precedence || is_string($labels_precedence) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $labels_precedence)));
		$day_separator = (is_string($day_separator) && $day_separator != '') ? $day_separator : NULL;
		$day_suffix = (array_key_exists('day_suffix', $atts) && is_string($day_suffix)) ? rtrim($day_suffix) : NULL;
		$day_suffix_special = (array_key_exists('day_suffix_special', $atts) && is_string($day_suffix_special)) ? rtrim($day_suffix_special) : NULL;
		$day_suffix_consolidated = (array_key_exists('day_suffix_consolidated', $atts) && is_string($day_suffix_consolidated)) ? rtrim($day_suffix_consolidated) : NULL;
		$day_range_separator = (is_string($day_range_separator) && $day_range_separator != '') ? $day_range_separator : NULL;
		$day_range_suffix = (array_key_exists('day_range_suffix', $atts) && is_string($day_range_suffix)) ? rtrim($day_range_suffix) : get_option($this->prefix . 'day_range_suffix');
		$day_range_suffix_special = (array_key_exists('day_range_suffix_special', $atts) && (is_null($day_range_suffix_special) || is_string($day_range_suffix_special))) ? rtrim($day_range_suffix_special) : get_option($this->prefix . 'day_range_suffix_special');
		$day_range_min = (is_numeric($day_range_min) && $day_range_min >= 2 && $day_range_min <= 31) ? intval($day_range_min) : ((array_key_exists('hours_24', $atts)) ? NULL : $this->day_range_min);
		$weekdays_text = (array_key_exists('weekdays_text', $atts)) ? ((is_string($weekdays_text) && $weekdays_text != '') ? $weekdays_text : NULL) : get_option($this->prefix . 'weekdays_text');
		$weekend_text = (array_key_exists('weekend_text', $atts)) ? ((is_string($weekend_text) && $weekend_text != '') ? $weekend_text : NULL) : get_option($this->prefix . 'weekend_text');
		$everyday_text = (array_key_exists('everyday_text', $atts)) ? ((is_string($everyday_text) && $everyday_text != '') ? $everyday_text : NULL) : get_option($this->prefix . 'everyday_text');
		$today = (array_key_exists('today', $atts) && is_string($today)) ? $today : NULL;
		$tomorrow = (array_key_exists('tomorrow', $atts) && is_string($tomorrow)) ? $tomorrow : NULL;
		$closed = (is_string($closed) && $closed != '') ? $closed : get_option($this->prefix . 'closed_text');
		$closed_show = ((array_key_exists('closed_show', $atts) && (is_null($closed_show) || is_bool($closed_show) && $closed_show || is_string($closed_show) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $closed_show))) || !array_key_exists('closed_show', $atts) && get_option($this->prefix . 'closed_show', TRUE));
		$stylesheet = (is_null($stylesheet) || is_bool($stylesheet) && $stylesheet || is_string($stylesheet) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $stylesheet));
		$midday = (array_key_exists('midday', $atts) && (is_null($midday) || is_string($midday))) ? $midday : get_option($this->prefix . 'midday_text', NULL);
		$midnight = (array_key_exists('midnight', $atts) && (is_null($midnight) || is_string($midnight))) ? $midnight : get_option($this->prefix . 'midnight_text', NULL);
		$hours_24 = (array_key_exists('hours_24', $atts) && (is_null($hours_24) || is_string($hours_24))) ? $hours_24 : NULL;
		$notes = (array_key_exists('notes', $atts) && is_string($notes) && preg_match('/^(?:(?:pre|suf)fix|replace(?: (?:closed|24 hours))?)$/i', $notes)) ? mb_strtolower($notes) : (!array_key_exists('notes', $atts) || array_key_exists('notes', $atts) && (is_null($notes) || is_bool($notes) && $notes || is_string($notes) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $notes)));
		$note_affixes = (is_string($note_affixes) && preg_match('/^(.{0,16})[|](.{0,16})$/', $note_affixes, $m)) ? array($m[1], $m[2]) : NULL;
		$week_start = (is_numeric($week_start) && $week_start < 0 || is_string($week_start) && preg_match('/^(?:today|now|yesterday|-\d+)$/i', $week_start)) ? ((is_numeric($week_start) && $week_start == -2 || is_string($week_start) && preg_match('/^(?:yesterday|-2)$/i', $week_start)) ? $this->yesterday : $this->today) : ((is_numeric($week_start) && $week_start >= 0 && $week_start <= 6) ? intval($week_start) : $this->week_start);
		$start = (is_string($start) && preg_match('#^(\d{4})[ .-/](\d{1,2})[ .-/](\d{1,2})$#', $start, $m)) ? mktime(0, 0, ($this->offset * -1), $m[2], $m[3], $m[1]) : ((is_numeric($start) && $start >= -91 && $start <= 724) ? intval($start) : NULL);
		$end = (is_string($end) && preg_match('#^(\d{4})[ .-/](\d{1,2})[ .-/](\d{1,2})$#', $end, $m)) ? mktime(0, 0, ($this->offset * -1), $m[2], $m[3] + 1, $m[1]) : ((is_numeric($end) && $end >= -7 && $end <= 731) ? intval($end) : NULL);
		$count = (is_numeric($count) && $count >= 1 && $count <= ((!$regular && $special) ? 366 : 31)) ? intval($count) : NULL;
		$update_immediate = (array_key_exists('update', $atts) && is_string($update) && preg_match('/^(?:immediate|instant)(?:ly)?$/i', $update));
		$update = ($update_immediate || array_key_exists('update', $atts) && (is_bool($update) && $update || is_string($update) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $update)));
		$reload = ($update && array_key_exists('reload', $atts) && (is_bool($reload) && $reload || is_string($reload) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $reload)));
		$class_strip = (is_bool($class_strip) && $class_strip || is_string($class_strip) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $class_strip));
		$span_strip = (is_bool($span_strip) && $span_strip || is_string($span_strip) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $span_strip));
		$tag = (array_key_exists('tag', $atts) && is_string($tag) && preg_match('/^(?:span|div|p|section|aside|em|strong|abbr|label|h[123456]|li)$/', trim(strtolower($tag)))) ? preg_replace('/[^0-9a-z]/', '', trim(strtolower($tag))) : NULL;
		$outer_tag = (is_null($outer_tag) || is_bool($outer_tag) && $outer_tag || is_string($outer_tag) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $outer_tag));
		$shortcodes = (is_null($shortcodes) || is_bool($shortcodes) && $shortcodes || is_string($shortcodes) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $shortcodes));
		$errors = (is_bool($errors) && !$errors || is_string($errors) && preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $errors)) ? FALSE : ((defined('WP_DEBUG')) ? WP_DEBUG : FALSE);

		if ($errors && (!array_key_exists($time_format_key, $this->time_formats) || get_option($this->prefix . 'time_format', NULL) == NULL))
		{
			$html = ($errors) ? '<p class="opening-hours error">' . __('Error: Please set <em>We’re Open!</em> preferences in Dashboard→Settings', 'opening-hours') . '</p>' : '';
			
			return $html;
		}
		
		if ($day_format != NULL && $day_format_key == NULL && preg_match($this->accepted_day_format, $day_format))
		{
			$day_format_length = NULL;
		}
		else
		{
			if ($day_format_key == NULL)
			{
				$day_format_key = get_option($this->prefix . 'day_format');
			}
			
			$day_format = $this->day_formats[$day_format_key][1];
			$day_format_length = $this->day_formats[$day_format_key][2];
		}
		
		if ($day_format_special != NULL && $day_format_special_key == NULL && preg_match($this->accepted_day_format, $day_format_special))
		{
			$day_format_special_length = NULL;
			$day_range_suffix_special = $day_range_suffix;
		}
		else
		{
			if ($day_format_special_key == NULL)
			{
				$day_format_special_key = get_option($this->prefix . 'day_format_special');
			}
			
			$day_format_special = ($day_format_special_key != NULL) ? $this->day_formats[$day_format_special_key][1] : $day_format;
			$day_range_suffix_special = ($day_format_special_key != NULL) ? $day_range_suffix_special : $day_range_suffix;
			$day_format_special_length = ($day_format_special_key != NULL) ? $this->day_formats[$day_format_special_key][2] : $day_format_length;
		}
		
		if ($regular != $special || $regular != $closure || $special != $closure)
		{
			$day_preferences['regular'] = $regular;
			$day_preferences['special'] = $special;
			$day_preferences['closure'] = $closure;
		}
		
		if (is_numeric($week_start) && $week_start != $this->week_start)
		{
			$day_preferences['week_start'] = $week_start;
		}
		
		if (is_numeric($start))
		{
			$day_preferences['start'] = $start;
		}
		
		if (is_numeric($end))
		{
			$day_preferences['end'] = $end;
		}
		
		if (is_numeric($count))
		{
			$day_preferences['count'] = $count;
		}
		
		if (!$closed_show)
		{
			$day_preferences['closed_show'] = FALSE;
		}
		
		if ($consolidation != get_option($this->prefix . 'consolidation'))
		{
			$day_preferences['consolidation'] = $consolidation;
		}
		
		if ($day_separator != NULL && $day_separator != get_option($this->prefix . 'day_separator'))
		{
			$day_preferences['day_separator'] = $day_separator;
		}

		if ($day_range_separator != NULL && $day_range_separator != get_option($this->prefix . 'day_range_separator'))
		{
			$day_preferences['day_range_separator'] = $day_range_separator;
		}

		if ($day_range_min != $this->day_range_min)
		{
			$day_preferences['day_range_min'] = $day_range_min;
		}

		if ($weekdays_text != get_option($this->prefix . 'weekdays_text'))
		{
			$day_preferences['weekdays_text'] = $weekdays_text;
		}

		if ($weekend_text != get_option($this->prefix . 'weekend_text'))
		{
			$day_preferences['weekend_text'] = $weekend_text;
		}

		if ($everyday_text != get_option($this->prefix . 'everyday_text'))
		{
			$day_preferences['everyday_text'] = $everyday_text;
		}

		if (array_key_exists('today', $atts))
		{
			$day_preferences['today'] = $today;
		}

		if (array_key_exists('tomorrow', $atts))
		{
			$day_preferences['tomorrow'] = $tomorrow;
		}

		if ($time_format_key != NULL && $time_format_key != get_option($this->prefix . 'time_format'))
		{
			$time_preferences['time_format'] = $time_format_key;
		}

		if ($time_separator != NULL)
		{
			$time_preferences['time_separator'] = $time_separator;
		}

		if ($time_group_separator != NULL)
		{
			$time_preferences['time_group_separator'] = $time_group_separator;
		}

		if ($time_group_prefix != NULL)
		{
			$time_preferences['time_group_prefix'] = $time_group_prefix;
		}

		if ($time_group_suffix != NULL)
		{
			$time_preferences['time_group_suffix'] = $time_group_suffix;
		}

		if ($closed != NULL && $closed != get_option($this->prefix . 'closed_text'))
		{
			$time_preferences['closed'] = $closed;
		}

		if (array_key_exists('midday', $atts))
		{
			$time_preferences['midday'] = $midday;
		}
		
		if (array_key_exists('midnight', $atts))
		{
			$time_preferences['midnight'] = $midnight;
		}

		if (array_key_exists('hours_24', $atts))
		{
			$time_preferences['hours_24'] = $hours_24;
		}

		if (is_string($notes) || is_bool($notes) && !$notes)
		{
			$time_preferences['notes'] = $notes;
		}

		if (is_array($note_affixes))
		{
			$time_preferences['note_affixes'] = $note_affixes;
		}
		
		$this->set($day_preferences);

		if (!is_array($this->data) || empty($this->data))
		{
			$html = ($errors) ? '<' . ((isset($tag) && $tag != NULL) ? $tag : ((preg_match('/^te?xt|sentence$/i', $type)) ? 'span' : 'p')) . ' class="opening-hours error">' . __('Error: No opening hours are available to display', 'opening-hours') . '</' . ((isset($tag) && $tag != NULL) ? $tag : ((preg_match('/^te?xt|sentence$/i', $type)) ? 'span' : 'p')) . '>' : '';
			
			return $html;
		}
		
		if ($update && !$span_strip && !$class_strip)
		{
			list($open_now, $seconds_to_change) = $this->open_change(NULL, FALSE, FALSE);
			
			$update_data = array(
				'open_now' => $open_now,
				'closed_now' => !$open_now,
				'parameters' => $atts,
				'change' => (($seconds_to_change > 0) ? $seconds_to_change : 0),
				'immediate' => $update_immediate,
				'reload' => $reload
			);
		}

		if (!array_key_exists('day_separator', $day_preferences))
		{
			$day_preferences['day_separator'] = $day_separator;
		}

		if (!array_key_exists('day_range_separator', $day_preferences))
		{
			$day_preferences['day_range_separator'] = $day_range_separator;
		}

		if (!array_key_exists('day_suffix', $day_preferences))
		{
			$day_preferences['day_suffix'] = $day_suffix;
		}

		if (!array_key_exists('day_suffix_special', $day_preferences))
		{
			$day_preferences['day_suffix_special'] = $day_suffix_special;
		}

		if (!array_key_exists('day_suffix_consolidated', $day_preferences))
		{
			$day_preferences['day_suffix_consolidated'] = $day_suffix_consolidated;
		}

		if (!array_key_exists('day_range_min', $day_preferences))
		{
			$day_preferences['day_range_min'] = $day_range_min;
		}

		if (is_array($days) && count($days) == 7)
		{
			$day_preferences['days'] = $days;
		}

		if (!$labels)
		{
			$day_preferences['labels'] = $labels;
		}
		
		if ($labels_precedence)
		{
			$day_preferences['labels_precedence'] = $labels_precedence;
		}
		
		switch ($type)
		{
		case 'schema':
		case 'structured-data':
		case 'structured_data':
			$html = '<pre>' . $this->structured_data('html') . '</pre>';
			break;
		case 'now':
		case 'opennow':
		case 'open_now':
		case 'open-now':
		case 'closednow':
		case 'closed_now':
		case 'closed-now':
			if ($content != NULL)
			{
				return $this->wp_display_open_now($atts, $content, !preg_match('/closed/i', $type));
			}
			$html = '';
			break;
		case 'txt':
		case 'text':
		case 'sentence':
			if ($content != NULL)
			{
				if (!$outer_tag || !$update_immediate && !$update)
				{
					return $this->wp_display_text($content, $time_preferences, $shortcodes);
				}
				
				$update_data['content'] = $content;

				$html = $this->wp_display_text($content, $time_preferences, FALSE);
				
				if ($tag == NULL)
				{
					$tag = ($this->phrasing_content($html)) ? 'span' : 'div';
				}

				return '<'
					. $tag . (($id != NULL) ? ' id="' . esc_attr($id) . '"' : '')
					. ' class="opening-hours open-text'
					. (($class != NULL) ? ' ' . $class : '')
					. ' update' . (($reload) ? ' reload' : '') . (($open_now) ? ' open-now' : ' closed-now') . '"'
					. ' data-data="' . esc_attr(json_encode($update_data)) . '"'
					. '>' . $html . '</' . $tag . '>';
			}
			
			/* translators: The characters used to separate each days and their opening hours in a list (sometimes these items contain a comma), so usually a semi-colon with a space */
			$day_separator = (is_string($day_separator) && $day_separator != '') ? $day_separator : __('; ', 'opening-hours');
			$day_separator_last = (is_string($day_separator_last) && $day_separator_last != '') ? $day_separator_last : $day_separator;
			/* translators: The character to use at the end of a sentence, usually just a period/full-stop */
			$day_end = (array_key_exists('day_end', $atts) && (is_null($day_end) || is_string($day_end))) ? $day_end : __('.', 'opening-hours');
			$first = TRUE;
			$text = array();

			if (preg_match('/^([^|]+)\|([^|]+)$/', $day_separator, $m))
			{
				$day_separator = $m[1];
				$day_separator_last = $m[2];
			}	
			else
			{
				$day_separator_last = $day_separator;
			}
			
			if ($outer_tag)
			{
				$html .= '<' . (($tag != NULL) ? $tag : 'span')
				. (($id != NULL) ? ' id="' . esc_attr($id) . '"' : '')
				. ' class="opening-hours'
				. (($class != NULL) ? ' ' . $class : '')
				. (($update) ? ' update' . (($reload) ? ' reload' : '') . (($open_now) ? ' open-now' : ' closed-now') : '') . '"'
				. (($update) ? ' data-data="' . (($update_data != NULL) ? esc_attr(json_encode($update_data)) : '') . '"' : '')
				. '>
';
			}

			foreach ($this->data as $timestamp => $a)
			{
				$day = $a['day'];
				$day_name = $this->days[$day];
				$day_alias = preg_replace('/[^0-9a-z-]/', '-', strtolower($day_name));
				$special = $a['special'];
				$count = $a['count'];
				$today = $a['today'];
				$tomorrow = $a['tomorrow'];
				$future = $a['future'];
				$closed = $a['closed'];
				$hours_24 = $a['hours_24'];
				$hours = (is_array($a['hours']) && !$closed && !$hours_24) ? $a['hours'] : array();
				$note = (isset($a['note'])) ? $a['note'] : NULL;
				$consolidated = $a['consolidated'];
				$consolidated_first = $a['consolidated_first'];
				
				if ($consolidation != NULL && is_array($consolidated) && !$consolidated_first)
				{
					continue;
				}
				
				if ($first)
				{
					$text[] = '<span class="day-name' . (($labels && $a['label'] != NULL) ? ' label' : '') . '">' . $this->sentence_case(($special) ? $this->day_string($a, $day_format_special, $day_range_suffix_special, $day_format_special_length, 'html', $day_preferences) : $this->day_string($a, $day_format, $day_range_suffix, $day_format_length, 'html', $day_preferences), FALSE, FALSE) . '</span> '
					. '<span class="hours' . (($closed) ? ' closed' : (($hours_24) ? ' hours-24' : ((count($hours) > 1) ? ' group-' . count($hours) : ''))) . ((($note != NULL && (is_bool($notes) && $notes || is_string($notes))) ? ' note' . ((($notes === 'replace' || $closed && $notes === 'replace closed' || $hours_24 && $notes === 'replace 24 hours')) ? '-only' : (($notes === 'prefix') ? '-prefix' : '-suffix')) : '')) . '">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</span>';
					$first = FALSE;
					continue;
				}
				
				$text[] = '<span class="day-name' . (($labels && $a['label'] != NULL) ? ' label' : '') . '">' . (($special) ? $this->day_string($a, $day_format_special, $day_range_suffix_special, $day_format_special_length, 'html', $day_preferences) : $this->day_string($a, $day_format, $day_range_suffix, $day_format_length, 'html', $day_preferences)) . '</span> '
				. '<span class="hours' . (($closed) ? ' closed' : (($hours_24) ? ' hours-24' : ((count($hours) > 1) ? ' group-' . count($hours) : ''))) . ((($note != NULL && (is_bool($notes) && $notes || is_string($notes))) ? ' note' . ((($notes === 'replace' || $closed && $notes === 'replace closed' || $hours_24 && $notes === 'replace 24 hours')) ? '-only' : (($notes === 'prefix') ? '-prefix' : '-suffix')) : '')) . '">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</span>';
			}
			
			if (count($text) > 2)
			{
				$text_last = array_pop($text);
				$html .= implode($day_separator, $text) . $day_separator_last . $text_last . $day_end;
			}
			else
			{
				$html .= implode($day_separator_last, $text) . $day_end;
			}
			
			if ($outer_tag)
			{
				$html .= '</' . (($tag != NULL) ? $tag : 'span') . '>
';
			}
			break;
		case 'br':
		case 'line':
		case 'lines':
		case 'newline':
		case 'newlines':
		case 'new-line':
		case 'new-lines':
		case 'new_line':
		case 'new_lines':
			if ($outer_tag)
			{
				$html = '<' . (($tag != NULL) ? $tag : 'p')
				. (($id != NULL) ? ' id="' . esc_attr($id) . '"' : '')
				. ' class="opening-hours'
				. (($class != NULL) ? ' ' . $class : '')
				. (($update) ? ' update' . (($reload) ? ' reload' : '') . (($open_now) ? ' open-now' : ' closed-now') : '') . '"'
				. (($update) ? ' data-data="' . (($update_data != NULL) ? esc_attr(json_encode($update_data)) : '') . '"' : '')
				. '>
';
			}
			
			foreach ($this->data as $timestamp => $a)
			{
				$day = $a['day'];
				$day_name = $this->days[$day];
				$special = $a['special'];
				$count = $a['count'];
				$closed = $a['closed'];
				$hours_24 = $a['hours_24'];
				$hours = (is_array($a['hours']) && !$closed && !$hours_24) ? $a['hours'] : array();
				$note = (isset($a['note'])) ? $a['note'] : NULL;
				$consolidated = $a['consolidated'];
				$consolidated_first = $a['consolidated_first'];
				
				if ($consolidation != NULL && is_array($consolidated) && !$consolidated_first)
				{
					continue;
				}
				
				$html .= '	<span class="day-name' . (($labels && $a['label'] != NULL) ? ' label' : '') . '">' . $this->sentence_case(($special) ? $this->day_string($a, $day_format_special, $day_range_suffix_special, $day_format_special_length, 'html', $day_preferences) : $this->day_string($a, $day_format, $day_range_suffix, $day_format_length, 'html', $day_preferences), FALSE, FALSE) . '</span> <span class="hours">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</span>' . (($count < (count($this->data) - 1)) ? '<br>' . PHP_EOL : PHP_EOL);
			}
			
			if ($outer_tag)
			{
				$html .= '</' . (($tag != NULL) ? $tag : 'p') . '>
';
			}
			break;
		case 'list':
		case 'ol':
		case 'ol_ol':
		case 'ol-ol':
		case 'orderedlist':
		case 'ordered_list':
		case 'ordered-list':
		case 'p':
		case 'paragraph':
		case 'paragraphs':
		case 'ul':
		case 'ul_ul':
		case 'ul-ul':
		case 'ulul':
		case 'unorderedlist':
		case 'unordered_list':
		case 'unordered-list':
		case 'structured':
		case 'structuredlist':
		case 'structured_list':
		case 'structured-list':
			$structured = (preg_match('/^(?:(?:[ou]l[_-]?){2}|structured.*)$/i', $type));

			if ($outer_tag)
			{
				$outer_tag = (preg_match('/^(?:[lou].+|structured.*)$/i', $type)) ? ((preg_match('/^(?:[lu].+|structured.*)$/i', $type)) ? 'ul' : 'ol') : 'div';
	
				$html = '<' . $outer_tag
				. (($id != NULL) ? ' id="' . esc_attr($id) . '"' : '')
				. ' class="opening-hours'
				. (($class != NULL) ? ' ' . $class : '')
				. (($update) ? ' update' . (($reload) ? ' reload' : '') . (($open_now) ? ' open-now' : ' closed-now') : '') . '"'
				. (($update) ? ' data-data="' . (($update_data != NULL) ? esc_attr(json_encode($update_data)) : '') . '"' : '')
				. '>
';
			}
			
			$inner_tag = (preg_match('/^(?:[lou].+|structured.*)$/i', $type)) ? 'li' : 'p';
			
			foreach ($this->data as $timestamp => $a)
			{
				$day = $a['day'];
				$day_name = $this->days[$day];
				$day_alias = preg_replace('/[^0-9a-z-]/', '-', strtolower($day_name));
				$special = $a['special'];
				$count = $a['count'];
				$today = $a['today'];
				$tomorrow = $a['tomorrow'];
				$weekday = $a['weekday'];
				$weekend = $a['weekend'];
				$past = $a['past'];
				$future = $a['future'];
				$closed = $a['closed'];
				$hours_24 = $a['hours_24'];
				$hours = (is_array($a['hours']) && !$closed && !$hours_24) ? $a['hours'] : array();
				$note = (isset($a['note'])) ? $a['note'] : NULL;
				$consolidated = $a['consolidated'];
				$consolidated_first = $a['consolidated_first'];
				
				if ($consolidation != NULL && is_array($consolidated) && !$consolidated_first)
				{
					continue;
				}
				
				$html .= '	<' . $inner_tag . ' class="day '
				. esc_attr($day_alias)
				. (($special) ? ' special' : '')
				. (($labels && $a['label'] != NULL) ? ' label' : '')
				. (($today) ? ' today' : (($tomorrow) ? ' tomorrow' : ''))
				. (($future) ? ' future' : (($past) ? ' past' : ''))
				. (($weekday) ? ' weekday' : (($weekend) ? ' weekend' : ''))
				. (($closed) ? ' closed' : (($hours_24) ? ' hours-24' : ((count($hours) > 1) ? ' group-' . count($hours) : '')))
				. ((($note != NULL && (is_bool($notes) && $notes || is_string($notes))) ? ' note' . ((($notes === 'replace' || $closed && $notes === 'replace closed' || $hours_24 && $notes === 'replace 24 hours')) ? '-only' : (($notes === 'prefix') ? '-prefix' : '-suffix')) : ''))
				. '">'
				. '<span class="day-name">' . $this->sentence_case(($special) ? $this->day_string($a, $day_format_special, $day_range_suffix_special, $day_format_special_length, 'html', $day_preferences) : $this->day_string($a, $day_format, $day_range_suffix, $day_format_length, 'html', $day_preferences), FALSE, FALSE) . '</span>';

				if ($structured)
				{
					$html .= '		<' . $outer_tag . ' class="hours">
';

					if ($closed || $hours_24 || count($hours) <= 1)
					{
						$html .= '			<' . $inner_tag . ' class="hours">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</' . $inner_tag . '>
';
					}
					else
					{
						foreach ($hours as $hv)
						{
							$html .= '			<' . $inner_tag . ' class="hours">' . $this->hours_string(array($hv), $closed, $hours_24, $note, 'html', $time_preferences) . '</' . $inner_tag . '>
';
						}
					}

					$html .= '		</' . $outer_tag . '>
';
				}
				else
				{
					$html .= ' <span class="hours">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</span>';
				}

				$html .= '</' . $inner_tag . '>
';
			}

			if (is_string($outer_tag))
			{
				$html .= '</' . $outer_tag . '>
';
			}
			break;
		case 'html':
		case 'open_hours':
		case 'opening_hours':
		case 'open_hours_html':
		case 'opening_hours_html':
		default:
			if ($content != NULL)
			{
				if (!$outer_tag || !$update_immediate && !$update)
				{
					return $this->wp_display_text($content, $time_preferences, $shortcodes);
				}
				
				$update_data['content'] = $content;

				$html = $this->wp_display_text($content, $time_preferences, FALSE);
				
				if ($tag == NULL)
				{
					$tag = ($this->phrasing_content($html)) ? 'span' : 'div';
				}
				
				return '<'
					. $tag . (($id != NULL) ? ' id="' . esc_attr($id) . '"' : '')
					. ' class="opening-hours open-text'
					. (($class != NULL) ? ' ' . $class : '')
					. ' update' . (($reload) ? ' reload' : '') . (($open_now) ? ' open-now' : ' closed-now') . '"'
					. ' data-data="' . esc_attr(json_encode($update_data)) . '"'
					. '>' . $html . '</' . $tag . '>';
			}
			
			if ($outer_tag)
			{
				$html = '<table'
				. (($id != NULL) ? ' id="' . esc_attr($id) . '"' : '')
				. ' class="opening-hours'
				. (($class != NULL) ? ' ' . $class : '')
				. (($update) ? ' update' . (($reload) ? ' reload' : '') . (($open_now) ? ' open-now' : ' closed-now') : '') . '"'
				. (($update) ? ' data-data="' . (($update_data != NULL) ? esc_attr(json_encode($update_data)) : '') . '"' : '')
				. '>
';
			}
			
			foreach ($this->data as $timestamp => $a)
			{
				$day = $a['day'];
				$day_name = $this->days[$day];
				$day_alias = preg_replace('/[^0-9a-z-]/', '-', strtolower($day_name));
				$special = $a['special'];
				$count = $a['count'];
				$today = $a['today'];
				$tomorrow = $a['tomorrow'];
				$weekday = $a['weekday'];
				$weekend = $a['weekend'];
				$past = $a['past'];
				$future = $a['future'];
				$closed = $a['closed'];
				$hours_24 = $a['hours_24'];
				$hours = (is_array($a['hours']) && !$closed && !$hours_24) ? $a['hours'] : array();
				$note = (isset($a['note'])) ? $a['note'] : NULL;
				$consolidated = $a['consolidated'];
				$consolidated_first = $a['consolidated_first'];

				if ($consolidation != NULL && is_array($consolidated) && !$consolidated_first)
				{
					continue;
				}
				
				$html .= '	<tr class="day '
				. esc_attr($day_alias)
				. (($special) ? ' special' : '')
				. (($labels && $a['label'] != NULL) ? ' label' : '')
				. (($today) ? ' today' : (($tomorrow) ? ' tomorrow' : ''))
				. (($future) ? ' future' : (($past) ? ' past' : ''))
				. (($weekday) ? ' weekday' : (($weekend) ? ' weekend' : ''))
				. (($closed) ? ' closed' : (($hours_24) ? ' hours-24' : ((count($hours) > 1) ? ' group-' . count($hours) : '')))
				. (($note != NULL && (is_bool($notes) && $notes || is_string($notes))) ? ' note' . ((($notes === 'replace' || $closed && $notes === 'replace closed' || $hours_24 && $notes === 'replace 24 hours')) ? '-only' : (($notes === 'prefix') ? '-prefix' : '-suffix')) : '')
				. '">
		<th class="day-name">' . $this->sentence_case(($special) ? $this->day_string($a, $day_format_special, $day_range_suffix_special, $day_format_special_length, 'html', $day_preferences) : $this->day_string($a, $day_format, $day_range_suffix, $day_format_length, 'html', $day_preferences), FALSE, FALSE) . '</th>
		<td class="hours">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</td>
	</tr>
';
			}
			
			if ($outer_tag)
			{
				$html .= '</table>
';
			}
			
			break;
		}
		
		if ($span_strip && $class_strip)
		{
			$html = preg_replace('#</?span[^>]*>|\s+class=["\'][^"\'>]*["\']#i', '', $html);
		}
		elseif ($span_strip)
		{
			$html = preg_replace('#</?span[^>]*>#i', '', $html);
		}
		elseif ($class_strip)
		{
			$html = preg_replace('/\s+class=["\'][^"\'>]*["\']/i', '', $html);
		}

		return $html;
	}
	
	public function wp_display_open_now($atts = NULL, $content = NULL, $shortcode = NULL, $open = TRUE)
	{
		// Display conditional content based on open or closed now
		
		$this->set_localized_dates();
		
		$shortcode_defaults = array(
			'tag' => NULL,
			'id' => NULL,
			'class' => NULL,
			'class_strip' => NULL,
			'update' => NULL,
			'reload' => NULL,
			'hide' => NULL,
			'remove_html' => NULL,
			'shortcodes' => NULL
		);
		$data = NULL;
		$open = (boolean)$open;
		$args = shortcode_atts($shortcode_defaults, $atts);
		list($open_now, $seconds_to_change) = $this->open_change();
		
		if (!is_array($atts))
		{
			$atts = array();
		}

		foreach ($args as $k => $v)
		{
			if (is_string($v) && (strlen($v) == 0 || $v == 'NULL' || $v == 'null'))
			{
				$args[$k] = NULL;
			}
		}

		extract($args, EXTR_SKIP);
		
		$shortcodes = (is_null($shortcodes) || is_bool($shortcodes) && $shortcodes || is_string($shortcodes) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $shortcodes));
		
		if ($shortcodes && preg_match('#\[/?[a-z][^[\]]+\]#i', $content))
		{
			$content = do_shortcode($content);
		}
		
		$update_immediate = (array_key_exists('update', $atts) && is_string($update) && preg_match('/^(?:immediate|instant)(?:ly)?$/i', $update));
		$update = ($update_immediate || !array_key_exists('update', $atts) || array_key_exists('update', $atts) && (is_bool($update) && $update || is_string($update) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $update)));
		$reload = ($update && array_key_exists('reload', $atts) && (is_null($reload) || is_bool($reload) && $reload || is_string($reload) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $reload)));
		$hide = (is_null($hide) || is_bool($hide) && $hide || is_string($hide) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|show)$/i', $hide));
		$remove_html = (is_null($remove_html) || is_bool($remove_html) && $remove_html || is_string($remove_html) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off)$/i', $remove_html));
		$tag = (array_key_exists('tag', $atts) && is_string($tag) && preg_match('/^(?:span|div|p|section|aside|em|strong|abbr|label|h[123456]|li)$/', trim(strtolower($tag)))) ? preg_replace('/[^0-9a-z]/', '', trim(strtolower($tag))) : ((!array_key_exists('tag', $atts) && (!array_key_exists('update', $atts) || array_key_exists('update', $atts) && $update)) ? (($this->phrasing_content($content)) ? 'span' : 'div') : NULL);
		$id = (is_string($id)) ? preg_replace('/[^\w_-]/', '-', trim($id)) : NULL;
		$class = (is_string($class)) ? preg_replace('/[^\w _-]/', '-', trim(strtolower($class))) : NULL;
		$class_strip = (is_bool($class_strip) && $class_strip || is_string($class_strip) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $class_strip));
		
		if ($tag != NULL)
		{
			$class = 'opening-hours-conditional'
			. (($class != NULL) ? ' ' . $class : '')
			. (($open) ? ' open' : ' closed')
			. (($hide) ? (($open == $open_now) ? ' show' : ' hide') : '')
			. (($update) ? ' update' : '')
			. (($reload) ? ' reload' : '');
			
			$data = array(
				'open' => $open,
				'open_now' => $open_now,
				'closed' => !$open,
				'closed_now' => !$open_now,
				'hide' => $hide,
				'remove_html' => $remove_html,
				'change' => (($seconds_to_change > 0) ? $seconds_to_change : 0),
				'reload' => $reload,
				'immediate' => $update_immediate,
				'html' => ($remove_html && $open != $open_now) ? $content : NULL
			);
		}
		
		$html = ($tag != NULL) ? '<' . $tag
		. (($id != NULL) ? ' id="' . esc_attr($id) . '"' : '')
		. ((!$class_strip && $class != NULL) ? ' class="' . esc_attr($class) . '"' : '')
		. ' data-data="' . (($data != NULL) ? esc_attr(json_encode($data)) : '') . '"'
		. '>' : '';
		
		if (!$remove_html || $open == $open_now)
		{
			$html .= $content;
		}

		$html .= (($tag != NULL) ? '</' . $tag . '>' : '');
		return $html;
	}
	
	public function wp_display_closed_now($atts = NULL, $content = NULL, $shortcode = NULL, $closed = TRUE)
	{
		// Display conditional content based on closed now
		
		return $this->wp_display_open_now($atts, $content, $shortcode, FALSE);
	}
	
	public function wp_display_open_special($atts = NULL, $content = NULL, $shortcode = NULL)
	{
		// Display conditional content based upcoming special opening hours
		
		$this->set_localized_dates();

		$html = '';
		$special = ($shortcode == NULL || is_string($shortcode) && !preg_match('/[\b_]not[\b_]/i', $shortcode));
		$shortcode_defaults = array(
			'class' => NULL,
			'class_strip' => NULL,
			'count' => NULL,
			'end' => NULL,
			'empty' => NULL,
			'hide' => NULL,
			'id' => NULL,
			'shortcodes' => NULL,
			'start' => NULL,
			'tag' => NULL,
			'week_start' => NULL
		);
		$day_preferences = array(
			'regular' => FALSE,
			'special' => TRUE
		);	
		$args = shortcode_atts($shortcode_defaults, $atts);
		
		if (!is_array($atts))
		{
			$atts = array();
		}

		foreach ($args as $k => $v)
		{
			if (is_string($v) && (strlen($v) == 0 || $v == 'NULL' || $v == 'null'))
			{
				$args[$k] = NULL;
			}
		}

		extract($args, EXTR_SKIP);
		
		$start = (is_string($start) && preg_match('#^(\d{4})[ .-/](\d{1,2})[ .-/](\d{1,2})$#', $start, $m)) ? mktime(0, 0, ($this->offset * -1), $m[2], $m[3], $m[1]) : ((is_numeric($start) && $start >= -91 && $start <= 724) ? intval($start) : NULL);
		$end = (is_string($end) && preg_match('#^(\d{4})[ .-/](\d{1,2})[ .-/](\d{1,2})$#', $end, $m)) ? mktime(0, 0, ($this->offset * -1), $m[2], $m[3] + 1, $m[1]) : ((is_numeric($end) && $end >= -7 && $end <= 731) ? intval($end) : NULL);
		$count = (is_numeric($count) && $count >= 1 && $count <= 366) ? intval($count) : NULL;
		$week_start = (is_numeric($week_start) && $week_start < 0 || is_string($week_start) && preg_match('/^(?:today|now|yesterday|-\d+)$/i', $week_start)) ? ((is_numeric($week_start) && $week_start == -2 || is_string($week_start) && preg_match('/^(?:yesterday|-2)$/i', $week_start)) ? $this->yesterday : $this->today) : ((is_numeric($week_start) && $week_start >= 0 && $week_start <= 6) ? intval($week_start) : $this->week_start);		
		$empty = (is_bool($empty) && $empty || is_string($empty) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $empty));
		$tag = (array_key_exists('tag', $atts) && is_string($tag) && preg_match('/^(?:span|div|p|section|aside|em|strong|abbr|label|h[123456]|li)$/', trim(strtolower($tag)))) ? preg_replace('/[^0-9a-z]/', '', trim(strtolower($tag))) : ((!array_key_exists('tag', $atts) && (!array_key_exists('update', $atts) || array_key_exists('update', $atts) && $update)) ? (($this->phrasing_content($content)) ? 'span' : 'div') : NULL);
		
		if (is_numeric($start))
		{
			$day_preferences['start'] = $start;
		}
		
		if (is_numeric($end))
		{
			$day_preferences['end'] = $end;
		}
		
		if (is_numeric($count))
		{
			$day_preferences['count'] = $count;
		}

		if (is_numeric($week_start) && $week_start != $this->week_start)
		{
			$day_preferences['week_start'] = $week_start;
		}

		$this->set($day_preferences);
		
		if ((!$empty || $tag == NULL) && $special == empty($this->data))
		{
			return $html;
		}
		
		$shortcodes = (is_null($shortcodes) || is_bool($shortcodes) && $shortcodes || is_string($shortcodes) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $shortcodes));

		if ($empty && $tag != NULL && empty($this->data))
		{
			$content = '';
		}
		elseif ($shortcodes && preg_match('#\[/?[a-z][^[\]]+\]#i', $content))
		{
			$content = do_shortcode($content);
		}

		$id = (is_string($id)) ? preg_replace('/[^\w_-]/', '-', trim($id)) : NULL;
		$class = (is_string($class)) ? preg_replace('/[^\w _-]/', '-', trim(strtolower($class))) : NULL;
		$class_strip = (is_bool($class_strip) && $class_strip || is_string($class_strip) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $class_strip));
		$hide = (is_null($hide) || is_bool($hide) && $hide || is_string($hide) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|show)$/i', $hide));

		if ($tag != NULL)
		{
			$class = 'opening-hours-conditional special'
			. (($class != NULL) ? ' ' . $class : '')
			. (($empty && $content == NULL) ? ' empty' : '')
			. (($hide && $content == NULL) ? ' hide' : '');
		}
		
		$html = (($tag != NULL) ? '<' . $tag
		. (($id != NULL) ? ' id="' . esc_attr($id) . '"' : '')
		. ((!$class_strip && $class != NULL) ? ' class="' . esc_attr($class) . '"' : '')
		. '>' : '')
		. $content
		. (($tag != NULL) ? '</' . $tag . '>' : '');
		
		return $html;
	}
	
	private function wp_display_text($content, $time_preferences = NULL, $shortcodes = TRUE)
	{
		// Display text with replacement codes for logic and variables
		
		if ($shortcodes && preg_match('#\[/?[a-z][^[\]]+\]#i', $content))
		{
			$content = do_shortcode($content);
		}
		
		if (!preg_match('/%[a-z _]+[ :]?%/i', $content) || !preg_match_all('/(?:(%[a-z][1-3a-z _]{1,30}[ :]?(?:[{][^}%]{1,200}[}])?%)|([^%]*[^%\s]+[^%]*))/i', $content, $match))
		{
			return $content;
		}

		$this->set_localized_dates();
		
		$text = array();
		$logic_variables = array();
		$logic_parameters = array();
		$if_open_now = -1;
		$if_closed_now = -1;
		$if_open_today = -1;
		$if_closed_today = -1;
		$if_open_later = -1;
		$if_not_open_later = -1;
		$if_open_tomorrow = -1;
		$if_closed_tomorrow = -1;
		$if_hours_24_today = -1;
		$if_not_hours_24_today = -1;
		$if_hours_24_tomorrow = -1;
		$if_not_hours_24_tomorrow = -1;
		$if_open_today_1 = -1;
		$if_not_open_today_1 = -1;
		$if_open_today_2 = -1;
		$if_not_open_today_2 = -1;
		$if_open_today_3 = -1;
		$if_not_open_today_3 = -1;
		$if_open_tomorrow_1 = -1;
		$if_not_open_tomorrow_1 = -1;
		$if_open_tomorrow_2 = -1;
		$if_not_open_tomorrow_2 = -1;
		$if_open_tomorrow_3 = -1;
		$if_not_open_tomorrow_3 = -1;
		$if_note_today = -1;
		$if_not_note_today = -1;
		$if_note_tomorrow = -1;
		$if_not_note_tomorrow = -1;
		$if_regular_today = -1;
		$if_not_regular_today = -1;
		$if_special_today = -1;
		$if_not_special_today = -1;
		$if_closure_today = -1;
		$if_not_closure_today = -1;
		$if_regular_tomorrow = -1;
		$if_not_regular_tomorrow = -1;
		$if_special_tomorrow = -1;
		$if_not_special_tomorrow = -1;
		$if_closure_tomorrow = -1;
		$if_not_closure_tomorrow = -1;
		$if_closure_exists = -1;
		$if_not_closure_exists = -1;
		$now = $this->hours_string(array(array(wp_date("H:i", $this->current_timestamp), '00:00')), FALSE, FALSE, NULL, 'start', $time_preferences);
		$today_closed = NULL;
		$today_hours_24 = NULL;
		$today_hours = NULL;
		$today_end = NULL;
		$today_text = NULL;
		$today_start_text = NULL;
		$today_end_text = NULL;
		$today_next_text = NULL;
		$today_1_text = NULL;
		$today_2_text = NULL;
		$today_3_text = NULL;
		$today_name = NULL;
		$today_note = NULL;
		$today_hours_type = NULL;
		$tomorrow_closed = NULL;
		$tomorrow_hours_24 = NULL;
		$tomorrow_hours = NULL;
		$tomorrow_start = NULL;
		$tomorrow_text = NULL;
		$tomorrow_start_text = NULL;
		$tomorrow_end_text = NULL;
		$tomorrow_1_text = NULL;
		$tomorrow_2_text = NULL;
		$tomorrow_3_text = NULL;
		$tomorrow_name = NULL;
		$tomorrow_note = NULL;
		$tomorrow_hours_type = NULL;
		
		list($open_now, $seconds_to_change) = $this->open_change();
		$closed_now = !$open_now;
		$open_later = NULL;
		
		foreach (array_keys($this->days) as $d)
		{
			if ($this->today != $d && $this->tomorrow != $d)
			{
				continue;
			}

			if ($this->today == $d)
			{
				$today_hours_type = (!empty($this->closure) && $this->today_timestamp >= $this->closure['start'] && $this->today_timestamp < $this->closure['end']) ? 'closure' : ((is_array($this->special) && array_key_exists($this->today_timestamp, $this->special)) ? 'special' : NULL);
				$a = ($today_hours_type == 'closure') ? array('closed' => TRUE) : (($today_hours_type == 'special') ? $this->special[$this->today_timestamp] : ((isset($this->regular[$d])) ? $this->regular[$d] : array()));
				$today_closed = (empty($a) || !empty($a) && isset($a['closed']) && $a['closed']);
				$today_hours_24 = (!$today_closed && isset($a['hours_24']) && $a['hours_24']);
				$today_hours = (!$today_closed && isset($a['hours']) && is_array($a['hours'])) ? $a['hours'] : array();
				$today_text = $this->hours_string($today_hours, $today_closed, $today_hours_24, NULL, 'text', $time_preferences);
				$today_start_text = $this->hours_string($today_hours, $today_closed, $today_hours_24, NULL, 'start', $time_preferences);
				$today_end_text = $this->hours_string($today_hours, $today_closed, $today_hours_24, NULL, 'end', $time_preferences);
				$today_name = $this->days[$d];
				$open_later = (!$today_closed && !$open_now && $this->current_timestamp + $seconds_to_change < $this->tomorrow_timestamp);
				$today_next_text = $this->hours_string($today_hours, (!$open_now && ($today_closed || !$open_later)), $today_hours_24, NULL, 'next', $time_preferences);
				$today_1_text = (!$today_closed && !$today_hours_24 && count($today_hours) >= 1) ? $this->hours_string(array($today_hours[0]), $today_closed, $today_hours_24, NULL, 'text', $time_preferences) : NULL;
				$today_2_text = (!$today_closed && !$today_hours_24 && count($today_hours) >= 2) ? $this->hours_string(array($today_hours[1]), $today_closed, $today_hours_24, NULL, 'text', $time_preferences) : NULL;
				$today_3_text = (!$today_closed && !$today_hours_24 && count($today_hours) >= 3) ? $this->hours_string(array($today_hours[2]), $today_closed, $today_hours_24, NULL, 'text', $time_preferences) : NULL;
				$today_note = ($today_hours_type == 'special' && isset($this->special[$this->today_timestamp]) && isset($this->special[$this->today_timestamp]['note'])) ? $this->special[$this->today_timestamp]['note'] : NULL;
				continue;
			}

			$tomorrow_hours_type = (!empty($this->closure) && $this->tomorrow_timestamp >= $this->closure['start'] && $this->tomorrow_timestamp < $this->closure['end']) ? 'closure' : ((is_array($this->special) && array_key_exists($this->tomorrow_timestamp, $this->special)) ? 'special' : NULL);
			$a = ($tomorrow_hours_type == 'closure') ? array('closed' => TRUE) : (($tomorrow_hours_type == 'special') ? $this->special[$this->tomorrow_timestamp] : ((isset($this->regular[$d])) ? $this->regular[$d] : array()));
			$tomorrow_closed = (empty($a) || !empty($a) && isset($a['closed']) && $a['closed']);
			$tomorrow_hours_24 = (!$tomorrow_closed && isset($a['hours_24']) && $a['hours_24']);
			$tomorrow_hours = (!$tomorrow_closed && isset($a['hours']) && is_array($a['hours'])) ? $a['hours'] : array();
			$tomorrow_text = $this->hours_string($tomorrow_hours, $tomorrow_closed, $tomorrow_hours_24, NULL, 'text', $time_preferences);
			$tomorrow_start_text = $this->hours_string($tomorrow_hours, $tomorrow_closed, $tomorrow_hours_24, NULL, 'start', $time_preferences);
			$tomorrow_end_text = $this->hours_string($tomorrow_hours, $tomorrow_closed, $tomorrow_hours_24, NULL, 'end', $time_preferences);
			$tomorrow_name = $this->days[$d];
			$tomorrow_1_text = (!$tomorrow_closed && !$tomorrow_hours_24 && count($tomorrow_hours) >= 1) ? $this->hours_string(array($tomorrow_hours[0]), $tomorrow_closed, $tomorrow_hours_24, NULL, 'text', $time_preferences) : NULL;
			$tomorrow_2_text = (!$tomorrow_closed && !$tomorrow_hours_24 && count($tomorrow_hours) >= 2) ? $this->hours_string(array($tomorrow_hours[1]), $tomorrow_closed, $tomorrow_hours_24, NULL, 'text', $time_preferences) : NULL;
			$tomorrow_3_text = (!$tomorrow_closed && !$tomorrow_hours_24 && count($tomorrow_hours) >= 3) ? $this->hours_string(array($tomorrow_hours[2]), $tomorrow_closed, $tomorrow_hours_24, NULL, 'text', $time_preferences) : NULL;
			$tomorrow_note = ($tomorrow_hours_type == 'special' && isset($this->special[$this->tomorrow_timestamp]) && isset($this->special[$this->tomorrow_timestamp]['note'])) ? $this->special[$this->tomorrow_timestamp]['note'] : NULL;
		}
		
		foreach ($match[0] as $i => $v)
		{
			if ($v == NULL)
			{
				continue;
			}

			$logic_variables[$i] = NULL;
			$logic_parameters[$i] = NULL;

			if (preg_match('/^^%\s*(.+)[ :]?[{]([^}%]{1,200})[}]\s*%$/', $match[1][$i], $n))
			{
				$logic_variables[$i] = strtolower(preg_replace('/[^1-3a-z_]/', '_', preg_replace('/^%\s*([1-3a-z _]+)\s*%$/i', '$1', $n[1])));
				$logic_parameters[$i] = array();

				foreach (preg_split('/;\s*/', $n[2], 20, PREG_SPLIT_NO_EMPTY) as $lp)
				{
					$logic_parameter = preg_split('/:\s*/', $lp, 2);

					if (count($logic_parameter) != 2 || !preg_match('/^[0-9a-z_-]+$/i', $logic_parameter[0]))
					{
						continue;
					}

					switch(mb_strtolower($logic_parameter[0]))
					{
					case 'format':
						if (!preg_match($this->accepted_day_format, $logic_parameter[1]))
						{
							break;
						}
					default:
						$logic_parameters[$i][mb_strtolower($logic_parameter[0])] = $logic_parameter[1];
						break;
					}
				}

				if (empty($logic_parameters[$i]))
				{
					$logic_parameters[$i] = NULL;
				}
			}
			else
			{
				$logic_variables[$i] = strtolower(preg_replace('/[^1-3a-z_]/', '_', preg_replace('/^%\s*([1-3a-z _]+)\s*%$/i', '$1', $match[1][$i])));
			}
			
			$text[$i] = ($match[2][$i] != NULL) ? $match[2][$i] : NULL;
			
			if ($logic_variables[$i] == NULL && $text[$i] == NULL)
			{
				continue;
			}
		
			if ($i == 0 || ($i > 0 && isset($logic_variables[($i - 1)]) && preg_match('/^(?:if_.+|else|end(?:if)?)$/i', $logic_variables[($i - 1)])))
			{
				$text[$i] = (is_string($text[$i])) ? preg_replace('/\s*([^\s].+)$/', '$1', $text[$i]) : NULL;
			}
			
			if ($i == (count($match[0]) - 1) || ($i < (count($match[0]) - 2) && isset($match[1][($i + 1)]) && preg_match('/^%\s*(?:if_.+|else|end(?:if)?)\s*%$/i', $match[1][($i + 1)])))
			{
				$text[$i] = (is_string($text[$i])) ? preg_replace('/(.+[^\s])\s*$/', '$1', $text[$i]) : NULL;
			}
		}
		
		foreach ($logic_variables as $i => $lv)
		{
			if ($lv == 'end' || $lv == 'endif' || $lv == 'else')
			{
				if (is_numeric($if_open_now) && $if_open_now >= 0
					|| is_numeric($if_closed_now) && $if_closed_now >= 0
					|| is_numeric($if_open_today) && $if_open_today >= 0
					|| is_numeric($if_closed_today) && $if_closed_today >= 0
					|| is_numeric($if_open_later) && $if_open_later >= 0
					|| is_numeric($if_not_open_later) && $if_not_open_later >= 0
					|| is_numeric($if_open_tomorrow) && $if_open_tomorrow >= 0
					|| is_numeric($if_closed_tomorrow) && $if_closed_tomorrow >= 0
					|| is_numeric($if_hours_24_today) && $if_hours_24_today >= 0
					|| is_numeric($if_not_hours_24_today) && $if_not_hours_24_today >= 0
					|| is_numeric($if_hours_24_tomorrow) && $if_hours_24_tomorrow >= 0
					|| is_numeric($if_not_hours_24_tomorrow) && $if_not_hours_24_tomorrow >= 0
					|| is_numeric($if_open_today_1) && $if_open_today_1 >= 0
					|| is_numeric($if_not_open_today_1) && $if_not_open_today_1 >= 0
					|| is_numeric($if_open_today_2) && $if_open_today_2 >= 0
					|| is_numeric($if_not_open_today_2) && $if_not_open_today_2 >= 0
					|| is_numeric($if_open_today_3) && $if_open_today_3 >= 0
					|| is_numeric($if_not_open_today_3) && $if_not_open_today_3 >= 0
					|| is_numeric($if_open_tomorrow_1) && $if_open_tomorrow_1 >= 0
					|| is_numeric($if_not_open_tomorrow_1) && $if_not_open_tomorrow_1 >= 0
					|| is_numeric($if_open_tomorrow_2) && $if_open_tomorrow_2 >= 0
					|| is_numeric($if_not_open_tomorrow_2) && $if_not_open_tomorrow_2 >= 0
					|| is_numeric($if_open_tomorrow_3) && $if_open_tomorrow_3 >= 0
					|| is_numeric($if_not_open_tomorrow_3) && $if_not_open_tomorrow_3 >= 0
					|| is_numeric($if_note_today) && $if_note_today >= 0
					|| is_numeric($if_not_note_today) && $if_not_note_today >= 0
					|| is_numeric($if_note_tomorrow) && $if_note_tomorrow >= 0
					|| is_numeric($if_not_note_tomorrow) && $if_not_note_tomorrow >= 0
					|| is_numeric($if_regular_today) && $if_regular_today >= 0
					|| is_numeric($if_not_regular_today) && $if_not_regular_today >= 0
					|| is_numeric($if_special_today) && $if_special_today >= 0
					|| is_numeric($if_not_special_today) && $if_not_special_today >= 0
					|| is_numeric($if_closure_today) && $if_closure_today >= 0
					|| is_numeric($if_not_closure_today) && $if_not_closure_today >= 0
					|| is_numeric($if_regular_tomorrow) && $if_regular_tomorrow >= 0
					|| is_numeric($if_not_regular_tomorrow) && $if_not_regular_tomorrow >= 0
					|| is_numeric($if_special_tomorrow) && $if_special_tomorrow >= 0
					|| is_numeric($if_not_special_tomorrow) && $if_not_special_tomorrow >= 0
					|| is_numeric($if_closure_tomorrow) && $if_closure_tomorrow >= 0
					|| is_numeric($if_not_closure_tomorrow) && $if_not_closure_tomorrow >= 0
					|| is_numeric($if_closure_exists) && $if_closure_exists >= 0
					|| is_numeric($if_not_closure_exists) && $if_not_closure_exists >= 0)
				{
					$maxes = array_keys(
						array(
							$if_open_now,
							$if_closed_now,
							$if_open_today,
							$if_closed_today,
							$if_open_later,
							$if_not_open_later,
							$if_open_tomorrow,
							$if_closed_tomorrow,
							$if_hours_24_today,
							$if_not_hours_24_today,
							$if_hours_24_tomorrow,
							$if_not_hours_24_tomorrow,
							$if_open_today_1,
							$if_not_open_today_1,
							$if_open_today_2,
							$if_not_open_today_2,
							$if_open_today_3,
							$if_not_open_today_3,
							$if_open_tomorrow_1,
							$if_not_open_tomorrow_1,
							$if_open_tomorrow_2,
							$if_not_open_tomorrow_2,
							$if_open_tomorrow_3,
							$if_not_open_tomorrow_3,
							$if_note_today,
							$if_not_note_today,
							$if_note_tomorrow,
							$if_not_note_tomorrow,
							$if_regular_today,
							$if_not_regular_today,
							$if_special_today,
							$if_not_special_today,
							$if_closure_today,
							$if_not_closure_today,
							$if_regular_tomorrow,
							$if_not_regular_tomorrow,
							$if_special_tomorrow,
							$if_not_special_tomorrow,
							$if_closure_tomorrow,
							$if_not_closure_tomorrow,
							$if_closure_exists,
							$if_not_closure_exists
						),
						max(
							array(
								$if_open_now,
								$if_closed_now,
								$if_open_today,
								$if_closed_today,
								$if_open_later,
								$if_not_open_later,
								$if_open_tomorrow,
								$if_closed_tomorrow,
								$if_hours_24_today,
								$if_not_hours_24_today,
								$if_hours_24_tomorrow,
								$if_not_hours_24_tomorrow,
								$if_open_today_1,
								$if_not_open_today_1,
								$if_open_today_2,
								$if_not_open_today_2,
								$if_open_today_3,
								$if_not_open_today_3,
								$if_open_tomorrow_1,
								$if_not_open_tomorrow_1,
								$if_open_tomorrow_2,
								$if_not_open_tomorrow_2,
								$if_open_tomorrow_3,
								$if_not_open_tomorrow_3,
								$if_note_today,
								$if_not_note_today,
								$if_note_tomorrow,
								$if_not_note_tomorrow,
								$if_regular_today,
								$if_not_regular_today,
								$if_special_today,
								$if_not_special_today,
								$if_closure_today,
								$if_not_closure_today,
								$if_regular_tomorrow,
								$if_not_regular_tomorrow,
								$if_special_tomorrow,
								$if_not_special_tomorrow,
								$if_closure_tomorrow,
								$if_not_closure_tomorrow,
								$if_closure_exists,
								$if_not_closure_exists
							)
						)
					);
					$max = $maxes[0];
					
					if ($max == 1)
					{
						if ($lv == 'else')
						{
							$if_open_now = $if_closed_now;
						}
						
						$if_closed_now = -1;
					}
					elseif ($max == 2)
					{
						if ($lv == 'else')
						{
							$if_closed_today = $if_open_today;
						}
						
						$if_open_today = -1;
					}
					elseif ($max == 3)
					{
						if ($lv == 'else')
						{
							$if_open_today = $if_closed_today;
						}
						
						$if_closed_today = -1;
					}
					elseif ($max == 4)
					{
						if ($lv == 'else')
						{
							$if_not_open_later = $if_open_later;
						}
						
						$if_open_later = -1;
					}
					elseif ($max == 5)
					{
						if ($lv == 'else')
						{
							$if_open_later = $if_not_open_later;
						}
						
						$if_not_open_later = -1;
					}
					elseif ($max == 6)
					{
						if ($lv == 'else')
						{
							$if_closed_tomorrow = $if_open_tomorrow;
						}
						
						$if_open_tomorrow = -1;
					}
					elseif ($max == 7)
					{
						if ($lv == 'else')
						{
							$if_open_tomorrow = $if_closed_tomorrow;
						}
						
						$if_closed_tomorrow = -1;
					}
					elseif ($max == 8)
					{
						if ($lv == 'else')
						{
							$if_not_hours_24_today = $if_hours_24_today;
						}
						
						$if_hours_24_today = -1;
					}
					elseif ($max == 9)
					{
						if ($lv == 'else')
						{
							$if_hours_24_today = $if_not_hours_24_today;
						}
						
						$if_not_hours_24_today = -1;
					}
					elseif ($max == 10)
					{
						if ($lv == 'else')
						{
							$if_not_hours_24_tomorrow = $if_hours_24_tomorrow;
						}
						
						$if_hours_24_tomorrow = -1;
					}
					elseif ($max == 11)
					{
						if ($lv == 'else')
						{
							$if_hours_24_tomorrow = $if_not_hours_24_tomorrow;
						}
						
						$if_not_hours_24_tomorrow = -1;
					}
					elseif ($max == 12)
					{
						if ($lv == 'else')
						{
							$if_not_open_today_1 = $if_open_today_1;
						}
						
						$if_open_today_1 = -1;
					}
					elseif ($max == 13)
					{
						if ($lv == 'else')
						{
							$if_open_today_1 = $if_not_open_today_1;
						}
						
						$if_not_open_today_1 = -1;
					}
					elseif ($max == 14)
					{
						if ($lv == 'else')
						{
							$if_not_open_today_2 = $if_open_today_2;
						}
						
						$if_open_today_2 = -1;
					}
					elseif ($max == 15)
					{
						if ($lv == 'else')
						{
							$if_open_today_2 = $if_not_open_today_2;
						}
						
						$if_not_open_today_2 = -1;
					}
					elseif ($max == 16)
					{
						if ($lv == 'else')
						{
							$if_not_open_today_3 = $if_open_today_3;
						}
						
						$if_open_today_3 = -1;
					}
					elseif ($max == 17)
					{
						if ($lv == 'else')
						{
							$if_open_today_3 = $if_not_open_today_3;
						}
						
						$if_not_open_today_3 = -1;
					}
					elseif ($max == 18)
					{
						if ($lv == 'else')
						{
							$if_not_open_tomorrow_1 = $if_open_tomorrow_1;
						}
						
						$if_open_tomorrow_1 = -1;
					}
					elseif ($max == 19)
					{
						if ($lv == 'else')
						{
							$if_open_tomorrow_1 = $if_not_open_tomorrow_1;
						}
						
						$if_not_open_tomorrow_1 = -1;
					}
					elseif ($max == 20)
					{
						if ($lv == 'else')
						{
							$if_not_open_tomorrow_2 = $if_open_tomorrow_2;
						}
						
						$if_open_tomorrow_2 = -1;
					}
					elseif ($max == 21)
					{
						if ($lv == 'else')
						{
							$if_open_tomorrow_2 = $if_not_open_tomorrow_2;
						}
						
						$if_not_open_tomorrow_2 = -1;
					}
					elseif ($max == 22)
					{
						if ($lv == 'else')
						{
							$if_not_open_tomorrow_3 = $if_open_tomorrow_3;
						}
						
						$if_open_tomorrow_3 = -1;
					}
					elseif ($max == 23)
					{
						if ($lv == 'else')
						{
							$if_open_tomorrow_3 = $if_not_open_tomorrow_3;
						}
						
						$if_not_open_tomorrow_3 = -1;
					}
					elseif ($max == 24)
					{
						if ($lv == 'else')
						{
							$if_not_note_today = $if_note_today;
						}
						
						$if_note_today = -1;
					}
					elseif ($max == 25)
					{
						if ($lv == 'else')
						{
							$if_note_today = $if_not_note_today;
						}
						
						$if_not_note_today = -1;
					}
					elseif ($max == 26)
					{
						if ($lv == 'else')
						{
							$if_not_note_tomorrow = $if_note_tomorrow;
						}
						
						$if_note_tomorrow = -1;
					}
					elseif ($max == 27)
					{
						if ($lv == 'else')
						{
							$if_note_tomorrow = $if_not_note_tomorrow;
						}
						
						$if_not_note_tomorrow = -1;
					}
					elseif ($max == 28)
					{
						if ($lv == 'else')
						{
							$if_not_regular_today = $if_regular_today;
						}
						
						$if_regular_today = -1;
					}
					elseif ($max == 29)
					{
						if ($lv == 'else')
						{
							$if_regular_today = $if_not_regular_today;
						}
						
						$if_not_regular_today = -1;
					}
					elseif ($max == 30)
					{
						if ($lv == 'else')
						{
							$if_not_special_today = $if_special_today;
						}
						
						$if_special_today = -1;
					}
					elseif ($max == 31)
					{
						if ($lv == 'else')
						{
							$if_special_today = $if_not_special_today;
						}
						
						$if_not_special_today = -1;
					}
					elseif ($max == 32)
					{
						if ($lv == 'else')
						{
							$if_not_closure_today = $if_closure_today;
						}
						
						$if_closure_today = -1;
					}
					elseif ($max == 33)
					{
						if ($lv == 'else')
						{
							$if_closure_today = $if_not_closure_today;
						}
						
						$if_not_closure_today = -1;
					}
					elseif ($max == 34)
					{
						if ($lv == 'else')
						{
							$if_not_regular_tomorrow = $if_regular_tomorrow;
						}
						
						$if_regular_tomorrow = -1;
					}
					elseif ($max == 35)
					{
						if ($lv == 'else')
						{
							$if_regular_tomorrow = $if_not_regular_tomorrow;
						}
						
						$if_not_regular_tomorrow = -1;
					}
					elseif ($max == 36)
					{
						if ($lv == 'else')
						{
							$if_not_special_tomorrow = $if_special_tomorrow;
						}
						
						$if_special_tomorrow = -1;
					}
					elseif ($max == 37)
					{
						if ($lv == 'else')
						{
							$if_special_tomorrow = $if_not_special_tomorrow;
						}
						
						$if_not_special_tomorrow = -1;
					}
					elseif ($max == 38)
					{
						if ($lv == 'else')
						{
							$if_not_closure_tomorrow = $if_closure_tomorrow;
						}
						
						$if_closure_tomorrow = -1;
					}
					elseif ($max == 39)
					{
						if ($lv == 'else')
						{
							$if_closure_tomorrow = $if_not_closure_tomorrow;
						}
						
						$if_not_closure_tomorrow = -1;
					}
					elseif ($max == 40)
					{
						if ($lv == 'else')
						{
							$if_not_closure_exists = $if_closure_exists;
						}
						
						$if_closure_exists = -1;
					}
					elseif ($max == 41)
					{
						if ($lv == 'else')
						{
							$if_closure_exists = $if_not_closure_exists;
						}
						
						$if_not_closure_exists = -1;
					}
					else
					{
						if ($lv == 'else')
						{
							$if_closed_now = $if_open_now;
						}
						
						$if_open_now = -1;
					}
				}
				continue;
			}

			if ($lv == 'if_open' || $lv == 'if_open_now' || $lv == 'if_not_closed' || $lv == 'if_not_closed_now')
			{
				$if_open_now = $i;
				continue;
			}
			
			if ($lv == 'if_closed' || $lv == 'if_closed_now' || $lv == 'if_not_open' || $lv == 'if_not_open_now')
			{
				$if_closed_now = $i;
				continue;
			}
			
			if ($lv == 'if_open_today' || $lv == 'if_not_closed_today')
			{
				$if_open_today = $i;
				continue;
			}
			
			if ($lv == 'if_closed_today' || $lv == 'if_not_open_today')
			{
				$if_closed_today = $i;
				continue;
			}

			if ($lv == 'if_open_later' || $lv == 'if_open_later_today')
			{
				$if_open_later = $i;
				continue;
			}

			if ($lv == 'if_not_open_later' || $lv == 'if_not_open_later_today')
			{
				$if_not_open_later = $i;
				continue;
			}

			if ($lv == 'if_open_tomorrow' || $lv == 'if_not_closed_tomorrow')
			{
				$if_open_tomorrow = $i;
				continue;
			}

			if ($lv == 'if_closed_tomorrow' || $lv == 'if_not_open_tomorrow')
			{
				$if_closed_tomorrow = $i;
				continue;
			}
			
			if ($lv == 'if_24_hours_today' || $lv == 'if_hours_24_today' || $lv == 'if_24_hours' || $lv == 'if_hours_24')
			{
				$if_hours_24_today = $i;
				continue;
			}
			
			if ($lv == 'if_not_24_hours_today' || $lv == 'if_not_hours_24_today' || $lv == 'if_not_24_hours' || $lv == 'if_not_hours_24')
			{
				$if_not_hours_24_today = $i;
				continue;
			}
			
			if ($lv == 'if_24_hours_tomorrow' || $lv == 'if_hours_24_tomorrow')
			{
				$if_hours_24_tomorrow = $i;
				continue;
			}
			
			if ($lv == 'if_not_24_hours_tomorrow' || $lv == 'if_not_hours_24_tomorrow')
			{
				$if_not_hours_24_tomorrow = $i;
				continue;
			}

			if ($lv == 'if_open_today_1' || $lv == 'if_open_today_1_set' || $lv == 'if_open_today_1_sets')
			{
				$if_open_today_1 = $i;
				continue;
			}

			if ($lv == 'if_not_open_today_1' || $lv == 'if_not_open_today_1_set' || $lv == 'if_not_open_today_1_sets')
			{
				$if_not_open_today_1 = $i;
				continue;
			}

			if ($lv == 'if_open_today_2' || $lv == 'if_open_today_2_set' || $lv == 'if_open_today_2_sets')
			{
				$if_open_today_2 = $i;
				continue;
			}

			if ($lv == 'if_not_open_today_2' || $lv == 'if_not_open_today_2_set' || $lv == 'if_not_open_today_2_sets')
			{
				$if_not_open_today_2 = $i;
				continue;
			}

			if ($lv == 'if_open_today_3' || $lv == 'if_open_today_3_set' || $lv == 'if_open_today_3_sets')
			{
				$if_open_today_3 = $i;
				continue;
			}

			if ($lv == 'if_not_open_today_3' || $lv == 'if_not_open_today_3_set' || $lv == 'if_not_open_today_3_sets')
			{
				$if_not_open_today_3 = $i;
				continue;
			}

			if ($lv == 'if_open_tomorrow_1' || $lv == 'if_open_tomorrow_1_set' || $lv == 'if_open_tomorrow_1_sets')
			{
				$if_open_tomorrow_1 = $i;
				continue;
			}

			if ($lv == 'if_not_open_tomorrow_1' || $lv == 'if_not_open_tomorrow_1_set' || $lv == 'if_not_open_tomorrow_1_sets')
			{
				$if_not_open_tomorrow_1 = $i;
				continue;
			}

			if ($lv == 'if_open_tomorrow_2' || $lv == 'if_open_tomorrow_2_set' || $lv == 'if_open_tomorrow_2_sets')
			{
				$if_open_tomorrow_2 = $i;
				continue;
			}

			if ($lv == 'if_not_open_tomorrow_2' || $lv == 'if_not_open_tomorrow_2_set' || $lv == 'if_not_open_tomorrow_2_sets')
			{
				$if_not_open_tomorrow_2 = $i;
				continue;
			}

			if ($lv == 'if_open_tomorrow_3' || $lv == 'if_open_tomorrow_3_set' || $lv == 'if_open_tomorrow_3_sets')
			{
				$if_open_tomorrow_3 = $i;
				continue;
			}

			if ($lv == 'if_not_open_tomorrow_3' || $lv == 'if_not_open_tomorrow_3_set' || $lv == 'if_not_open_tomorrow_3_sets')
			{
				$if_not_open_tomorrow_3 = $i;
				continue;
			}

			if ($lv == 'if_note_today' || $lv == 'if_today_note')
			{
				$if_note_today = $i;
				continue;
			}
			
			if ($lv == 'if_not_note_today' || $lv == 'if_not_today_note')
			{
				$if_not_note_today = $i;
				continue;
			}
			
			if ($lv == 'if_note_tomorrow' || $lv == 'if_tomorrow_note')
			{
				$if_note_tomorrow = $i;
				continue;
			}
			
			if ($lv == 'if_not_note_tomorrow' || $lv == 'if_not_tomorrow_note')
			{
				$if_not_note_tomorrow = $i;
				continue;
			}
			
			if ($lv == 'if_regular_today' || $lv == 'if_regular_today')
			{
				$if_regular_today = $i;
				continue;
			}
			
			if ($lv == 'if_regular' || $lv == 'if_regular_today')
			{
				$if_regular_today = $i;
				continue;
			}

			if ($lv == 'if_not_regular' || $lv == 'if_not_regular_today')
			{
				$if_not_regular_today = $i;
				continue;
			}

			if ($lv == 'if_special' || $lv == 'if_special_today')
			{
				$if_special_today = $i;
				continue;
			}

			if ($lv == 'if_not_special' || $lv == 'if_not_special_today')
			{
				$if_not_special_today = $i;
				continue;
			}

			if ($lv == 'if_closure' || $lv == 'if_temporary_closure' || $lv == 'if_closure_today' || $lv == 'if_temporary_closure_today')
			{
				$if_closure_today = $i;
				continue;
			}

			if ($lv == 'if_not_closure' || $lv == 'if_not_temporary_closure' || $lv == 'if_not_closure_today' || $lv == 'if_not_temporary_closure_today')
			{
				$if_not_closure_today = $i;
				continue;
			}

			if ($lv == 'if_regular_tomorrow')
			{
				$if_regular_tomorrow = $i;
				continue;
			}

			if ($lv == 'if_not_regular_tomorrow')
			{
				$if_not_regular_tomorrow = $i;
				continue;
			}

			if ($lv == 'if_special_tomorrow')
			{
				$if_special_tomorrow = $i;
				continue;
			}

			if ($lv == 'if_not_special_tomorrow')
			{
				$if_not_special_tomorrow = $i;
				continue;
			}

			if ($lv == 'if_closure_tomorrow' || $lv == 'if_temporary_closure_tomorrow')
			{
				$if_closure_tomorrow = $i;
				continue;
			}

			if ($lv == 'if_not_closure_tomorrow' || $lv == 'if_not_temporary_closure_tomorrow')
			{
				$if_not_closure_tomorrow = $i;
				continue;
			}

			if ($lv == 'if_closure_exists' || $lv == 'if_temporary_closure_exists')
			{
				$if_closure_exists = $i;
				continue;
			}

			if ($lv == 'if_not_closure_exists' || $lv == 'if_not_temporary_closure_exists')
			{
				$if_not_closure_exists = $i;
				continue;
			}

			if ((is_numeric($if_open_now) && $if_open_now >= 0
				|| is_numeric($if_closed_now) && $if_closed_now >= 0
				|| is_numeric($if_open_today) && $if_open_today >= 0
				|| is_numeric($if_closed_today) && $if_closed_today >= 0
				|| is_numeric($if_open_later) && $if_open_later >= 0
				|| is_numeric($if_not_open_later) && $if_not_open_later >= 0
				|| is_numeric($if_open_tomorrow) && $if_open_tomorrow >= 0
				|| is_numeric($if_closed_tomorrow) && $if_closed_tomorrow >= 0
				|| is_numeric($if_hours_24_today) && $if_hours_24_today >= 0
				|| is_numeric($if_not_hours_24_today) && $if_not_hours_24_today >= 0
				|| is_numeric($if_hours_24_tomorrow) && $if_hours_24_tomorrow >= 0
				|| is_numeric($if_not_hours_24_tomorrow) && $if_not_hours_24_tomorrow >= 0
				|| is_numeric($if_open_today_1) && $if_open_today_1 >= 0
				|| is_numeric($if_not_open_today_1) && $if_not_open_today_1 >= 0
				|| is_numeric($if_open_today_2) && $if_open_today_2 >= 0
				|| is_numeric($if_not_open_today_2) && $if_not_open_today_2 >= 0
				|| is_numeric($if_open_today_3) && $if_open_today_3 >= 0
				|| is_numeric($if_not_open_today_3) && $if_not_open_today_3 >= 0
				|| is_numeric($if_open_tomorrow_1) && $if_open_tomorrow_1 >= 0
				|| is_numeric($if_not_open_tomorrow_1) && $if_not_open_tomorrow_1 >= 0
				|| is_numeric($if_open_tomorrow_2) && $if_open_tomorrow_2 >= 0
				|| is_numeric($if_not_open_tomorrow_2) && $if_not_open_tomorrow_2 >= 0
				|| is_numeric($if_open_tomorrow_3) && $if_open_tomorrow_3 >= 0
				|| is_numeric($if_not_open_tomorrow_3) && $if_not_open_tomorrow_3 >= 0
				|| is_numeric($if_note_today) && $if_note_today >= 0
				|| is_numeric($if_not_note_today) && $if_not_note_today >= 0
				|| is_numeric($if_note_tomorrow) && $if_note_tomorrow >= 0
				|| is_numeric($if_not_note_tomorrow) && $if_not_note_tomorrow >= 0
				|| is_numeric($if_regular_today) && $if_regular_today >= 0
				|| is_numeric($if_not_regular_today) && $if_not_regular_today >= 0
				|| is_numeric($if_special_today) && $if_special_today >= 0
				|| is_numeric($if_not_special_today) && $if_not_special_today >= 0
				|| is_numeric($if_closure_today) && $if_closure_today >= 0
				|| is_numeric($if_not_closure_today) && $if_not_closure_today >= 0
				|| is_numeric($if_regular_tomorrow) && $if_regular_tomorrow >= 0
				|| is_numeric($if_not_regular_tomorrow) && $if_not_regular_tomorrow >= 0
				|| is_numeric($if_special_tomorrow) && $if_special_tomorrow >= 0
				|| is_numeric($if_not_special_tomorrow) && $if_not_special_tomorrow >= 0
				|| is_numeric($if_closure_tomorrow) && $if_closure_tomorrow >= 0
				|| is_numeric($if_not_closure_tomorrow) && $if_not_closure_tomorrow >= 0
				|| is_numeric($if_closure_exists) && $if_closure_exists >= 0
				|| is_numeric($if_not_closure_exists) && $if_not_closure_exists >= 0)
				&& (is_bool($closed_now) && $closed_now && is_numeric($if_open_now) && $if_open_now >= 0
				|| is_bool($open_now) && $open_now && is_numeric($if_closed_now) && $if_closed_now >= 0
				|| is_bool($today_closed) && $today_closed && is_numeric($if_open_today) && $if_open_today >= 0
				|| is_bool($tomorrow_closed) && $tomorrow_closed && is_numeric($if_open_tomorrow) && $if_open_tomorrow >= 0
				|| is_bool($today_closed) && !$today_closed && is_numeric($if_closed_today) && $if_closed_today >= 0
				|| is_bool($tomorrow_closed) && !$tomorrow_closed && is_numeric($if_closed_tomorrow) && $if_closed_tomorrow >= 0
				|| is_bool($today_hours_24) && !$today_hours_24 && is_numeric($if_hours_24_today) && $if_hours_24_today >= 0
				|| is_bool($today_hours_24) && $today_hours_24 && is_numeric($if_not_hours_24_today) && $if_not_hours_24_today >= 0
				|| is_bool($tomorrow_hours_24) && !$tomorrow_hours_24 && is_numeric($if_hours_24_tomorrow) && $if_hours_24_tomorrow >= 0
				|| is_bool($tomorrow_hours_24) && $tomorrow_hours_24 && is_numeric($if_not_hours_24_tomorrow) && $if_not_hours_24_tomorrow >= 0
				|| is_bool($open_later) && !$open_later && is_numeric($if_open_later) && $if_open_later >= 0
				|| (is_bool($open_now) && $open_now || is_bool($open_later) && $open_later) && is_numeric($if_not_open_later) && $if_not_open_later >= 0
				|| (!is_array($today_hours) || is_array($today_hours) && count($today_hours) != 1) && is_numeric($if_open_today_1) && $if_open_today_1 >= 0
				|| is_array($today_hours) && count($today_hours) == 1 && is_numeric($if_not_open_today_1) && $if_not_open_today_1 >= 0
				|| (!is_array($today_hours) || is_array($today_hours) && count($today_hours) != 2) && is_numeric($if_open_today_2) && $if_open_today_2 >= 0
				|| is_array($today_hours) && count($today_hours) == 2 && is_numeric($if_not_open_today_2) && $if_not_open_today_2 >= 0
				|| (!is_array($today_hours) || is_array($today_hours) && count($today_hours) != 3) && is_numeric($if_open_today_3) && $if_open_today_3 >= 0
				|| is_array($today_hours) && count($today_hours) == 3 && is_numeric($if_not_open_today_3) && $if_not_open_today_3 >= 0
				|| (!is_array($tomorrow_hours) || is_array($tomorrow_hours) && count($tomorrow_hours) != 1) && is_numeric($if_open_tomorrow_1) && $if_open_tomorrow_1 >= 0
				|| is_array($tomorrow_hours) && count($tomorrow_hours) == 1 && is_numeric($if_not_open_tomorrow_1) && $if_not_open_tomorrow_1 >= 0
				|| (!is_array($tomorrow_hours) || is_array($tomorrow_hours) && count($tomorrow_hours) != 2) && is_numeric($if_open_tomorrow_2) && $if_open_tomorrow_2 >= 0
				|| is_array($tomorrow_hours) && count($tomorrow_hours) == 2 && is_numeric($if_not_open_tomorrow_2) && $if_not_open_tomorrow_2 >= 0
				|| (!is_array($tomorrow_hours) || is_array($tomorrow_hours) && count($tomorrow_hours) != 3) && is_numeric($if_open_tomorrow_3) && $if_open_tomorrow_3 >= 0
				|| is_array($tomorrow_hours) && count($tomorrow_hours) == 3 && is_numeric($if_not_open_tomorrow_3) && $if_not_open_tomorrow_3 >= 0
				|| $today_note == NULL && is_numeric($if_note_today) && $if_note_today >= 0
				|| $today_note != NULL && is_numeric($if_not_note_today) && $if_not_note_today >= 0
				|| $tomorrow_note == NULL && is_numeric($if_note_tomorrow) && $if_note_tomorrow >= 0
				|| $tomorrow_note != NULL && is_numeric($if_not_note_tomorrow) && $if_not_note_tomorrow >= 0
				|| $today_hours_type != NULL && is_numeric($if_regular_today) && $if_regular_today >= 0
				|| $today_hours_type == NULL && is_numeric($if_not_regular_today) && $if_not_regular_today >= 0
				|| $today_hours_type != 'special' && is_numeric($if_special_today) && $if_special_today >= 0
				|| $today_hours_type == 'special' && is_numeric($if_not_special_today) && $if_not_special_today >= 0
				|| $today_hours_type != 'closure' && is_numeric($if_closure_today) && $if_closure_today >= 0
				|| $today_hours_type == 'closure' && is_numeric($if_not_closure_today) && $if_not_closure_today >= 0
				|| $tomorrow_hours_type != NULL && is_numeric($if_regular_tomorrow) && $if_regular_tomorrow >= 0
				|| $tomorrow_hours_type == NULL && is_numeric($if_not_regular_tomorrow) && $if_not_regular_tomorrow >= 0
				|| $tomorrow_hours_type != 'special' && is_numeric($if_special_tomorrow) && $if_special_tomorrow >= 0
				|| $tomorrow_hours_type == 'special' && is_numeric($if_not_special_tomorrow) && $if_not_special_tomorrow >= 0
				|| $tomorrow_hours_type != 'closure' && is_numeric($if_closure_tomorrow) && $if_closure_tomorrow >= 0
				|| $tomorrow_hours_type == 'closure' && is_numeric($if_not_closure_tomorrow) && $if_not_closure_tomorrow >= 0
				|| (!is_array($this->closure) || is_array($this->closure) && empty($this->closure) || is_array($this->closure) && (!isset($this->closure['end_display']) || $this->closure['end_display'] == NULL) || isset($this->closure['end_display']) && $this->closure['end_display'] < $this->today_timestamp) && is_numeric($if_closure_exists) && $if_closure_exists >= 0
				|| (is_array($this->closure) && !empty($this->closure) && isset($this->closure['end_display']) && $this->closure['end_display'] != NULL && $this->closure['end_display'] >= $this->today_timestamp) && is_numeric($if_not_closure_exists) && $if_not_closure_exists >= 0))
			{
				$text[$i] = NULL;
				continue;
			}
			
			if ($lv == 'now' || $lv == 'current' || $lv == 'current_time' || $lv == 'currenttime')
			{
				$text[$i] = $now;
				continue;
			}
			
			if ($lv == 'today' || $lv == 'today_name' || $lv == 'today_day_name')
			{
				$text[$i] = $today_name;
				continue;
			}
			
			if ($lv == 'tomorrow' || $lv == 'tomorrow_name' || $lv == 'tomorrow_day_name')
			{
				$text[$i] = $tomorrow_name;
				continue;
			}
			
			if ($lv == 'hours_today' || $lv == 'today_hours' || $lv == 'hours_tomorrow' || $lv == 'tomorrow_hours')
			{
				$text[$i] = ($lv == 'hours_tomorrow' || $lv == 'tomorrow_hours') ? $tomorrow_text : $today_text;
				continue;
			}
			
			if ($lv == 'today_start')
			{
				$text[$i] = $today_start_text;
				continue;
			}
			
			if ($lv == 'today_end')
			{
				$text[$i] = $today_end_text;
				continue;
			}
			
			if ($lv == 'today_next')
			{
				$text[$i] = $today_next_text;
				continue;
			}
			
			if ($lv == 'today_1' || $lv == 'today_set_1' || $lv == 'today_hour_1' || $lv == 'today_hours_1')
			{
				$text[$i] = $today_1_text;
				continue;
			}
			
			if ($lv == 'today_2' || $lv == 'today_set_2' || $lv == 'today_hour_2' || $lv == 'today_hours_2')
			{
				$text[$i] = $today_2_text;
				continue;
			}
			
			if ($lv == 'today_3' || $lv == 'today_set_3' || $lv == 'today_hour_3' || $lv == 'today_hours_3')
			{
				$text[$i] = $today_3_text;
				continue;
			}
			
			if ($lv == 'today_note' || $lv == 'note_today')
			{
				$text[$i] = $today_note;
				continue;
			}
			
			if ($lv == 'tomorrow_start')
			{
				$text[$i] = $tomorrow_start_text;
				continue;
			}
			
			if ($lv == 'tomorrow_end')
			{
				$text[$i] = $tomorrow_end_text;
				continue;
			}
			
			if ($lv == 'tomorrow_1' || $lv == 'tomorrow_set_1' || $lv == 'tomorrow_hour_1' || $lv == 'tomorrow_hours_1')
			{
				$text[$i] = $tomorrow_1_text;
				continue;
			}
			
			if ($lv == 'tomorrow_2' || $lv == 'tomorrow_set_2' || $lv == 'tomorrow_hour_2' || $lv == 'tomorrow_hours_2')
			{
				$text[$i] = $tomorrow_2_text;
				continue;
			}
			
			if ($lv == 'tomorrow_3' || $lv == 'tomorrow_set_3' || $lv == 'tomorrow_hour_3' || $lv == 'tomorrow_hours_3')
			{
				$text[$i] = $tomorrow_3_text;
				continue;
			}
			
			if ($lv == 'tomorrow_note' || $lv == 'note_tomorrow')
			{
				$text[$i] = $tomorrow_note;
				continue;
			}

			if ($lv == 'closure_start' || $lv == 'temporary_closure_start' || $lv == 'closure_end' || $lv == 'temporary_closure_end')
			{
				if (!is_array($this->closure) || is_array($this->closure) && empty($this->closure) || is_array($this->closure) && (!isset($this->closure['end_display']) || $this->closure['end_display'] == NULL) || isset($this->closure['end_display']) && $this->closure['end_display'] < $this->today_timestamp)
				{
					$text[$i] = NULL;
					continue;
				}
				
				$text[$i] = wp_date((isset($logic_parameters[$i]['format']) && $logic_parameters[$i]['format'] != NULL) ? $logic_parameters[$i]['format'] : get_option('date_format'), ($lv == 'closure_start' || $lv == 'temporary_closure_start') ? $this->closure['start_display'] : $this->closure['end_display']); 
				continue;
			}
			
			if ($lv == 'days_status' || $lv == 'days_status_padded' || $lv == 'days_change' || $lv == 'days_change_padded' || $lv == 'days' || $lv == 'days_padded')
			{
				$text[$i] = ($lv == 'days_padded' || $lv == 'days_status_padded' || $lv == 'days_change_padded') ? str_pad(floor($seconds_to_change / DAY_IN_SECONDS), 2, '0', STR_PAD_LEFT) : floor($seconds_to_change / DAY_IN_SECONDS);
				continue;
			}
			
			if ($lv == 'hours' || $lv == 'hours_padded')
			{
				$text[$i] = ($lv == 'hours_padded') ? str_pad(floor($seconds_to_change / HOUR_IN_SECONDS), 2, '0', STR_PAD_LEFT) : floor($seconds_to_change / HOUR_IN_SECONDS);
				continue;
			}
			
			if ($lv == 'hours_divisor' || $lv == 'hours_divisor_padded')
			{
				$text[$i] = ($lv == 'hours_divisor_padded') ? str_pad((floor($seconds_to_change / HOUR_IN_SECONDS) % HOUR_IN_SECONDS), 2, '0', STR_PAD_LEFT) : (floor($seconds_to_change / HOUR_IN_SECONDS) % HOUR_IN_SECONDS);
				continue;
			}
			
			if ($lv == 'minutes' || $lv == 'minutes_padded')
			{
				$text[$i] = ($lv == 'minutes_padded') ? str_pad(floor($seconds_to_change / MINUTE_IN_SECONDS), 2, '0', STR_PAD_LEFT) : floor($seconds_to_change / MINUTE_IN_SECONDS);
				continue;
			}
			
			if ($lv == 'minutes_divisor' || $lv == 'minutes_divisor_padded')
			{
				$text[$i] = ($lv == 'minutes_divisor_padded') ? str_pad((floor($seconds_to_change / MINUTE_IN_SECONDS) % MINUTE_IN_SECONDS), 2, '0', STR_PAD_LEFT) : (floor($seconds_to_change / MINUTE_IN_SECONDS) % MINUTE_IN_SECONDS);
				continue;
			}
			
			if ($lv == 'seconds' || $lv == 'seconds_padded')
			{
				$text[$i] = ($lv == 'seconds_padded') ? str_pad($seconds_to_change, 2, '0', STR_PAD_LEFT) : $seconds_to_change;
				continue;
			}
			
			if ($lv == 'seconds_divisor' || $lv == 'seconds_divisor_padded')
			{
				$text[$i] = ($lv == 'seconds_divisor_padded') ? str_pad(($seconds_to_change % MINUTE_IN_SECONDS), 2, '0', STR_PAD_LEFT) : ($seconds_to_change % MINUTE_IN_SECONDS);
				continue;
			}
			
			if ($lv == 'space' || $lv == 'nbsp')
			{
				$text[$i] = ' ';
				continue;
			}
			
			if ($lv == 'comma')
			{
				$text[$i] = ',';
				continue;
			}
			
			if ($lv == 'semicolon' || $lv == 'semi_colon')
			{
				$text[$i] = ';';
				continue;
			}

			if ($lv == 'colon')
			{
				$text[$i] = ':';
				continue;
			}

			if ($lv == 'query' || $lv == 'question' || $lv == 'querymark' || $lv == 'questionmark' || $lv == 'question_mark' || $lv == 'query_mark')
			{
				$text[$i] = '?';
				continue;
			}

			if ($lv == 'exclamation' || $lv == 'exclamationmark' || $lv == 'exclamation_mark')
			{
				$text[$i] = '!';
				continue;
			}

			if ($lv == 'fullstop' || $lv == 'full_stop' || $lv == 'stop' || $lv == 'period' || $lv == 'dot' || $lv == 'point')
			{
				$text[$i] = '.';
				continue;
			}
			
			if ($lv == 'percent' || $lv == 'percentage')
			{
				$text[$i] = '%';
				continue;
			}
			
			if ($lv != NULL)
			{
				$text[$i] = '%' . $lv . '%';
				continue;
			}
		}
		
		$text = array_filter($text,
			function($v)
			{
				return !in_array($v, array(FALSE, NULL, ''), TRUE);
			}
		);
		
		$text = implode('', $text);

		return $text;
	}

	public function sanitize_input($data)
	{
		// Sanitizes and normalizes input data
		
		$stripslashes = (function_exists('wp_magic_quotes')); // Unfortunately, no flag exists
		
		if (!is_array($data))
		{
			if (is_null($data))
			{
				return NULL;
			}
			
			if (is_bool($data))
			{
				return (boolean)$data;
			}

			if (is_string($data) || is_numeric($data))
			{
				return ($stripslashes && is_string($data)) ? stripslashes(wp_kses_stripslashes(sanitize_text_field($data), array())) : wp_kses(sanitize_text_field($data), array());
			}

			return FALSE;
		}
		
		foreach (array_keys($data) as $k)
		{
			if (sanitize_key($k) != $k)
			{
				unset($data[$k]);
				continue;
			}

			if (is_array($data[$k]))
			{
				$data[$k] = $this->sanitize_input($data[$k]);
				continue;
			}

			if (is_null($data[$k]))
			{
				$data[$k] = NULL;
				continue;
			}
			
			if (is_bool($data[$k]))
			{
				$data[$k] = (boolean)$data[$k];
				continue;
			}

			if (!is_string($data[$k]) && !is_numeric($data[$k]))
			{
				$data[$k] = FALSE;
				continue;
			}

			$data[$k] = ($stripslashes && is_string($data[$k])) ? stripslashes(wp_kses_stripslashes(sanitize_text_field($data[$k]), array())) : wp_kses(sanitize_text_field($data[$k]), array());
		}
	
		return $data;
	}
	
	public function phrasing_content($html)
	{
		// Check if HTML string only contains phrasing content
		
		if (preg_match('#</?(?:div|p)(?:\s*|[^a-z][^>]*)>#i', $html))
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	public function sentence_case($string, $force = FALSE, $add_spaces = TRUE)
	{
		// Set text to use sentence case
		
		$ret = '';
		$sentences = preg_split('/([.?!]+)/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($sentences as $key => $sentence)
		{
			if ($add_spaces)
			{
				$sentence = trim($sentence);
			}
			
			if ($force)
			{
				$sentence = strtolower($sentence);
			}
			
			$ret .= (($key & 1) == 0) ? ucfirst($sentence) : $sentence . (($add_spaces) ? ' ' : '');
		}
		
		return trim($ret);
	}
	
	public function loaded()
	{
		// Load languages
		
		load_plugin_textdomain('opening-hours', FALSE, basename(dirname(__FILE__)) . '/languages');

		return TRUE;
	}
	
	public function widget()
	{
		// Initiate widget
		
		register_widget('we_are_open_widget');
	}
	
	private function notification($message = NULL, $heading = NULL, $type = NULL)
	{
		// Display a relevant notification

		if ($message != NULL)
		{
			$html = '<p class="plugin-notification notice is-dismissable' . (($type != NULL) ? esc_attr(' ' . $type) : '') . ' visible">' . PHP_EOL
			. '<span class="close"><a href="#opening-hours-settings" class="button dismiss later" data-notification-action="notification rate later" title="' . esc_attr__('Remind me later', 'opening-hours') . '"><span class="dashicons dashicons-dismiss"></span></a></span>' . PHP_EOL
			. (($heading != NULL) ? '<span class="heading">' . $heading . '</span>' . PHP_EOL : '')
			. '<span class="message">'
			. $message
			. '</span>' . PHP_EOL
			. '<span class="buttons">'
			. '<a href="#opening-hours-settings" class="button ui-button later" data-notification-action="notification rate later">' . esc_html__('Remind me later', 'opening-hours') . '</a> '
			. '<a href="#opening-hours-settings" class="button ui-button dismiss" data-notification-action="notification rate dismiss">' . esc_html__('Dismiss for a year', 'opening-hours') . '</a> '
			. '<a href="#opening-hours-settings" class="button ui-button done" data-notification-action="notification rate done">' . esc_html__('I’ve already left a review', 'opening-hours') . '</a>'
			. '</span>' . PHP_EOL
			. '</p>';
			self::log('notification rate', (($heading != NULL) ? $heading . PHP_EOL : '') . strip_tags($message));

			return $html;
		}

		if (!function_exists('array_column'))
		{
			return '';
		}

		$log = get_option($this->prefix . 'log', array());
		
		if (!is_array($log) || empty($log))
		{
			$initial_version = get_option($this->prefix . 'initial_version', NULL);
			
			if ($initial_version != NULL && floatval($initial_version) <= 1.49 && (empty($log) || is_array($log) && !in_array('notification rate', array_column($log, 'type'))))
			{
				/* translators: 1: The initial version of this plugin, 2: refers to review URL at wordpress.org, 3: string to handle notification data */
				return $this->notification(sprintf(__('You have used this plugin for quite a while, since version %1$s. We’d love to hear what you think about its design, features, support… So, please consider <a href="%2$s" target="_blank" %3$s>leaving a review</a>!', 'opening-hours'), $initial_version, 'https://wordpress.org/support/plugin/opening-hours/reviews/#new-post', 'data-notification-action="notification rate now"'), esc_html__('You’ve experienced using this plugin', 'opening-hours'), 'version-change');
			}
			
			return '';
		}

		if (!is_array($log) || in_array('notification rate done', array_column($log, 'type')))
		{
			return '';
		}
		
		$installation_timestamp = array_search('install', array_column($log, 'type'));
		$reset_timestamp = NULL;
		$reset_timestamp_notify = $installation_timestamp_notify = FALSE;

		if (is_numeric($installation_timestamp) && isset($log[$installation_timestamp]) && isset($log[$installation_timestamp]['time']))
		{
			$installation_timestamp = $log[$installation_timestamp]['time'];

			if (is_numeric($installation_timestamp) && $installation_timestamp < time() - YEAR_IN_SECONDS)
			{
				$installation_timestamp_notify = TRUE;
			}
		}
		else
		{
			$reset_timestamp = array_search('reset', array_column($log, 'type'));
			$reset_timestamp_notify = FALSE;

			if (is_numeric($reset_timestamp) && isset($log[$reset_timestamp]) && isset($log[$reset_timestamp]['time']))
			{
				$reset_timestamp = $log[$reset_timestamp]['time'];

				if (is_numeric($reset_timestamp) && $reset_timestamp < time() - YEAR_IN_SECONDS)
				{
					$reset_timestamp_notify = TRUE;
				}
			}
			else
			{
				$reset_timestamp = NULL;
			}

			$installation_timestamp = NULL;
		}

		$notification_rating = TRUE;
		$notification_rating_now_timestamp = time() - HOUR_IN_SECONDS;
		$notification_rating_later_timestamp = time() - 2 * WEEK_IN_SECONDS;
		$notification_rating_dismiss_timestamp = time() - YEAR_IN_SECONDS;
		$log_keys = array_reverse(array_keys($log));

		foreach ($log_keys as $k)
		{
			if (!isset($log[$k]['type']) || !isset($log[$k]['time']))
			{
				continue;
			}

			if ($log[$k]['type'] == 'notification rate now' && $log[$k]['time'] >= $notification_rating_now_timestamp)
			{
				$notification_rating = FALSE;
				break;
			}

			if ($log[$k]['type'] == 'notification rate later' && $log[$k]['time'] >= $notification_rating_later_timestamp)
			{
				$notification_rating = FALSE;
				break;
			}

			if ($log[$k]['type'] == 'notification rate dismiss' && $log[$k]['time'] >= $notification_rating_dismiss_timestamp)
			{
				$notification_rating = FALSE;
				break;
			}

			if (($log[$k]['type'] == 'install' || $log[$k]['type'] == 'reset') && $log[$k]['time'] >= $notification_rating_later_timestamp)
			{
				$notification_rating = FALSE;
				break;
			}

			if ($log[$k]['time'] < $notification_rating_dismiss_timestamp)
			{
				break;
			}
		}
		
		if ($notification_rating)
		{
			if (is_numeric($installation_timestamp) && $installation_timestamp_notify || is_numeric($reset_timestamp) && $reset_timestamp_notify)
			{
				/* translators: 1: The plugin installation date, 2: refers to review URL at wordpress.org, 3: string to handle notification data */
				return $this->notification(sprintf(__('You have used this plugin for quite a while, since %1$s. We’d love to hear what you think about its design, features, support… So, please consider <a href="%2$s" target="_blank" %3$s>leaving a review</a>!', 'opening-hours'), wp_date("F Y", $installation_timestamp), 'https://wordpress.org/support/plugin/opening-hours/reviews/#new-post', 'data-notification-action="notification rate now"'), esc_html__('You’ve experienced this plugin', 'opening-hours'), 'version-change');
			}

			/* translators: 1: refers to review URL at wordpress.org, 2: string to handle notification data */
			return $this->notification(sprintf(__('We’d love to hear what you think about its design, features, support… So, please consider <a href="%1$s" target="_blank" %2$s>leaving a review</a>!', 'opening-hours'), 'https://wordpress.org/support/plugin/opening-hours/reviews/#new-post', 'data-notification-action="notification rate now"'), esc_html__('Please leave a review for We’re Open!', 'opening-hours'), 'review-reminder');
		}

		return '';
	}

	private function notification_reset()
	{
		// Clear all notifications from log file

		$log = get_option($this->prefix . 'log', array());
		
		if (!is_array($log) || is_array($log) && empty($log))
		{
			return FALSE;
		}

		$cleaned_log = array();

		foreach ($log as $a)
		{
			if (!isset($a['type']) || isset($a['type']) && preg_match('/^notification rate [a-z]{2,25}$/', $a['type']))
			{
				continue;
			}

			$cleaned_log[] = $a;
		}

		if (count($log) == count($cleaned_log))
		{
			return FALSE;
		}

		update_option($this->prefix . 'log', $cleaned_log, 'no');

		return TRUE;
	}

	public static function log($type, $data = NULL)
	{
		// Log actions

		$log = get_option(__CLASS__ . '_log', array());

		if (!is_array($log))
		{
			$log = array();
		}

		$log = array_splice($log, -1000);

		$log[] = array(
			'type' => $type,
			'data' => $data,
			'user' => (function_exists('get_current_user_id')) ? get_current_user_id() : NULL,
			'cron' => (defined('DOING_CRON') && DOING_CRON),
			'time' => time()
		);

		update_option(__CLASS__ . '_log', $log, 'no');

		return TRUE;
	}
}

new we_are_open;
