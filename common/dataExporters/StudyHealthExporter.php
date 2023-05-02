<?php

namespace common\dataExporters;

use Yii;
use yii\db\Query;

use common\models\ExportQueue;

class StudyHealthExporter extends BaseDataExporter
{

	protected $query;
	protected $limit = 100;
	protected $offset = 0;
	protected $total = 0;
	protected $count = 0;
	
	protected $rows = [];
	
	
	public function setupQueue()
	{	

		$item_ids = json_decode($this->queueItem->item_ids, true);
				
		$this->limit = 100;
		$this->offset = 0;
		$query = (new Query())->select('*')
		->from('study')
		->where(['id' => $item_ids])
		->orderBy('id asc');
		
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
			$type = "study_metadata";
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
	
	public function exportFilename()
	{
		$study_name = $this->getStudyName();
		return $study_name . "_study_health_" . date('d-m-y');
	}
	
}

?>