<?php

/*
 * rah_flat - Flat templates for Textpattern CMS
 * https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2017 Jukka Svahn
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
 * Imports sections.
 */

class Rah_Flat_Import_Sections extends Rah_Flat_Import_Pages
{
    /**
     * {@inheritdoc}
     */

    public function getPanelName()
    {
        return 'section';
    }

    /**
     * {@inheritdoc}
     */

    public function getTableName()
    {
        return 'txp_section';
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(Rah_Flat_TemplateIterator $file)
    {
        $sql = array();
        $where = "name = '".doSlash($file->getTemplateName())."'";

        foreach ($file->getTemplateJSONContents() as $key => $value) {
            if ($key !== 'name' && in_array(strtolower((string) $key), $this->getTableColumns(), true)) {
                $sql[] = $this->formatStatement($key, $value);
            }
        }

        return $sql && safe_upsert($this->getTableName(), implode(',', $sql), $where);
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
}
