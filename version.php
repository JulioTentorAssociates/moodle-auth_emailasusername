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
 * @package    auth_emailasusername
 * @copyright  2016 onwards David Pesce (http://exputo.com)
 * @copyright  2026 Julio Tentor & Associates <https://juliotentor.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'auth_emailasusername';
$plugin->version   = 2026090800;
$plugin->requires  = 2026042002; // Moodle 5.2.2.
$plugin->supported = [502, 502];
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '2.1.0';
