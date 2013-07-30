<?php

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

    public function getTemplateName()
    {
        return pathinfo(pathinfo($this->getFilename(), PATHINFO_FILENAME), PATHINFO_FILENAME);
    }

    /**
     * Gets the template type.
     *
     * @return string
     */

    public function getTemplateType()
    {
        if ($type = pathinfo(pathinfo($this->getFilename(), PATHINFO_FILENAME), PATHINFO_EXTENSION))
        {
            return $type;
        }

        return 'misc';
    }
}