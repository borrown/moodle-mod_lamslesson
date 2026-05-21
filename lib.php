<?php
//declare(strict_types=1);

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
 * Library of interface functions and constants for module LAMS Lesson
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the lamslesson specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod_lamslesson
 * @copyright 2011 LAMS Foundation - Ernie Ghiglione (ernieg@lamsfoundation.org) 
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GNU GPL v2
 */

defined('MOODLE_INTERNAL') || die();

/** Include required files */
require_once($CFG->libdir.'/datalib.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->libdir.'/xmlize.php');
require_once($CFG->libdir.'/filelib.php');

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $lamslesson An object from the form in mod_form.php
 * @return int The id of the newly inserted lamslesson record
 */
function lamslesson_add_instance(stdClass $lamslesson): int {
    global $DB;

    $lamslesson->timecreated = time();
    lamslesson_add_lesson($lamslesson);

    # You may have to add extra stuff in here #

    $lamslesson->id = $DB->insert_record('lamslesson', $lamslesson);

    lamslesson_grade_item_update($lamslesson);

    return $lamslesson->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $lamslesson An object from the form in mod_form.php
 * @return bool Success/Fail
 */
function lamslesson_update_instance(stdClass $lamslesson): bool {
    global $DB;

    //Get first the original record and check whether the sequence_id has changed of not
    $originallamslesson  = $DB->get_record('lamslesson', array('id' => $lamslesson->instance));
    $lamslesson->timemodified = time();
    $lamslesson->id = $lamslesson->instance;

    if ($originallamslesson->sequence_id != $lamslesson->sequence_id) {
      lamslesson_add_lesson($lamslesson);
    }

    // if the displaydesign setting is unchecked with make sure we do that
    if (isset($originallamslesson->displaydesign) && !isset($lamslesson->displaydesign)) {
       	$lamslesson->displaydesign = 0;
    }

    // if the allowlearnerrestart setting is unchecked with make sure we do that
    if (isset($originallamslesson->allowlearnerrestart) && !isset($lamslesson->allowlearnerrestart)) {
       	$lamslesson->allowlearnerrestart = 0;
    }

    return $DB->update_record('lamslesson', $lamslesson);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it. Besides, it will also delete lesson from the LAMS server.
 *
 * @param int $id Id of the module instance
 * @return bool Success/Failure
 */
function lamslesson_delete_instance(int $id): bool {
    global $USER, $DB;

    if (! $lamslesson = $DB->get_record('lamslesson', array('id' => $id))) {
        return false;
    }

    $lsId = $lamslesson->lesson_id;

    # Delete any dependent records here #
    $DB->delete_records('lamslesson', array('id' => $lamslesson->id));

    // Delete grade item for given lamslesson
    lamslesson_grade_item_delete($lamslesson);

    // delete the lesson from LAMS server
    lamslesson_delete_lesson($USER->username, $lsId);

    return true;
}

/// CONSTANTS ///////////////////////////////////////////////////////////

define('LAMSLESSON_LOGIN_REQUEST', '/LoginRequest');
define('LAMSLESSON_PARAM_UID', 'uid');
define('LAMSLESSON_PARAM_FIRSTNAME', 'firstName');
define('LAMSLESSON_PARAM_LASTNAME', 'lastName');
define('LAMSLESSON_PARAM_EMAIL', 'email');
define('LAMSLESSON_PARAM_SERVERID', 'sid');
define('LAMSLESSON_PARAM_TIMESTAMP', 'ts');
define('LAMSLESSON_PARAM_HASH', 'hash');
define('LAMSLESSON_PARAM_METHOD', 'method');
define('LAMSLESSON_PARAM_COURSEID', 'courseid');
define('LAMSLESSON_PARAM_COURSENAME', 'courseName');
define('LAMSLESSON_PARAM_COUNTRY', 'country');
define('LAMSLESSON_PARAM_LANG', 'lang');
define('LAMSLESSON_PARAM_LSID', 'lsid');
define('LAMSLESSON_PARAM_AUTHOR_METHOD', 'author');
define('LAMSLESSON_PARAM_MONITOR_METHOD', 'monitor');
define('LAMSLESSON_PARAM_LEARNER_METHOD', 'learner');
define('LAMSLESSON_PARAM_LEARNER_STRICT_METHOD', 'learnerStrictAuth');
define('LAMSLESSON_PARAM_PREVIEW_METHOD', 'preview');
define('LAMSLESSON_PARAM_VERIFY_METHOD', 'verify');
define('LAMSLESSON_PARAM_JOIN', 'join');
define('LAMSLESSON_PARAM_SINGLE_PROGRESS_METHOD', 'singleStudentProgress');
define('LAMSLESSON_PARAM_PROGRESS_METHOD', 'studentProgress');
define('LAMSLESSON_PARAM_CUSTOM_CSV', 'customCSV');
define('LAMSLESSON_LD_SERVICE', '/services/xml/LearningDesignRepository');
define('LAMSLESSON_LD_SERVICE_SVG', '/services/LearningDesignSVG');
define('LAMSLESSON_LESSON_MANAGER', '/services/xml/LessonManager');
define('LAMSLESSON_LAMS_SERVERTIME', 'services/getServerTime');
define('LAMSLESSON_POPUP_OPTIONS', 'location=0,toolbar=0,menubar=0,statusbar=0,width=996,height=700,resizable');
define('LAMSLESSON_OUTPUT_METHOD', 'gradebookMarksUser');

/**
 * @param string $username The username of the user. Set this to "" if you would just like the currently logged in user to delete the lesson
 * @param int $lsId The id of the lesson that should be deleted
 * @return bool whether lesson was successfully deleted or not
 */
function lamslesson_delete_lesson(string $username, int $lsId): bool {
  
  global $CFG, $USER;
  if (!isset($CFG->lamslesson_serverid, $CFG->lamslesson_serverkey) || $CFG->lamslesson_serverid == "") {
    print_error(get_string('notsetup', 'lamslesson'));
    return false;
  }

  $datetime = lamslesson_get_datetime();
  $datetime_str = (string)$datetime;
  if(!isset($username)){
    $username = $USER->username;
  }
  $plaintext = $datetime_str.$username.(string)$CFG->lamslesson_serverid.(string)$CFG->lamslesson_serverkey;
  $hashvalue = sha1(strtolower($plaintext));

   $request = "$CFG->lamslesson_serverurl" . LAMSLESSON_LESSON_MANAGER;

   $load = array('method'	=>	'removeLesson',
		'serverId'	=>	(string)$CFG->lamslesson_serverid,
		'datetime'	=>	$datetime_str,
		'hashValue'	=>	$hashvalue,
		'username'	=>	$username,
		'lsId'		=>	$lsId);

   // GET call to LAMS
   $xml = lamslesson_http_call_post($request, $load);

   if ($xml === false) {
     return false;
   }

   $xml_array = xmlize($xml);
   $deleted = $xml_array['Lesson']['@']['deleted'] ?? false;
   return (bool)$deleted;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function lamslesson_user_outline(stdClass $course, stdClass $user, stdClass $mod, stdClass $lamslesson): stdClass {
    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function lamslesson_user_complete(stdClass $course, stdClass $user, stdClass $mod, stdClass $lamslesson): bool {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in lamslesson activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function lamslesson_print_recent_activity(stdClass $course, $viewfullnames, int $timestart): bool {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function lamslesson_cron (): bool {
    return true;
}

/**
 * Must return an array of users who are participants for a given instance
 * of lamslesson. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $lamslessonid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function lamslesson_get_participants(int $lamslessonid): array|bool {
    return false;
}

/**
 * This function returns if a scale is being used by one lamslesson
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $lamslessonid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function lamslesson_scale_used(int $lamslessonid, int $scaleid): bool {
    global $DB;

    $return = false;

    //$rec = $DB->get_record("lamslesson", array("id" => "$lamslessonid", "scale" => "-$scaleid"));
    //
    //if (!empty($rec) && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}

/**
 * Checks if scale is being used by any instance of lamslesson.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any lamslesson
 */
function lamslesson_scale_used_anywhere(int $scaleid): bool {
    global $DB;

    if ($scaleid and $DB->record_exists('lamslesson', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function lamslesson_uninstall(): bool {
    return true;
}



/**
 * Get design images
 *
 * 
 */
function lamslesson_get_design_image(string $username, int $courseid, string $coursename, $coursecreatedate, string $country, string $lang, int $ldid, string $format): string {
    global $CFG,$USER;
    // append month/year to course name
    $coursename = $coursename.' '.date('n/Y', (int)$coursecreatedate);

    // generate hash
    $datetime = lamslesson_get_datetime();
    $datetime_str = (string)$datetime;
    $datetime_encoded = urlencode($datetime_str);
   $rawstring = trim($datetime_str).trim((string)$username).trim((string)$CFG->lamslesson_serverid).trim((string)$CFG->lamslesson_serverkey);
    $hashvalue = sha1(strtolower($rawstring));

    // Put together REST URL
    $request = "$CFG->lamslesson_serverurl".LAMSLESSON_LD_SERVICE_SVG."?serverId=" . $CFG->lamslesson_serverid . "&datetime=" . $datetime_encoded . "&hashValue=" . $hashvalue . "&username=" . $username  . "&courseId=" . $courseid . "&courseName=" . urlencode((string)$coursename) . "&mode=2&country=" . $country . "&lang=" . $lang . "&ldId=" . $ldid . "&svgFormat=" . $format;

    return $request;
}


/**
 * Get sequences(learning designs) for the user in lamslesson using the REST interface
 *
 * @param string $username The username of the user. Set this to "" if you would just like to get sequences for the currently logged in user.
 * @return string to define the tree structure
 * 
 */
function lamslesson_get_sequences_rest(string $username, string $firstname, string $lastname, string $email, int $courseid, string $coursename, $coursecreatedate, string $country, string $lang): string {
    global $CFG,$USER;
    if(!isset($CFG->lamslesson_serverurl)||!isset($CFG->lamslesson_serverid)||!isset($CFG->lamslesson_serverkey)) {
        return get_string('notsetup', 'lamslesson');
    }

    // append month/year to course name
    $coursename = $coursename.' '.date('n/Y', (int)$coursecreatedate);

    // generate hash
    //$datetime = date('F d,Y g:i a');
$datetime = lamslesson_get_datetime();
   $datetime_str = (string)$datetime;
   $rawstring = trim($datetime_str).trim((string)$username).trim((string)$CFG->lamslesson_serverid).trim((string)$CFG->lamslesson_serverkey);
    $hashvalue = sha1(strtolower($rawstring));


    // Put together REST URL
    $request = "$CFG->lamslesson_serverurl".LAMSLESSON_LD_SERVICE;

   $load = array('serverId'	=>	$CFG->lamslesson_serverid,
 		'datetime' 	=>	$datetime_str,
 		'hashValue' 	=>	$hashvalue,
		  'username'    =>	$username,
		  'firstName'	=> 	$firstname,
		  'lastName'	=> 	$lastname,
		  'email'	=>	$email,
		  'courseId'	=>	$courseid,
		  'courseName'	=> 	$coursename,
		  'mode'	=> 	'2',
		  'country'	=>	$country,
		  'lang'	=> 	$lang);

   // GET call to LAMS
   $xml = lamslesson_http_call_post($request, $load);

   if ($xml === false) {
        print_error('restcallfail', 'lamslesson', $CFG->wwwroot.'/course/view.php?id='.$courseid);
   }

   $xml_array = xmlize($xml);

   // Process the Folder element and return as JavaScript object string
   // The result will be wrapped in [] by the JavaScript in mod_form.php
   $result = lamslesson_process_array($xml_array['Folder'] ?? []);
   return (string)$result;
}


/*
 * Convert workspace contents from an xmlize array into a string that YUI Tree
 * can use.
 */
function lamslesson_process_array(array $array): string {
  $output = '';
  if (empty($array['@']['resourceId'])) {
    // it's a folder
    $folder_name = preg_replace("/'/", "\\1'", $array['@']['name'] ?? '');
    $output .= "{type:'Text', label:'" . $folder_name . "',id:0";

    $hasLearningDesign = !empty($array['#']['LearningDesign']);
    $hasFolder = !empty($array['#']['Folder']);

    if (!$hasLearningDesign && !$hasFolder) {
      $output .= ",expanded:0,children:[{type:'HTML',html:'<i>-" . get_string('empty', 'lamslesson') . "-</i>', id:0}]}";
      return $output;
    } else {
      $output .= ",children:[";
    }

    if ($hasLearningDesign) {
      $lds = $array['#']['LearningDesign'];
      for($i=0; $i<count($lds); $i++) {
	$output .= "," . lamslesson_process_sequence($lds[$i]) ;
      }
    }

    if ($hasFolder) {
      $folders = $array['#']['Folder'];

      for($i=0; $i<count($folders); $i++) {
	$output .= "," . lamslesson_process_array($folders[$i]);
	if ($i < count($folders)-1) {
	  if (!empty($array['#']['Folder']['#'])) {
	    $output .= ']},';
	  }
	}
      }
    }
    $output .= "]}";
  }
  return $output;
}

function lamslesson_process_sequence(array $xml_node): string {
  $output = '';
  $ld_name = preg_replace("/'/", "\\1'", $xml_node['@']['name'] ?? '');
  $output .= "{type:'Text',label:'" . $ld_name . "',id:'" . ($xml_node['@']['resourceId'] ?? '') . "'}";
  return $output;
}


/*
 * Add a lesson instance.
 */
function lamslesson_add_lesson(stdClass $form): void {
  global $USER, $DB;
    
    $form->timemodified = time();
    
    // start the lesson
    $form->lesson_id = lamslesson_get_lesson(
        $USER->username,
        (int)$form->sequence_id,
        (int)$form->course,
        $form->name,
        $form->intro,
        (bool)$form->allowlearnerrestart,
        'start',
        $USER->country,
        $USER->lang,
        $form->customCSV ?? '',
        (int)($form->displaydesign ?? 0)
    );

    if (!isset($form->lesson_id) || $form->lesson_id <= 0) {
        return;
    }

    $members = lamslesson_get_members($form);

    // call threaded lams servlet to populate the class
    $result = lamslesson_fill_lesson(
        $USER->username,
        (int)$form->lesson_id,
        (int)$form->course,
        $USER->country,
        $USER->lang,
        $members
    );
    
    // log adding of lesson
    $cmid = 0;
    if ($cm = get_coursemodule_from_instance('lamslesson', $form->coursemodule, $form->course)) {
      $cmid = $cm->id;
    }
}

/*
 * Returns a list of learners and monitors in the given course or group.
 */
function lamslesson_get_members(stdClass $form): array {
  global $CFG, $DB;

  // see LDEV-3098 for changes in LessonManagerServlet
  $learneridstr = '';
  $learnerfnamestr = '';
  $learnerlnamestr = '';
  $learneremailstr = '';
	
  $monitoridstr = '';
  $monitorfnamestr = '';
  $monitorlnamestr = '';
  $monitoremailstr = '';

  // $context = get_context_instance(CONTEXT_MODULE, $form->coursemodule);
  $context = context_module::instance($form->coursemodule);
	
  if (empty($form->groupingid)) {  // get all course members
    $userids = lamslesson_get_course_userids((int)$form->coursemodule, $context);
  } else {  // get members of group
    $userids = groups_get_members($form->groupid);
  }
	
  foreach ($userids as $userid) {
    $user = $DB->get_record('user', array('id'=>$userid));
    if (has_capability('mod/lamslesson:manage', $context, $user->id)) {
      $monitoridstr .= "$user->username,";
      $monitorfnamestr .= "$user->firstname,";
      $monitorlnamestr .= "$user->lastname,";
      $monitoremailstr .= "$user->email,";
    }
    if (has_capability('mod/lamslesson:participate', $context, $user->id)) {
      $learneridstr .= "$user->username,";
      $learnerfnamestr .= "$user->firstname,";
      $learnerlnamestr .= "$user->lastname,";
      $learneremailstr .= "$user->email,";
    }
  }

  // Adding monitor users to firstname / lastnames

  $learnerfnamestr = $learnerfnamestr . $monitorfnamestr;
  $learnerlnamestr = $learnerlnamestr . $monitorlnamestr;
  $learneremailstr = $learneremailstr . $monitoremailstr;
	
   // remove trailing comma
   if (strlen($learneridstr) > 0) {
       $learneridstr = substr($learneridstr, 0, strlen($learneridstr)-1);
   }
   if (strlen($learnerfnamestr) > 0) {
       $learnerfnamestr = substr($learnerfnamestr, 0, strlen($learnerfnamestr)-1);
   }
   if (strlen($learnerlnamestr) > 0) {
       $learnerlnamestr = substr($learnerlnamestr, 0, strlen($learnerlnamestr)-1);
   }
   if (strlen($learneremailstr) > 0) {
       $learneremailstr = substr($learneremailstr, 0, strlen($learneremailstr)-1);
   }
   if (strlen($monitoridstr) > 0) {
       $monitoridstr = substr($monitoridstr, 0, strlen($monitoridstr)-1);
   }

   $members = array('learnersids' => $learneridstr, 'monitorsids' => $monitoridstr, 'firstnames' => $learnerfnamestr, 'lastnames' => $learnerlnamestr, 'emails' => $learneremailstr);
  return $members;
}

/**
 * Fill lesson with learners and monitors.
 *
 * @param string $username The username
 * @param int $lsid The lesson ID
 * @param int $courseid The course ID
 * @param string $country Country code
 * @param string $lang Language code
 * @param array $members Array with learners and monitors
 * @return string|false XML response or false on failure
 */
function lamslesson_fill_lesson(string $username, int $lsid, int $courseid, string $country, string $lang, array $members): string|false {
  global $CFG, $USER;
  if (!isset($CFG->lamslesson_serverid, $CFG->lamslesson_serverkey) || $CFG->lamslesson_serverid == '') {
    print_error(get_string('notsetup', 'lamslesson'));
    return false;
  }

  $datetime = lamslesson_get_datetime();
  $datetime_str = (string)$datetime;
  if(!isset($username)){
    $username = $USER->username;
  }
  $plaintext = $datetime_str.$username.(string)$CFG->lamslesson_serverid.(string)$CFG->lamslesson_serverkey;
   $hashvalue = sha1(strtolower($plaintext));

   $url = "$CFG->lamslesson_serverurl" . LAMSLESSON_LESSON_MANAGER;

   $load = array(
 		'serverId' => $CFG->lamslesson_serverid,
 		'datetime' => $datetime_str,
		'hashValue' => $hashvalue,
		'username' => $username,
		'lsId' => $lsid,
		'courseId' => $courseid,
		'country' => $country,
		'lang' => $lang,
		'learnerIds' => $learnerids,
		'firstNames' => $firstnames,
		'lastNames' => $lastnames,
		'emails' => $emails,
		'monitorIds' => $monitorids);

  // GET call to LAMS
  // We use the post method as we might be passing tons of user data.
  return lamslesson_http_call_post($url,$load);
}

/**
 * Get lesson id from lamslesson
 *
 * @param string $username The username of the user. Set this to "" if you would just like the currently logged in user to create the lesson
 * @param int $ldid The id of the learning design that the lesson is based on
 * @param int $courseid The id of the course that the lesson is associated with.
 * @param string $title The title of the lesson
 * @param string $desc The description of the lesson
 * @param string $country The Country's ISO code
 * @param string $lang The Language's ISO code
 * @return int lesson id
 */
function lamslesson_get_lesson(string $username, int $ldid, int $courseid, string $title, string $desc, bool $allowlearnerrestart, string $method, string $country, string $lang, string $customcsv = '', int $displaydesign = 0): int {

   global $CFG, $USER;
   if (!isset($CFG->lamslesson_serverid, $CFG->lamslesson_serverkey) || $CFG->lamslesson_serverid == "") {
     print_error(get_string('notsetup', 'lamslesson'));
     return 0;
   }

   //$datetime =    date("F d,Y g:i a");
   $datetime = lamslesson_get_datetime();
   $datetime_str = (string)$datetime;
   if(!isset($username)){
     $username = $USER->username;
   }
   $plaintext = $datetime_str.$username.(string)$CFG->lamslesson_serverid.(string)$CFG->lamslesson_serverkey;
   $hashvalue = sha1(strtolower($plaintext));

  $request = "$CFG->lamslesson_serverurl" . LAMSLESSON_LESSON_MANAGER;

   $load = array(
 		'method'	  		  =>	$method,
		'serverId'			  =>	$CFG->lamslesson_serverid,
		'datetime'			  =>	$datetime_str,
		'hashValue'			  =>	$hashvalue,
		'username'			  =>	$username,
		'ldId'				  =>	$ldid,
		'courseId'			  =>	$courseid,
		'title'				  =>	$title,
		'desc'				  =>	$desc,
		'country'			  =>	$country,
		'lang'				  =>	$lang,
		'allowLearnerRestart' =>	isset($allowlearnerrestart) && $allowlearnerrestart ? 'true' : 'false'
	);

   // GET call to LAMS
   $xml = lamslesson_http_call_post($request, $load);

   if ($xml === false) {
     return 0;
   }

   $xml_array = xmlize($xml);
   if (!$xml_array) {
     return 0;
   }

   $lessonId = $xml_array['Lesson']['@']['lessonId'] ?? 0;
   return (int)$lessonId;
}

function lamslesson_get_lams_outputs(string $username, stdClass $lamslesson, string $foruser): ?float {
  global $CFG, $DB;

  $datetime = lamslesson_get_datetime();
  $datetime_str = (string)$datetime;
  $plaintext = trim($datetime_str)
    .trim((string)$username)
    .trim((string)$CFG->lamslesson_serverid)
    .trim((string)$CFG->lamslesson_serverkey);
  $hash = sha1(strtolower($plaintext));
  $request = $CFG->lamslesson_serverurl. LAMSLESSON_LESSON_MANAGER;

  $load = array('serverId'	=> 	(string)$CFG->lamslesson_serverid,
		'username'	=>	(string)$username,
		'datetime'	=>	$datetime_str,
		'hashValue'	=>	$hash,
		'method'	=> 	LAMSLESSON_OUTPUT_METHOD,
		'lsId'	=>	(string)$lamslesson->lesson_id,
		'outputsUser'	=>	$foruser
	);

  // GET call to LAMS
   $xml = lamslesson_http_call_post($request, $load);

   if ($xml === false) {
     return null;
   }

   $results = xmlize($xml);
   if (!$results) {
     return null;
   }

   $lessonElement = $results['GradebookMarks']['#']['Lesson']['0'] ?? null;
   if ($lessonElement === null) {
     return null;
   }
   $learnerElement = $lessonElement['#']['Learner']['0'] ?? null;
   if ($learnerElement === null) {
     return null;
   }

   $maxresult = $lessonElement['@']['lessonMaxPossibleMark'] ?? 0;
   $userresult = $learnerElement['@']['userTotalMark'] ?? 0;

   // If there's outputs from LAMS, then we process them and add them to the gradebook
   if ($maxresult > 0) {

     // Now calculate the percentage and then multiply it by the lamslesson grade.
     $gradebookmark = ($userresult / $maxresult) *  $lamslesson->grade;

     // Put this into gradebook
     $user = $DB->get_record('user', array('username'=>$foruser));
     if ($user) {
         lamslesson_update_grades($lamslesson, $user->id, $gradebookmark);
     }

     return (float)$gradebookmark;
   }

    return null;
}

/**
 * Return URL to join a LAMS lesson as a learner or staff depending on method.
 * URL redirects LAMS to learner or monitor interface depending on method.
 */
function lamslesson_get_url(string $username, string $firstname, string $lastname, string $email, string $lang, string $country, int $lessonid, int $courseid, string $coursename, int $coursecreatedate, string $method, string $extraparam='', string $customcsv=''): string {
    global $CFG;

    // append month/year to course name
    $coursename = $coursename.' '.date('n/Y', (int)$coursecreatedate);

    // change datetime to enforce time to live for login request
    $datetime = lamslesson_get_datetime();
    $datetime_str = (string)$datetime;

    // check if we are to use lessonstrictauth
    if ($method == LAMSLESSON_PARAM_LEARNER_STRICT_METHOD) {
	$plaintext = trim($datetime_str)
            .trim((string)$username)
            .trim((string)$method)
	    .trim((string)$lessonid)
            .trim((string)$CFG->lamslesson_serverid)
            .trim((string)$CFG->lamslesson_serverkey);

} else {
    	$plaintext = trim($datetime_str)
             .trim((string)$username)
             .trim((string)$method)
             .trim((string)$CFG->lamslesson_serverid)
             .trim((string)$CFG->lamslesson_serverkey);
     }
     $hash = sha1(strtolower($plaintext));
     $url = $CFG->lamslesson_serverurl. LAMSLESSON_LOGIN_REQUEST .
        '?'.LAMSLESSON_PARAM_UID.'='.$username.
	'&'.LAMSLESSON_PARAM_FIRSTNAME.'='.urlencode((string)$firstname).
	'&'.LAMSLESSON_PARAM_LASTNAME.'='.urlencode((string)$lastname).
	'&'.LAMSLESSON_PARAM_EMAIL.'='.urlencode((string)$email).
        '&'.LAMSLESSON_PARAM_METHOD.'='.$method.
        '&'.LAMSLESSON_PARAM_TIMESTAMP.'='.urlencode($datetime_str).
        '&'.LAMSLESSON_PARAM_SERVERID.'='.urlencode((string)$CFG->lamslesson_serverid).
        '&'.LAMSLESSON_PARAM_HASH.'='.urlencode($hash).
        ($method==LAMSLESSON_PARAM_AUTHOR_METHOD ? '' : '&'. LAMSLESSON_PARAM_LSID .'='.(string)$lessonid).
        '&'. LAMSLESSON_PARAM_COURSEID .'='.$courseid.
        '&'. LAMSLESSON_PARAM_COURSENAME .'='.urlencode((string)$coursename).
		'&'. LAMSLESSON_PARAM_COUNTRY .'='.urlencode((string)$country).
		'&'. LAMSLESSON_PARAM_LANG .'='.urlencode(substr(trim((string)$lang),0,2));

    if ($extraparam != '') {
      $url .= '&'.$extraparam;
    }
if ($customcsv != '') {
       $url .= '&'. LAMSLESSON_PARAM_CUSTOM_CSV .'='.urlencode((string)$customcsv);
     }
    return $url;
}


/*
 * Returns list of userids of users in the given context
 */
function lamslesson_get_course_userids(int $lamslessonid, ?stdClass $context=NULL): array {
	global $CFG, $DB;

	if ($context == NULL) {
	  $lamslesson = $DB->get_record('lamslesson', array('id' => $lamslessonid));
		if (! $cm = get_coursemodule_from_instance('lamslesson', $lamslesson->id, $lamslesson->course)) {
			print_error('Course Module ID was incorrect', 'lamslesson');
		}
		// $context = get_context_instance(CONTEXT_MODULE, $cm->id);
		$context = context_module::instance($cm->id);
	}
	
	// we are looking for all users assigned in this context or higher
	if ($usercontexts = $context->get_parent_context_ids($context)) {
		$listofcontexts = '('.implode(',', $usercontexts).')';
	} else {
		// $sitecontext = get_context_instance(CONTEXT_SYSTEM);
		$sitecontext = context_system::instance();

		$listofcontexts = '('.$sitecontext->id.')'; // must be site
	}
	$sql = "SELECT DISTINCT u.id
		FROM {user} u INNER JOIN {role_assignments} r ON u.id=r.userid
		WHERE r.contextid IN $listofcontexts OR r.contextid=$context->id
		AND u.deleted=0 AND u.username!='guest'";
	$users = $DB->get_records_sql($sql);
	$userids = array_keys($users);  // turn list of id-backed objects into list of ids
	return $userids;
}


/*
 * Gets all the student progress for a lesson in one go
 * 
 */
function lamslesson_get_student_progress(string $username, int $ldid, int $courseid, string $firstname, string $lastname, string $email, string $country, string $lang): ?array {
	global $CFG;
	if (!isset($CFG->lamslesson_serverid, $CFG->lamslesson_serverkey) || $CFG->lamslesson_serverid == "") {
 		print_error(get_string('notsetup', 'lamslesson'));
 		return [];
	}
     
	//$datetime =    date("F d,Y g:i a");
  	$datetime = lamslesson_get_datetime();
  	$datetime_str = (string)$datetime;
   $plaintext = $datetime_str.$username.(string)$CFG->lamslesson_serverid.(string)$CFG->lamslesson_serverkey;
   $hashvalue = sha1(strtolower($plaintext));
 
   $request = "$CFG->lamslesson_serverurl" . LAMSLESSON_LESSON_MANAGER;
 
  	$load = array('method'	=>	LAMSLESSON_PARAM_SINGLE_PROGRESS_METHOD,
 		'serverId'	=>	(string)$CFG->lamslesson_serverid,
 		'datetime'	=>	$datetime_str,
 		'hashValue'	=>	$hashvalue,
 		'lsId'		=>	(string)$ldid,
 		'courseId'	=>	(string)$courseid,
 		'progressUser'	=>	$username,
 		'username'	=>	$username,
 		'firstName'	=> 	$firstname,
 		'lastName'	=> 	$lastname,
 		'email'		=> 	$email,
 		'country'	=> 	$country,
 		'lang'		=>	$lang
 	);
 
  	// GET call to LAMS
   
  	$xml = lamslesson_http_call_post($request, $load);
 
  	if ($xml === false) {
  	    return [];
  	}
  	
  	$xml_array = xmlize($xml);
 
  	$response = $xml_array['LessonProgress']['#'] ?? [];
  	if (empty($response)) {
  	    return [];
  	}
  	$learnerProgress = $response['LearnerProgress']['0'] ?? [];
  
  	return $learnerProgress['@'] ?? [];
}

//returns the moodle completion state for a user)
function lamslesson_get_moodle_completion(stdClass $course, stdClass $cm): stdClass {
	$completion = new completion_info($course);
	return $completion->get_data($cm);
}

function lamslesson_set_as_completed(stdClass $cm, stdClass $course, stdClass $lamslesson): void {
	// Update completion state
	$completion = new completion_info($course);
	if ($completion->is_enabled($cm) && $lamslesson->completionfinish) {
		lamslesson_set_completion_state($cm,$completion,COMPLETION_COMPLETE);
	}
}

function lamslesson_set_as_incomplete(stdClass $cm, stdClass $course, stdClass $lamslesson): void {
	// Update completion state
	$completion = new completion_info($course);
	if ($completion->is_enabled($cm) && $lamslesson->completionfinish) {
		lamslesson_set_completion_state($cm,$completion,COMPLETION_INCOMPLETE);
	}
}

function lamslesson_set_completion_state(stdClass $cm, stdClass $completion, int $state): void {
	//Update completion state

	switch ($state) {
	case COMPLETION_COMPLETE:

		$completion->update_state($cm, COMPLETION_COMPLETE);
		break;

 	default:
		$completion->update_state($cm, COMPLETION_INCOMPLETE);
		break;
	}
}

/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function lamslesson_supports(string $feature): bool|string|null {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return false;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_RATE:                    return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_MOD_PURPOSE:             return 'interaction';
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


/**
 * Return info for course module listing.
 *
 * @param stdClass $cm The course module object.
 * @return cached_cm_info|false
 */
function lamslesson_get_coursemodule_info($cm) {
    global $DB;

    $lamslesson = $DB->get_record('lamslesson', ['id' => $cm->instance], 'id, name, intro, introformat');
    if (!$lamslesson) {
        return false;
    }

    $info = new cached_cm_info();
    $info->name = $lamslesson->name;

    if ($cm->showdescription && !empty($lamslesson->intro)) {
        $info->content = format_module_intro('lamslesson', $lamslesson, $cm->id);
    }

    return $info;
}


/**
 * Create/update grade item for given lamslesson
 *
 * @global stdClass
 * @uses GRADE_TYPE_NONE
 * @uses GRADE_TYPE_VALUE
 * @uses GRADE_TYPE_SCALE
 * @param stdClass $lamslesson object with extra cmidnumber
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok
 */
function lamslesson_grade_item_update(stdClass $lamslesson, ?stdClass $grades=NULL): int {
    global $CFG;
    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    $params = array('itemname'=>$lamslesson->name);

    if ($lamslesson->grade > 0 ) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']   = $lamslesson->grade;
        $params['grademin']   = 0;
    } else {
      $params['gradetype']  = GRADE_TYPE_NONE;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = NULL;
    }

    return grade_update('mod/lamslesson', $lamslesson->course, 'mod', 'lamslesson', $lamslesson->id, 0, $grades, $params);
}

/**
 * Delete grade item for given lamslesson
 *
 * @global stdClass
 * @param stdClass $lamslesson object
 * @return int
 */
function lamslesson_grade_item_delete(stdClass $lamslesson): int {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/lamslesson', $lamslesson->course, 'mod', 'lamslesson', $lamslesson->id, 0, NULL, array('deleted'=>1));
}

/**
 * Update grades in central gradebook
 *
 * @global stdClass
 * @param stdClass $lamslesson
 * @param int $userid specific user only, 0 means all
 */
function lamslesson_update_grades(stdClass $lamslesson, int $userid, float $usermark): void {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if ($lamslesson->grade == 0) {
        lamslesson_grade_item_update($lamslesson);

    } else if ($userid) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = $usermark;
        lamslesson_grade_item_update($lamslesson, $grade);

    } else {
        lamslesson_grade_item_update($lamslesson);
    }

}

/**
 * Verify Moodle settings with LAMS
 *
 * @param url $url
 * @param string $id
 * @param string $key
 */
function lamslesson_verify(string $url, string $id, string $key): string {
    
    $datetime = lamslesson_get_datetime();
    $datetime_str = (string)$datetime;
    $plaintext = $datetime_str.$id.(string)$key;
    // create hash
    $hashvalue = sha1(strtolower($plaintext));

    $request = $url . LAMSLESSON_LESSON_MANAGER;

    $load = array('method' 	=>	LAMSLESSON_PARAM_VERIFY_METHOD,
		  'serverId'	=>	$id,
		  'datetime'	=>	$datetime_str,
		  'hashValue'	=>	$hashvalue
	);

    $validate = lamslesson_http_call_post($request, $load);

	if ( $validate == 1 )  {
		// validation successful
		return get_string('validationsuccessful', 'lamslesson');
	} else {
		// validation failed
		return get_string('validationfailed', 'lamslesson');
	}

}

/**
 * Submits an HTTP POST to a LAMS server
 * @param string $request
 * @return false or string with response if correct
 */
function lamslesson_http_call_post(string $url, array $request): string|false {
	global $CFG;

	# pass charset as part of headers so it is interpreted correctly
	# on the LAMS side. See LDEV-2875
	$curl = new curl();
	$curl->setHeader("Content-Type: application/x-www-form-urlencoded;charset=utf-8");

	// Build proper POST data string
	$postdata = http_build_query($request);

	$results = $curl->post($url, $postdata);

	// Check for curl errors
	if (!empty($curl->error)) {
		debugging('LAMS cURL error: ' . $curl->error, DEBUG_DEVELOPER);
		return false;
	}

	if ($results !== false) {
 		return $results;
	} else {
 		return false;
	}
}

/**
 * Returns a Number of milliseconds since January 1, 1970, 00:00:00 GMT 
 * (known as "the epoch") till the time when the call is made
 *
 * @return timestamp in milliseconds
*/
function lamslesson_get_datetime(): float {
	global $CFG;

    // change datetime to enforce time to live for login request
    // See LDEV-3382

	$offset = $CFG->lamslesson_servertimeoffset * 60 * 1000;

	$datetime = round(microtime(true) * 1000) + $offset;

	return $datetime;
}

/**
 * Gets LAMS server time
 *
 * @return timestamp in milliseconds (from LAMS server)
*/
function lamslesson_get_lamsserver_time(): string {
    // change datetime to enforce time to live for login request
    // See LDEV-3382
	global $CFG;
	$url = "$CFG->lamslesson_serverurl" . LAMSLESSON_LAMS_SERVERTIME;
 	$load = array('method'      =>      LAMSLESSON_PARAM_VERIFY_METHOD);

	$result = lamslesson_http_call_post($url, $load);

	$localtime = round(microtime(true) * 1000);

	$offset = $result - $localtime;
	
    return "LAMS time: " . date('m-d-Y H:i:s.u', (int)($result/1000)) . " \rMoodle time:" . date('m-d-Y H:i:s.u', (int)($localtime/1000)) . " \rOffset: " . $offset/1000/60 . " minutes";
}

/**
 * Trigger the course_module_viewed event.
 *
 * @param  stdClass $lamslesson        lamslesson object
 * @param  stdClass $course            course object
 * @param  stdClass $cm                course module object
 * @param  stdClass $context           context object
 * @since Moodle 3.0
 */
function lamslesson_view(stdClass $lamslesson, stdClass $course, stdClass $cm, stdClass $context): void {

  // Trigger course_module_viewed event.
  $params = array(
      'context' => $context,
      'objectid' => $lamslesson->id
  );

  $event = \mod_lamslesson\event\course_module_viewed::create($params);
  $event->add_record_snapshot('course_modules', $cm);
  $event->add_record_snapshot('course', $course);
  $event->add_record_snapshot('lamslesson', $lamslesson);
  $event->trigger();
}

/**
 * Get URL to join a LAMS lesson as a learner or staff.
 * URL redirects LAMS to learner or monitor interface depending on method.
 *
 * @param string $username The username
 * @param string $firstname User's first name
 * @param string $lastname User's last name
 * @param string $email User's email
 * @param string $lang Language code
 * @param string $country Country code
 * @param int $lessonid The lesson ID
 * @param int $courseid The course ID
 * @param string $coursename The course name
 * @param int $coursecreatedate Course creation date
 * @param string $method The method (learner, monitor, etc.)
 * @param string $extraparam Extra parameters
 * @param string $customcsv Custom CSV data
 * @return string The URL to access the lesson
 */
