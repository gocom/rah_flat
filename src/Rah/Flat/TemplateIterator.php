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
 * Template iterator.
 *
 * @see DirectoryIterator
 */

class Rah_Flat_TemplateIterator extends DirectoryIterator
{
    /**
     * Gets the template contents.
     *
     * @throws Exception
     */

    public function getTemplateContents()
    {
        if (($contents = file_get_contents($this->getPathName())) !== false)
        {
            return $contents;
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
        if (($file = $this->getTemplateContents()) && $file = @json_decode($file))
        {
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
     * Validates a template file.
     *
     * @return bool
     */

    public function isValidTemplate()
    {
        return $this->isFile() && !$this->isDot() && $this->isReadable() && preg_match('/[a-z][a-z0-9_\-\.]/i', $this->getTemplateName());
    }

    /**
     * {@inheritdoc}
     */

    public function valid()
    {
        while (parent::valid() && !$this->isValidTemplate())
        {
            $this->next();
        }

        if (parent::valid())
        {
            return true;
        }

        $this->rewind();
        return false;
    }
}