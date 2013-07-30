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

    public function dropRemoved()
    {
        if ($templates = $this->getImportedTemplates())
        {
            foreach ($templates as &$template)
            {
                $template = "'".doSlash($template->getTemplateName())."'";
            }

            safe_delete($this->getTableName(), 'name not in ('.implode(',', $templates).')');
        }
        else
        {
            safe_delete($this->getTableName(), '1 = 1');
        }
    }
}