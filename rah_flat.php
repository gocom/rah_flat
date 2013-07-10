<?php

/**
 * Rah_flat plugin for Textpattern CMS.
 *
 * @author  Jukka Svahn
 * @license GNU GPLv2
 * @link    https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2013 Jukka Svahn http://rahforum.biz
 * Licensed under GNU General Public License version 2
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
        add_privs('prefs.rah_flat', '1');
        register_callback(array($this, 'install'), 'plugin_lifecycle.rah_flat', 'installed');
        register_callback(array($this, 'uninstall'), 'plugin_lifecycle.rah_flat', 'deleted');

        if ($this->dir = get_pref('rah_flat_path'))
        {
            $this->dir = txpath . '/' . $this->dir;
            register_callback(array($this, 'fetch_form'), 'form.fetch');
            register_callback(array($this, 'fetch_page'), 'page.fetch');

            if (get_pref('production_status') !== 'live')
            {
                register_callback(array($this, 'importSections'), 'textpattern');
            }
        }
    }

    /**
     * Installer.
     */

    public function install()
    {
        $position = 250;

        foreach (
            array(
                'rah_flat_path' => array('text_input', '../templates'),
            ) as $name => $val
        )
        {
            if (get_pref($name, false) === false)
            {
                set_pref($name, $val[1], 'rah_flat', PREF_PLUGIN, $val[0], $position);
            }

            $position++;
        }
    }

    /**
     * Uninstaller.
     */

    public function uninstall()
    {
        safe_delete('txp_prefs', "name like 'rah\_flat\_%'");
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

        if ($this->is_valid_name($data['name']) && file_exists($path) && is_file($path) && is_readable($path))
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

        if ($this->is_valid_name($data['name']) && file_exists($path) && is_file($path) && is_readable($path))
        {
            return file_get_contents($path);
        }

        return safe_field('user_html', 'txp_page', "name = '".doSlash($data['name'])."'");
    }

    /**
     * Validates the given template name.
     *
     * This method makes sure the template name
     * can be safely used in a filename.
     *
     * @return bool TRUE if validates
     */

    protected function is_valid_name($name)
    {
        return (bool) preg_match('/^[a-z0-9_]+[a-z0-9_\-\.,]?$/i', $name);
    }

    /**
     * Imports sections.
     *
     * @return bool
     */

    public function importSections()
    {
        return $this->importTable('sections', 'txp_section');
    }

    /**
     * Imports a JSON files to a database table.
     *
     * @param  string $directory The directory
     * @param  string $table     The database table
     * @return bool
     */

    protected function importTable($directory, $table)
    {
        if (is_dir($this->dir . '/' . $directory) && $dir = getcwd() && chdir($this->dir . '/' . $directory))
        {
            safe_query('truncate table ' . safe_pfx($table));

            foreach ((array) glob('*') as $file)
            {
                if (is_file($file) && is_readable($file) && $content = file_get_contents($file))
                {
                    if ($json = json_decode($json, true))
                    {
                        $sql = array();

                        foreach ($json as $key => $value)
                        {
                            $sql[] = "`{$key}`='".doSlash((string) $value)."'";
                        }

                        if (safe_insert($table, implode(',', $sql)) === false)
                        {
                            return false;
                        }
                    }
                }
            }

            chdir($dir);
        }

        return true;
    }
}

new rah_flat();