<?php

/**
 * Plugin Name: We’re Open!
 * Plugin URI: https://wordpress.org/plugins/opening-hours/
 * Description: Smart and easy management for your business’ regular and special opening hours with Structured Data plus populate from Google My Business
 * Version: 2.3
 * Author: Noah Hearle, Design Extreme
 * Author URI: https://designextreme.com/wordpress/
 * Donate link: https://paypal.me/designextreme
 * License: GPLv3
 * Network: False
 *
 * Text Domain: opening-hours
 */

/**
 *  We’re Open!
 *  Copyright 2020 Noah Hearle <wordpress-plugins@designextreme.com>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


if (!defined('ABSPATH'))
{
	die();
}

require_once(plugin_dir_path(__FILE__) . 'index.php');
require_once(plugin_dir_path(__FILE__) . 'cron.php');
require_once(plugin_dir_path(__FILE__) . 'widget.php');

register_activation_hook(__FILE__, array('we_are_open', 'activate'));
register_deactivation_hook(__FILE__, array('we_are_open', 'deactivate'));
register_uninstall_hook(__FILE__, array('we_are_open', 'uninstall'));
add_action('upgrader_process_complete', array('we_are_open', 'upgrade'), 10, 2);
