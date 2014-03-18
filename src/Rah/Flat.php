<?php

/*
 * rah_flat - Flat templates for Textpattern CMS
 * https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2013 Jukka Svahn
 *
 * This file is part of rah_flat.
 *
 * rah_flat is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * rah_flat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with rah_flat. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Main plugin class.
 */

class Rah_Flat
{
    /**
     * Constructor.
     */

    public function __construct()
    {
        add_privs('prefs.rah_flat', '1');
        register_callback(array($this, 'install'), 'plugin_lifecycle.rah_flat', 'installed');
        register_callback(array($this, 'uninstall'), 'plugin_lifecycle.rah_flat', 'deleted');

        if (get_pref('rah_flat_path')) {
            register_callback(array($this, 'endpoint'), 'textpattern');

            if (get_pref('production_status') !== 'live') {
                register_callback(array($this, 'import'), 'textpattern');
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
                'rah_flat_path' => array('text_input', '../../src/templates'),
                'rah_flat_key'  => array('text_input', md5(uniqid(mt_rand(), true))),
            ) as $name => $val
        ) {
            if (get_pref($name, false) === false) {
                set_pref($name, $val[1], 'rah_flat', PREF_ADVANCED, $val[0], $position);
            }

            $position++;
        }
    }

    /**
     * Uninstaller.
     */

    public function uninstall() {
        safe_delete('txp_prefs', "name like 'rah\_flat\_%'");
    }

    /**
     * Initializes the importers.
     */

    private function init()
    {
        new Rah_Flat_Import_Prefs('prefs');
        new Rah_Flat_Import_Sections('sections');
        new Rah_Flat_Import_Pages('pages');
        new Rah_Flat_Import_Forms('forms');
        new Rah_Flat_Import_Styles('styles');

        safe_query('LOCK TABLES '.implode(' WRITE, ', getThings('show tables')).' WRITE');
        callback_event('rah_flat.import');
        safe_query('UNLOCK TABLES');
    }

    /**
     * Imports all assets.
     */

    public function import()
    {
        try {
            $this->init();
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }
    }

    /**
     * Import endpoint.
     */

    public function endpoint()
    {
        extract(gpsa(array(
            'rah_flat_key',
        )));

        if (!get_pref('rah_flat_key') || get_pref('rah_flat_key') !== $rah_flat_key) {
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        try {
            $this->init();
        } catch (Exception $e) {
            txp_status_header('500 Internal Server Error');

            die(json_encode(array(
                'success' => false,
                'error'   => $e->getMessage(),
            )));
        }

        update_lastmod();

        die(json_encode(array(
            'success' => true,
        )));
    }
}

new Rah_Flat();
