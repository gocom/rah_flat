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
 * Imports preferences.
 */

class Rah_Flat_Import_Prefs extends rah_flat_Import_Sections
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
