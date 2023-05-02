<?php
return [	
	'export_scopes' => [
		'all_tests',
		'baseline',
		'session_schedule',
		'wake_sleep_schedule',
		'study_health',
	],
	
	'export_scope_descriptions' => [
		'all_tests' => "All Test Data",
		'baseline' => "Baseline Test Data",
		'session_schedule' => "Study Health Data",
		'wake_sleep_schedule' => "Availability Data",
		'study_health' => "Study Health",
		'filters' => 'As Shown With Filters:',
	],
	
	'export_scope_permissions' => [
		'viewTestData' => ['all_tests', 'baseline', 'filters'],
		'viewParticipantTestSchedule' => ['session_schedule'],
		'viewStudyHealthData' => ['study_health'],
		'viewParticipantAvailabilitySchedule' => ['wake_sleep_schedule'],
	],
	
	'export_modal_permissions' => [
		'site' => ['viewTestData', 'viewStudyHealthData'],
		'participant' => ['viewTestData', 'viewParticipantTestSchedule', 'viewParticipantAvailabilitySchedule'],
		'participant_tracking' => ['viewTestData',],
	],
		
];
?>