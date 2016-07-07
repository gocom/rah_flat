<?php

/**
 * Imports preferences.
 */

class rah_flat_Import_Prefs extends rah_flat_Import_Sections
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'prefs';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_prefs';
    }

    /**
     * {@inheritdoc}
     */

     public function importTemplate(rah_flat_TemplateIterator $file)
     {
        extract(lAtts(array(
            'value'      => '',
        ), $file->getTemplateJSONContents(), false));

        safe_update($this->getTableName(), "val = '".doSlash($value)."'", "name = '".doSlash($file->getTemplateName())."' && type = '2'");
        safe_update($this->getTableName(), "val = '".doSlash($value)."', type = '21'", "name = '".doSlash($file->getTemplateName())."' && type = '1'");
        safe_update($this->getTableName(), "val = '".doSlash($value)."', type = '20'", "name = '".doSlash($file->getTemplateName())."' && type = '0'");
     }

    /**
     * {@inheritdoc}
     */

     public function dropRemoved(rah_flat_TemplateIterator $template)
     {
        $name = array();

        while ($template->valid()) {
            $name[] = "'".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        if ($name) {
            safe_update($this->getTableName(), "type = '0'", 'type = "20" && name not in ('.implode(',', $name).')');
            safe_update($this->getTableName(), "type = '1'", 'type = "21" && name not in ('.implode(',', $name).')');
        } else {
            safe_update($this->getTableName(), "type = '0'", "type = '20'");
            safe_update($this->getTableName(), "type = '1'", "type = '21'");
        }
     }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}
