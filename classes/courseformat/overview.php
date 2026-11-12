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
     * Cache of sequence id => name for the current request.
     *
     * @var array<int, string>
     */
    private static array $sequencenames = [];

    /**
     * Whether we already attempted to load sequence names this request.
     */
    private static bool $sequencenamesloaded = false;

    /**
     * Load all sequence names available for the current user (best-effort).
     */
    private static function load_sequence_names_for_user(int $courseid, string $coursename, int $coursecreatedate): void {
        global $CFG, $USER;

        if (self::$sequencenamesloaded) {
            return;
        }
        self::$sequencenamesloaded = true;

        if (!isset($CFG->lamslesson_serverurl, $CFG->lamslesson_serverid, $CFG->lamslesson_serverkey)) {
            return;
        }

        // Ensure the LAMS helper functions/constants are available.
        require_once($CFG->dirroot . '/mod/lamslesson/lib.php');

        // Append month/year to course name (matches lamslesson_get_sequences_rest()).
        $coursename = $coursename . ' ' . date('n/Y', $coursecreatedate);

        $datetime = lamslesson_get_datetime();
        $datetime_str = (string) $datetime;
        $rawstring = trim($datetime_str)
            . trim((string) $USER->username)
            . trim((string) $CFG->lamslesson_serverid)
            . trim((string) $CFG->lamslesson_serverkey);
        $hashvalue = sha1(strtolower($rawstring));

        $request = $CFG->lamslesson_serverurl . LAMSLESSON_LD_SERVICE;
        $load = [
            'serverId' => $CFG->lamslesson_serverid,
            'datetime' => $datetime_str,
            'hashValue' => $hashvalue,
            'username' => $USER->username,
            'firstName' => $USER->firstname,
            'lastName' => $USER->lastname,
            'email' => $USER->email,
            'courseId' => $courseid,
            'courseName' => $coursename,
            'mode' => '2',
            'country' => $USER->country,
            'lang' => $USER->lang,
        ];

        $xml = lamslesson_http_call_post($request, $load);
        if ($xml === false) {
            return;
        }

        $xml_array = xmlize($xml);
        if (empty($xml_array['Folder'])) {
            return;
        }

        self::extract_sequence_names_from_node($xml_array['Folder']);
    }

    /**
     * Recursively extract sequence names from an xmlize() node.
     *
     * @param array $node
     */
    private static function extract_sequence_names_from_node(array $node): void {
        // LearningDesign elements under this folder.
        $lds = $node['#']['LearningDesign'] ?? null;
        if (is_array($lds)) {
            foreach ($lds as $ld) {
                $id = $ld['@']['resourceId'] ?? null;
                $name = $ld['@']['name'] ?? null;
                if ($id !== null && $name !== null) {
                    self::$sequencenames[(int) $id] = (string) $name;
                }
            }
        }

        // Nested folders.
        $folders = $node['#']['Folder'] ?? null;
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                if (is_array($folder)) {
                    self::extract_sequence_names_from_node($folder);
                }
            }
        }
    }

    /**
     * Get the sequence name for a given id (best-effort).
     */
    private function get_sequence_name(int $sequenceid): ?string {
        self::load_sequence_names_for_user((int) $this->course->id, (string) $this->course->fullname, (int) $this->course->timecreated);
        return self::$sequencenames[$sequenceid] ?? null;
    }

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
            $sequenceid = (int) $lamslesson->sequence_id;
            $sequencename = $this->get_sequence_name($sequenceid);

            $sequencelabel = $sequencename ?? (string) $sequenceid;

            $sequencecontent = s($sequencelabel);
            $coursecontext = \context_course::instance($this->course->id);
            if (has_capability('mod/lamslesson:manage', $coursecontext)) {
                $previewurl = new \moodle_url('/mod/lamslesson/preview.php', [
                    'course' => $this->course->id,
                    'ldId' => $sequenceid,
                ]);
                $sequencecontent = \html_writer::link(
                    $previewurl,
                    $sequencecontent,
                    ['target' => '_blank', 'rel' => 'noopener', 'title' => get_string('previewthislesson', 'lamslesson')]
                );
            }

            // Sequence name (linked to preview for teachers).
            $items[] = new \core_courseformat\local\overview\overviewitem(
                get_string('sequencename', 'lamslesson'),
                $sequencelabel,
                $sequencecontent
            );

            // Sequence ID.
            $items[] = new \core_courseformat\local\overview\overviewitem(
                get_string('sequenceid', 'lamslesson'),
                $sequenceid,
                (string) $sequenceid
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
