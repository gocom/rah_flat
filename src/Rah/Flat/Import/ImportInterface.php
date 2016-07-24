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
