<?php

/**
 * Imports Textpacks.
 */

class rah_flat_Import_Textpacks extends rah_flat_Import_Sections
{

    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'lang';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_lang';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(rah_flat_TemplateIterator $file)
    {
        extract(lAtts(array(
            'data' => '',
        ), $file->getTemplateJSONContents(), false));

        $data = "data = '".$data."'";
        $owner = "owner = 'rah_flat_lang'";
        $path = explode('/', $this->directory);
        $lang = "lang = '".doSlash($path[1])."'";
        $event = "event = '".doSlash($path[2])."'";
        $name = "name = '".doSlash($file->getTemplateName())."'";

        safe_upsert(
            $this->getTableName(),
            $data.', '.$owner.', '.$event,
            $name.' AND '.$lang
        );
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

        $path = explode('/', $this->directory);

        if ($name) {
            safe_delete($this->getTableName(), 'event = "'.doSlash($path[2]).'" && owner = "rah_flat_lang" && name not in ('.implode(',', $name).')');
        } else {
            safe_delete($this->getTableName(), 'event = "'.doSlash($path[2]).'" && owner = "rah_flat_lang"');
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}
