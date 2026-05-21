<?php
declare(strict_types=1);

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
 * This page lists all the instances of lamslesson in a particular course
 *
 * @package   mod_lamslesson
 * @copyright 2011 LAMS Foundation - Ernie Ghiglione (ernieg@lamsfoundation.org)
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPL v2
 */

require_once('../../config.php');

$courseid = required_param('id', PARAM_INT);

// Validate course exists.
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// Require login to the course.
require_course_login($course);

// Set up page.
$PAGE->set_url('/mod/lamslesson/index.php', ['id' => $courseid]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('incourse');

// Get course context.
$context = context_course::instance($course->id);

// Check capabilities.
// This plugin does not define a dedicated "mod/lamslesson:view" capability.
// Treat "participate" as the read capability and allow managers too.
if (!has_capability('mod/lamslesson:participate', $context) && !has_capability('mod/lamslesson:manage', $context)) {
    require_capability('mod/lamslesson:participate', $context);
}

// Get all lamslesson instances in this course.
$lamslessons = get_all_instances_in_course('lamslesson', $course);

// Output header.
echo $OUTPUT->header();

// Display course name.
echo $OUTPUT->heading(get_string('modulenameplural', 'lamslesson'));

// Check if there are any lamslessons.
if (empty($lamslessons)) {
    echo $OUTPUT->notification(get_string('nolessons', 'lamslesson'), 'notifymessage');
    echo $OUTPUT->footer();
    exit;
}

// Build table of lamslessons.
$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

// Table headers.
$table->head = [
    get_string('lessonname', 'lamslesson'),
    get_string('introduction', 'lamslesson'),
    get_string('links', 'lamslesson'),
    get_string('lastmodified', 'lamslesson'),
];
if (has_capability('mod/lamslesson:manage', $context)) {
    $table->head[] = get_string('grade');
}

// Table rows.
foreach ($lamslessons as $lamslesson) {
    $cm = get_coursemodule_from_id('lamslesson', $lamslesson->coursemodule, $course->id);

    $cmcontext = $cm ? context_module::instance($cm->id) : null;
    if (!$cm || (!$cmcontext)) {
        continue;
    }

    // Same read capability logic as above.
    if (!has_capability('mod/lamslesson:participate', $cmcontext) && !has_capability('mod/lamslesson:manage', $cmcontext)) {
        continue;
    }

    $row = [];

    // Lesson name with link.
    $lessonname = format_string($lamslesson->name);
    $url = new moodle_url('/mod/lamslesson/view.php', ['id' => $cm->id]);
    $row[] = html_writer::link($url, $lessonname);

    // Introduction.
    if (!empty($lamslesson->intro)) {
        $intro = format_module_intro('lamslesson', $lamslesson, $cm->id);
        $row[] = $intro;
    } else {
        $row[] = '';
    }

    // Links.
    $links = [];
    $links[] = html_writer::link(
        new moodle_url('/mod/lamslesson/view.php', ['id' => $cm->id]),
        get_string('openlesson', 'lamslesson')
    );
    if (has_capability('mod/lamslesson:manage', $cmcontext)) {
        $links[] = html_writer::link(
            new moodle_url('/mod/lamslesson/view.php', ['id' => $cm->id, 'mode' => 'monitor']),
            get_string('openmonitor', 'lamslesson')
        );
    }
    $row[] = implode(' | ', $links);

    // Last modified.
    $row[] = userdate($lamslesson->timemodified);

    // Grade (only for managers).
    if (has_capability('mod/lamslesson:manage', $context)) {
        if ($lamslesson->grade > 0) {
            $row[] = get_string('outofmark', 'lamslesson') . ' ' . $lamslesson->grade;
        } else {
            $row[] = '-';
        }
    }

    $table->data[] = $row;
}

// Output table.
echo html_writer::table($table);

// Output footer.
echo $OUTPUT->footer();
