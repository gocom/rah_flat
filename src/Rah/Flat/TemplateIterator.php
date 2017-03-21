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
 * Template iterator.
 *
 * This class iterates over template files.
 *
 * <code>
 * $templates = new RecursiveIteratorIterator(
 *     new Rah_Flat_FilterIterator(
 *         new Rah_Flat_TemplateIterator('/path/to/dir')
 *     )
 * );
 * foreach ($templates as $template) {
 *     $template->getTemplateName();
 *     $template->getTemplateContents();
 * }
 * </code>
 *
 * @see \RecursiveDirectoryIterator
 */

class Rah_Flat_TemplateIterator extends RecursiveDirectoryIterator
{
    /**
     * Template name pattern.
     *
     * This regular expression pattern is used to
     * validate template filenames.
     *
     * @var string
     */

    protected $templateNamePattern = '/[a-z][a-z0-9_\-\.]{0,63}\.[a-z0-9]+/i';

    /**
     * {@inheritdoc}
     */

    public function __construct($path, $flags = null)
    {
        if ($flags === null) {
            $flags = FilesystemIterator::FOLLOW_SYMLINKS |
                FilesystemIterator::CURRENT_AS_SELF |
                FilesystemIterator::SKIP_DOTS;
        }

        parent::__construct($path, $flags);
    }

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
     * Template file must be a regular file or a symbolic link,
     * readable and the name must be fewer than 65 characters long,
     * start with an ASCII character (A-z), followed by A-z, 0-9, -, _ and
     * and end to a file extension.
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
     * 0template.html
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
}
