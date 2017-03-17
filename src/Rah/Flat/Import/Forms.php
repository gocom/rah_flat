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
 * Imports form partials.
 */

class Rah_Flat_Import_Forms extends Rah_Flat_Import_Pages
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

    public function getTemplateIterator($directory)
    {
        return new RecursiveIteratorIterator(
            new Rah_Flat_FilterIterator(
                new Rah_Flat_FormIterator($directory)
            )
        );
    }

    /**
     * {@inheritdoc}
     */

    public function importTemplate(Rah_Flat_TemplateIterator $file)
    {
        safe_upsert(
            $this->getTableName(),
            "Form = '".doSlash($file->getTemplateContents())."',
            type = '".doSlash($file->getTemplateType())."'",
            "name = '".doSlash($file->getTemplateName())."'"
        );
    }
}
