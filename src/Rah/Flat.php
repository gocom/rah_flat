<?php

/*
 * rah_flat - Flat templates for Textpattern CMS
 * https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2015 Jukka Svahn
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
 *
 * @internal
 */

class Rah_Flat
{
    protected $deleting = false;

    /**
     * Constructor.
     */

    public function __construct()
    {
        if (@txpinterface == 'admin') {
            add_privs('prefs.rah_flat', '1');
            add_privs('prefs.rah_flat_var', '1');
            register_callback(array($this, 'options'), 'plugin_prefs.rah_flat', null, 1);
            register_callback(array($this, 'install'), 'plugin_lifecycle.rah_flat', 'installed');
            register_callback(array($this, 'disable'), 'plugin_lifecycle.rah_flat', 'disabled');
            register_callback(array($this, 'uninstall'), 'plugin_lifecycle.rah_flat', 'deleted');
        }

        if (get_pref('rah_flat_path')) {
            new rah_flat_Import_Variables('variables');
            new rah_flat_Import_Prefs('prefs');
            new rah_flat_Import_Sections('sections');
            new rah_flat_Import_Pages('pages');
            new rah_flat_Import_Styles('styles');
            new rah_flat_Import_Textpacks('textpacks');
            $forms = txpath . '/' . get_pref('rah_flat_path') . '/forms';
            if (file_exists($forms) && is_dir($forms) && is_readable($forms)) {
                foreach (array_diff(scandir($forms), array('.', '..')) as $formType) {
                    if (is_dir($forms . '/' . $formType)) {
                        new rah_flat_Import_Forms('forms/'.$formType);
                    }
                }
            }

            register_callback(array($this, 'injectVars'), 'pretext_end');
            register_callback(array($this, 'endpoint'), 'textpattern');
            register_callback(array($this, 'initWrite'), 'rah_flat.import');

            if (in_list(get_pref('production_status'), get_pref('rah_flat_upload_levels'))) {
                register_callback(array($this, 'callbackHandler'), 'textpattern');
                register_callback(array($this, 'callbackHandler'), 'admin_side', 'body_end');
            }
        }
    }

    /**
     * Inject Variables.
     */

    public function injectVars()
    {
        global $variable;

        $prefset = safe_rows('name, val', 'txp_prefs', "name like 'rah\_flat\_var\_%'");
        foreach ($prefset as $pref) {
            $variable[substr($pref['name'], strlen('rah_flat_var_'))] = $pref['val'];
        }
    }

    /**
     * Installer
     *
     * Set plugin prefs.
     */

    public function install()
    {
        $position = 250;

        $options = array(
            'rah_flat_path' => array('text_input', ''),
            'rah_flat_key'  => array('text_input', md5(uniqid(mt_rand(), true))),
            'rah_flat_upload_levels'  => array('upload_levels', 'debug, testing'),
        );

        foreach ($options as $name => $val) {
            if (get_pref($name, false) === false) {
                set_pref(
                    $name,
                    $val[1],
                    'rah_flat',
                    defined('PREF_PLUGIN') ? PREF_PLUGIN : PREF_ADVANCED,
                    $val[0],
                    $position
                );
            }

            $position++;
        }
    }

    /**
     * Jump to the prefs panel.
     */

    public function options()
    {
        $url = defined('PREF_PLUGIN')
               ? '?event=prefs#prefs_group_rah_flat'
               : '?event=prefs&step=advanced_prefs';
        header('Location: ' . $url);
    }

    /**
     * Disabled event
     *
     * Changes custom form types to misc;
     * restores pref types.
     */

    public function disable()
    {
        safe_update(
            'txp_form',
            "type = 'misc'",
            "type not in ('article', 'category', 'comment', 'file', 'link', 'misc', 'section')"
        );
        safe_update('txp_prefs', "type = '0'", "type = '20'");
        safe_update('txp_prefs', "type = '1'", "type = '21'");
    }

    /**
     * Uninstaller
     *
     * Removes plugin prefs;
     * removes textpack strings.
     */

    public function uninstall()
    {
        safe_delete('txp_prefs', "name like 'rah\_flat\_%'");
        safe_delete('txp_lang', "owner = 'rah_flat'");
        safe_delete('txp_lang', "owner = 'rah_flat_lang'");
        $this->deleting = true;
    }

    /**
     * Initializes the importers.
     */

    public function initWrite()
    {
        callback_event('rah_flat.import_to_database');
    }

    /**
     * Registered callback handler.
     */

    public function callbackHandler()
    {
        if (!$this->deleting) {
            try {
                callback_event('rah_flat.import');
            } catch (Exception $e) {
                trigger_error($e->getMessage());
            }
        }
    }

    /**
     * Import endpoint.
     */

    public function endpoint()
    {
        if (!get_pref('rah_flat_key') || get_pref('rah_flat_key') !== gps('rah_flat_key')) {
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        try {
            callback_event('rah_flat.import');
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

/**
 * Set upload_levels pref
 * To do: move in rah_flat class?
 */
function upload_levels($name, $val)
{
     $vals = array(
         'debug'   => gTxt('production_debug'),
        'testing' => gTxt('production_test'),
        'debug, testing' => gTxt('production_debug').', '.lcfirst(gTxt('production_test')),
    );

    return selectInput($name, $vals, $val, true, '', $name);
}

new rah_flat();
