<?php

namespace backend\components;

use yii;
use yii\db\Query;

use backend\models\ParticipantSearch;
use common\models\ExportQueue;
	
class ExportCreator extends yii\base\Component
{
	public $error_msg = null;
	
	// createExport()
	// Based on the $format, $scope, and $queryParams, this gathers all of the information
	// needed to properly create an ExportQueue item.
	// Returns the created ExportQueue item, or false.
	// If this method returns false, check the $error_msg property for more details.
	
	public function createExport($format, $scope, $queryParams)
	{		
		if($this->canUserExportScope($scope) == false)
		{
			$this->error_msg = "You do not have permission to export the selected option.";
			return false;
		}
		
		// $scope can be one of several values: all_tests, baseline, filters, study_health, session_schedule, wake_sleep_schedule
		// With the exception of study_health, all of the others could be for one or more participants.
		// all_tests, baseline, and filters are all dealing with exporting test data.
		// session_schedule and wake_sleep_schedule are for exporting the wake/sleep, and test schedules for one or more participants
		// study_health is for exporting overall study data (as seen on the site overview page)
		
		$participant_ids = $this->getRelevantParticipantIds($queryParams);
		
		$test_scopes = ["all_tests", "baseline", "filters"];
		$schedule_scopes = ["session_schedule", "wake_sleep_schedule"];
		
		$item_ids = [];
		$item_type = null;
	    $export_type = $format ?? Yii::$app->params['default_export_type'];
	    		
	    $exportQueue = false;
	    
		if(in_array($scope, $test_scopes))
		{
			$exportQueue = $this->createTestScopeExport($scope, $export_type, $participant_ids);
		}
		else if($scope == "session_schedule")
		{			
			$exportQueue = $this->createSessionScheduleExport($scope, $export_type, $participant_ids);
		}
		else if($scope == "wake_sleep_schedule")
		{
			$exportQueue = $this->createWakeSleepScheduleExport($scope, $export_type, $participant_ids);
		}
		else if($scope == "study_health")
		{
			$exportQueue = $this->createStudyHealthExport($scope, $export_type, $participant_ids);
		}
		else
		{
			$this->error_msg = "You have provided an invalid export scope.";
			return false;
		}
		
		if($exportQueue == false)
		{
			return false;
		}
		
		if(isset($queryParams['formatterOptions']))
		{
			$exportQueue->options = $queryParams['formatterOptions'];
		}
		
		$exportQueue->save();

		$this->startWebJob($exportQueue);
		return $exportQueue;
	}
	
	//! Scope-specific methods
	// Based on the value of $scope, createExport() will call one of these methods.
	
	
	// createTestScopeExport()
	// Given the $export_type and $participant_ids, finds the relevant test sessions from
	// participant_test_session, and sets their table ids as the item_ids for the ExportQueue item.
	
	private function createTestScopeExport($scope, $export_type, $participant_ids)
	{
		$test_session_query = (new Query())->select('id')->from('participant_test_session')->where(['participant' => $participant_ids])->andWhere('test_data_id IS NOT NULL')->orderBy('participant asc, session_date asc');
		
		if($scope == "baseline")
		{
			$section = Yii::$app->studyDefinitions->studySchedule()[0] ?? null;
			if($section != null)
			{
				$start = $section->abs_days_from_beginning;
				$end = $start + $section->length;
				
				$test_session_query->addSelect("((week * 7) + day) as dayCount");
				$test_session_query->having("dayCount >= $start AND dayCount <= $end");
			}
		}	
		
		$item_ids = $test_session_query->column();
		$item_type = "participant_test_session";
		
		return $this->createExportQueue($export_type, $item_type, $item_ids);
	}
	
	// createSessionScheduleExport()
	// Creates a list of participant ids, whos session_schedules need to be exported.
	
	private function createSessionScheduleExport($scope, $export_type, $participant_ids)
	{
		$participant_list_query = (new Query())->select("participant")->distinct()->from("participant_test_session")->where(["participant" => $participant_ids]);
		$item_ids = $participant_list_query->column();
		$item_type = "session_schedule";
		return $this->createExportQueue($export_type, $item_type, $item_ids);
	}
	
	// createWakeSleepScheduleExport()
	// Creates a list of relevant schedule_data items, whose schedule_type is wake_sleep_schedule
	
	private function createWakeSleepScheduleExport($scope, $export_type, $participant_ids)
	{
		$schedule_query = (new Query())->select('id')->from('schedule_data')
		->where(['participant' => $participant_ids])
		->andWhere(['schedule_type' => $scope])->orderBy('participant asc, created_at asc');
		
		$item_ids = $schedule_query->column();
		$item_type = "schedule_data";
		
		return $this->createExportQueue($export_type, $item_type, $item_ids);
	}
	
	// createStudyHealthExport()
	// Creates an ExportQueue item that will export the study metadata for the currently selected Study.
	
	private function createStudyHealthExport($scope, $export_type, $participant_ids)
	{
		$item_ids = [Yii::$app->study->getStudyId()];
		$item_type = "study_health";
		return $this->createExportQueue($export_type, $item_type, $item_ids);
	}
	
	// createExportQueue()
	// This method puts all of the pieces together, and creates the ExportQueue item.
	
	private function createExportQueue($export_type, $item_type, $item_ids)
	{
		if(count($item_ids) == 0)
		{
			$this->error_msg = "Your export resulted in zero items being selected.<br /> Please review any selected filters and try again.";
			return false;
		}
		
		$exportQueue = new ExportQueue();
		$exportQueue->created_by = Yii::$app->user->getIdentity()->id;
		$exportQueue->item_ids = json_encode($item_ids);
		$exportQueue->status = ExportQueue::STATUS_NEW;
		$exportQueue->export_type = $export_type;
		$exportQueue->item_type = $item_type;
		$exportQueue->study_id = Yii::$app->study->getStudyId();
		$exportQueue->save();
		
		return $exportQueue;
	}
	
	// startWebJob()
	// Executes a command that starts a background task to process the given $exportQueue
	
	private function startWebJob($exportQueue)
	{
		$yiiExec = Yii::getAlias("@top/yii");
		$command = "php $yiiExec web-job/process-export-queue " . $exportQueue->id . " true " . " 2>&1 > /dev/null";
		exec("nohup $command > /dev/null 2>&1 & echo $!");
	}
	
	// Searches export_scope_permissions for permissions that allow the given scope,
	// and sees if the user has at least one of them.
	
	private function canUserExportScope($scope)
	{
		foreach(Yii::$app->params['export_scope_permissions'] as $permission => $scopes)
		{
			if(in_array($scope, $scopes) && Yii::$app->user->can($permission))
			{
				return true;
			}
		}
		
		return false;
	}
	
	private function getRelevantParticipantIds($queryParams)
    {	    	    
		// if we've been passed a participant_id, this is pointing to their database row id, not their
		// login id.
		
			    
	    if(isset($queryParams["participant_id"]))
	    {
		    $ids = [$queryParams["participant_id"]];
		    return $ids;
	    }	
	    else
		{				
			// if $scope is "filters", then we need to take the query params and pass them to
			// the ParticipantSearch query. We should get rid of our other parameters first,
			// just to make sure nothing conflicts.
			
			$searchParams = [];
			if($queryParams["scope"] == "filters")	
			{
				$searchParams = Yii::$app->request->queryParams['filterOptions'] ?? [];
			}
						
			$participant_ids_query = ParticipantSearch::buildQuery(Yii::$app->study->getStudyId(), $searchParams, Yii::$app->user->getId());
			$participant_ids_query->select('participant.id');
			return $participant_ids_query->column();
		}
    }
}
?>