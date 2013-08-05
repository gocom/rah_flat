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
     * Drop removes templates.
     */

    public function dropRemoved(Rah_Flat_TemplateIterator $template);

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
     * Gets an array of database columns.
     *
     * @return array
     */

    public function getTableColumns();
}