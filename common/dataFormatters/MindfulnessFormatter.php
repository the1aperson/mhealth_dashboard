<?php

namespace common\dataFormatters;

use yii\db\Query;

use common\models\TestSessionData;


class MindfulnessFormatter extends MHTestFormatter
{
	public function orderedFields()
	{
		return ["id", "day", "survey number", "time", "mindfulness_1a", "mindfulness_1b", "mindfulness_1c", "mindfulness_1d", "timestamp_mindfulness_1", "mindfulness_sub_1", "timestamp_mindfulness_sub_1", "mindfulness_sub_2", "timestamp_mindfulness_sub_2", "mindfulness_sub_3", "timestamp_mindfulness_sub_3"];
	}

	public function responseOptionsForQuestion($question_id)
	{
		if($question_id == "mindfulness_1")
		{
			return ["mindfulness_1a", "mindfulness_1b", "mindfulness_1c", "mindfulness_1d"];
		}
		return null;
	}
}