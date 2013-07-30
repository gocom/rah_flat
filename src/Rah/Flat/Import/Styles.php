<?php

/**
 * Imports template styles.
 */

class Rah_Flat_Import_Styles extends Rah_Flat_Import_Pages
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'css';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_css';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(Rah_Flat_TemplateIterator $file)
    {
        safe_upsert(
            $this->getTableName(),
            "css = '".doSlash($file->getTemplateContents())."'",
            "name = '".doSlash($file->getTemplateName())."'"
        );
    }
}