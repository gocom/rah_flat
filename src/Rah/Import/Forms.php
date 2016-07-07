<?php

/**
 * Imports form partials.
 */

class rah_flat_Import_Forms extends rah_flat_Import_Base
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

    public function importTemplate(rah_flat_TemplateIterator $file)
    {
        safe_upsert(
            $this->getTableName(),
            "Form = '".doSlash($file->getTemplateContents())."',
            type = '".doSlash(substr($this->directory, strrpos($this->directory, '/') + 1))."'",
            "name = '".doSlash($file->getTemplateName())."'"
        );
    }

    public function dropRemoved(rah_flat_TemplateIterator $template)
    {
        $name = array();

        while ($template->valid()) {
            $name[] = "'".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        $formtype = substr($this->directory, strrpos($this->directory, '/') + 1);

        if ($name) {
            safe_delete($this->getTableName(), 'type = "'.doSlash($formtype).'" && name not in ('.implode(',', $name).')');
        } else {
            safe_delete($this->getTableName(), 'type = "'.doSlash($formtype).'"');
        }
    }
}
