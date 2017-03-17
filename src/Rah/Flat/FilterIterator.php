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
 * Filters template iterator results.
 *
 * This class iterates over template files.
 *
 * <code>
 * $filteredTemplates = new Rah_Flat_FilterIterator(
 *    Rah_Flat_TemplateIterator('/path/to/dir')
 * );
 * </code>
 *
 * @see \RecursiveFilterIterator
 */

class Rah_Flat_FilterIterator extends RecursiveFilterIterator
{
    /**
     * {@inheritdoc}
     */

    public function accept() {
        return $this->isDir() || $this->isValidTemplate();
    }
}
