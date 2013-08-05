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
        register_callback(array($this, 'init'), 'rah_flat.import');
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
                $template = $this->getTemplateIterator($directory);

                while ($template->valid())
                {
                    if ($this->importTemplate($template) === false)
                    {
                        throw new Exception('Unable to import ' . $template->getTemplateName());
                    }

                    $template->next();
                }

                $template->rewind();
                $this->dropRemoved($template);
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

    public function getTableColumns()
    {
        if (!$this->columns)
        {
            $this->columns = doArray((array) @getThings('describe '.safe_pfx($this->getTableName())), 'strtolower');
        }

        return $this->columns;
    }
}