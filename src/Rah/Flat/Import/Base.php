<?php

/**
 * Base class for import definitions.
 *
 * @example
 * class MyImportDefinition extends Rah_Flat_Import_Base
 * {
 *     public function getPanelName()
 *     {
 *         return 'MyPanel';
 *     }
 * }
 */

abstract class Rah_Flat_Import_Base implements Rah_Flat_Import_Template
{
    /**
     * Stores an array of affected tables.
     *
     * @var array
     */

    static public $tables = array();

    /**
     * The directory.
     *
     * @var string
     */

    protected $directory;

    /**
     * An array of imported templates.
     *
     * @var array
     */

    private $importedTemplates = array();

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
        if (!in_array($this->getTableName(), self::$tables, true))
        {
            $this->directory = $directory;
            register_callback(array($this, 'init'), 'rah_flat.import');
            self::$tables[] = $this->getTableName();
        }
    }

    /**
     * {@inheritdoc}
     */

    public function getTemplateIterator($directory)
    {
        return new Rah_Flat_TemplateIterator($directory);
    }

    /**
     * {@inheritdoc}
     */

    public function init()
    {
        if ($directory = get_pref('rah_flat_path', '', true))
        {
            $directory = txpath . '/' . $directory . '/' . $this->directory;

            if (file_exists($directory) && is_dir($directory) && is_readable($directory))
            {
                $templates = $this->getTemplateIterator($directory);

                while ($templates->valid())
                {
                    if (($template = $file->current()) && $template->isValidTemplate())
                    {
                        if ($this->importTemplate($template) === false)
                        {
                            throw new Exception('Unable to import ' . $template->getTemplateName());
                        }

                        $this->importedTemplates[] = $template;
                    }

                    $file->next();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
        unset($GLOBALS['txp_permissions'][$this->getPanelName()]);
    }

    /**
     * {@inheritdoc}
     */

    public function getImportedTemplates()
    {
        return $this->importedTemplates;
    }

    /**
     * {@inheritdoc}
     */

    public function getTableColumns()
    {
        if (!$this->columns)
        {
            $this->columns = doArray((array) @getThings('describe '.safe_pfx($this->getTableName())), 'strtolower');
        }

        return $this->columns;
    }
}