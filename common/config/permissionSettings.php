<?php
	/* These parameters define what permissions will be available to assign for staff members on this site. */
	
	return [
		'all_permissions' => [
			'createParticipants',
			'updateParticipants',
			'viewParticipants',
			'flagParticipants',
			'dropParticipants',
			'hideParticipants',
			'removeParticipants',
			'viewParticipantNotes',
			'noteParticipants',
			'viewAlerts',
			'manageAlerts',
			'modifyRoles',
			'modifyStudies',
			'viewUsers',
			'modifyUsers',
			'removeUsers',
			'viewTestData',
			'viewStudyHealthData',
			'viewParticipantTestSchedule',
			'viewParticipantAvailabilitySchedule',
		],
		
		'permission_descriptions' => [
			"createParticipants" => "Add participants",
			"updateParticipants" => "Update participants",
			"viewParticipants" => "View Participants",
			
			"flagParticipants" => "Flag/unflag Participants",
			"dropParticipants" => "Drop Participants from Study",
			
			"hideParticipants" => "Remove Participants from Study",	// Note! there's a discrepency between the naming of these permissions.
			"removeParticipants" => "Delete Participants",
			"viewParticipantNotes" => "View notes on Participant pages",
			'noteParticipants' => "Write notes on Participant pages",
			"viewAlerts" => "View alerts",
			"manageAlerts" => 'View, manage, and clear alerts',
			"modifyRoles" => "Create and update Staff roles",
			"modifyStudies"=> "Create and update studies",
			"viewUsers" => "View CMS users",
			"modifyUsers" => 'Add/update CMS users',
			"removeUsers" => 'Remove CMS users',
			"viewTestData" => "View test data",
			"viewStudyHealthData" => "Export Study Health data",
			'viewParticipantTestSchedule' => "Export Participants' test schedule",
			'viewParticipantAvailabilitySchedule' => "Export Participants' availability schedule",
		],
		
		'admin_permissions' => [
			'modifyRoles',
			'modifyStudies',
			'viewUsers',
			'modifyUsers',
			'removeUsers',
		],
		
		// This is intended mostly just to help group permissions when being displayed.
		'permission_groups' => [
			"Participants" => [
				'createParticipants',
				'updateParticipants',
				'viewParticipants',
				'flagParticipants',
				'dropParticipants',
				'hideParticipants',
				'removeParticipants',
				'viewParticipantNotes',
				'noteParticipants',
			],
			"Alerts" => [
				'viewAlerts',
				'manageAlerts',
			],
			"Site Management" => [
			'modifyRoles',
			'modifyStudies',
			'viewUsers',
			'modifyUsers',
			'removeUsers',

			],
			"Data Export" => [
			'viewTestData',
			'viewStudyHealthData',
			'viewParticipantTestSchedule',
			'viewParticipantAvailabilitySchedule',
			],
		],

	];
?>