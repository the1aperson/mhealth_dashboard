<?php

namespace common\dataFormatters;

use yii\db\Query;

use common\models\TestSessionData;


class EmaFormatter extends MHTestFormatter
{
			
	public function orderedFields()
	{
		return ["id", "day", "survey number", "time", "ema_1", "timestamp_ema_1", "ema_2", "timestamp_ema_2", "ema_3", "timestamp_ema_3", "ema_4", "timestamp_ema_4", "ema_5", "timestamp_ema_5",
		"ema_6", "timestamp_ema_6", "ema_7", "timestamp_ema_7", "ema_8", "timestamp_ema_8", "ema_9", "timestamp_ema_9"];
	}
	
}