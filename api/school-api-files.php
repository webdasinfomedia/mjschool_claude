<?php
require_once MJSCHOOL_PLUGIN_DIR . '/api/class/schoolApi.class.php';
require_once MJSCHOOL_PLUGIN_DIR . '/api/class/school-login.php';
require_once MJSCHOOL_PLUGIN_DIR . '/api/class/student-registration.php';
require_once MJSCHOOL_PLUGIN_DIR . '/api/class/attendance.php';
require_once MJSCHOOL_PLUGIN_DIR . '/api/class/subject-listing.php';
require_once MJSCHOOL_PLUGIN_DIR . '/api/class/class-listing.php';
require_once MJSCHOOL_PLUGIN_DIR . '/api/class/view-profile.php';
$school = new SchoolApi();
$school->load();