<?php

/*
 * rah_flat - Flat templates for Textpattern CMS
 * https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2015 Jukka Svahn
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
 * Imports custom preferences (variables).
 */

class Rah_Flat_Import_Variables extends rah_flat_Import_Prefs
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
