<?php

class Rah_Flat_Import_Prefs extends Rah_Flat_Import_Base
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

    public function importTemplate(Rah_Flat_TemplateIterator $file)
    {
        extract(lAtts(array(
            'name'     => '',
            'value'    => '',
            'event'    => 'publish',
            'type'     => 0,
            'html'     => 'text_input',
            'position' => 80,
        ), $file->getTemplateJSONContents(), false));

        set_pref($name, $value, $event, $type, $html, $position);
    }

    /**
     * {@inheritdoc}
     */

    public function dropRemoved()
    {
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}