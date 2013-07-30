<?php

/**
 * Imports page templates.
 */

class Rah_Flat_Import_Pages extends Rah_Flat_Import_Base
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'page';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_page';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(Rah_Flat_TemplateIterator $file)
    {
        safe_upsert(
            $this->getTableName(),
            "user_html = '".doSlash($file->getTemplateContents())."'",
            "name = '".doSlash($file->getTemplateName())."'"
        );
    }

    /**
     * {@inheritdoc}
     */

    public function dropRemoved(Rah_Flat_TemplateIterator $template)
    {
        $name = array();

        while ($template->valid())
        {
            $name[] = "'".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        if ($name)
        {
            safe_delete($this->getTableName(), 'name not in ('.implode(',', $name).')');
        }
        else
        {
            safe_delete($this->getTableName(), '1 = 1');
        }
    }
}