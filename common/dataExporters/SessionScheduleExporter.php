<?php

namespace common\dataExporters;

use Yii;
use yii\db\Query;

use common\models\ExportQueue;

class SessionScheduleExporter extends BaseDataExporter
{
	protected $item_ids;
	
	protected $total = 0;
	protected $count = 0;
	
	
	public function setupQueue()
	{	
		$this->item_ids = json_decode($this->queueItem->item_ids, true);
		$this->total = count($this->item_ids);
		$this->count = 0;
	}
	
	public function getTotalQueueCount()
	{
		return $this->total;
	}
	
	
	public function next()
	{
		// keep pulling a new item until we get one that actually has data to return.
		// If we run out of items, then just return null
		
		while(count($this->item_ids) > 0)
		{
			$this->count += 1;
			$participant_id = array_shift($this->item_ids);
			$query = (new Query())->select('participant_test_session.id, session_date, start_date, type, session_identifier, study_section, day, week, session, completed, participant.participant_id')
			->from('participant_test_session')
			->where(['participant_test_session.participant' => $participant_id])
			->leftJoin('participant', 'participant.id = participant_test_session.participant')
			->orderBy(" session_date asc");
	
			$sessions = $query->all();
			
			if(count($sessions) != 0)
			{
				$participant_app_id = $sessions[0]["participant_id"];
				$formatter = $this->getFormatter("session_schedule", $this->queueItem->export_type, $sessions);
				return $formatter;
			}
		}
		return null;
	}
	
	
	// If we have more than one participant to export, then we want the filename to be
	// Site ID_session_schedule_dd-mm-yy
	// But if we only have one participant, then we want
	// Participant ID_session_schedule_dd-mm-yy
	
	public function exportFilename()
	{
		$prefix = "";
		if($this->total > 1)
		{
			$prefix = $this->getStudyName();
		}
		else
		{
			$item_ids = json_decode($this->queueItem->item_ids, true);
			$prefix = (new Query())->select('participant_id')->from('participant')->where(['id' => $item_ids[0]])->scalar();
		}
		
		return $prefix . "_session_schedule_" . date('d-m-y');
	}
	
}

?>