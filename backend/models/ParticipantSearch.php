<?php
	
namespace backend\models;

use yii;
use yii\base\Model;

use yii\data\ActiveDataProvider;

use yii\db\Query;

use common\models\Participant;
use common\models\ParticipantDevice;

class ParticipantSearch extends Model
{

	public $participant_id;
	
	public $device_type;
	public $os_type;
	public $os_version;
	public $app_version;
	
	public $enabled;
	public $device_active;
	public $status;
    public $device_created;
	
	public $study_id;
	
	public $adherence;
	public $last_seen;
	public $flagged;
	public $installed;
	
	public $study_phase;
    public $last_session_date;
    public $next_session_date;
	
	public $thoughts_of_death;
	
	public $enrolled_start;
	public $enrolled_end;
	public $enrolled_range;

	public function rules()
	{
		return [
			[['participant_id', 'device_type', 'os_type', 'os_version', 'app_version', 'device_active', 'device_created', 'study_id', 'adherence', 'last_seen', 'flagged', 'installed', 'study_phase', 'last_session_date', 'next_session_date', 'status', 'thoughts_of_death', 'enrolled_start', 'enrolled_end', 'enrolled_range'], 'safe'],

		];
	}
	
	public function formName()
	{
		return '';
	}
	
	public function search($params, $user_id)
	{
		$query = $this->getQuery($params, $user_id);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => 10,
			],
		]);
		
		$count = (new Query())->from(['c' => $query])->count();
		$dataProvider->totalCount = $count;
		return $dataProvider;
	}
	
	public function attributeLabels()
	{
		return [
			'last_session_id' => 'Last Session',
			'os_type' => 'OS',
		];	
	}
	public function getQuery($params, $user_id)
	{
		$this->load($params);
		$query = new Query();
		$columns = [
			"participant.id",
			"(CASE WHEN participant_user_flag.participant_id = participant.id THEN 1 ELSE 0 END) AS flagged",
			"(CASE WHEN participant_device.active = 1 THEN 'Yes' ELSE 'No' END) AS device_active",
			"(CASE WHEN participant_device.participant = participant.id THEN 'Yes' ELSE 'No' END) AS installed",
			"participant.participant_id",
			"participant_device.os_type",
			"participant_device.os_version",
			"participant_device.app_version",
			"participant_device.created_at AS device_created",
			"CONCAT(last_session.week, '-', last_session.day, '-', last_session.session) AS last_session_id",
			"last_session.session_date AS last_session_date",
			"CONCAT(next_session.week, '-', next_session.day, '-', next_session.session) AS next_session_id",
			"next_session.session_date AS next_session_date",
			"IFNULL(pdc.pdc_install_count,0) as install_count",
			"participant_adherence.adherence AS adherence",
			"participant_last_seen.last_seen AS last_seen",
			"(CASE WHEN participant_thoughts_of_death.value IS NOT NULL THEN participant_thoughts_of_death.value ELSE 0 END) AS thoughts_of_death",
			"(CASE WHEN participant.enabled = 0 THEN 'Dropped' WHEN IFNULL(pdc.pdc_install_count,0) = 0 THEN 'Inactive' WHEN IFNULL(pcs.pcs_completed, 0) > 0 THEN 'Completed' ELSE 'Active' END) AS status",
		];
		
		$query->select($columns)->from(Participant::tableName());
		$query->where('hidden = 0');
		
		$deviceSubQuery = new Query();
		$deviceSubQuery->select("participant_device.id")
		->from('participant_device')
		->where("`participant_device`.`participant` = `participant`.`id`")
		// ->andWhere(["participant_device.active" => 1])
		->orderBy('updated_at DESC')
		->limit(1);
		
		
		$query->leftJoin('participant_device', ['=', 'participant_device.id', $deviceSubQuery]);
	
		if((isset($this->enrolled_start) && $this->enrolled_start != '') && (isset($this->enrolled_end) && $this->enrolled_end != ''))
		{
			$startTime = strtotime($this->enrolled_start);	
			$endTime = strtotime($this->enrolled_end);
			$enrolled_range = $startTime . '-' . $endTime;	
			$this->makeWhereStatement($query, 'participant_device.created_at', $enrolled_range);
			$query->andFilterWhere(["(CASE WHEN participant.enabled = 0 THEN 'Dropped' WHEN IFNULL(pdc.pdc_install_count,0) = 0 THEN 'Inactive' WHEN IFNULL(pcs.pcs_completed, 0) > 0 THEN 'Completed' ELSE 'Active' END)" => 'Active']);
		}			

        if(isset($this->device_created) && $this->device_created != '')
        {
            $timestamp = strtotime($this->device_created);	
            $range = $this->createDateSearchRange($timestamp);
            $this->makeWhereStatement($query, 'participant_device.created_at', $range);
        }

		$query->andFilterWhere(['participant_device.active' => $this->device_active]);
		$query->andFilterWhere(['participant.study_id' => $this->study_id]);
		$query->andFilterWhere(['participant_device.os_type' => $this->os_type]);
		$query->andFilterWhere(['like', 'participant.participant_id', $this->participant_id]);

		// flagged
		
		$query->leftJoin('participant_user_flag', 'participant_user_flag.participant_id = participant.id AND participant_user_flag.user_id = :user_id', [':user_id' => $user_id]);
		
		if(isset($this->flagged) && $this->flagged != '')
		{
			if($this->flagged == 1)
			{
				$query->andWhere('(CASE WHEN participant_user_flag.participant_id = participant.id THEN 1 ELSE 0 END) = 1');
			}
			else
			{
				$query->andWhere('(CASE WHEN participant_user_flag.participant_id = participant.id THEN 1 ELSE 0 END) = 0');
			}
		}
		
		
		// adherence

		$query->leftJoin("participant_adherence", "participant_adherence.participant = participant.id AND participant_adherence.test_type = 'all' AND participant_adherence.study_section = 'all'");
		
		if(isset($this->adherence))
		{
			$this->makeWhereStatement($query, 'adherence', $this->adherence);			
		}

		// last_seen
		
		$query->leftJoin("participant_last_seen", "participant_last_seen.participant = participant.id");
		
		if(isset($this->last_seen) && $this->last_seen != '')
		{
			// If last_seen is just a date string (it doesn't start with a "<" or ">" symbols),
			// then let's make it a date range for the entire day that it represents.
			
			$value = $this->last_seen;
			if($this->doesStringContainComparisonSymbol($this->last_seen) == false)
			{
		        $timestamp = strtotime($this->last_seen);	
		        $value = $this->createDateSearchRange($timestamp);				
			}
            $this->makeWhereStatement($query, 'participant_last_seen.last_seen', $value);
		}
		
		
		// installed
		
		if(isset($this->installed) && $this->installed != '')
		{
			if($this->installed == 1)
			{
				$query->andWhere('participant_device.id IS NOT NULL');
			}
			else
			{	
				$query->andWhere('participant_device.id IS NULL');
			}
		}
		
		// install count
		
		$installCountQuery = (new Query())->select("COUNT(pdc.id) as pdc_install_count, pdc.participant")->from("participant_device pdc")->groupBy('pdc.participant');
		$query->leftJoin(["pdc" => $installCountQuery], "pdc.participant = participant.id");
		
				
		// last session
		
		$lastSeshSubQuery = new Query();
		$lastSeshSubQuery->select("ptsj.id")
		->from('participant_test_session ptsj')
		->where("ptsj.participant = participant.id")
		->andWhere(['ptsj.completed' => 1])
		->andWhere(['<=', 'session_date', time()])
		->orderBy('ptsj.session_date DESC')
		->limit(1);
		
		$query->leftJoin('participant_test_session last_session', ['=', 'last_session.id', $lastSeshSubQuery]);
	
        if(isset($this->last_session_date) && $this->last_session_date != '')
        {
            $timestamp = strtotime($this->last_session_date);
            $range = $this->createDateSearchRange($timestamp);
            $this->makeWhereStatement($query, 'last_session.session_date', $range);
        }

		// next session
		
		$lastSeshSubQuery = new Query();
		$lastSeshSubQuery->select("ptsn.id")
		->from('participant_test_session ptsn')
		->where("ptsn.participant = participant.id")
		->andWhere(['>', 'ptsn.session_date', time()])
		->orderBy('ptsn.session_date ASC')
		->limit(1);
		
		$query->leftJoin('participant_test_session next_session', ['=', 'next_session.id', $lastSeshSubQuery]);		
		
        if(isset($this->next_session_date) && $this->next_session_date != '')
        {
            $timestamp = strtotime($this->next_session_date);
            $range = $this->createDateSearchRange($timestamp);
            $this->makeWhereStatement($query, 'next_session.session_date', $range);
        }
		
		// study_phase
		
		$study_schedule_cases = [];
		
		// We have to deduce what phase they're in based on the session_date of their first test. 
		
		$previous_end = 0;
		$now = time();
		foreach(Yii::$app->studyDefinitions->study_schedule as $schedule)
		{
			$start = $previous_end + $schedule->start;
			$end = $start + $schedule->length;
			
			$min = $start * 86400;
			$max = $end * 86400;
			$case = "WHEN ($now - first_session.session_date) >= $min AND ($now - first_session.session_date) < $max THEN '" . $schedule->name . "'";
			$study_schedule_cases []= $case;
			$previous_end = $end;
		}
		$case = "(CASE " . implode("\n", $study_schedule_cases) . "ELSE 'No Phase' END)";

		$lastSeshSubQuery = new Query();
		$lastSeshSubQuery->select("ptsf.id")
		->from('participant_test_session ptsf')
		->where("ptsf.participant = participant.id")
		->orderBy('ptsf.session_date ASC')
		->limit(1);
	
		$query->leftJoin('participant_test_session first_session', ['=', 'first_session.id', $lastSeshSubQuery]);
		$query->addSelect($case . "  AS study_phase");

		if(isset($this->study_phase) && $this->study_phase != '')
		{
			$query->andWhere(['=', $case, $this->study_phase]);
 		}
 		
 		// completed

 		$completedQuery = (new Query())->select("COUNT(pcs.id) as pcs_completed, pcs.participant")->from("participant_completed_study pcs")->groupBy('pcs.participant');
 		$query->leftJoin(["pcs" => $completedQuery], "pcs.participant = participant.id");
 		
 		
 		// status
 		// we have to duplicate the case because we can't reference aliased columns in where clauses
 		$query->andFilterWhere([	"(CASE WHEN participant.enabled = 0 THEN 'Dropped' WHEN IFNULL(pdc.pdc_install_count,0) = 0 THEN 'Inactive' WHEN IFNULL(pcs.pcs_completed, 0) > 0 THEN 'Completed' ELSE 'Active' END)" => $this->status]);
 				

		// OS Version
		
		
		// Basically, if os_version is "up_to_date" or "prevoius", we need to get the latest (or second-latest)
		// OS version for each OS type, and then filter the devices based on that combination (OS type AND OS version).
		// Because obviously, the latest version of each OS are going to have very different version numbers.
		
		if(isset($this->os_version) && ($this->os_version == "up_to_date" || $this->os_version == "previous"))
		{

			$pieces = null;
			$offset = $this->os_version == "previous" ? 1 : 0;

			if(!isset($this->os_type) || $this->os_type != "iOS")
			{
				$osVersion = $this->getParticipantDeviceBiggestValue("os_version", "Android", $offset);				
				$pieces = ['and', ['participant_device.os_type' => "Android"], ['participant_device.os_version' => $osVersion]];
			}
			
			if(!isset($this->os_type) || $this->os_type != "Android")
			{
				$osVersion = $this->getParticipantDeviceBiggestValue("os_version", "iOS", $offset);
				$iosPiece = ['and', ['participant_device.os_type' => "iOS"], ['participant_device.os_version' => $osVersion]];
				
				if($pieces != null)
				{
					$pieces = ['or', $pieces, $iosPiece];
				}
				else
				{
					$pieces = $iosPiece;
				}
			}
			
			$query->andWhere($pieces);
		}
		else	// Otherwise, let's just sort by it and see what happens.
		{
			$query->andFilterWhere(['participant_device.os_version' => $this->os_version]);	
		}
		
		
		// App Version
		
		if(isset($this->app_version) && ($this->app_version == "up_to_date" || $this->app_version == "previous"))
		{
			$pieces = null;
			$offset = $this->app_version == "previous" ? 1 : 0;
			
			if(!isset($this->os_type) || $this->os_type != "iOS")
			{
				$appVersion = $this->getParticipantDeviceBiggestValue("app_version", "Android", $offset);				
				$pieces = ['and', ['participant_device.os_type' => "Android"], ['participant_device.app_version' => $appVersion]];
			}
			
			if(!isset($this->os_type) || $this->os_type != "Android")
			{
				$appVersion = $this->getParticipantDeviceBiggestValue("app_version", "iOS", $offset);
				$iosPiece = ['and', ['participant_device.os_type' => "iOS"], ['participant_device.app_version' => $appVersion]];
				
				if($pieces != null)
				{
					$pieces = ['or', $pieces, $iosPiece];
				}
				else
				{
					$pieces = $iosPiece;
				}
			}
			
			$query->andWhere($pieces);
		}
		else
		{
			$query->andFilterWhere(['participant_device.app_version' => $this->app_version]);		
		}
		
		
		// thoughts of death
		
		$query->leftJoin("participant_thoughts_of_death", "participant_thoughts_of_death.participant = participant.id");
		
		if(isset($this->thoughts_of_death))
		{
			$this->makeWhereStatement($query, 'participant_thoughts_of_death.value', $this->thoughts_of_death);
		}
		
		
		
		// finally, just return the query
		
		return $query;
	}
	
	public function getFilterOptions($attribute, $include_attr_name = false)
	{
		$values = [];
		switch($attribute)
		{
			case 'flagged':
				return [0 => "Not Flagged", 1 => "Flagged"];
			case 'installed':
			case 'device_active':
			// case 'enabled':
				return [0 => "Not Enabled", 1 => "Enabled"];
			case 'os_type':
				return ['iOS' => 'iOS', 'Android' => 'Android'];
			case 'os_version':
			case 'app_version':
				$values = [
						"up_to_date" => "Up To Date",
						"previous" => "Previous Version"
					];
					
			break;
			case 'last_seen':
				$seven_days_ago = strtotime("midnight - 7 days");
				$values[">$seven_days_ago"] = "During Last 7 Days";
				$values["<=$seven_days_ago"] = "Over 7 Days Ago";
			break;
			case 'adherence':

				$values[">75"] = "75% or Greater";
				$values["60-74.9"] = "60% to 74.9%";
				$values["<60"] = "59.9% or Less";
				break;
			case 'study_phase':
				foreach(Yii::$app->studyDefinitions->study_schedule as $schedule)
				{
					$values[$schedule->name] = $schedule->name;
				}
				break;
			case 'thoughts_of_death':
				$v = [];
				$v[">=50"] = "50% or Greater";
				$v["<50"] = "49.9% or Less";
				return $v;
			case 'status':
				$values = ['Inactive' => 'Inactive', 'Active' => 'Active', 'Completed' => 'Completed', 'Dropped' => 'Dropped'];
				break;
		}
		
		if($include_attr_name)
		{
			$attributeName = $this->getAttributeLabel($attribute);
			foreach($values as $k => $v)
			{
				$values[$k] = $attributeName . ": " . $v;
			}
		}
		
		return $values;
	}

    public function createDateSearchRange($timestamp)
    {		
        // Rounded to midnight today
        $today = strtotime('today', $timestamp);

        // Rounded to midnight of next day
        $midnight = strtotime('tomorrow', $timestamp); 
           
        // Make our search range
        $range = $today . "-" . $midnight;
    
        return $range;
    }
	
	public function getFilterOptionName($attribute, $include_attr_name = false)
	{
		$options = $this->getFilterOptions($attribute, $include_attr_name);
		if(isset($options[$this->$attribute]))
		{
			return $options[$this->$attribute];
		}
		else
		{
			return $this->getAttributeLabel($attribute) . ": " . $this->$attribute;
		}
	}
	
	public function getFilters()
	{
		return [
			'adherence' => 'Adherence',
			'thoughts_of_death' => 'Thoughts of Death',
			'study_phase' => 'Study Phase',
			'last_seen' => 'Last Seen',
			'app_version' => 'App Version',
			'os_version' => 'OS Version',
			'flagged' => 'Flags',
			

		];
	}

	// public function getFilterChoice($attribute, $include_attr_name = false)
	// {
	// 	switch($attribute)
	// 	{
	// 		case 'os_version':
	// 		case 'app_version':
	// 			return "CHOOSE VERSION";
	// 		break;
	// 		case 'adherence':
	// 		case 'thoughts_of_death':
	// 			return "CHOOSE RANGE";
	// 		break;
	// 		case 'last_seen':
	// 			return "CHOOSE TIMEFRAME";
	// 		break;
	// 		case 'study_phase':
	// 			return "CHOOSE WEEK??";
	// 		break;
	// 		case 'flagged':
	// 			return " ";
	// 		break;
	// 	}
	// }

	public function getCount($params, $user_id)
	{
		$params["study_id"] = $this->study_id;
		$params["model-name"] = "participant-search";
		
		return Yii::$app->cache->getOrSet($params, function () use ($params, $user_id) {
			
			$model = new ParticipantSearch();
			$query = $model->getQuery($params, $user_id);
			return (new Query())->from(['c' => $query])->count();

		}, YII_DEBUG ? 60: 3600);
		
	}
	
	
	
	public static function buildQuery($studyId, $searchParams, $userId)
	{
		$form = new ParticipantSearch();
		$form->study_id = $studyId;
		return $form->getQuery($searchParams, $userId);
	}

	// makeWhereStatement parses a query value that may contain an operator.
	// We have several possibilities:
	// $value is something like ">70" or "<=60"
	// $value is a range, like "70-80"
	// $value is just a single value, like "33"

	private function makeWhereStatement($query, $columnName, $value)
	{	
		if($value == '')
		{
			return;
		}
		
		if($this->doesStringContainComparisonSymbol($value))
		{
			$length = strpos($value, "=") === 1 ? 2 : 1;
			$operator = substr($value, 0, $length);
			$value = substr($value, $length);
			return $query->andFilterWhere([$operator, $columnName, $value]);
		}
		
		if(strpos($value, "-") > 0)
		{
			$values = explode("-", $value);
			if(count($values) == 2)
			{	
				return $query->andFilterWhere(['>=', $columnName, $values[0]])->andFilterWhere(['<=', $columnName, $values[1]]);
			}
		}
		
		return $query->andFilterWhere(['=', $columnName, $value]);
	}
	
	// Wrapper for checking whether a string contains one of the comparison symbols
	// used in makeWhereStatement().
	
	private function doesStringContainComparisonSymbol($string)
	{
		if(strpos($string, ">") === 0 || strpos($string, "<") === 0)
		{
			return true;
		}
		
		return false;
	}
	
	
	// Finds the highest (or nth-highest if $offset > 0) value for $column
	// and $os_type.
	// ie: getParticipantDeviceBiggestValue("os_version", "iOS", 1) gets the second highest OS version
	
	private function getParticipantDeviceBiggestValue($column, $os_type, $offset = 0)
	{
		$query = new Query();
		$query->select($column)->distinct()->from('participant_device');
		$query->where(['os_type' => $os_type]);
		$query->orderBy($column . ' desc')->limit(1);
		$query->offset($offset);
		return $query->scalar();
			
	}
	

	
	//! TODO: Figure out how to do counts for each of the filters, and their options from getFilterOptions()
	// We should cache that data if we can.
	
	
	
	
	
}
