<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    repository_dropzone
 * @copyright  2024 Andrei Bautu <abautu@gmail.com>
 * @author     Andrei Bautu <abautu@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'repository_dropzone';
$plugin->maturity  = MATURITY_STABLE;
$plugin->version   = 2025071800;
$plugin->release   = $plugin->version;
$plugin->requires  = 2022041900;
$plugin->dependencies = [
    'repository_upload' => 2022041900,
];
