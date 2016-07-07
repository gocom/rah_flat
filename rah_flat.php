<?php

# --- BEGIN PLUGIN CODE ---

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
 * Template iterator.
 *
 * This class iterates over template files.
 *
 * <code>
 * $template = new rah_flat_TemplateIterator();
 * while ($template->valid()) {
 *  $template->getTemplateName();
 *  $template->getTemplateContents();
 * }
 * </code>
 *
 * @see DirectoryIterator
 */

class rah_flat_TemplateIterator extends DirectoryIterator
{
    /**
     * Template name pattern.
     *
     * This regular expression pattern is used to
     * validate template filenames.
     *
     * @var string
     */

    protected $templateNamePattern = '/[a-z][a-z0-9_\-\.]{1,63}\.[a-z0-9]+/i';

    /**
     * Gets the template contents.
     *
     * @throws Exception
     */

    public function getTemplateContents()
    {
        if (($contents = file_get_contents($this->getPathname())) !== false) {
            return preg_replace('/[\r|\n]+$/', '', $contents);
        }

        throw new Exception('Unable to read.');
    }

    /**
     * Gets JSON file contents as an object.
     *
     * @return stdClass
     * @throws Exception
     */

    public function getTemplateJSONContents()
    {
        if (($file = $this->getTemplateContents()) && $file = @json_decode($file)) {
            return $file;
        }

        throw new Exception('Invalid JSON file.');
    }

    /**
     * Gets the template name.
     *
     * @return string
     */

    public function getTemplateName()
    {
        return pathinfo($this->getFilename(), PATHINFO_FILENAME);
    }

    /**
     * Validates a template file name and stats.
     *
     * Template file must be a regular file or symbolic links,
     * readable and the name must be fewer than 64 characters long,
     * start with an ASCII character, followed by A-z, 0-9, -, _ and
     * and ends to a file extension.
     *
     * Valid template name would include:
     *
     * <code>
     * sitename.json
     * default.article.txp
     * form.name.misc.txp
     * default.txp
     * error_default.html
     * </code>
     *
     * But the following would be invalid:
     *
     * <code>
     * .sitename
     * _form.misc.txp
     * </code>
     *
     * @return bool TRUE if the name is valid
     */

    public function isValidTemplate()
    {
        if (!$this->isDot() && $this->isReadable() && ($this->isFile() || $this->isLink())) {
            return (bool) preg_match($this->templateNamePattern, $this->getFilename());
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */

    public function valid()
    {
        while (parent::valid() && !$this->isValidTemplate()) {
            $this->next();
        }

        if (parent::valid()) {
            return true;
        }

        $this->rewind();
        return false;
    }
}

/**
 * Interface for import definitions.
 *
 * <code>
 * class Abc_Import_Definition implements rah_flat_Import_ImportInterface
 * {
 * }
 * </code>
 */

interface rah_flat_Import_ImportInterface
{
    /**
     * Constructor.
     *
     * Registers the importer definition when the class is initialized.
     * The used event should be considered private and should not
     * be accessed manually.
     *
     * <code>
     * new rah_flat_Import_Forms('forms');
     * </code>
     *
     * @param string $directory The directory hosting the templates
     */

    public function __construct($directory);

    /**
     * Initializes the importer.
     *
     * This method is called when the import event is executed.
     *
     * @throws Exception
     */

    public function init();

    /**
     * Drops permissions to the panel.
     *
     * This makes sure the template items are not
     * modified through the GUI.
     *
     * This method only affects the admin-side interface and doesn't
     * truly reset permissions application wide. This is to
     * avoid unneccessary I/O activity that would otherwise have to
     * take place.
     */

    public function dropPermissions();

    /**
     * Drops removed template rows from the database.
     *
     * For most impletations this method removes all rows that aren't
     * present in the flat directory, but for some it might
     * not do anything.
     *
     * @throws Exception
     */

    public function dropRemoved(rah_flat_TemplateIterator $template);

    /**
     * Gets the panel name.
     *
     * The panel name is used to recognize the content-types
     * registered event and remove access to it.
     *
     * @return string
     */

    public function getPanelName();

    /**
     * Gets database table name.
     *
     * @return string
     */

    public function getTableName();

    /**
     * Imports the template file.
     *
     * This method executes the SQL statement to import
     * the template file.
     *
     * @param  rah_flat_TemplateIterator $file The template file
     * @throws Exception
     */

    public function importTemplate(rah_flat_TemplateIterator $file);

    /**
     * Gets an array of database columns in the table.
     *
     * @return array
     */

    public function getTableColumns();

    /**
     * Gets a path to the directory hosting the flat files.
     *
     * @return string|bool The path, or FALSE
     */

    public function getDirectoryPath();

    /**
     * Whether the content-type is enabled and has a directory.
     *
     * @return bool TRUE if its enabled, FALSE otherwise
     */

    public function isEnabled();
}

/**
 * Base class for import definitions.
 *
 * To create a new importable template object, extend
 * this class or its theriatives.
 *
 * For instance the following would create a new import
 * definition using the rah_flat_Import_Pages as the
 * base:
 *
 * <code>
 * class Abc_My_Import_Definition extends rah_flat_Import_Pages
 * {
 *     public function getPanelName()
 *     {
 *         return 'abc_mypanel';
 *     }
 *     public function getTableName()
 *     {
 *         return 'abc_mytable';
 *     }
 * }
 * </code>
 *
 * It would automatically disable access to 'abc_mypanel' admin-side panel
 * and import items to 'abc_mytable' database table, consisting of 'name'
 * and 'user_html' columns, as with page templates and its txp_page table.
 *
 * To initialize the import, just create a new instance of the class. Pass
 * constructor the directory name the templates reside in the configured
 * templates directory.
 *
 * <code>
 * new Abc_My_Import_Definition('abc_mydirectory');
 * </code>
 */

abstract class rah_flat_Import_Base implements rah_flat_Import_ImportInterface
{
    /**
     * The directory.
     *
     * @var string
     */

    protected $directory;

    /**
     * An array of database table columns.
     *
     * @var array
     */

    private $columns = array();

    /**
     * {@inheritdoc}
     */

    public function __construct($directory)
    {
        $this->directory = $directory;
        register_callback(array($this, 'init'), 'rah_flat.import_to_database');
        $this->dropPermissions();
    }

    /**
     * {@inheritdoc}
     */

    public function getTemplateIterator($directory)
    {
        return new rah_flat_TemplateIterator($directory);
    }

    /**
     * {@inheritdoc}
     */

    public function getDirectoryPath()
    {
        if ($this->directory && ($directory = get_pref('rah_flat_path'))) {
            $directory = txpath . '/' . $directory . '/' . $this->directory;
            return $directory;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */

    public function isEnabled()
    {
        if ($directory = $this->getDirectoryPath()) {
            return file_exists($directory) && is_dir($directory) && is_readable($directory);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */

    public function init()
    {
        if ($this->isEnabled()) {
            $template = $this->getTemplateIterator($this->getDirectoryPath());

            while ($template->valid()) {
                if ($this->importTemplate($template) === false) {
                    throw new Exception('Unable to import ' . $template->getTemplateName());
                }

                $template->next();
            }

            $this->dropRemoved($template);
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
        if (txpinterface === 'admin' && $this->isEnabled()) {
            unset($GLOBALS['txp_permissions'][$this->getPanelName()]);
        }
    }

    /**
     * {@inheritdoc}
     */

    public function getTableColumns()
    {
        if (!$this->columns) {
            $this->columns = doArray((array) @getThings('describe '.safe_pfx($this->getTableName())), 'strtolower');
        }

        return $this->columns;
    }
}

/**
 * Imports page templates.
 */

class rah_flat_Import_Pages extends rah_flat_Import_Base
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'page';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_page';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(rah_flat_TemplateIterator $file)
    {
        safe_upsert(
            $this->getTableName(),
            "user_html = '".doSlash($file->getTemplateContents())."'",
            "name = '".doSlash($file->getTemplateName())."'"
        );
    }

    /**
     * {@inheritdoc}
     */

    public function dropRemoved(rah_flat_TemplateIterator $template)
    {
        $name = array();

        while ($template->valid()) {
            $name[] = "'".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        if ($name) {
            safe_delete($this->getTableName(), 'name not in ('.implode(',', $name).')');
        } else {
            safe_delete($this->getTableName(), '1 = 1');
        }
    }
}

/**
 * Imports form partials.
 */

class rah_flat_Import_Forms extends rah_flat_Import_Base
{

    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_form';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(rah_flat_TemplateIterator $file)
    {
        safe_upsert(
            $this->getTableName(),
            "Form = '".doSlash($file->getTemplateContents())."',
            type = '".doSlash(substr($this->directory, strrpos($this->directory, '/') + 1))."'",
            "name = '".doSlash($file->getTemplateName())."'"
        );
    }

    public function dropRemoved(rah_flat_TemplateIterator $template)
    {
        $name = array();

        while ($template->valid()) {
            $name[] = "'".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        $formtype = substr($this->directory, strrpos($this->directory, '/') + 1);

        if ($name) {
            safe_delete($this->getTableName(), 'type = "'.doSlash($formtype).'" && name not in ('.implode(',', $name).')');
        } else {
            safe_delete($this->getTableName(), 'type = "'.doSlash($formtype).'"');
        }
    }
}

/**
 * Imports sections.
 */

class rah_flat_Import_Sections extends rah_flat_Import_Pages
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'section';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_section';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(rah_flat_TemplateIterator $file)
    {
        $sql = array();
        $where = "name = '".doSlash($file->getTemplateName())."'";

        foreach ($file->getTemplateJSONContents() as $key => $value) {
            if ($key !== 'name' && in_array(strtolower((string) $key), $this->getTableColumns(), true)) {
                $sql[] = $this->formatStatement($key, $value);
            }
        }

        return $sql && safe_upsert($this->getTableName(), implode(',', $sql), $where);
    }

    /**
     * Formats a SQL insert statement value.
     *
     * @param  string $field The field
     * @param  string $value The value
     * @return string
     */

    protected function formatStatement($field, $value)
    {
        if ($value === null) {
            return "`{$field}` = NULL";
        }

        if (is_bool($value) || is_int($value)) {
            return "`{$field}` = ".intval($value);
        }

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return "`{$field}` = '".doSlash((string) $value)."'";
    }
}

/**
 * Imports preferences.
 */

class rah_flat_Import_Prefs extends rah_flat_Import_Sections
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'prefs';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_prefs';
    }

    /**
     * {@inheritdoc}
     */

     public function importTemplate(rah_flat_TemplateIterator $file)
     {
        extract(lAtts(array(
            'value'      => '',
        ), $file->getTemplateJSONContents(), false));

        safe_update($this->getTableName(), "val = '".doSlash($value)."'", "name = '".doSlash($file->getTemplateName())."' && type = '2'");
        safe_update($this->getTableName(), "val = '".doSlash($value)."', type = '21'", "name = '".doSlash($file->getTemplateName())."' && type = '1'");
        safe_update($this->getTableName(), "val = '".doSlash($value)."', type = '20'", "name = '".doSlash($file->getTemplateName())."' && type = '0'");
     }

    /**
     * {@inheritdoc}
     */

     public function dropRemoved(rah_flat_TemplateIterator $template)
     {
        $name = array();

        while ($template->valid()) {
            $name[] = "'".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        if ($name) {
            safe_update($this->getTableName(), "type = '0'", 'type = "20" && name not in ('.implode(',', $name).')');
            safe_update($this->getTableName(), "type = '1'", 'type = "21" && name not in ('.implode(',', $name).')');
        } else {
            safe_update($this->getTableName(), "type = '0'", "type = '20'");
            safe_update($this->getTableName(), "type = '1'", "type = '21'");
        }
     }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}

/**
 * Imports preferences.
 */

class rah_flat_Import_Variables extends rah_flat_Import_Prefs
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'prefs';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_prefs';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(rah_flat_TemplateIterator $file)
    {
        extract(lAtts(array(
            'value'      => '',
            'type'       => defined('PREF_PLUGIN') ? 'PREF_PLUGIN' : 'PREF_ADVANCED',
            'event'      => 'rah_flat_var',
            'html'       => 'text_input',
            'position'   => '',
            'is_private' => false,
        ), $file->getTemplateJSONContents(), false));

        $name = 'rah_flat_var_'.$file->getTemplateName();

        if (get_pref($name, false) === false) {
            set_pref($name, $value, $event, constant($type), $html, $position, $is_private);
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropRemoved(rah_flat_TemplateIterator $template)
    {
        $name = array();

        while ($template->valid()) {
            $name[] = "'rah_flat_var_".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        if ($name) {
            safe_delete($this->getTableName(), 'name like "rah\_flat\_var%" && name not in ('.implode(',', $name).')');
        } else {
            safe_delete($this->getTableName(), 'name like "rah\_flat\_var%"');
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}

/**
 * Imports form partials.
 */

class rah_flat_Import_Textpacks extends rah_flat_Import_Sections
{

    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'lang';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_lang';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(rah_flat_TemplateIterator $file)
    {
        extract(lAtts(array(
            'data' => '',
        ), $file->getTemplateJSONContents(), false));

        $data = "data = '".$data."'";
        $owner = "owner = 'rah_flat_lang'";
        $path = explode('/', $this->directory);
        $lang = "lang = '".doSlash($path[1])."'";
        $event = "event = '".doSlash($path[2])."'";
        $name = "name = '".doSlash($file->getTemplateName())."'";

        safe_upsert(
            $this->getTableName(),
            $data.', '.$owner.', '.$event,
            $name.' AND '.$lang
        );
    }

    /**
     * {@inheritdoc}
     */

    public function dropRemoved(rah_flat_TemplateIterator $template)
    {
        $name = array();

        while ($template->valid()) {
            $name[] = "'".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        $path = explode('/', $this->directory);

        if ($name) {
            safe_delete($this->getTableName(), 'event = "'.doSlash($path[2]).'" && owner = "rah_flat_lang" && name not in ('.implode(',', $name).')');
        } else {
            safe_delete($this->getTableName(), 'event = "'.doSlash($path[2]).'" && owner = "rah_flat_lang"');
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}

/**
 * Imports template styles.
 */

class rah_flat_Import_Styles extends rah_flat_Import_Pages
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'css';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_css';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(rah_flat_TemplateIterator $file)
    {
        safe_upsert(
            $this->getTableName(),
            "css = '".doSlash($file->getTemplateContents())."'",
            "name = '".doSlash($file->getTemplateName())."'"
        );
    }
}

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
