<?php

/*
 * rah_flat - Flat templates for Textpattern CMS
 * https://github.com/gocom/rah_flat
 *
 * Copyright (C) 2019 Jukka Svahn
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
 * Main plugin class.
 *
 * @internal
 */
class Rah_Flat
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        register_callback(array($this, 'import'), 'rah_flat.import');

        if (get_pref('production_status') !== 'live') {
            register_callback(array($this, 'import'), 'textpattern');
            register_callback(array($this, 'import'), 'admin_side', 'body_end');
        }
    }

    /**
     * Imports themes.
     *
     * @return void
     */
    public function import()
    {
        $skin = \Txp::get('\Textpattern\Skin\Skin');
        $skin->setNames(array_keys((array)$skin->getUploaded()))->import(true, true);
    }
}

new Rah_Flat();
