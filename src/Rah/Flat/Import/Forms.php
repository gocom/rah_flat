<?php

/**
 * Imports form partials.
 */

class Rah_Flat_Import_Forms extends Rah_Flat_Import_Pages
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_form';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(Rah_Flat_TemplateIterator $file)
    {
        safe_upsert(
            $this->getTableName(),
            "Form = '".doSlash($file->getTemplateContents())."',
            type = '".doSlash($file->getTemplateType())."'",
            "name = '".doSlash(pathinfo($file->getTemplateName(), PATHINFO_FILENAME))."'"
        );
    }
}