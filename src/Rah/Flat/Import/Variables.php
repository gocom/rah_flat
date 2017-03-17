<?php

/*
 * rah_flat - Flat templates for Textpattern CMS
 * https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2014 Jukka Svahn
 *
 * This file is part of rah_flat.
 *
 * rah_flat is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * rah_flat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with rah_flat. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Imports preferences.
 */

class Rah_Flat_Import_Variables extends Rah_Flat_Import_Prefs
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
            'value'    => '',
            'html'     => 'text_input',
            'position' => '',
        ), $file->getTemplateJSONContents(), false));

        $name = $file->getTemplateName();

        if (get_pref($name, false) === false) {
            set_pref($name, $value, 'rah_flat_var', PREF_ADVANCED, $html, $position);
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropRemoved(Rah_Flat_TemplateIterator $template)
    {
        $name = array();

        while ($template->valid()) {
            $name[] = "'".doSlash($template->getTemplateName())."'";
            $template->next();
        }

        if ($name) {
            safe_delete($this->getTableName(), 'event = "rah_flat_var" && name not in ('.implode(',', $name).')');
        }    
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}