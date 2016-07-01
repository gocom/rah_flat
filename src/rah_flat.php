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

$plugin['version'] = '0.5.0-beta';
$plugin['author'] = 'Jukka Svahn (forked by Nicolas Morand)';
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

*Warning: this plugin will permanently remove your current templates when a valid path will be saved under the plugin prefs.*

h2. Table of contents

* "Plugin requirements":#requirements
* "Installation":#installation
* "Preferences":#prefs
* "Basics":#basics
* "Stucture":#structure
* "File examples":#examples
** "Sections":#sections
** "Preferences":#preferences
** "Variables":#variables
* "Author":#author
* "Licence":#licence
* "Changelog":#changelog

h2(#requirements). Plugin requirements

rah_flat’s minimum requirements:

* Textpattern 4.5+

h2(#installation). Installation

# Paste the content of the plugin file under the *Admin > Plugins*, upload it and install;
# Visit the *Admin>Preferences* tab to fill the plugin prefs.

h2(#prefs). Preferences / options

* *Path to the templates directory* - Specifies path to the root templates directory containing all the content-type specific directories. This path is relative to your 'textpattern' installation directory. For example, a path @../themes/my_theme@ would point to a directory located in the same directory as your _textpattern_ directory and the main _index.php_ file.
* *Security key for the public callback* - Security key for the public callback hook URL. Importing is done when the URL is accessed. The URL follows the format of:

bc. http://example.com/?rah_flat_key={yourKey}

Where @http://example.com/@ is your site's URL, and @{yourKey}@ is the security key you specified.

h2(#basics). Basics

Your flat files are imported to the database:

* Automatically on the background when the site is either in Debugging or Testing mode ^1^.
* When the public callback hook URL is accessed. The URL can be used for deployment.

1. Variables are imported only once to avoid the override of user strings. To update them, remove or rename .json files and refresh.

If you want to exclude a certain content type from importing, just don't create a directory for it. No directory, and rah_flat will leave the database alone when it comes to that content type.

h3. About variables

This plugin allow to set variables via flat files by setting custom preferences.
These prefs are visible by default under the _Preferences_ tab to allow users to set or change their value but you can also hide some of them (see "here":#variables) if they don't need to be override. Custom preferences are then injected by the plugin to be used as Txp variables like so: @<txp:variable name="my-variable"/>@ where _my-variable_ is the name of the related .json file.

h2(#structure). Structure

* my-folder
** sections
*** my-section.json
** pages
**** my-page.txp
** forms
*** article
**** my-article-form.txp
*** comment
**** my-comment-form.txp
*** category
**** …
*** section
*** link
*** file
*** misc
*** custom
** prefs
*** rah_flat_path.json
** variables
*** my-variable.json
** styles
*** my-styles.css

*Warning: while forms are now organised by types, they all still need to have different names.*

h2(#examples). File examples

h3(#sections). Sections

Here is an example of content for an @about.json@ file.

bc.. {
    "title": "About",
    "page": "default",
    "css": "default",
    "is_default": false,
    "in_rss": false,
    "on_frontpage": false,
    "searchable": true
}

p. where:

* @"title": "…"@ - The title of the section.
* @"page": "…"@ - The name of the section.
* @"css": "…"@ - The stylesheet used by the section.
* @"is_default": "…"@ - ?
* @"in_rss": "…"@ - Whether to display section articles in the feeds or not.
* @"on_frontpage": "…"@ - Whether to display section articles on the front page or not.
* @"searchable": "…"@ - Use false to exclude section articles of the search results.

h3(#preferences). Preferences

The plugin has set of preferences you can find on Textpattern's normal preferences panel.
Here is an example of content for a @sitename.json@ file.

bc.. {
    "value": "My website"
}

p. where:

* @"value": "…"@ - The value of the preference.

h3(#variables). variables (through custom prefs)

Here is an example of content for a @menu-sections.json@ file.

bc.. {
    "value": "articles, about, contact",
    "type": "PREF_HIDDEN",
    "event": "rah_flat_var",
    "html": "text_input",
    "position": "10",
    "is_private": true
}

p. where each data is optional and the default values are the following:

* @"value": "…"@ - _default: '' (empty)_ - The default value of the preference.
* @"type": "…"@ - _default: PREF_PLUGIN (for Txp 4.6, else PREF_ADVANCED)_ - To hide a pref in the _Preferences_ tab, use _PREF_HIDDEN_, you should probably not use _PREF_CORE_.
* "event": "…" - _default: 'rah_flat_var'_ - The prefs group name where you want to display your custom pref.
* @"html": "…"@ - _default: 'text_input'_ - To display radio buttons, use _onoffradio_ or _yesnoradio_.
* @"position": "…"@ - _default: 0_ - Use position to sort your prefs.
* @"is_private": "…"@ - _default: false_ - If _true_, the pref will be user related.

p. You can then call your custom preference as a Txp variable like so:

bc. <txp:variable name="menu-sections" />

h2(#author). Author

"Jukka Svahn":http://rahforum.biz/, forked by "Nicolas Morand":https://github.com/ from v0.3.0.

h2(#licence). Licence

This plugin is distributed under "GPLv2":http://www.gnu.org/licenses/gpl-2.0.fr.html.

h2(#Changelog). Changelog

* To do: Improve code comments?

To do: Fix the _Options_ link

h3. Version 0.5.0-beta - 2016/07/04

* Changed: Forms are stored by types in subfolders and don't need prefixes anymore.
* Added: Custom form types are changed to 'misc' when the plugin is disable to avoid an error in the Forms tab.
* Changed: Preferences update affects values only.
* Added: Prefs are hidden in the admin if set via flat files and get back to visible when the plugin is disabled.
* Added: Custom prefs (in the variables folder) accept more paramters in .json files.
* Changed: Custom prefs (in the variables folder) now have a rah_flat_var_ prefix added to their name.

h3. Version 0.4.0 (named oui_flat) - 2015/11/29

* Changed: Forked by Nicolas Morand.
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

        safe_update('txp_prefs', "val = '".doSlash($value)."'", "name = '".doSlash($file->getTemplateName())."' && type = '2'");
        safe_update('txp_prefs', "val = '".doSlash($value)."', type = '21'", "name = '".doSlash($file->getTemplateName())."' && type = '1'");
        safe_update('txp_prefs', "val = '".doSlash($value)."', type = '20'", "name = '".doSlash($file->getTemplateName())."' && type = '0'");
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
            safe_delete($this->getTableName(), 'event = "rah_flat_var" && name not in ('.implode(',', $name).')');
        } else {
            safe_delete($this->getTableName(), 'event = "rah_flat_var"');
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
        register_callback('rah_flat_options', 'plugin_prefs.rah_flat', null, 1);
        register_callback(array($this, 'install'), 'plugin_lifecycle.rah_flat', 'installed');
        register_callback(array($this, 'disable'), 'plugin_lifecycle.rah_flat', 'disabled');
        register_callback(array($this, 'uninstall'), 'plugin_lifecycle.rah_flat', 'deleted');

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

    public function rah_flat_options() {
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
        safe_delete('txp_lang', "name like 'rah\_flat\_%'");
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
