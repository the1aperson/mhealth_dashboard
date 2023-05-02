<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "alert".
 *
 * @property int $id
 * @property int $alert_level
 * @property int $participant
 * @property string $message
 * @property int $follow_up_by
 * @property string $follow_up_message
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Participant $participant0
 * @property User $followUpBy
 * @property UserAlert[] $userAlerts
 */
class Alert extends \yii\db\ActiveRecord
{
	const LEVEL_MESSAGE = 10;
	const LEVEL_WARNING = 20;
	const LEVEL_DANGER = 30;
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alert';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alert_level', 'participant', 'follow_up_by', 'created_at', 'updated_at', 'expires', 'follow_up_date'], 'integer'],
            ['alert_level', 'in', 'range' => [Alert::LEVEL_MESSAGE, Alert::LEVEL_WARNING, Alert::LEVEL_DANGER]],
            [['message', 'follow_up_message', 'tag'], 'string'],
            ['requires_follow_up', 'boolean'],
            [['participant'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['participant' => 'id']],
            [['follow_up_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['follow_up_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'alert_level' => 'Alert Level',
            'participant' => 'Participant',
            'message' => 'Message',
            'requires_follow_up' => 'Requires Follow Up',
            'follow_up_by' => 'Follow Up By',
            'follow_up_message' => 'Follow Up Message',
            'expires' => 'Expires',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'tag' => 'Tag',
            'follow_up_date' => 'Follow Up Date',
        ];
    }
    
    public function alertLevelString()
    {
	   	return Alert::alertLevelAttributeName($this->alert_level);
    }
    
    public static function alertLevelAttributeName($alert_level)
    {
	    switch($alert_level)
	    {
		    case Alert::LEVEL_MESSAGE: 
			    return "message";
			case Alert::LEVEL_WARNING:
				return "warning";
			case Alert::LEVEL_DANGER:
				return "danger";
			default:
				return "";
	    }
    }

	public static function alertLevelLabel($alert_level)
	{
		switch($alert_level)
	    {
		    case Alert::LEVEL_MESSAGE: 
			    return "Success";
			case Alert::LEVEL_WARNING:
				return "At Risk";
			case Alert::LEVEL_DANGER:
				return "Issues & Errors";
			default:
				return "";
	    }
	}
	
	public static function getAlertLevels($danger_first = false)
	{
		$levels = [Alert::LEVEL_MESSAGE, Alert::LEVEL_WARNING, Alert::LEVEL_DANGER];
		if($danger_first)
		{
			$levels = array_reverse($levels);
		}
		
		return $levels;
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowUpBy()
    {
        return $this->hasOne(User::className(), ['id' => 'follow_up_by']);
    }

    
    public static function createAlert($participant_id, $alert_level, $message, $expires = null, $tag = null, $require_follow_up = false)
    {
	    if(stristr($message, "{{participant}}"))
	    {
		    $participant_app_id = (new Query())->select('participant_id')->from('participant')->where(['id' => $participant_id])->scalar();
		    $url = "/participant/view?id=" . $participant_id;
		    $link_placeholder = "[$participant_app_id]($url)";
		    $message = str_replace("{{participant}}", $link_placeholder, $message);
	    }
	    
	    $alert = new Alert();
	    $alert->participant = $participant_id;
	    $alert->message = $message;
	    $alert->alert_level = $alert_level;
	    $alert->expires = $expires;
	    $alert->requires_follow_up = $require_follow_up;
	    $alert->tag = $tag;
	    
	    return $alert->save();
    }
    
    public static function clearAlert($alert_id, $user_id)
    {
	    $alert = Alert::find()->where(['id' => $alert_id])->one();
	    if($alert == null || ($alert->requires_follow_up && $alert->follow_up_by == null))
	    {
		    return false;
	    }
	    
	    if(HiddenAlert::hideAlert($alert_id, $user_id))
	    {
		    return true;
	    }
	    else
	    {
		    return false;
	    }
    }
    
       
    // $options array:
    // $study_id (required), $user_id ($required), $showHidden = false, $type = null, $participant_id = null, $limit = null, $offset = null
    
    public static function getAlerts($options)
    {
	    $alertsQuery = Alert::find();

	    $participantQuery = (new Query())->select('id')
		->from('participant')
		->where(['study_id' => $options["study_id"]]);
				
		if(isset($options["participant_id"]))
		{
			$participantQuery->andFilterWhere(['id' => $options["participant_id"]]);
		}
		
		$alertsQuery->where(['in', 'participant', $participantQuery]);
		
		// if $showHidden is false, we have to get the list of alerts that this user has marked hidden, and select only alerts
		// not in that list
		if(!isset($options["showHidden"]) || $options["showHidden"] == false)
		{
			$hiddenQuery = (new yii\db\Query())->select('alert_id')->from('hidden_alert')->where(['user_id' => $options["user_id"] ]);
			$alertsQuery->andWhere(['not in', 'id', $hiddenQuery]);
		}
		
		if(isset($options["type"]))
		{
			$alertsQuery->andFilterWhere(['alert_level' => $options["type"]]);
		}
		
		if(isset($options["id"]))
		{
			$alertsQuery->andFilterWhere(['id' => $options["id"]]);
		}
		
		if(isset($options["limit"]))
		{
			$alertsQuery->limit($options["limit"]);
		}
		
		if(isset($options["offset"]))
		{
			$alertsQuery->offset($options["offset"]);
		}
		
		$alertsQuery->orderBy('id desc');
		
		return $alertsQuery->all();
    }
    
    /*
	    getTotalCount()
	    returns the total count of alerts for the given level, study, and user.
	*/
    
    public static function getTotalCount($user_id, $study_id, $alert_level)
    {
	    $allCount = Yii::$app->db->createCommand('SELECT COUNT(alert.id) FROM alert WHERE alert_level = :alert_level AND alert.participant IN (SELECT participant.id FROM participant WHERE participant.study_id = :study_id)', [':alert_level' => $alert_level, ':study_id' => $study_id])->queryScalar();
	    
	    return $allCount;
    }
    
    /*
	    getNewCount()
	    returns the count of all new (not hidden) alerts for the given level, study, and user.
	*/
    
    public static function getNewCount($user_id, $study_id, $alert_level)
    {
	    $newCount = Yii::$app->db->createCommand('SELECT COUNT(alert.id) FROM alert WHERE alert_level = :alert_level AND alert.participant IN (SELECT participant.id FROM participant WHERE participant.study_id = :study_id) AND alert.id NOT IN (SELECT hidden_alert.alert_id FROM hidden_alert WHERE hidden_alert.user_id = :user_id)', [':alert_level' => $alert_level, ':study_id' => $study_id, ':user_id' => $user_id])->queryScalar();
	    return $newCount;
    }
    
    /* 
	    getRequireFollowUpCount()
	    returns the count of alerts that require follow-up for the given level, study, and user.
	*/
    public static function getRequireFollowUpCount($user_id, $study_id, $alert_level)
    {
	    $requireFollowUpCount = Yii::$app->db->createCommand('SELECT COUNT(alert.id) FROM alert WHERE alert_level = :alert_level AND alert.participant IN (SELECT participant.id FROM participant WHERE participant.study_id = :study_id) AND alert.requires_follow_up = 1 AND alert.follow_up_by IS NULL', [':alert_level' => $alert_level, ':study_id' => $study_id,])->queryScalar();
	    
	    return $requireFollowUpCount;
    }
	
	/*
	 	getFollowedUpCount()
	  	returns the count of red alerts that have been followed up
	 */
	public static function getFollowedUpCount($user_id, $study_id, $alert_level)
    {
	    $followedUpCount = Yii::$app->db->createCommand('SELECT COUNT(alert.id) FROM alert WHERE alert_level = :alert_level AND alert.participant IN (SELECT participant.id FROM participant WHERE participant.study_id = :study_id) AND alert.follow_up_by IS NOT NULL', [':alert_level' => $alert_level, ':study_id' => $study_id,])->queryScalar();
	    
	    return $followedUpCount;
    }
    
    // Returns info about the number of alerts, new and total.
    // Each array element contains alert_label, alert_name, alert_level, new, all
    
    
    public static function getAlertCounts($user_id, $study_id, $levels = null)
    {
	    $alertCounts = [];
	    $levels = $levels ?? [Alert::LEVEL_DANGER, Alert::LEVEL_WARNING, Alert::LEVEL_MESSAGE];
	    foreach($levels as $level)
	    {
	    	$allCount = self::getTotalCount($user_id, $study_id, $level);
	    	$newCount = self::getNewCount($user_id, $study_id, $level);
	    	
			
			$alertInfo = ["all" => $allCount, "new" => $newCount, "alert_level"=> $level, "alert_name" => Alert::alertLevelAttributeName($level), "alert_label" => Alert::alertLevelLabel($level)];
			$alertCounts []= $alertInfo;
		}
		
		return $alertCounts;
    }
        
    // countAlertsByTag() and getAlertByTag()
    // Searches to see if the participant has an alert with the given tag, that has an expiration date greater than $expires.
    // To check against only alerts that still require follow-up (requires_follow_up = 1, but follow_up_by is still null), set $requires_follow_up to true
    
    public static function countAlertsByTag($participant_id, $tag, $expires = 0, $requires_follow_up = false)
    {
	    $query = Alert::find()->where(['participant' => $participant_id, 'tag' => $tag])->andWhere(['or', ['>=', 'expires', $expires], ['expires' => null]])->orderBy('id desc');
	    
	    if($requires_follow_up)
	    {
		    $query->andWhere(['requires_follow_up' => 1])
		    ->andWhere(['follow_up_by' => null]);
	    }
	    return $query->count();
    }
	
	public static function countAlertsByDay($participant_id)
	{
		$current_day = strtotime('midnight');
		$query = Alert::find()->where(['participant' => $participant_id])->andWhere(['>', 'created_at', $current_day]);

		return $query->count();
	}
	
    public static function getAlertByTag($participant_id, $tag, $expires = 0, $requires_follow_up = false)
    {	    
	    $query = Alert::find()->where(['participant' => $participant_id, 'tag' => $tag])->andWhere(['or', ['>=', 'expires', $expires], ['expires' => null]])->orderBy('id desc');
	    
	    if($requires_follow_up)
	    {
		    $query->andWhere(['requires_follow_up' => 1])
		    ->andWhere(['follow_up_by' => null]);
	    }
	    return $query->one();
    }
    
    public function getParsedMessage()
    {
	    
	    if(preg_match("/\[(?<pid>.+?)\]\((?<url>.+?)\)/", $this->message, $matches))
	    {
		    $message = $this->message;
		    $pid = $matches["pid"];
		    $url = $matches["url"];
		    $replacement = '<a href="' . \yii\helpers\Url::to($url, true) . '">Participant ' . $pid . '</a>';
		    
		    $message = str_replace("[$pid]($url)", $replacement, $message);
		    return $message;
		    
	    }
	    else
	    {
		    return $this->message;
	    }
    }
    
    public function markFollowup($user_id, $message, $date)
    {
	    $this->follow_up_by = $user_id;
	    $this->follow_up_message = $message;
	    $this->follow_up_date = $date;
	    return $this->save();
    }

	public function markNoFollowup($user_id, $message, $date)
	{
		if($this->requires_follow_up == true)
		{
			$this->requires_follow_up = false;
			$this->follow_up_by = $user_id;
			$this->follow_up_message = $message;
			$this->follow_up_date = $date;
		} 
		else
		{
			$this->requires_follow_up = true;
			$this->follow_up_by = null;
			$this->follow_up_message = null;
			$this->follow_up_date = null;
		}
		return $this->save();
	}

}
