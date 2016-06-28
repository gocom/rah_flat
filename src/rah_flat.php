<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ('abc' is just an example).
// Uncomment and edit this line to override:
# $plugin['name'] = 'abc_plugin';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 0;

$plugin['version'] = '0.5.0-dev';
$plugin['author'] = 'Jukka Svahn (modified by Nicolas Morand)';
$plugin['author_uri'] = '';
$plugin['description'] = 'Edit Textpattern\'s database prefs, contents and page templates as flat files';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
# $plugin['order'] = 5;

// Plugin 'type' defines where the plugin is loaded
// 0 = public       : only on the public side of the website (default)
// 1 = public+admin : on both the public and non-AJAX admin side
// 2 = library      : only when include_plugin() or require_plugin() is called
// 3 = admin        : only on the non-AJAX admin side
// 4 = admin+ajax   : only on admin side
// 5 = public+admin+ajax   : on both the public and admin side
$plugin['type'] = 1;

// Plugin 'flags' signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use.
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = PLUGIN_HAS_PREFS | PLUGIN_LIFECYCLE_NOTIFY;

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String
$plugin['textpack'] = <<< EOT
#@public
#@language en-gb
rah_flat => Template files (rah_flat)
rah_flat_path => Templates path
rah_flat_key => Update key
rah_flat_var => Template variables (rah_flat)
#@language fr-fr
rah_flat => Fichiers du thème (rah_flat)
rah_flat_path => Chemin vers les fichiers
rah_flat_key => Clé de mise à jour
rah_flat_var => Variables du thème (rah_flat)
EOT;
// End of textpack

if (!defined('txpinterface'))
	@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. rah_flat

This plugin makes your "Textpattern CMS":http://www.textpattern.com database more flat, manageable and editable. Edit templates, forms, pages, preferences, variables and sections as flat files. Use any editor, work in teams and store your website's source under your favorite "version control system":http://en.wikipedia.org/wiki/Revision_control.

*Warning: this plugin will permanently remove your current templates when activated.*

h2. Installing

"download":https://github.com/NicolasGraph/rah_flat the plugin, paste it under the textpattern plugin tab, and upload it.

h2. Basics

rah_flat imports normal, flat files from a specified directory to your Textpattern database. This, in essence, lets you to edit your database contents from any regular editor and store the source as flat files.

rah_flat comes with built-in support for a few essential content types: variables (via custom prefs), styles, forms, pages, preferences and sections. See the "templates":https://github.com/nicolasgraph/rah_flat/tree/master/templates directory on GitHub for an example how this all works.

Your flat files are imported to the database:

* Automatically on the background when the site is either in Debugging or Testing mode ^1^.
* When the public callback hook URL is accessed. The URL can be used for deployment.

1. Variables are imported only once to avoid the override of user strings. To update them, remove or rename .json files and refresh.

If you want to exclude a certain content type from importing, just don't create a directory for it. No directory, and rah_flat will leave the database alone when it comes to that content type.

h2. Preferences

The plugin has set of preferences you can find on Textpattern's normal preferences panel.

h3. Path to the templates directory

Specifies path to the root templates directory containing all the content-type specific directories. This path is relative to your 'textpattern' installation directory. For example, a path @../themes/my_theme@ would point to a directory located in the same directory as your _textpattern_ directory and the main _index.php_ file.

h3. Security key for the public callback

Security key for the public callback hook URL. Importing is done when the URL is accessed. The URL follows the format of:

bc. http://example.com/?rah_flat_key={yourKey}

Where @http://example.com/@ is your site's URL, and @{yourKey}@ is the security key you specified.

h2. Toolshed notice

This is a toolshed project. Experimental and not part of the main supported product line of Rah. Not yet at least. Please use at your own risk.

h2. Changelog

h4. Version 0.5.0-dev - 2016/06/27

To do: Help and comments updates.
Changed: Forms are stored by types in subfolders (custom types can be used).
Changed: Forms get their type from their parent folder and don't need prefixes anymore.

h4. Version 0.4.0 - 2015/11/29

* Added: Custom preferences can be created and use as Txp variables.
* Changed: Forms naming convention is now @type.name.txp@.

h3. Version 0.3.0 (rah_flat) - 2014/03/28

* Added: Drop access to a admin-side panel only if the specific content-type is active and has a directory set up.
* Added: Invokable @rah_flat.import@ callback event.
* Added: Sections and preferences get their names from the filename.
* Added: Preferences are always just updated, no strands are created.
* Added: Preference fields that are not specified in a file are kept as-is in the database.
* Added: French translation by "Patrick Lefevre":https://github.com/cara-tm.
* Changed: Renamed confusing @rah_flat_Import_Template@ interface to @rah_flat_Import_ImportInterface@.

h3. Version 0.2.0 (rah_flat) - 2014/03/19

* Reworked.

h3. Version 0.1.0 (rah_flat) - 2013/05/07

* Initial release.

# --- END PLUGIN HELP ---
<?php
}

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

        if ($name) {
            safe_delete($this->getTableName(), 'type = "'.doSlash(substr($this->directory, strrpos($this->directory, '/') + 1)).'" && name not in ('.implode(',', $name).')');
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
        $sql = array();
        $where = "name = '".doSlash($file->getTemplateName())."' and user_name = ''";

        foreach ($file->getTemplateJSONContents() as $key => $value) {
            if (in_array(strtolower((string) $key), $this->getTableColumns(), true)) {
                $sql[] = $this->formatStatement($key, $value);
            }
        }

        return $sql && safe_update($this->getTableName(), implode(',', $sql), $where);
    }

    /**
     * {@inheritdoc}
     */

    public function dropRemoved(rah_flat_TemplateIterator $template)
    {
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
            'value'    => '',
            'html'     => 'text_input',
            'position' => '',
        ), $file->getTemplateJSONContents(), false));

		$event = 'rah_flat_var';
        $name = $file->getTemplateName();

        if (get_pref($name, false) === false) {
            set_pref($name, $value, $event, defined('PREF_PLUGIN') ? PREF_PLUGIN : PREF_ADVANCED, $html, $position);
        }
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
            safe_delete($this->getTableName(), 'event = "rah_flat_var" && name not in ('.implode(',', $name).')');
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
    /**
     * Constructor.
     */

    public function __construct()
    {
        add_privs('prefs.rah_flat', '1');
        add_privs('prefs.rah_flat_var', '1');
        register_callback(array($this, 'install'), 'plugin_lifecycle.rah_flat', 'installed');
        register_callback('rah_flat_options', 'plugin_prefs.rah_flat', null, 1);
        register_callback(array($this, 'uninstall'), 'plugin_lifecycle.rah_flat', 'deleted');

        if (get_pref('rah_flat_path')) {

            new rah_flat_Import_Variables('variables');
            new rah_flat_Import_Prefs('prefs');
            new rah_flat_Import_Sections('sections');
            new rah_flat_Import_Pages('pages');
            new rah_flat_Import_Styles('styles');

            $formsDir = txpath . '/' . get_pref('rah_flat_path') . '/forms';
            if (is_dir($formsDir)) {
                foreach (array_diff(scandir($formsDir), array('.', '..')) as $formType) {
                    if (is_dir($formsDir . '/' . $formType)) {
                        new rah_flat_Import_Forms('forms/'.$formType);
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

		$prefset = safe_rows('name, val', 'txp_prefs', 'event = "rah_flat_var"');
        foreach ($prefset as $pref) {
            $variable[$pref['name']] = $pref['val'];
        }
    }

    /**
     * Installer.
     */

    public function install()
    {
        $position = 250;

        $options = array(
            'rah_flat_path' => array('text_input', '../../src/templates'),
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
    function rah_flat_options() {
        $url = '?event=prefs#prefs_group_rah_flat';
        header('Location: ' . $url);
    }

    /**
     * Uninstaller.
     */

    public function uninstall()
    {
        safe_delete('txp_prefs', "name like 'rah\_flat\_%'");
        safe_delete('txp_prefs', "event like 'rah\_flat\_var%'");
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
        try {
            callback_event('rah_flat.import');
        } catch (Exception $e) {
            trigger_error($e->getMessage());
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

# --- END PLUGIN CODE ---

?>
