<?php

if (!defined('ABSPATH'))
{
	die();
}

class we_are_open_cron
{
	private $sapi = NULL;
	
	public function __construct()
	{
		$this->sapi = php_sapi_name();
		
		add_action('wp', array($this, 'cron_scheduler'));
		add_action('we_are_open_run', array($this, 'cron_cast'));
		
		return TRUE;
	}
	
	public function cron_scheduler()
	{
		if (!wp_next_scheduled('we_are_open_run'))
		{
			wp_schedule_event(time(), 'hourly', 'we_are_open_run');
		}
		
		return TRUE;
	}
	
	public function deactivate()
	{
		wp_clear_scheduled_hook('we_are_open_run');
		
		return TRUE;
	}
	
	public function cron_cast()
	{
		require_once(plugin_dir_path(__FILE__) . 'index.php');
		
		defined('DOING_CRON') or define('DOING_CRON', (preg_match('/^cli/i', $this->sapi)));
		$we_are_open = new we_are_open;
		$we_are_open->sync();
		
		return TRUE;
	}
}

new we_are_open_cron();
