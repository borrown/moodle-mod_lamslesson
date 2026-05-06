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
 * Unit tests for mod_lamslesson lib.php
 *
 * @package    mod_lamslesson
 * @copyright  2011 LAMS Foundation - Ernie Ghiglione
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU GPL v2
 * @covers    \mod_lamslesson\*
 */
class lib_test extends advanced_testcase {

    /**
     * Test that all required functions exist
     */
    public function test_required_functions_exist(): void {
        $this->assertTrue(function_exists('lamslesson_add_instance'),
            'lamslesson_add_instance() function must exist');
        $this->assertTrue(function_exists('lamslesson_update_instance'),
            'lamslesson_update_instance() function must exist');
        $this->assertTrue(function_exists('lamslesson_delete_instance'),
            'lamslesson_delete_instance() function must exist');
        $this->assertTrue(function_exists('lamslesson_get_url'),
            'lamslesson_get_url() function must exist');
        $this->assertTrue(function_exists('lamslesson_get_lesson'),
            'lamslesson_get_lesson() function must exist');
        $this->assertTrue(function_exists('lamslesson_fill_lesson'),
            'lamslesson_fill_lesson() function must exist');
        $this->assertTrue(function_exists('lamslesson_get_sequences_rest'),
            'lamslesson_get_sequences_rest() function must exist');
        $this->assertTrue(function_exists('lamslesson_get_lams_outputs'),
            'lamslesson_get_lams_outputs() function must exist');
        $this->assertTrue(function_exists('lamslesson_http_call_post'),
            'lamslesson_http_call_post() function must exist');
    }
}
