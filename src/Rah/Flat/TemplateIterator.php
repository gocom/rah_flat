<?php

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

        return parent::valid();
    }
}