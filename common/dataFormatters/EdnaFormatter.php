<?php

namespace common\dataFormatters;

use yii\db\Query;

use common\models\TestSessionData;


class EdnaFormatter extends MHTestFormatter
{
	public function orderedFields()
	{
		return ["id", "day", "survey number", "time", "edna_1a", "edna_1b", "edna_1c", "edna_1d", "edna_1e", "timestamp_edna_1", "edna_2", "timestamp_edna_2", "edna_3", "timestamp_edna_3"];
	}
				
	public function responseOptionsForQuestion($question_id)
	{
		if($question_id == "edna_1")
		{
			return ['edna_1a', 'edna_1b', 'edna_1c', 'edna_1d', 'edna_1e'];
		}
		
		return null;
	}
}