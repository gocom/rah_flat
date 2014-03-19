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
