<?php

if (!defined('ABSPATH'))
{
	die();
}

?>
	<div class="<?php echo esc_attr($this->reference); ?>">
		<p>
			<label for="<?php echo esc_attr($this->get_field_name('title')); ?>"><?php esc_html_e('Title:', 'opening-hours'); ?></label>
			<input type="text" id="<?php echo esc_attr($this->get_field_id('title')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($title); ?>">
		</p>
		<p class="layout">
			<label for="<?php echo esc_attr($this->get_field_name('layout')); ?>"><?php esc_html_e('Layout:', 'opening-hours'); ?></label>
			<select id="<?php echo esc_attr($this->get_field_id('layout')); ?>" name="<?php echo esc_attr($this->get_field_name('layout')); ?>">
<?php
	foreach ($this->layouts as $k => $layout_name)
	{
?>
				<option value="<?php echo esc_attr($k); ?>"<?php echo (($layout == $k) ? ' selected' : ''); ?>><?php echo esc_html($layout_name); ?></option>
<?php
	}
?>
			</select>
		</p>
		<p class="day-format">
			<label for="<?php echo esc_attr($this->get_field_name('day_format')); ?>"><?php esc_html_e('Day Format:', 'opening-hours'); ?></label>
			<select id="<?php echo esc_attr($this->get_field_id('day_format')); ?>" name="<?php echo esc_attr($this->get_field_name('day_format')); ?>">
<?php
	foreach ($this->day_formats as $k => $a)
	{
?>
				<option value="<?php echo esc_attr($k); ?>"<?php echo ($day_format == $k) ? ' selected' : ''; ?>><?php echo esc_html(((is_numeric($a[3])) ? substr($a[0], 0, $a[3]) : $a[0]) . (($a[2] != NULL) ? $a[2] : '')); ?></option>
<?php
	}
?>
			</select>
		</p>
		<p class="time-format">
			<label for="<?php echo esc_attr($this->get_field_name('time_format')); ?>"><?php esc_html_e('Time Format:', 'opening-hours'); ?></label>
			<select id="<?php echo esc_attr($this->get_field_id('time_format')); ?>" name="<?php echo esc_attr($this->get_field_name('time_format')); ?>">
<?php
	foreach ($this->time_formats as $k => $a)
	{
?>
				<option value="<?php echo esc_attr($k); ?>" class="<?php echo (preg_match('/^g/', $a[1])) ? 'hours-12' : 'hours-24'; ?>" data-php="<?php echo esc_attr($a[1]); ?>" data-initial="<?php echo esc_attr($a[0]); ?>"<?php echo ($time_format == $k) ? ' selected' : ''; ?>><?php echo esc_html($a[0]); ?></option>
<?php
	}
?>
			</select>
		</p>
		<p class="consolidation">
			<label for="<?php echo esc_attr($this->get_field_name('consolidation')); ?>"><?php esc_html_e('Consolidation:', 'opening-hours'); ?></label>
			<select id="<?php echo esc_attr($this->get_field_id('consolidation')); ?>" name="<?php echo esc_attr($this->get_field_name('consolidation')); ?>">
<?php
	foreach ($this->consolidation_types as $k => $consolidation_name)
	{
?>
				<option value="<?php echo esc_attr($k); ?>"<?php echo (($consolidation == $k) ? ' selected' : ''); ?>><?php echo esc_html($consolidation_name); ?></option>
<?php
	}
?>
			</select>
		</p>
		<p class="regular-only">
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr($this->get_field_id('regular_only')); ?>" name="<?php echo esc_attr($this->get_field_name('regular_only')); ?>" value="1"<?php echo ((isset($regular_only) && $regular_only) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('regular_only')); ?>"><?php esc_html_e('Display regular opening hours only', 'opening-hours'); ?></label><br>
		</p>
		<p class="closed-show">
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr($this->get_field_id('closed_show')); ?>" name="<?php echo esc_attr($this->get_field_name('closed_show')); ?>" value="1"<?php echo ((!isset($closed_show) || isset($closed_show) && $closed_show) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('closed_show')); ?>"><?php esc_html_e('Display closed days', 'opening-hours'); ?></label><br>
		</p>
		<p class="buttons"><a href="<?php echo esc_attr($this->plugin_url); ?>" class="button button-secondary"><?php esc_html_e('Opening Hours', 'opening-hours'); ?></a> <a href="<?php echo esc_attr($this->plugin_settings_url); ?>" class="button button-secondary"><?php esc_html_e('Settings', 'opening-hours'); ?></a></p>
	</div>
