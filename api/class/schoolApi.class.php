<?php
if ( ! class_exists( 'SchoolApi' ) ) {
	class SchoolApi {
		public function load() {
			$this->mjschool_activateStudentRegistrationAPI();
			$this->mjschool_activateSchoolLoginAPI();
			$this->mjschool_activateSaveAttendanceAPI();
			$this->mjschool_activateSubjectListAPI();
			$this->mjschool_activateClassListAPI();
			$this->mjschool_activateViewProfileAPI();
		}
		function mjschool_activateViewProfileAPI() {
			$viewprofile_obj = new ViewProfile();
		}
		function mjschool_activateStudentRegistrationAPI() {
			$stud_register = new StudentRegistration();
		}
		function mjschool_activateSchoolLoginAPI() {
			$school_login = new SchoolLogin();
		}
		function mjschool_activateSaveAttendanceAPI() {
			$attendance_obj = new MJSchool_Attendance();
		}
		function mjschool_activateClassListAPI() {
			$classlist = new ClassListing();
		}
		function mjschool_activateSubjectListAPI() {
			$subjectlist = new SubjectListing();
		}
	}
}