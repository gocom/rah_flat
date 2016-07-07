<?php

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
