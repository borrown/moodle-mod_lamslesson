<?php
//declare(strict_types=1);

/**
 * Overview class for LAMS Lesson module.
 *
 * @package   mod_lamslesson
 * @copyright 2011 LAMS Foundation - Ernie Ghiglione (ernieg@lamsfoundation.org)
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPL v2
 */

namespace mod_lamslesson\courseformat;

defined('MOODLE_INTERNAL') || die();

 /**
  * Overview class for LAMS Lesson module.
  */
 class overview extends \core_courseformat\activityoverviewbase {

     /**
      * Get extra overview items showing sequence and completion stats.
      *
      * @return \core_courseformat\local\overview\overviewitem[]
      */
     public function get_extra_overview_items(): array {
        global $DB;
        $items = [];
        $lamslesson = $this->cm->get_instance_record();
        if (!$lamslesson) {
            return $items;
        }

         // Sequence ID.
         if (!empty($lamslesson->sequence_id)) {
            $items[] = new \core_courseformat\local\overview\overviewitem(
                 get_string('selectsequence', 'lamslesson'),
                 $lamslesson->sequence_id,
                 $lamslesson->sequence_id
             );
         }

        // Completion stats: X of Y completed.
        $coursecontext = \context_course::instance($this->course->id);
        // get_enrolled_users() has many optional args; be explicit to avoid
        // passing a boolean into the $orderby position (can break SQL on 5.x).
        $enrolledusers = get_enrolled_users($coursecontext, 'mod/lamslesson:participate', 0, 'u.id', '', 0, 0, true);
        $totalstudents = count($enrolledusers);

        if ($totalstudents > 0) {
            $completed = $DB->count_records('lamslesson_grade', [
                'lamslessonlesson' => $lamslesson->id,
                'completed' => 1,
            ]);
            $completiontext = $completed . ' / ' . $totalstudents;
        } else {
            $completiontext = '-';
        }

        $items[] = new \core_courseformat\local\overview\overviewitem(
             get_string('yourprogress', 'lamslesson'),
             $completiontext,
             $completiontext
         );

        return $items;
    }
}
