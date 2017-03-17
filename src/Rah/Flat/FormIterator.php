<?php

/*
 * rah_flat - Flat templates for Textpattern CMS
 * https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2017 Jukka Svahn
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
 * Form partial iterator.
 *
 * @see DirectoryIterator
 */

class Rah_Flat_FormIterator extends Rah_Flat_TemplateIterator
{
    /**
     * {@inheritdoc}
     */

    protected $templateNamePattern = '/[a-z][a-z0-9_\-\.]{1,63}\.[a-z0-9]{1,28}\.[a-z0-9]+/i';

    /**
     * {@inheritdoc}
     */

    public function getTemplateName()
    {
        return pathinfo(pathinfo($this->getFilename(), PATHINFO_FILENAME), PATHINFO_FILENAME);
    }

    /**
     * Gets the template type.
     *
     * If the template name doesn't specify a type, it
     * defaults to 'misc'. The second to last extension
     * is expected to be the type.
     *
     * If the file is named as:
     *
     * <code>
     * filename.red.ext
     * </code>
     *
     * The 'red' would be used as the type.
     *
     * @return string
     */

    public function getTemplateType()
    {
        if ($type = pathinfo(pathinfo($this->getFilename(), PATHINFO_FILENAME), PATHINFO_EXTENSION)) {
            return $type;
        }

        return 'misc';
    }
}
