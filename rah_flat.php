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

            if (get_pref('production_status') !== 'live')
            {
                register_callback(array($this, 'import'), 'textpattern');
            }

            if (txpinterface === 'admin')
            {
                unset(
                    $GLOBALS['txp_permissions']['section'],
                    $GLOBALS['txp_permissions']['form'],
                    $GLOBALS['txp_permissions']['page']
                );

                register_callback(array($this, 'import'), 'admin_side', 'body_end');
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
                set_pref($name, $val[1], 'rah_flat', PREF_ADVANCED, $val[0], $position);
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
     * Imports all assets.
     */

    public function import()
    {
        $this->importSections();
        $this->importPages();
        $this->importForms();
    }

    /**
     * Imports form partials.
     */

    public function importForms()
    {
        if (($files = $this->getFiles('forms')) !== false)
        {
            if (safe_query('truncate table ' . safe_pfx('txp_form')) === false)
            {
                return false;
            }

            foreach ($files as $file)
            {
                $name = pathinfo(pathinfo($file, PATHINFO_FILENAME));
                $code = file_get_contents($file);

                safe_insert(
                    'txp_form',
                    "name = '".doSlash($name['filename'])."',
                    type = '".doSlash($name['extension'])."',
                    Form = '".doSlash($code)."'"
                );
            }
        }
    }

    /**
     * Imports page templates.
     */

    public function importPages()
    {
        if (($files = $this->getFiles('pages')) !== false)
        {
            if (safe_query('truncate table ' . safe_pfx('txp_page')) === false)
            {
                return false;
            }

            foreach ($files as $file)
            {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $code = file_get_contents($file);

                safe_insert(
                    'txp_page',
                    "name = '".doSlash($name)."',
                    user_html = '".doSlash($code)."'"
                );
            }
        }
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
        $files = $this->getFiles($directory);

        if ($files !== false)
        {
            if (safe_query('truncate table ' . safe_pfx($table)) === false)
            {
                return false;
            }

            $columns = doArray((array) @getThings('describe '.safe_pfx($table)), 'strtolower');

            foreach ($files as $file)
            {
                if (($json = file_get_contents($file)) && $json = @json_decode($json, true))
                {
                    $sql = array();

                    foreach ($json as $key => $value)
                    {
                        if (in_array(strtolower((string) $key), $columns, true))
                        {
                            $sql[] = $this->formatStatement($key, $value);
                        }
                    }

                    if ($sql && safe_insert($table, implode(',', $sql)) === false)
                    {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Lists files in a directory.
     *
     * @return array|bool
     */

    protected function getFiles($directory)
    {
        $out = array();
        $dir = $this->dir . '/' . $directory;

        if (file_exists($dir) && is_dir($dir) && is_readable($dir) && $cwd = getcwd() && chdir($dir))
        {
            foreach ((array) glob('*') as $file)
            {
                if (is_file($file) && is_readable($file))
                {
                    $out[] = $directory . '/' . $file;
                }
            }

            chdir($cwd);
            return $out;
        }

        return false;
    }

    /**
     * Formats a SQL insert statement value.
     *
     * @param  string $field The field
     * @param  string $value The value
     * @return mixed
     */

    protected function formatStatement($field, $value)
    {
        if ($value === null)
        {
            return "`{$field}` = NULL";
        }

        if (is_bool($value) || is_int($value))
        {
            return "`{$field}` = ".intval($value);
        }

        if (is_array($value))
        {
            $value = implode(', ', $value);
        }

        return "`{$field}` = '".doSlash((string) $value)."'";
    }
}

new rah_flat();