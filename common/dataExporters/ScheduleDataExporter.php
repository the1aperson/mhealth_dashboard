<?php

namespace common\dataExporters;

use Yii;
use yii\db\Query;

use common\models\ExportQueue;

class ScheduleDataExporter extends BaseDataExporter
{

	protected $query;
	protected $limit = 100;
	protected $offset = 0;
	protected $total = 0;
	protected $count = 0;
	
	protected $rows = [];
	private $participant_app_ids = [];
	
	public function setupQueue()
	{	

		$item_ids = json_decode($this->queueItem->item_ids, true);
		
		
		$scheduleDataSubQuery = (new Query())->select('schedule_data.participant')->distinct()->from('schedule_data')
		->where(['schedule_data.id' => $item_ids]);
		$participantIdQuery = (new Query())->select('participant.participant_id')->distinct()->from('participant')
		->where(['participant.id' => $scheduleDataSubQuery]);
		
		$this->participant_app_ids = $participantIdQuery->column();
		
		$this->limit = 100;
		$this->offset = 0;
		$query = (new Query())->select('schedule_data.id, schedule_data.blob_data, schedule_data.created_at, schedule_data.schedule_type, participant.participant_id')
		->from('schedule_data')
		->where(['schedule_data.id' => $item_ids])
		->leftJoin('participant', 'participant.id = schedule_data.participant')
		->orderBy("participant asc, created_at asc");
				
		$this->total = $query->count();
		
		$this->count = 0;
		
		$query->limit($this->limit);
		$query->offset($this->offset);
		
		$this->query = $query;
	}
	
	public function getTotalQueueCount()
	{
		return $this->total;
	}
	
	public function next()
	{
		$row = $this->nextQueryRow();
		if($row != null)
		{
			$type =  $row["schedule_type"];
			$participant_app_id = $row["participant_id"];
			$formatter = $this->getFormatter($type, $this->queueItem->export_type, $row);
			return $formatter;
		}
		return null;
	}
	
	protected function nextQueryRow()
	{
		if(count($this->rows) == 0)
		{
			$this->query->offset($this->offset);
			$this->rows = $this->query->all();
			$this->offset += $this->limit;
			
			if(count($this->rows) == 0)
			{
				return null;
			}
		}
		
		$row = array_shift($this->rows);
		$this->count += 1;
		return $row;
	}
	
	// If we have more than one participant to export, then we want the filename to be
	// Site ID_schedule_data_dd-mm-yy
	// But if we only have one participant, then we want
	// Participant ID_schedule_data_dd-mm-yy
	
	public function exportFilename()
	{
		$prefix = "";
		if(count($this->participant_app_ids) > 1)
		{
			$prefix = $this->getStudyName();
		}
		else
		{
			$prefix = $this->participant_app_ids[0];
		}
		
		return $prefix . "_" . $this->queueItem->item_type . "_" . date('d-m-y');
	}
	
}

?>