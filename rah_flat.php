<?php

/**
 * Rah_flat plugin for Textpattern CMS.
 *
 * @author  Jukka Svahn
 * @date    2012-
 * @license GNU GPLv2
 * @link    https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2013 Jukka Svahn http://rahforum.biz
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * The plugin class.
 */

class rah_flat
{
	/**
	 * The directory hosting all template files.
	 *
	 * @var string
	 */

	protected $dir;

	/**
	 * Constructor.
	 */

	public function __construct()
	{
		$this->dir = dirname(txpath).'/rah_templates';
		register_callback(array($this, 'fetch_form'), 'form.fetch');
		register_callback(array($this, 'fetch_page'), 'page.fetch');
	}

	/**
	 * Fetches a form template from a flat file.
	 *
	 * @param  string      $event
	 * @param  string      $step
	 * @param  array       $data
	 * @return string|bool
	 */

	public function fetch_form($event, $step, $data)
	{
		$path = $this->dir . '/forms/' . $data['name'] . '.html';

		if (file_exists($path) && is_file($path) && is_readable($path))
		{
			return file_get_contents($path);
		}

		return safe_field('Form', 'txp_form', "name = '".doSlash($data['name'])."'");
	}

	/**
	 * Fetches a page template from a flat file.
	 *
	 * @param  string      $event
	 * @param  string      $step
	 * @param  array       $data
	 * @return string|bool
	 */

	public function fetch_page($event, $step, $data)
	{
		$path = $this->dir . '/pages/' . $data['name'] . '.html';

		if (file_exists($path) && is_file($path) && is_readable($path))
		{
			return file_get_contents($path);
		}

		return safe_field('user_html', 'txp_page', "name = '".doSlash($data['name'])."'");
	}
}

new rah_flat();