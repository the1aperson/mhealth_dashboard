<?php

namespace common\dataExporters;

use Yii;
use yii\db\Query;

use common\models\ExportQueue;

class ParticipantTestSessionExporter extends BaseDataExporter
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
		$testSessionIds = json_decode($this->queueItem->item_ids, true);
		
		// First, let's get a list of all of the participants that we're querying.
		
		$testSessionSubQuery = (new Query())->select('participant_test_session.participant')->distinct()->from('participant_test_session')
		->where(['participant_test_session.id' => $testSessionIds]);
		$participantIdQuery = (new Query())->select('participant.participant_id')->distinct()->from('participant')
		->where(['participant.id' => $testSessionSubQuery]);
		
		$this->participant_app_ids = $participantIdQuery->column();
		
		
		
		// Then, setup the query that we'll use to retrieve the data later.		
		$this->limit = 100;
		$this->offset = 0;
		$query = (new Query())->select('test_data_id, type, participant.participant_id, session_date, pd.os_type, pd.os_version, pd.app_version, participant_test_session.participant AS participant_db_id')
		->from('participant_test_session')
		->where(['participant_test_session.id' => $testSessionIds])
		->leftJoin('participant', 'participant.id = participant_test_session.participant')
		->leftJoin('test_session_data', 'test_session_data.id = participant_test_session.test_data_id')
		->leftJoin('participant_device pd', 'pd.id = test_session_data.device')
		->orderBy("participant_test_session.participant asc, session_date asc");
				
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
			$test_data_id = $row["test_data_id"];
			$type =  $row["type"];
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
	
	// If we're exporting more than one participant's data, then the filename is
	// Study Name_assessment_data_dd-mm-yy
	// If we're only exporting one participant's data, then the filename is
	// Participant ID_assessment_data_dd-mm-yy
	
	public function exportFilename()
	{
		$prefix = "";
		if(count($this->participant_app_ids) > 1 || ($this->formatterOptions['combineParticipants'] ?? false))
		{
			$prefix = $this->getStudyName();
		}
		else
		{
			$prefix = $this->participant_app_ids[0];
		}
		
		return $prefix . "_assessment_data_" . date('d-m-y');
	}
	
}

?>