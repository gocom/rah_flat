<?php

/**
 * Imports custom preferences (variables).
 */

class rah_flat_Import_Variables extends rah_flat_Import_Prefs
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
            'type'       => defined('PREF_PLUGIN') ? 'PREF_PLUGIN' : 'PREF_ADVANCED',
            'event'      => 'rah_flat_var',
            'html'       => 'text_input',
            'position'   => '',
            'is_private' => false,
        ), $file->getTemplateJSONContents(), false));

        $name = 'rah_flat_var_'.$file->getTemplateName();

        if (get_pref($name, false) === false) {
            set_pref($name, $value, $event, constant($type), $html, $position, $is_private);
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropRemoved(rah_flat_TemplateIterator $template)
    {
        $name = array();

        while ($template->valid()) {
            $name[] = "'rah_flat_var_".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        if ($name) {
            safe_delete($this->getTableName(), 'name like "rah\_flat\_var%" && name not in ('.implode(',', $name).')');
        } else {
            safe_delete($this->getTableName(), 'name like "rah\_flat\_var%"');
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}
