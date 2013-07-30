<?php

/**
 * Interface for import definition.
 *
 * @example
 * class MyImportDefinition implements Rah_Import_Template
 * {
 * }
 */

interface Rah_Flat_Import_Template
{
    /**
     * Constructor.
     *
     * Registers the importer definition when the class is initialized.
     *
     * @param   string $directory The directory hosting the templates
     * @example
     * new Rah_Import_Template('directoryName');
     */

    public function __construct($directory);

    /**
     * Initializes the importer.
     */

    public function init();

    /**
     * Remove permissions to the panel.
     */

    public function dropPermissions();

    /**
     * Gets the panel name.
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
     * @param  Rah_Flat_TemplateIterator $file The template file
     * @throws Exception
     */

    public function importTemplate(Rah_Flat_TemplateIterator $file);

    /**
     * Gets an array of imported templates.
     *
     * @return array
     */

    public function getImportedTemplates();

    /**
     * Gets an array of database columns.
     *
     * @return array
     */

    public function getTableColumns();
}