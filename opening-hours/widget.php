<?php

if (!defined('ABSPATH'))
{
	die();
}

class we_are_open_widget extends WP_Widget
{
	public
		$data = array(),
		$days = array(),
		$regular = array(),
		$special = array(),
		$closure = array(),
		$consolidation = array();

	private 
		$class_name = NULL,
		$alias = NULL,
		$prefix = NULL,
		$reference = NULL,
		$week_start = NULL,
		$current_timestamp = NULL,
		$today_timestamp = NULL,
		$today = NULL,
		$yesterday_timestamp = NULL,
		$yesterday = NULL,
		$tomorrow_timestamp = NULL,
		$tomorrow = NULL,
		$week_start_timestamp = NULL,
		$next_week_start_timestamp = NULL,
		$day_range_min = NULL,
		$day_formats = array(),
		$offset = NULL,
		$time_formats = array(),
		$accepted_day_format = NULL,
		$layouts = array(),
		$consolidation_types = array(),
		$time_format = NULL,
		$weekdays = array(),
		$weekend = array(),
		$api_key = NULL,
		$place_id = NULL,
		$plugin_url = NULL,
		$plugin_settings_url = NULL;
	
	public function __construct()
	{
		// Class contructor that starts everything

		$this->class_name = 'we_are_open_widget';
		$this->alias = preg_replace('/^(.+)[_-][^_-]+$/', '$1', $this->class_name);
		$this->prefix = $this->alias . '_';
		$this->reference = preg_replace('/[^0-9a-z-]/', '-', $this->alias);

		parent::__construct($this->alias, __('We’re Open!', 'opening-hours'), array(
			'description' => __('Have your opening hours appear in your sidebar', 'opening-hours'),
			'classname' => $this->reference
		));
		
		$this->offset = round(floatval(get_option('gmt_offset')) * 3600);
		$this->current_timestamp = time();
		$this->week_start_timestamp = NULL;
		$this->next_week_start_timestamp = NULL;		
		$this->accepted_day_format = '/^[dDjlSwzFmntYy ,.:;_()-]+$/';
		$this->day_range_min = 3;
		$this->days = array();
		$this->weekdays = get_option($this->prefix . 'weekdays', array());
		$this->weekend = get_option($this->prefix . 'weekend', array());

		$this->today_timestamp = mktime(0, 0, ($this->offset * -1), wp_date("m", $this->current_timestamp), wp_date("j", $this->current_timestamp), wp_date("Y", $this->current_timestamp));
		$this->yesterday_timestamp = mktime(0, 0, ($this->offset * -1), wp_date("m", $this->current_timestamp), wp_date("j", $this->current_timestamp) - 1, wp_date("Y", $this->current_timestamp));
		$this->tomorrow_timestamp = mktime(0, 0, ($this->offset * -1), wp_date("m", $this->current_timestamp), wp_date("j", $this->current_timestamp) + 1, wp_date("Y", $this->current_timestamp));
		$this->today = wp_date("w", $this->today_timestamp);
		$this->yesterday = wp_date("w", $this->yesterday_timestamp);
		$this->tomorrow = wp_date("w", $this->tomorrow_timestamp);
		$this->week_start = (intval(get_option($this->prefix . 'week_start')) < 0) ? wp_date("w") : get_option($this->prefix . 'week_start');

		for ($i = 0; $i < 7; $i++)
		{
			$this->days[$i] = $this->sentence_case(wp_date("l", 1590883200 + $i * 86400 + ($this->offset * -1)));
			
			if ($this->week_start_timestamp == NULL && $this->week_start == wp_date("w", mktime(0, 0, 0, wp_date("m"), wp_date("j") + $i, wp_date("Y"))))
			{
				$this->week_start_timestamp = mktime(0, 0, ($this->offset * -1), wp_date("m"), wp_date("j") + (($i > 0) ? $i - 7 : 0), wp_date("Y"));
				$this->next_week_start_timestamp = mktime(0, 0, ($this->offset * -1), wp_date("m"), wp_date("j") + (($i > 0) ? $i : 7), wp_date("Y"));
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
			'12_dot_gap' => array('9:30am – 5.00 pm', 'g.i a', FALSE),
			'12_dot_gap_uc' => array('9:30AM – 5.00 PM', 'g.i A', FALSE),
			'12_dot_gap_trim' => array('9:30 am – 5 pm', 'g.i a', TRUE),
			'12_dot_gap_uc_trim' => array('9:30 AM – 5 PM', 'g.i A', TRUE),
			'12_dot' => array('9:30am – 5.00pm', 'g.ia', FALSE),
			'12_dot_uc' => array('9:30AM – 5.00PM', 'g.iA', FALSE),
			'12_dot_trim' => array('9.30am – 5pm', 'g.ia', TRUE),
			'12_dot_uc_trim' => array('9.30AM – 5PM', 'g.iA', TRUE),
			'24_none' => array('0930 – 1700', 'Hi', FALSE),
			'24_colon' => array('09:30 – 17:00', 'H:i', FALSE),
			'24_dot_single_digit' => array('9:30 – 17:00', 'G:i', FALSE),
			'24_colon_trim' => array('09:30 – 17', 'H:i', TRUE),
			'24_colon_dash' => array('09:30 – 17:–', 'H:i', '–'),
			'24_colon_mdash' => array('09:30 – 17:—', 'H:i', '—'),
			'24_dot_single_digit_dash' => array('9:30 – 17:–', 'G:i', '–'),
			'24_dot_single_digit_mdash' => array('9:30 – 17:—', 'G:i', '—'),
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
		
		$this->layouts = array(
			'table' => __('Table', 'opening-hours'),
			'table_hours-right' => __('Table, Hours Right', 'opening-hours'),
			'table_closed-italic' => __('Table, Closed Italic', 'opening-hours'),
			'table_closed-bold' => __('Table, Closed Bold', 'opening-hours'),
			'table_hours-right_closed-italic' => __('Table, Hours Right, Closed Italic', 'opening-hours'),
			'table_hours-right_closed-bold' => __('Table, Hours Right, Closed Bold', 'opening-hours'),
			'table_outside-flush' => __('Table, Outside Flush', 'opening-hours'),
			'table_hours-right_outside-flush' => __('Table, Hours Right, Outside Flush', 'opening-hours'),
			'table_closed-italic_outside-flush' => __('Table, Closed Italic, Outside Flush', 'opening-hours'),
			'table_hours-right_closed-italic_outside-flush' => __('Table, Hours Right, Closed Italic, Outside Flush', 'opening-hours'),
			'table_closed-bold_outside-flush' => __('Table, Closed Bold, Outside Flush', 'opening-hours'),
			'table_hours-right_closed-bold_outside-flush' => __('Table, Hours Right, Closed Bold, Outside Flush', 'opening-hours'),
			'list' => __('Paragraph List', 'opening-hours'),
			'ul' => __('Unordered List', 'opening-hours'),
			'ol' => __('Ordered List', 'opening-hours'),
			'lines' => __('Text, Separate Lines', 'opening-hours'),
			'sentence' => __('Text, Sentence', 'opening-hours')
		);
		
		$this->consolidation_types = array(
			NULL => __('None', 'opening-hours'),
			'weekdays' => __('Weekdays only', 'opening-hours'),
			'weekend' => __('Weekend only', 'opening-hours'),
			'separate' => __('Weekdays and weekend, separately', 'opening-hours'),
			'all' => __('All days', 'opening-hours')
		);
		
		$this->regular = array();
		$this->special = array();
		$this->consolidation = array();
		$this->time_format = get_option($this->prefix . 'time_format');
		$this->plugin_url = admin_url('admin.php') . '?page=opening_hours';
		$this->plugin_settings_url = admin_url('options-general.php') . '?page=opening_hours_settings';

		add_action('admin_enqueue_scripts', array($this, 'admin_css_load'));
		add_action('admin_enqueue_scripts', array($this, 'admin_js_load'));

		return TRUE;
	}
	
	private function set_localized_dates()
	{
		if (is_array($this->day_formats) && !empty($this->day_formats))
		{
			return TRUE;
		}
		
		for ($i = 0; $i < 7; $i++)
		{
			$this->days[$i] = $this->sentence_case(wp_date("l", 1590883200 + $i * 86400 + ($this->offset * -1)));
		}

		$this->day_formats = array(
			'full' => array($this->sentence_case(wp_date("l", $this->next_week_start_timestamp)), 'l', NULL, NULL),
			'full_colon' => array($this->sentence_case(wp_date("l", $this->next_week_start_timestamp)), 'l', ':', NULL),
			'short' => array($this->sentence_case(wp_date("D", $this->next_week_start_timestamp)), 'D', NULL, NULL),
			'short_colon' =>array($this->sentence_case(wp_date("D", $this->next_week_start_timestamp)), 'D', ':', NULL),
			'short_dot' => array($this->sentence_case(wp_date("D", $this->next_week_start_timestamp)), 'D', '.', NULL),
			'initial' => array(substr(ucfirst(wp_date("D", $this->next_week_start_timestamp)), 0, 1), 'D', NULL, 1),
			'initial_colon' => array(substr(ucfirst(wp_date("D", $this->next_week_start_timestamp)), 0, 1), 'D', ':', 1),
			'initial_dot' => array(substr(ucfirst(wp_date("D", $this->next_week_start_timestamp)), 0, 1), 'D', '.', 1),
			'short_date_short_month' => array($this->sentence_case(wp_date("D jS M", $this->next_week_start_timestamp)), 'D jS M', NULL, NULL),
			'short_date_short_month_comma' => array($this->sentence_case(wp_date("D, jS M", $this->next_week_start_timestamp)), 'D, jS M', NULL, NULL),
			'short_date_short_month_colon' => array($this->sentence_case(wp_date("D jS M", $this->next_week_start_timestamp)), 'D jS M', ':', NULL),
			'short_date_short_month_comma_colon' => array($this->sentence_case(wp_date("D, jS M", $this->next_week_start_timestamp)), 'D, jS M', ':', NULL),
			'full_date' => array($this->sentence_case(wp_date("l jS", $this->next_week_start_timestamp)), 'l jS', NULL, NULL),
			'full_date_comma' => array($this->sentence_case(wp_date("l, jS", $this->next_week_start_timestamp)), 'l, jS', NULL, NULL),
			'full_date_colon' => array($this->sentence_case(wp_date("l jS", $this->next_week_start_timestamp)), 'l jS', ':', NULL),
			'full_date_comma_colon' => array($this->sentence_case(wp_date("l, jS", $this->next_week_start_timestamp)), 'l, jS', ':', NULL),
			'full_date_month' => array($this->sentence_case(wp_date("l jS F", $this->next_week_start_timestamp)), 'l jS F', NULL, NULL),
			'full_date_month_comma' => array($this->sentence_case(wp_date("l, jS F", $this->next_week_start_timestamp)), 'l, jS F', NULL, NULL),
			'full_date_month_colon' => array($this->sentence_case(wp_date("l jS F", $this->next_week_start_timestamp)), 'l jS F', ':', NULL),
			'full_date_month_comma_colon' => array($this->sentence_case(wp_date("l, jS F", $this->next_week_start_timestamp)), 'l, jS F', ':', NULL),
			'full_date_short_month' => array($this->sentence_case(wp_date("l jS M", $this->next_week_start_timestamp)), 'l jS M', NULL, NULL),
			'full_date_short_month_comma' => array($this->sentence_case(wp_date("l, jS M", $this->next_week_start_timestamp)), 'l, jS M', NULL, NULL),
			'full_date_short_month_colon' => array($this->sentence_case(wp_date("l jS M", $this->next_week_start_timestamp)), 'l jS M', ':', NULL),
			'full_date_short_month_comma_colon' => array($this->sentence_case(wp_date("l, jS M", $this->next_week_start_timestamp)), 'l, jS M', ':', NULL)
		);
		
		return TRUE;				
	}
	
	public function set($data = NULL)
	{
		// Set changeable data to use in Widget
		
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
		$consolidation_cache = FALSE;
		
		if ($this->time_format == NULL)
		{
			return TRUE;
		}

		$this->regular = (isset($regular) && is_array($regular)) ? $regular : ((is_array($this->regular) && !empty($this->regular)) ? $this->regular : get_option($this->prefix . 'regular'));
		$this->special = (isset($special) && is_array($special)) ? $special : ((is_array($this->special) && !empty($this->special)) ? $this->special : get_option($this->prefix . 'special'));
		$this->closure = (isset($closure) && is_array($closure)) ? $closure : ((is_array($this->closure) && !empty($this->closure)) ? $this->closure : get_option($this->prefix . 'closure'));
		$this->consolidation = (isset($consolidation) && is_array($consolidation)) ? $consolidation : ((is_array($this->consolidation) && !empty($this->consolidation)) ? $this->consolidation : array());
		$this->api_key = (isset($api_key) && is_array($api_key) && $api_key != NULL) ? $api_key : get_option($this->prefix . 'api_key');
		$this->place_id = (isset($place_id) && is_array($place_id) && $place_id != NULL) ? $place_id : get_option($this->prefix . 'place_id');
		$consolidation = (is_array($data) && array_key_exists('consolidation', $data)) ? ((isset($consolidation)) ? $consolidation : NULL) : get_option($this->prefix . 'consolidation');

		$cache = FALSE;
		$consolidation_cache = FALSE;
		
		$this->regular = get_option($this->prefix . 'regular');
		$this->special = get_option($this->prefix . 'special');
				
		if (!is_array($this->data) || is_array($this->data) && empty($this->data))
		{
			$cache = wp_cache_get('data', $this->alias);
			
			if (is_array($cache) && array_key_exists($hash_key, $cache))
			{
				$this->data = $cache[$hash_key];
			}
		}
		
		if ($consolidation != NULL && (!is_array($this->consolidation) || is_array($this->consolidation) && empty($this->consolidation) && $consolidation != NULL))
		{
			$consolidation_cache = wp_cache_get('consolidation', $this->alias);
			
			if (is_array($consolidation_cache) && array_key_exists($hash_key, $consolidation_cache))
			{
				$this->consolidation = $consolidation_cache[$hash_key];
			}
		}

		$this->regular = get_option($this->prefix . 'regular');
		$this->special = get_option($this->prefix . 'special');
		$this->consolidation = get_option($this->prefix . 'consolidation');

		$this->data = array();
		$this->consolidation = array();
		
		if (is_array($cache) || is_array($consolidation_cache))
		{
			wp_cache_delete('data', $this->alias);
			wp_cache_delete('consolidation', $this->alias);
		}
		
		if (isset($start) && is_numeric($start) && $start > 0)
		{
			$week_start = wp_date("w", $start);
		}
		else
		{
			$start = NULL;
		}
		
		if (isset($end) && is_numeric($end) && $end > 0)
		{
			if (is_numeric($start) && $start < $end && ceil(($end - $start)/86400) > 31)
			{
				$end = ($start + 31 * 86400);
			}
			
			$count = (is_numeric($start) && $start > 0 && $start < $end) ? ceil(($end - $start)/86400) : NULL;
		}
		else
		{
			$end = NULL;
		}
		
		$days = array();
		$closed_show = (!isset($closed_show) || isset($closed_show) && $closed_show);
		$count = (isset($count) && is_numeric($count) && $count >= 1 && $count <= 31) ? $count : 7;
		
		$week_start = (isset($week_start) && is_numeric($week_start)) ? (($week_start < 0) ? (($week_start == -2) ? $this->yesterday : $this->today) : $week_start) : $this->week_start;
		
		for ($i = (($this->today == $week_start) ? 0 : -7); $i <= $count; $i++)
		{
			if (count($days) == $count)
			{
				break;
			}
			
			$timestamp = mktime(0, 0, ($this->offset * -1), gmdate("m", $this->current_timestamp), gmdate("j", $this->current_timestamp) + $i, gmdate("Y", $this->current_timestamp));
			
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
		$regular = (isset($regular) && is_bool($regular)) ? $regular : TRUE;
		$special = (isset($special) && is_bool($special) && ($special || $regular)) ? $special : TRUE;
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
			
			if (!$regular && (!is_array($this->special) || !array_key_exists($timestamp, $this->special)))
			{
				continue;
			}
			
			if ($consolidation == 'all' || $consolidation == 'separate' || $consolidation == 'weekdays' && $day_weekday || $consolidation == 'weekend' && $day_weekend)
			{
				if ($closed)
				{
					$consecutive[$i] = 'closed';
					
					if (!array_key_exists('closed', $this->consolidation))
					{
						$this->consolidation['closed'] = array();
					}
					
					$this->consolidation['closed'][$i] = array(
						'timestamp' => $timestamp,
						'weekday' => $day_weekday,
						'weekend' => $day_weekend
					);
				}
				elseif (isset($a['hours_24']) && $a['hours_24'])
				{
					$consecutive[$i] = 'hours_24';
					
					if (!array_key_exists('hours_24', $this->consolidation))
					{
						$this->consolidation['hours_24'] = array();
					}
					
					$this->consolidation['hours_24'][$i] = array(
						'timestamp' => $timestamp,
						'weekday' => $day_weekday,
						'weekend' => $day_weekend
					);
				}
				else
				{
					$hours_key = md5(serialize(array_values($a['hours'])));
					
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
								if ($consecutive[$j] == 'hours_' . $hours_key)
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
						'weekend' => $day_weekend
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
					'weekend' => $day_weekend
				);
			}
			
			if ($closed && !$closed_show)
			{
				continue;
			}
						
			$this->data[$timestamp] = array(
				'date' => $timestamp,
				'regular' => ((!is_array($this->special) || !array_key_exists($timestamp, $this->special)) && (empty($this->closure) || !empty($this->closure) && ($timestamp < $this->closure['start'] || $timestamp >= $this->closure['end']))),
				'special' => (is_array($this->special) && array_key_exists($timestamp, $this->special) || !empty($this->closure) && $timestamp >= $this->closure['start'] && $timestamp < $this->closure['end']),
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
						$weekday = $days[($count + $i)]['weekday'];
						$weekend = $days[($count + $i)]['weekend'];
						
						if ($consolidation == 'separate')
						{
							if ($weekday)
							{
								$consolidated['weekdays'][] = $days[($count + $i)]['timestamp'];
							}
							elseif ($weekend)
							{
								$consolidated['weekend'][] = $days[($count + $i)]['timestamp'];
							}
						}
						
						$consolidated['days'][] = $days[($count + $i)]['timestamp'];
						
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
		$cache_refresh_time = ($cache_refresh_time > 3600) ? 3600 : $cache_refresh_time;
		
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
			wp_cache_add('data', $cache, $this->alias, $cache_refresh_time);
			
			if ($consolidation != NULL)
			{
				$consolidation_cache[$hash_key] = $this->consolidation;
				wp_cache_add('consolidation', $consolidation_cache, $this->alias, $cache_refresh_time);
			}
		}
		
		return TRUE;
	}
	
	private function default_values()
	{
		// Set the default values
		
		$defaults = array(
			'title' => __('Opening Hours', 'opening-hours'),
			'regular_only' => FALSE,
			'class' => NULL,
			'closed_show' => TRUE,
			'consolidation' => NULL,
			'day_format' => 'full_colon',
			'time_format' => NULL,
			'layout' => 'table'
		);
		
		foreach (array_keys($defaults) as $k)
		{
			if (get_option($this->prefix . $k, NULL) !== NULL)
			{
				$defaults[$k] = (is_bool($defaults[$k])) ? (boolean)get_option($this->prefix . $k) : get_option($this->prefix . $k);
			}
		}
		
		return $defaults;
	}

	public function update($new_instance, $old_instance = array())
	{
		// Process Dashboard form updates
		
		$ret = array();
		$default_values = $this->default_values();
		$set_default = (!array_key_exists('title', $new_instance));

		foreach ($default_values as $k => $v)
		{
			if ($set_default)
			{
				$ret[$k] = $v;
				continue;
			}

			if (is_bool($v))
			{
				$ret[$k] = FALSE;
			}
		}

		foreach ($new_instance as $k => $v)
		{
			if ($k == 'time_format')
			{
				$this->time_format = $v;
			}
			
			if (!array_key_exists($k, $new_instance))
			{
				$default_value = (array_key_exists($k, $default_values)) ? $default_values[$k] : NULL;

				if ($set_default)
				{
					$ret[$k] = $default_value;
					continue;
				}
				
				$ret[$k] = (is_bool($default_value)) ? FALSE : NULL;
				continue;
			}
			
			if ($v == NULL)
			{
				$ret[$k] = NULL;
				continue;
			}
			
			if (is_numeric($v))
			{
				if ($k == 'regular_only' || $k == 'closed_show')
				{
					$ret[$k] = ($v == 1);
					continue;
				}
				
				$ret[$k] = intval($v);
				continue;
			}
			
			$ret[$k] = $v;
		}
		
		return $ret;
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
		$time_trim = (is_bool($this->time_formats[$time_format_key][2]) && $this->time_formats[$time_format_key][2]);
		$time_minute_replacement = (is_string($this->time_formats[$time_format_key][2])) ? $this->time_formats[$time_format_key][2] : NULL;
		$time_separator = (is_array($preferences) && isset($preferences['time_separator'])) ? $preferences['time_separator'] : get_option($this->prefix . 'time_separator');
		$time_group_separator = (is_array($preferences) && isset($preferences['time_group_separator'])) ? $preferences['time_group_separator'] : get_option($this->prefix . 'time_group_separator');
		$time_group_prefix = (is_array($preferences) && isset($preferences['time_group_prefix'])) ? $preferences['time_group_prefix'] : NULL;
		$time_group_suffix = (is_array($preferences) && isset($preferences['time_group_suffix'])) ? $preferences['time_group_suffix'] : NULL;
		$midday_text = (is_array($preferences) && array_key_exists('midday', $preferences)) ? $preferences['midday'] : get_option($this->prefix . 'midday_text');
		$midnight_text = (is_array($preferences) && array_key_exists('midnight', $preferences)) ? $preferences['midnight'] : get_option($this->prefix . 'midnight_text');
		
		if (preg_match('/^([^|]+)\|([^|]+)$/', $time_group_separator, $m))
		{
			$time_group_separator_first = $m[1];
			$time_group_separator_last = $m[2];
		}
		
		$hours = (is_array($hours)) ? array_values($hours) : array();
		
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
				. esc_html($html)
				. (($note != NULL && (!isset($preferences['notes']) || isset($preferences['notes']) && (is_bool($preferences['notes']) && $preferences['notes'] || $preferences['notes'] === 'suffix'))) ? ' <span class="note">' . esc_html($note) . '</span>' : '');
			break;
		}

		return $html;
	}

	public function admin_css_load()
	{
		// Load style sheet in the Dashboard
		
		global $pagenow;
		
		if (!preg_match('/^(?:widgets|customize)\.php$/', $pagenow))
		{
			return;
		}
		
		wp_register_style($this->alias . '_admin_css', plugins_url('opening-hours/admin/css/css.css'));
		wp_enqueue_style($this->alias . '_admin_css');
	}
	
	public function admin_js_load()
	{
		// Load Javascript in the Dashboard
		
		global $pagenow;
		
		if (!preg_match('/^(?:widgets|customize)\.php$/', $pagenow))
		{
			return;
		}
		
		wp_register_script('open_admin_js', plugins_url('opening-hours/admin/js/js.js'));
		wp_localize_script('open_admin_js', 'we_are_open_admin_ajax', array('url' => admin_url('admin-ajax.php'), 'action' => 'we_are_open_admin_ajax'));
		wp_enqueue_script('open_admin_js');
	}
	
	public function widget($args, $instance)
	{
		// Display the widget
		
		$this->set_localized_dates();

		$html = '';
		$default_values = $this->default_values();
		$day_preferences = array();
		$time_preferences = array();

		extract($args, EXTR_SKIP);
		extract($instance, EXTR_SKIP);

		if (count($default_values) > count($instance))
		{
			extract($default_values, EXTR_SKIP);
		}

		$title = apply_filters('widget_title', $title);
		$type = (preg_match('/^([^_]+)_.+$/', $layout, $m)) ? $m[1] : $layout;
		$class = (is_string($class) && $class != NULL) ? $class : ((preg_match('/^[^_]+_(.+)$/', $layout, $m)) ? trim(preg_replace('/[_\s]+/', ' ', $m[1])) : NULL);
		$regular_only = (is_bool($regular_only) && $regular_only || is_string($regular_only) && $regular_only == '1' || is_numeric($regular_only) && $regular_only == 1);
		$closed_show = (is_null($closed_show) || is_bool($closed_show) && $closed_show || is_string($closed_show) && $closed_show == '1' || is_numeric($closed_show) && $closed_show == 1);
		$day_format_key = (is_string($day_format) && array_key_exists($day_format, $this->day_formats)) ? $day_format : get_option($this->prefix . 'day_format');
		$time_format_key = (is_string($time_format) && array_key_exists($time_format, $this->time_formats)) ? $time_format : get_option($this->prefix . 'time_format');
		$time_separator = get_option($this->prefix . 'time_format');
		$time_group_separator = get_option($this->prefix . 'time_group_separator');
		$day_format = $this->day_formats[$day_format_key][1];
		$day_format_suffix = $this->day_formats[$day_format_key][2];
		$day_format_length = $this->day_formats[$day_format_key][3];
		
		if ($consolidation != get_option($this->prefix . 'consolidation'))
		{
			$day_preferences['consolidation'] = $consolidation;
		}
		
		if ($regular_only)
		{
			$day_preferences['special'] = FALSE;
		}
		
		if (!$closed_show)
		{
			$day_preferences['closed_show'] = $closed_show;
		}

		if ($day_format_key != get_option($this->prefix . 'day_format'))
		{
			$day_preferences['day_format'] = $day_format_key;
		}
		
		if ($time_format_key != NULL && $time_format_key != get_option($this->prefix . 'time_format'))
		{
			$time_preferences['time_format'] = $time_format_key;
		}
		
		$this->set($day_preferences);

		switch ($type)
		{
		case 'sentence':
			$first = TRUE;
			$day_separator = (isset($day_separator) && is_string($day_separator) && $day_separator != '') ? $day_separator : ';';
			$day_separator_last = $day_separator;
			$day_end = (isset($day_end) && array_key_exists('day_end', $atts) && isset($day_end)) ? $day_end : '.';
			
			if (preg_match('/^([^|]+)\|([^|]+)$/', $day_separator, $m))
			{
				$day_separator = $m[1];
				$day_separator_last = $m[2];
			}	
			else
			{
				$day_separator_last = $day_separator;
			}
			
			$html .= '<span class="opening-hours opening-hours-widget' . (($class != NULL) ? ' ' . $class : '') . '">
';
			foreach ($this->data as $timestamp => $a)
			{
				$day = $a['day'];
				$day_name = $this->days[$day];
				$day_alias = preg_replace('/[^0-9a-z-]/', '-', strtolower($day_name));
				$count = $a['count'];
				$today = $a['today'];
				$tomorrow = $a['tomorrow'];
				$future = $a['future'];
				$closed = $a['closed'];
				$hours_24 = $a['hours_24'];
				$hours = (is_array($a['hours']) && !$closed && !$hours_24) ? $a['hours'] : array();
				$note = $a['note'];
				$consolidated = $a['consolidated'];
				$consolidated_first = $a['consolidated_first'];
				
				if ($consolidation != NULL && is_array($consolidated) && !$consolidated_first)
				{
					continue;
				}
				
				$html .= '<span class="day-name">' . (($first) ? $this->sentence_case($this->day_string($a, $day_format, $day_format_suffix, $day_format_length, 'html', $day_preferences)) : $this->day_string($a, $day_format, $day_format_suffix, $day_format_length, 'html', $day_preferences)) . '</span> '
				. '<span class="hours' . (($closed) ? ' closed' : (($hours_24) ? ' hours-24' : ((count($hours) > 1) ? ' group-' . count($hours) : ''))) . '">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</span>'
				. (($count < (count($this->data) - 2)) ? $day_separator : (($count < (count($this->data) - 1)) ? $day_separator_last : ''));
				$first = FALSE;
			}

			$html .= $day_end . '</span>
';
			break;
		case 'lines':
			$html .= '<p class="opening-hours opening-hours-widget' . (($class != NULL) ? ' ' . $class : '') . '">
';
			foreach ($this->data as $timestamp => $a)
			{
				$day = $a['day'];
				$day_name = $this->days[$day];
				$count = $a['count'];
				$closed = $a['closed'];
				$hours_24 = $a['hours_24'];
				$hours = (is_array($a['hours']) && !$closed && !$hours_24) ? $a['hours'] : array();
				$note = $a['note'];
				$consolidated = $a['consolidated'];
				$consolidated_first = $a['consolidated_first'];
				
				if ($consolidation != NULL && is_array($consolidated) && !$consolidated_first)
				{
					continue;
				}
				
				$html .= '	<span class="day-name">' . $this->sentence_case($this->day_string($a, $day_format, $day_format_suffix, $day_format_length, 'html', $day_preferences)) . '</span> <span class="hours">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</span>' . (($count < (count($this->data) - 1)) ? '<br>' . PHP_EOL : PHP_EOL);
			}

			$html .= '</p>
';
			break;
		case 'list':
		case 'ol':
		case 'ul':
			$outer_tag = (preg_match('/^[ou].+$/i', $type)) ? ((preg_match('/^[u].+$/i', $type)) ? 'ul' : 'ol') : NULL;
			$inner_tag = (preg_match('/^[ou].+$/i', $type)) ? 'li' : 'p';
			
			if (preg_match('/^[ou]l$/', $outer_tag))
			{
				$html .= '<' . $outer_tag . ' class="opening-hours opening-hours-widget' . (($class != NULL) ? ' ' . $class : '') . '">
';
			}
	
			foreach ($this->data as $timestamp => $a)
			{
				$day = $a['day'];
				$day_name = $this->days[$day];
				$day_alias = preg_replace('/[^0-9a-z-]/', '-', strtolower($day_name));
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
				$note = $a['note'];
				$consolidated = $a['consolidated'];
				$consolidated_first = $a['consolidated_first'];
				
				if ($consolidation != NULL && is_array($consolidated) && !$consolidated_first)
				{
					continue;
				}
				
				$html .= '	<' . $inner_tag . ' class="day '
				. esc_attr($day_alias)
				. (($today) ? ' today' : (($tomorrow) ? ' tomorrow' : ''))
				. (($future) ? ' future' : (($past) ? ' past' : ''))
				. (($weekday) ? ' weekday' : (($weekend) ? ' weekend' : ''))
				. (($closed) ? ' closed' : (($hours_24) ? ' hours-24' : ((count($hours) > 1) ? ' group-' . count($hours) : '')))
				. '">'
				. '<span class="day-name">' . $this->sentence_case($this->day_string($a, $day_format, $day_format_suffix, $day_format_length, 'html', $day_preferences)) . '</span> <span class="hours">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</span></' . $inner_tag . '>
';
			}

			if (preg_match('/^[ou]l$/', $outer_tag))
			{
				$html .= '</' . $outer_tag . '>
';
			}
			break;
		default:
			$html .= '<table class="opening-hours opening-hours-widget' . (($class != NULL) ? ' ' . $class : '') . '">
';
			foreach ($this->data as $timestamp => $a)
			{
				$day = $a['day'];
				$day_name = $this->days[$day];
				$day_alias = preg_replace('/[^0-9a-z-]/', '-', strtolower($day_name));
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
				$note = $a['note'];
				$consolidated = $a['consolidated'];
				$consolidated_first = $a['consolidated_first'];
				
				if ($consolidation != NULL && is_array($consolidated) && !$consolidated_first)
				{
					continue;
				}
				
				$html .= '	<tr class="day '
				. esc_attr($day_alias)
				. (($today) ? ' today' : (($tomorrow) ? ' tomorrow' : ''))
				. (($future) ? ' future' : (($past) ? ' past' : ''))
				. (($weekday) ? ' weekday' : (($weekend) ? ' weekend' : ''))
				. (($closed) ? ' closed' : (($hours_24) ? ' hours-24' : ((count($hours) > 1) ? ' group-' . count($hours) : '')))
				. '">
		<th class="day-name">' . $this->sentence_case($this->day_string($a, $day_format, $day_format_suffix, $day_format_length, 'html', $day_preferences)) . '</th>
		<td class="hours">' . $this->hours_string($hours, $closed, $hours_24, $note, 'html', $time_preferences) . '</td>
	</tr>
';
			}
			$html .= '</table>
';
		}
		
	echo $before_widget . wp_kses(((is_string($title) && $title != NULL) ? $before_title . esc_html($title) . $after_title : '') . $html, array('div' => array('id' => array(), 'class' => array()), 'aside' => array('id' => array(), 'class' => array()), 'section' => array('id' => array(), 'class' => array()), 'span' => array('id' => array(), 'class' => array()), 'ul' => array('id' => array(), 'class' => array()), 'ol' => array('id' => array(), 'class' => array()), 'li' => array('id' => array(), 'class' => array()), 'h1' => array('id' => array(), 'class' => array()), 'h2' => array('id' => array(), 'class' => array()), 'h3' => array('id' => array(), 'class' => array()), 'h4' => array('id' => array(), 'class' => array()), 'h5' => array('id' => array(), 'class' => array()), 'h6' => array('id' => array(), 'class' => array()), 'p' => array('id' => array(), 'class' => array()), 'br' => array(), 'a' => array('id' => array(), 'class' => array(), 'href' => array(), 'target' => array(), 'rel' => array(), 'title' => array()), 'table' => array('id' => array(), 'class' => array()), 'tr' => array('id' => array(), 'class' => array(), 'rowspan' => array()), 'th' => array('id' => array(), 'class' => array(), 'colspan' => array()), 'td' => array('id' => array(), 'class' => array(), 'colspan' => array()), 'thead' => array(), 'tbody' => array(), 'tfoot' => array(), 'code' => array(), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'abbr' => array('title' => array()))) . $after_widget;
	}

	public function sentence_case($string, $force = FALSE)
	{
		// Set text to use sentence case
		
		$ret = '';
		$sentences = preg_split('/([.?!]+)/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($sentences as $key => $sentence)
		{
			if ($force)
			{
				$ret .= (($key & 1) == 0) ? ucfirst(strtolower(trim($sentence))) : $sentence.' ';
				continue;
			}
			
			$ret .= (($key & 1) == 0) ? ucfirst(trim($sentence)) : $sentence.' ';
		}
		
		return trim($ret);
	}
	
	public function form($instance)
	{
		// Display the widget form in Dashboard
				
		$this->set_localized_dates();
		$this->set();
		
		$html = '';
		
		if ($this->time_format == NULL)
		{
			$html = '		<p class="error"><a href="' . esc_attr($this->plugin_settings_url) . '">' . esc_html__('Please set your general opening hours settings before adding this widget', 'opening-hours') . '</a>.</p>
		<p class="buttons"><a href="' . esc_attr($this->plugin_settings_url) . '" class="button button-secondary">' . esc_html__('Settings', 'opening-hours') . '</a></p>
';
			echo wp_kses($html, array('p' => array('id' => array(), 'class' => array()), 'a' => array('href' => array(), 'target' => array(), 'class' => array()), 'strong' => array(), 'em' => array()));
			return;
		}
		
		if (!is_array($this->regular) || empty($this->regular))
		{
			$html = '		<p class="error">' . esc_html__('No regular opening hours are set.', 'opening-hours') . '</p>
';
			echo wp_kses($html, array('p' => array('id' => array(), 'class' => array())));
			return;
		}
		
		$default_values = $this->default_values();

		if (!array_key_exists('title', $instance))
		{
			$instance = array_merge($default_values, $instance);
		}
		
		extract($instance, EXTR_SKIP);
		
		if (count($default_values) != count($instance))
		{
			extract($default_values, EXTR_SKIP);
		}

		include(plugin_dir_path(__FILE__) . 'templates/widget.php');
		return;
	}
}
