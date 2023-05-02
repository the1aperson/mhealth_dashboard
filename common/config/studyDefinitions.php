<?php
	/* This file is included by common/config/main.php */
	
return [
    'class' => 'common\studyDefinitions\StudyDefinitions',
    
    'participant_id_rules' => [
	    'min' => 5,
	    'max' => 5,
    ],
    
    'participant_password_rules' => [
	    'min' => 5,
	    'max'=> 5,
    ],
    
    'test_types' => [
	    'cognitive' => "Cognitive Test",
		'edna' => "EDNA",
		'ema' => "EMA",
		'mindfulness' => "Mindfulness Survey"
    ],
    
    'expiration_time' => 7200,
        
    'study_schedule' => [
	    [
		    'name' => 'Pre-Intervention',
		    'start' => 0,
		    'length' => 28,
		    'tests' => [
			    [
					'type' => 'cognitive',
					'frequency' => 4,
					'frequency_label' => "Four Times Daily",
					'contiguous' => true,
				],
				[
					'type' => 'ema',
					'frequency' => 4,
					'frequency_label' => "Four Times Daily",
					'contiguous' => true,
				],
		    ]
	    ],
	    [
		    'name' => 'Intervention',
		    'start' => 7,
		    'length' => 63,
		    'tests' => [
			    [
						'type' => 'edna',
						'frequency' => 1,
						'frequency_label' => "Once Daily",
				],
				[
						'type' => 'mindfulness',
						'frequency' => 1,
						'frequency_label' => "Once Daily",
				]
		    ]
	    ],
	    [
		    'name' => 'Post-Intervention',
		    'start' => 0,
		    'length' => 28,
		    'tests' => [
			    [
					'type' => 'cognitive',
					'frequency' => 4,
					'frequency_label' => "Four Times Daily",
					'contiguous' => true,
				],
				[
					'type' => 'ema',
					'frequency' => 4,
					'frequency_label' => "Four Times Daily",
					'contiguous' => true,
				],
				[
					'type' => 'mindfulness',
					'frequency' => 1,
					'frequency_label' => "Once Daily",
				]
		    ]
	    ],
    ],
    
];
?>