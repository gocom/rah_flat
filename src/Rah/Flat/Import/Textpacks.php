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
        global $DB;

        foreach ($file->getTemplateJSONContents() as $event => $array) {
            foreach ($array as $key => $value) {
                $set = array();
                $set[] = $this->formatStatement('event', $event);
                $set[] = $this->formatStatement('data', $value);
                $where = "lang = '".doSlash($file->getTemplateName())."' AND ".$this->formatStatement('name', $key);

                $r = safe_update($this->getTableName(), implode(',', $set), $where);
                if ($r and (mysqli_affected_rows($DB->link) or safe_count($this->getTableName(), $where))) {
                    $r;
                } else {
                    $set[] = "owner = 'rah_flat_lang'";
                    $set = implode(', ', $set);
                    $where = implode(', ', (preg_split( "/ AND /", $where)));
                    safe_insert($this->getTableName(), join(', ', array($where, $set)));
                }
            }
        }
        return;
    }

    /**
     * Formats a SQL insert statement value.
     *
     * @param  string $field The field
     * @param  string $value The value
     * @return string
     */

    protected function formatStatement($field, $value)
    {
        if ($value === null) {
            return "`{$field}` = NULL";
        }

        if (is_bool($value) || is_int($value)) {
            return "`{$field}` = ".intval($value);
        }

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return "`{$field}` = '".doSlash((string) $value)."'";
    }

    /**
     * {@inheritdoc}
     */

     public function dropRemoved(rah_flat_TemplateIterator $template)
     {

        while ($template->valid()) {
            $lang = "lang = '".doSlash($template->getTemplateName())."'";

            if ($lang) {
                foreach ($template->getTemplateJSONContents() as $event => $array) {
                    $name = array();
                    $event = "event = '".$event."'";
                    foreach ($array as $key => $value) {
                        $name[] = "'".doSlash($key)."'";
                    }
                    if ($name) {
                        safe_delete($this->getTableName(), $lang.' AND '.$event.' AND name not in ('.implode(',', $name).') AND owner = "rah_flat_lang"');
                    } else {
                        safe_delete($this->getTableName(), $lang.' AND '.$event.' AND owner = "rah_flat_lang"');
                    }
                }
            }

            $template->next();
        }
    }

    /**
     * {@inheritdoc}
     */

    public function dropPermissions()
    {
    }
}
