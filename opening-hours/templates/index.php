<?php

if (!defined('ABSPATH'))
{
	die();
}

list($closure_timestamp_start, $closure_timestamp_end, $closure_date_start, $closure_date_end, $closure_count, $closure_modified) = $this->get_closure();

?>

<div id="opening-hours" class="opening-hours wrap banner closed" data-nonce="<?php echo esc_attr(wp_create_nonce($this->class_name . '_nonce')); ?>">
	<h1 class="wp-heading"><?php esc_html_e('We’re Open!', 'opening-hours'); ?></h1>
	<hr class="wp-header-end banner">
	<div class="regular-special">
		<div class="regular">
			<h2><?php esc_html_e('Regular', 'opening-hours'); ?></h2>
			<?php if ($this->synchronization == 'google_places') : ?>
				<p><?php _e('Your regular business opening hours are synchronized daily from Google My Business.', 'opening-hours'); ?></p>
				<?php elseif ($this->google_data_exists(TRUE)) : ?>
				<p><?php _e('Your regular business opening hours with an option to update from Google My Business.', 'opening-hours'); ?></p>
			<?php else : ?>
				<p><?php _e('Your business regular opening hours.', 'opening-hours'); ?></p>
			<?php endif; ?>
			<form id="open-regular" action="./" method="post">
				<table class="wp-list-table widefat hours">
					<thead>
						<tr>
							<th class="week-day-column"><?php esc_html_e('Weekday', 'opening-hours'); ?></th>
							<th class="hours-column"><?php esc_html_e('Hours', 'opening-hours'); ?></th>
							<th class="modified-column"><?php esc_html_e('Modified', 'opening-hours'); ?></th>
						</tr>
					</thead>
					<tbody>
<?php
for ($i = 0; $i < 2; $i++) :
	foreach ($this->days as $count => $day) :
		if ($i == 0 && $this->week_start > $count || $i == 1 && $this->week_start <= $count) :
			continue;
		endif;
		
		$a = (isset($this->regular[$count])) ? $this->regular[$count] : array();
		$closed = (empty($a) || !empty($a) && isset($a['closed']) && $a['closed']);
		$hours_24 = (!$closed && isset($a['hours_24']) && $a['hours_24']);
?>
						<tr id="regular-hours-<?php echo esc_attr($count); ?>" data-id="<?php echo esc_attr($count); ?>">
							<td class="week-day-column"><?php echo esc_html($day); ?></td>
							<td class="hours-column<?php echo (($closed) ? ' closed' : '') . (($hours_24) ? ' hours-24' : ''); ?>">
								<ul>
									<li id="regular-hours-<?php echo esc_attr($count); ?>-closed" class="closed"<?php echo (!$closed) ? ' style="display: none;"' : ''; ?>>
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-base" class="closed-text"><?php esc_html_e('Closed', 'opening-hours'); ?></a>
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-base" class="add-subtract-toggle"><span class="dashicons dashicons-plus"></span></a>
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-base" class="hours-24"><span class="dashicons dashicons-clock"></span> <?php esc_html_e('24H', 'opening-hours'); ?></a>
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-base" class="paste disabled" title="<?php esc_attr_e('Paste', 'opening-hours'); ?>"><span class="dashicons dashicons-admin-appearance"></span></a>
									</li>
									<li id="regular-hours-<?php echo esc_attr($count); ?>-base" class="base"<?php echo ($closed) ? ' style="display: none;"' : ''; ?>>
										<input type="time" name="regular-time[<?php echo esc_attr($count); ?>][]" id="regular-time-<?php echo esc_attr($count); ?>-start" value="<?php echo (!$hours_24 && isset($a['hours'][0][0])) ? esc_attr($a['hours'][0][0]) : (($hours_24) ? '00:00' : ''); ?>"> — 
										<input type="time" name="regular-time[<?php echo esc_attr($count); ?>][]" id="regular-time-<?php echo esc_attr($count); ?>-end" value="<?php echo (!$hours_24 && isset($a['hours'][0][1])) ? esc_attr($a['hours'][0][1]) : (($hours_24) ? '00:00' : ''); ?>">
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-extended" class="add-subtract-toggle"><span class="dashicons dashicons-plus"></span></a>
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-base" class="hours-24"><span class="dashicons dashicons-clock"></span> <?php esc_html_e('24H', 'opening-hours'); ?></a>
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-base" class="copy" title="<?php esc_attr_e('Copy', 'opening-hours'); ?>"><span class="dashicons dashicons-admin-page"></span></a>
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-base" class="paste disabled" title="<?php esc_attr_e('Paste', 'opening-hours'); ?>"><span class="dashicons dashicons-admin-appearance"></span></a>
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-base" class="closed"><span class="dashicons dashicons-dismiss"></span></a>
									</li>
									<li id="regular-hours-<?php echo esc_attr($count); ?>-extended" class="extended"<?php echo (!isset($a['hours'][1][0]) || $a['hours'][1][0] == NULL) ? ' style="display: none;"' : ''; ?>>
										<input type="time" name="regular-time[<?php echo esc_attr($count); ?>][]" id="regular-time-<?php echo esc_attr($count); ?>-start-extended" value="<?php echo (isset($a['hours'][1][0])) ? esc_attr($a['hours'][1][0]) : ''; ?>"> — 
										<input type="time" name="regular-time[<?php echo esc_attr($count); ?>][]" id="regular-time-<?php echo esc_attr($count); ?>-end-extended" value="<?php echo (isset($a['hours'][1][1])) ? esc_attr($a['hours'][1][1]) : ''; ?>">
										<a href="#regular-hours-<?php echo esc_attr($count); ?>-extended-2" class="add-subtract-toggle"><span class="dashicons dashicons-plus"></span></a>
									</li>
									<li id="regular-hours-<?php echo esc_attr($count); ?>-extended-2" class="extended-2"<?php echo (!isset($a['hours'][2][0]) || $a['hours'][2][0] == NULL) ? ' style="display: none;"' : ''; ?>>
										<input type="time" name="regular-time[<?php echo esc_attr($count); ?>][]" id="regular-time-<?php echo esc_attr($count); ?>-start-extended-2" value="<?php echo (isset($a['hours'][2][0])) ? esc_attr($a['hours'][2][0]) : ''; ?>"> — 
										<input type="time" name="regular-time[<?php echo esc_attr($count); ?>][]" id="regular-time-<?php echo esc_attr($count); ?>-end-extended-2" value="<?php echo (isset($a['hours'][2][1])) ? esc_attr($a['hours'][2][1]) : ''; ?>">
									</li>
								</ul>
							</td>
							<td class="modified-column"><?php echo (isset($a['modified'])) ? ((function_exists('wp_date')) ? esc_attr(wp_date("Y/m/d", $a['modified'])) : esc_attr(date("Y/m/d", $a['modified']))) : '—'; ?></td>
						</tr>
<?php
	endforeach;
endfor;
?>
					</tbody>
				</table>
			</form>
			
		</div>
		<div class="special">
			<h2><?php esc_html_e('Exceptions or Holidays', 'opening-hours'); ?></h2>
			<p><?php _e('These are special opening hours for upcoming holidays which override your regular opening hours.', 'opening-hours'); ?></p>
			<form id="open-special" action="./" method="post">
				<table class="wp-list-table widefat hours">
					<thead>
						<tr>
							<td class="check-column"><input type="checkbox" name="checked[]" id="special-date-status-all" value="all"></td>
							<th class="date-column"><?php esc_html_e('Date', 'opening-hours'); ?><span class="date-hours">/<?php esc_html_e('Hours', 'opening-hours'); ?></span></th>
							<th class="label-column"><?php esc_html_e('Label', 'opening-hours'); ?></th>
							<th class="hours-column"><?php esc_html_e('Hours', 'opening-hours'); ?></th>
							<th class="modified-column"><?php esc_html_e('Modified', 'opening-hours'); ?></th>
						</tr>
					</thead>
					<tbody>
<?php
foreach (array_values($this->special) as $count => $a):
	$closed = (empty($a) || !empty($a) && isset($a['closed']) && $a['closed']);
	$hours_24 = (!$closed && isset($a['hours_24']) && $a['hours_24']);
	$timestamp = (!empty($a) && isset($a['date']) && is_numeric($a['date'])) ? $a['date'] : NULL;
	$date = ($timestamp != NULL && is_numeric($timestamp)) ? ((function_exists('wp_date')) ? wp_date("Y-m-d", $timestamp) : date("Y-m-d", $timestamp)) : NULL;
	$label = (isset($a['label']) && is_string($a['label']) && $a['label'] != NULL) ? $a['label'] : NULL;
?>
						<tr id="special-hours-<?php echo esc_attr($count); ?>" data-id="<?php echo esc_attr($count); ?>">
							<th class="check-column"><input type="checkbox" name="checked[]" id="special-date-status-<?php echo esc_attr($count); ?>" value="<?php echo esc_attr($count); ?>"></th>
							<td class="date-column"><input type="date" name="special-date[<?php echo esc_attr($count); ?>]" id="special-date-<?php echo esc_attr($count); ?>" min="<?php echo ($timestamp < current_time('timestamp')) ? ((function_exists('wp_date')) ? wp_date("Y-m-d", $timestamp) : date("Y-m-d", $timestamp)) : current_time("Y-m-d"); ?>" value="<?php echo esc_attr($date); ?>"></td>
							<td class="label-column"><input type="text" name="special-label[<?php echo esc_attr($count); ?>]" id="special-label-<?php echo esc_attr($count); ?>" placeholder="<?php esc_attr_e('None', 'opening-hours'); ?>" value="<?php echo esc_attr($label); ?>"></td>
							<td class="hours-column<?php echo (($closed) ? ' closed' : '') . (($hours_24) ? ' hours-24' : ''); ?>">
								<ul>
									<li id="special-hours-<?php echo esc_attr($count); ?>-closed" class="closed"<?php echo (!$closed) ? ' style="display: none;"' : ''; ?>>
										<a href="#special-hours-<?php echo esc_attr($count); ?>-base" class="closed-text"><?php esc_html_e('Closed', 'opening-hours'); ?></a>
										<a href="#special-hours-<?php echo esc_attr($count); ?>-extended" class="add-subtract-toggle"><span class="dashicons dashicons-plus"></span></a>
										<a href="#special-hours-<?php echo esc_attr($count); ?>-base" class="hours-24"><span class="dashicons dashicons-clock"></span> <?php esc_html_e('24H', 'opening-hours'); ?></a>
										<a href="#special-hours-<?php echo esc_attr($count); ?>-base" class="copy disabled" title="<?php esc_attr_e('Copy', 'opening-hours'); ?>"><span class="dashicons dashicons-admin-page"></span></a>
										<a href="#special-hours-<?php echo esc_attr($count); ?>-base" class="paste disabled" title="<?php esc_attr_e('Paste', 'opening-hours'); ?>"><span class="dashicons dashicons-admin-appearance"></span></a>
									</li>
									<li id="special-hours-<?php echo esc_attr($count); ?>-base" class="base"<?php echo ($closed) ? ' style="display: none;"' : ''; ?>>
										<input type="time" name="special-time[<?php echo esc_attr($count); ?>][]" id="special-time-<?php echo esc_attr($count); ?>-start" value="<?php echo (!$hours_24 && isset($a['hours'][0][0])) ? esc_attr($a['hours'][0][0]) : (($hours_24) ? '00:00' : ''); ?>"> — 
										<input type="time" name="special-time[<?php echo esc_attr($count); ?>][]" id="special-time-<?php echo esc_attr($count); ?>-end" value="<?php echo (!$hours_24 && isset($a['hours'][0][1])) ? esc_attr($a['hours'][0][1]) : (($hours_24) ? '00:00' : ''); ?>">
										<a href="#special-hours-<?php echo esc_attr($count); ?>-extended" class="add-subtract-toggle"><span class="dashicons dashicons-plus"></span></a>
										<a href="#special-hours-<?php echo esc_attr($count); ?>-base" class="hours-24"><span class="dashicons dashicons-clock"></span> <?php esc_html_e('24H', 'opening-hours'); ?></a>
										<a href="#special-hours-<?php echo esc_attr($count); ?>-base" class="copy" title="<?php esc_attr_e('Copy', 'opening-hours'); ?>"><span class="dashicons dashicons-admin-page"></span></a>
										<a href="#special-hours-<?php echo esc_attr($count); ?>-base" class="paste disabled" title="<?php esc_attr_e('Paste', 'opening-hours'); ?>"><span class="dashicons dashicons-admin-appearance"></span></a>
										<a href="#special-hours-<?php echo esc_attr($count); ?>-base" class="closed"><span class="dashicons dashicons-dismiss"></span></a>
									</li>
									<li id="special-hours-<?php echo esc_attr($count); ?>-extended" class="extended"<?php echo (!isset($a['hours'][1][0]) || $a['hours'][1][0] == NULL) ? ' style="display: none;"' : ''; ?>>
										<input type="time" name="special-time[<?php echo esc_attr($count); ?>][]" id="special-time-<?php echo esc_attr($count); ?>-start-extended" value="<?php echo (isset($a['hours'][1][0])) ? esc_attr($a['hours'][1][0]) : ''; ?>"> — 
										<input type="time" name="special-time[<?php echo esc_attr($count); ?>][]" id="special-time-<?php echo esc_attr($count); ?>-end-extended" value="<?php echo (isset($a['hours'][1][1])) ? esc_attr($a['hours'][1][1]) : ''; ?>">
										<a href="#special-hours-<?php echo esc_attr($count); ?>-extended-2" class="add-subtract-toggle"><span class="dashicons dashicons-plus"></span></a>
									</li>
									<li id="special-hours-<?php echo esc_attr($count); ?>-extended-2" class="extended-2"<?php echo (!isset($a['hours'][2][0]) || $a['hours'][2][0] == NULL) ? ' style="display: none;"' : ''; ?>>
										<input type="time" name="special-time[<?php echo esc_attr($count); ?>][]" id="special-time-<?php echo esc_attr($count); ?>-start-extended-2" value="<?php echo (isset($a['hours'][2][0])) ? esc_attr($a['hours'][2][0]) : ''; ?>"> — 
										<input type="time" name="special-time[<?php echo esc_attr($count); ?>][]" id="special-time-<?php echo esc_attr($count); ?>-end-extended-2" value="<?php echo (isset($a['hours'][2][1])) ? esc_attr($a['hours'][2][1]) : ''; ?>">
									</li>
<?php if ($this->notes) : ?>
									<li id="special-hours-<?php echo esc_attr($count); ?>-note" class="note">
										<input type="text" name="special-note[<?php echo esc_attr($count); ?>][]" id="special-note-<?php echo esc_attr($count); ?>" placeholder="<?php esc_attr_e('Note', 'opening-hours'); ?>" value="<?php echo (isset($a['note'])) ? esc_attr($a['note']) : ''; ?>">
									</li>
<?php endif; ?>
								</ul>
							</td>
							<td class="modified-column"><?php echo (isset($a['modified'])) ? ((function_exists('wp_date')) ? esc_attr(wp_date("Y/m/d", $a['modified'])) : esc_attr(date("Y/m/d", $a['modified']))) : '—'; ?></td>
						</tr>
<?php
endforeach;
?>
						<tr id="special-hours-new">
							<th class="check-column"><input type="checkbox" name="checked[]" id="special-date-status-new" value="new" disabled="disabled"></th>
							<td class="date-column"><input type="date" name="special-date[new]" id="special-date-new" min="<?php echo current_time("Y-m-d"); ?>"></td>
							<td class="label-column"><input type="text" name="special-label[new]" id="special-label-new" placeholder="<?php esc_attr_e('None', 'opening-hours'); ?>" value=""></td>
							<td class="hours-column closed">
								<ul>
									<li id="special-hours-new-closed" class="closed">
										<a href="#special-hours-new-base" class="closed-text"><?php esc_html_e('Closed', 'opening-hours'); ?></a>
										<a href="#special-hours-new-base" class="add-subtract-toggle"><span class="dashicons dashicons-plus"></span></a>
										<a href="#special-hours-new-base" class="hours-24"><span class="dashicons dashicons-clock"></span> <?php esc_html_e('24H', 'opening-hours'); ?></a>
										<a href="#special-hours-new-base" class="copy disabled"><span class="dashicons dashicons-admin-page"></span></a>
										<a href="#special-hours-new-base" class="paste disabled"><span class="dashicons dashicons-admin-appearance"></span></a>
									</li>
									<li id="special-hours-new-base" class="base" style="display: none;">
										<input type="time" name="special-time[new][]" id="special-time-new-start"> — 
										<input type="time" name="special-time[new][]" id="special-time-new-end">
										<a href="#special-hours-new-extended" class="add-subtract-toggle"><span class="dashicons dashicons-plus"></span></a>
										<a href="#special-hours-new-base" class="hours-24"><span class="dashicons dashicons-clock"></span> <?php esc_html_e('24H', 'opening-hours'); ?></a>
										<a href="#special-hours-new-base" class="copy"><span class="dashicons dashicons-admin-page"></span></a>
										<a href="#special-hours-new-base" class="paste disabled"><span class="dashicons dashicons-admin-appearance"></span></a>
										<a href="#special-hours-new-base" class="closed"><span class="dashicons dashicons-dismiss"></span></a>
									</li>
									<li id="special-hours-new-extended" class="extended" style="display: none;">
										<input type="time" name="special-time[new][]" id="special-time-new-start-extended"> — 
										<input type="time" name="special-time[new][]" id="special-time-new-end-extended">
										<a href="#special-hours-new-extended-2" class="add-subtract-toggle"><span class="dashicons dashicons-plus"></span></a>
									</li>
									<li id="special-hours-new-extended-2" class="extended-2" style="display: none;">
										<input type="time" name="special-time[new][]" id="special-time-new-start-extended-2"> — 
										<input type="time" name="special-time[new][]" id="special-time-new-end-extended-2">
									</li>
<?php if ($this->notes) : ?>
									<li id="special-hours-new-note" class="note">
										<input type="text" name="special-note[new][]" id="special-note-new" placeholder="<?php esc_attr_e('Note', 'opening-hours'); ?>" value="">
									</li>
<?php endif; ?>
								</ul>
							</td>
							<td class="modified-column">—</td>
						</tr>
					</tbody>
				</table>
				<p id="delete-notes-possible" class="buttons">
					<a href="#open-special" id="open-delete" class="button ui-button button-secondary disabled"><span class="dashicons dashicons-trash"></span> <?php esc_html_e('Delete', 'opening-hours'); ?></a>
<?php if (!$this->notes) : ?>
					<a href="<?php echo esc_attr(admin_url('admin.php?page=opening_hours&notes=1')); ?>" id="open-notes" class="button ui-button button-secondary"><span class="dashicons dashicons-edit"></span> <?php esc_html_e('Enable Notes', 'opening-hours'); ?></a>
<?php endif; ?>
				</p>
			</form>
			<form id="open-closure" action="./" method="post">
				<div class="closure">
					<h2>
						<?php esc_html_e('Temporary Closure', 'opening-hours'); ?>
						<button type="button" id="closure-toggle" class="button ui-button button-secondary inline" data-show="<?php esc_attr_e('Show', 'opening-hours'); ?>" data-hide="<?php esc_attr_e('Hide', 'opening-hours'); ?>" value="1"><?php echo ($closure_timestamp_start == NULL) ? esc_html__('Show', 'opening-hours') . ' <span class="dashicons dashicons-arrow-down-alt2"></span>' : esc_html__('Hide', 'opening-hours') . ' <span class="dashicons dashicons-arrow-up-alt2"></span>'; ?></button>
					</h2>
					<p id="closure-information"<?php echo ($closure_timestamp_start == NULL) ? ' style="display: none;"' : ''; ?>>
						<?php _e('If you are closed for an extended period of time, you may set an inclusive date range for this period.', 'opening-hours'); ?>
					</p>
					<table id="closure-dates" class="wp-list-table widefat hours"<?php echo ($closure_timestamp_start == NULL) ? ' style="display: none;"' : ''; ?>>
						<thead>
							<tr>
								<th class="date-column"><?php esc_html_e('Date Start', 'opening-hours'); ?></th>
								<th class="date-column"><?php esc_html_e('Date End', 'opening-hours'); ?></th>
								<th class="hours-column"><?php esc_html_e('Hours', 'opening-hours'); ?></th>
								<th class="modified-column"><?php esc_html_e('Modified', 'opening-hours'); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="date-column"><input type="date" name="closure[start]" id="closure-start" min="<?php echo current_time("Y-m-d"); ?>" value="<?php echo esc_attr($closure_date_start); ?>"></td>
								<td class="date-column"><input type="date" name="closure[end]" id="closure-end" min="<?php echo current_time("Y-m-d"); ?>" value="<?php echo esc_attr($closure_date_end); ?>"></td>
								<td class="hours-column closed"><span class="closed-text" data-singular="<?php esc_attr_e('Closed for %s day', 'opening-hours'); ?>" data-plural="<?php
									/* translators: %s will be a number 2 or more */
									esc_attr_e('Closed for %s days', 'opening-hours');?>"><?php
									/* translators: 1: %s will be the number 1, 2: %s will be a number 2 or more */
									echo (is_numeric($closure_count)) ? sprintf(_n('Closed for %s day', 'Closed for %s days', $closure_count, 'opening-hours'), $closure_count) : esc_html_e('Closed', 'opening-hours'); ?></span></td>
								<td class="modified-column"><?php echo ($closure_modified != NULL) ? esc_attr((function_exists('wp_date')) ? wp_date("Y/m/d", $closure_modified) : date("Y/m/d", $closure_modified)) : '—'; ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</form>
		</div>
		<p class="buttons">
			<a href="#open-regular" id="open-save" class="button ui-button button-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Save', 'opening-hours'); ?></a>
<?php if (current_user_can('manage_options', $this->class_name)) : ?>
			<a href="<?php echo esc_attr(admin_url('options-general.php?page=opening_hours_settings')); ?>"  id="open-settings" class="button ui-button button-secondary<?php echo ($this->day_formats == NULL || $this->time_formats == NULL) ? ' action-required' : ''; ?>"><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e('Settings', 'opening-hours'); ?></a>
<?php endif; ?>
<?php if ($this->google_data_exists(TRUE)) : ?>
			<a href="#open-regular" id="open-google-business-populate" class="button ui-button button-secondary"><span class="dashicons dashicons-download"></span> <?php esc_html_e('Populate from Google My Business', 'opening-hours'); ?></a>
<?php endif; ?>
		</p>
	</div>
</div>
