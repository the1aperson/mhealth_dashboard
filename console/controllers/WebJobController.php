<?php

namespace console\controllers;

use yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use yii\db\Query;

use common\models\ExportQueue;

/**
 * This controller will contain actions to be run on a regular basis by a cron job/web job
 *
 */
class WebJobController extends \yii\console\Controller
{

	/*
		Retrieves an ExportQueue item, which contains a list of participant_test_session ids.
		The test_session_data paired with these ids is formatted and saved, and then 
		everything is compressed into a zip archive.	
	*/
	
	public function actionProcessExportQueue($id = null)
	{
		Yii::info("Starting export job. id = " . ($id == null? "null" : $id), 'export-queue');

		if($id != null)
		{
			$queueItem = ExportQueue::findOne($id);
		}
		else
		{
			$queueItem = ExportQueue::getNewlyEnqueuedItem();
		}
		
		if($queueItem == null || $queueItem->status != ExportQueue::STATUS_NEW )
		{
			Yii::info("No exports to process.", 'export-queue');
			return;
		}
		
		$exporter = new \common\components\ExportHandler();
		$exporter->processExport($queueItem);
	}
	
	
	public function actionUpdateAdherenceStats()
	{
		$category = "adherence-log";
		
		Yii::info("Starting update adherence job.", $category);
		
		$start = time();
		try
		{
			// First, update participants
			$idQuery = (new Query())->select('id')->from('participant');
			$limit = 100;
			$offset = 0;
			$idQuery->limit($limit);
			$idQuery->offset($offset);
			
			$count = 0;
			while($ids = $idQuery->all())
			{
				foreach($ids as $id)
				{	$id = $id["id"];
					Yii::$app->participantMetadataHandler->updateAdherence($id);
				}
				
				$offset += $limit;
				$idQuery->limit($limit);
				$idQuery->offset($offset);
				$count += count($ids);
			}
			Yii::info("Updated adherence for $count participants.", $category);
			
			// Next, update each study
			
			$studyQuery = (new Query())->select('id')->from('study');
			
			$limit = 100;
			$offset = 0;
			$studyQuery->limit($limit);
			$studyQuery->offset($offset);
			
			$count = 0;
			while($ids = $studyQuery->all())
			{
				foreach($ids as $id)
				{	$id = $id["id"];
					Yii::$app->studyMetadataHandler->updateStudyMetadata($id);
				}
				
				$offset += $limit;
				$studyQuery->limit($limit);
				$studyQuery->offset($offset);
				$count += count($ids);
			}
			Yii::info("Updated adherence for $count studies.", $category);
				
		}
		catch (\Exception $e)
		{
			Yii::error("Error while updating adherence:", $category);
			Yii::error($e, $category);
		}
		
		$end = time();
		
		$diff = $end - $start;
		
		Yii::info("Finished adherence job. Time elapsed: $diff seconds.", $category);
		Yii::info("===========================================", $category);
	}
	
}