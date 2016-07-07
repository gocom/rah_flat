<?php

/**
 * Main plugin class.
 *
 * @internal
 */

class rah_flat
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
            $forms = txpath . '/' . get_pref('rah_flat_path') . '/forms';
            if (file_exists($forms) && is_dir($forms) && is_readable($forms)) {
                foreach (array_diff(scandir($forms), array('.', '..')) as $formType) {
                    if (is_dir($forms . '/' . $formType)) {
                        new rah_flat_Import_Forms('forms/'.$formType);
                    }
                }
            }
            $textpacks = txpath . '/' . get_pref('rah_flat_path') . '/textpacks';
            if (file_exists($textpacks) && is_dir($textpacks) && is_readable($textpacks)) {
                foreach (array_diff(scandir($textpacks), array('.', '..')) as $lang) {
                    if (is_dir($textpacks . '/' . $lang)) {
                        new rah_flat_Import_Textpacks('textpacks/'.$lang.'/admin');
                        new rah_flat_Import_Textpacks('textpacks/'.$lang.'/public');
                        new rah_flat_Import_Textpacks('textpacks/'.$lang.'/common');
                    }
                }
            }

            register_callback(array($this, 'injectVars'), 'pretext_end');
            register_callback(array($this, 'endpoint'), 'textpattern');
            register_callback(array($this, 'initWrite'), 'rah_flat.import');

            if (get_pref('production_status') !== 'live') {
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
        );

        foreach ($options as $name => $val) {
            if (get_pref($name, false) === false) {
                set_pref($name, $val[1], 'rah_flat', defined('PREF_PLUGIN') ? PREF_PLUGIN : PREF_ADVANCED, $val[0], $position);
            }

            $position++;
        }
    }

    /**
     * Jump to the prefs panel.
     */

    public function options() {
        $url = defined('PREF_PLUGIN') ? '?event=prefs#prefs_group_rah_flat' : '?event=prefs&step=advanced_prefs';
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
        safe_update('txp_form', "type = 'misc'", "type not in ('article', 'category', 'comment', 'file', 'link', 'misc', 'section')");
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

new rah_flat();
