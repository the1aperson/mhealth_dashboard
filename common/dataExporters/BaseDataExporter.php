<?php

namespace common\dataExporters;

use Yii;
use yii\db\Query;
use yii\helpers\Inflector;

use common\models\ExportQueue;



class BaseDataExporter extends \yii\base\Component
{
	public $queueItem = null;
	
	public $verbose = true;
	public $formatterOptions = null;
	
	public function init()
	{
		$this->setupQueue();
	}
	
	// setupQueue()
	// given $this->queueItem, performs any setup to begin retrieving items.
	
	public function setupQueue()
	{
		// This would, for instance, setup a query item, limits, offsets
	}
	
	// getQueueCount()
	// returns the total number of items expected to be processed.
	public function getTotalQueueCount()
	{
		return 0;
	}
	
	// next()
	// Retrieves the next item from the ExportQueue, creates and returns a formatter for it.
	
	public function next()
	{
		return null;
	}
	
	// exportFilename()
	// Returns a filename that will be used to name the whole set of exported files.
	// Override this method in subclasses if you want a different name format.
	
	public function exportFilename()
	{
		return $this->queueItem->item_type . "_" . date('d-m-y');
	}
	
	// getFormatter()
	// attempts to create a formatter given the $data_type and $export_type, and sets the given $data on it.
	
	public function getFormatter($data_type, $export_type, $data)
	{
		if(isset(Yii::$app->params["data_formatters"][$data_type]))
		{
			$className = Yii::$app->params["data_formatters"][$data_type];
			Yii::info("used config classname $className", 'export-queue');
		}
		else
		{
			// note that $dataType and $data_type are two different variables!
			// $dataType reflects the camelCased version of $data_type.
			
			$dataType = Inflector::id2camel($data_type, "_");
			$className = "common\\dataFormatters\\" . $dataType . "Formatter";	
		}
		
		$export_start_date = $this->queueItem->created_at;	

		$options = ['class' => $className, 'data' => $data, 'data_type' => $data_type, 'export_type' => $export_type, "export_start_date" => $export_start_date, "options" => $this->formatterOptions ?? []];
		$formatter = Yii::createObject($options);
		return $formatter;
	}
	

	// getStudyName()
	// Retrieves the name of the study associated with the exporter's queueItem.
	
	protected function getStudyName()
	{
		if(isset($this->queueItem->study_id))
		{
			return (new Query())->select("name")->from('study')->where(['id' => $this->queueItem->study_id])->scalar();
		}
		
		return null;
	}
}

?>