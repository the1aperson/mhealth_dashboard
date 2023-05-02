<?php

namespace common\models;

use Yii;
use yii\db\Query;

use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "participant_test_session".
 *
 * @property int $id
 * @property int $participant
 * @property int $session_date
 * @property int $start_date
 * @property string $type
 * @property string $session_identifier
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Participant $participant0
 */
class ParticipantTestSession extends AuditableModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participant_test_session';
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
	        [['session_date', 'start_date'],'filter', 'filter' => 'intval'],
            [['participant', 'session_date', 'start_date', 'created_at', 'updated_at', 'test_data_id', 'week', 'day', 'session', 'study_section'], 'integer'],
            [['type', 'session_identifier'], 'string', 'max' => 255],
            [['completed'], 'boolean'],
            [['participant'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['participant' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'participant' => 'Participant',
            'session_date' => 'Session Date',
            'start_date' => 'Start Date',
            'type' => 'Type',
            'session_identifier' => 'Session Identifier',
            'test_data_id' => 'Test Data ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'completed' => 'Completed',
            'week' => 'Week',
            'day' => 'Day',
            'session' => 'Session',
            'study_section' => 'Study Section',
        ];
    }
    
    public static function findTestSession($participant_id, $session_id, $type, $createNew = true)
    {
	    $testSession = ParticipantTestSession::find()->where(['participant' => $participant_id, 'session_identifier' => $session_id, 'type' => $type])->one();
	    
	    if($testSession == null && $createNew)
	    {
		    $testSession = new ParticipantTestSession();
		    $testSession->participant = $participant_id;
		    $testSession->session_identifier = $session_id;
		    $testSession->type = $type;
	    }
	    
	    return $testSession;
    }
    
    // This method tries to insert new rows in one big batch, and then updates existing rows 
    // without having to create an Active Record object for each one.
    // $sessions is an array of associative arrays, containing:
    // session_id, session_date, type
    // This method can't be used to set the start_date of a session.
    
    public static function setTestSessions($participant_id, $sessions)
    {
		$new_sessions = [];
	    
	    // We're selecting a concatenation of the session_id and type, which together should form a unique pair.
	    // In some instances, one test session may have multiple test types, so we can't just rely on the session_id.
	    
	    $existing_ids = (new \yii\db\Query())->select(["CONCAT(session_identifier, '-', type)"])->from(self::tableName())->where(['participant' => $participant_id])->column();
	    
	    foreach($sessions as $session)
	    {
		    // If we already have an entry for this session, let's update it.
		    // The only caveat to this is that we don't want to update sessions that we already have test data for (because then the apparent session_date might differ from the test data's actual
		    // session_date, among other things).
		    
		    if(in_array($session["session_id"] . "-" . $session["type"], $existing_ids))
		    {
			    Yii::$app->getDb()->createCommand("UPDATE " . self::tableName() . " SET session_date = :session_date, updated_at = :updated_at, week = :week, day = :day, session = :session, study_section = :study_section WHERE participant = :participant_id AND session_identifier = :session_id AND type = :type AND test_data_id is null",[
				    ":session_date" => $session["session_date"],
				    ":type" => $session["type"],
				    ":updated_at" => time(),
				    ":participant_id" => $participant_id,
				    ":session_id" => $session["session_id"],
				    ":week" => $session["week"],
				    ":day" => $session["day"],
				    ":session" => $session["session"],
				    ":study_section" => $session["study_section"],
			    ])->execute(); 
		    }
		    else
		    {
			    $new_sessions []= [$participant_id, $session["session_id"], $session["session_date"], $session["type"], $session["week"], $session["day"], $session["session"], $session["study_section"], time(), time()];
		    }
	    }
	    
	    if(count($new_sessions) > 0)
	    {
		    Yii::$app->getDb()->createCommand()->batchInsert(self::tableName(), ['participant','session_identifier', 'session_date', 'type', 'week', 'day', 'session', 'study_section', 'created_at', 'updated_at'],
		    $new_sessions)->execute(); 
	    }
	    
    }
    
    
    // Returns number of tests for a given participant that occurred before $time.
    // You can specify the type to look for, as well as whether to only count completed tests.
    
    public static function countTests($participant_id, $completed = false, $type = null, $time = null)
    {
	    if($time == null)
	    {
		    $time = time();
	    }
	    
	    $query = (new Query())->select('id')
		->from(ParticipantTestSession::tableName())
		->where(['participant' => $participant_id])
		->andWhere(['<=', 'session_date', $time]);
		
		if($type != null)
		{
			$query->andWhere(['type' => $type]);
		}
		if($completed)
		{
			$query->andWhere('completed = 1');
		}
		
		return $query->count();
    }
    
    // Counts tests that have expired, and have yet to be marked as completed.
    
    public static function countMissedTests($participant_id, $type = null, $otherParams = [])
    {
	    $time = time();
	    $expiration_time = Yii::$app->studyDefinitions->expiration_time;
	    
	    $query = (new Query())->select('id')
		->from(ParticipantTestSession::tableName())
		->where(['participant' => $participant_id])
		->andWhere("session_date + $expiration_time < $time")
	    ->andWhere(["or", ["completed" => null], ["completed" => 0]]);
	    
	    if($type != null)
		{
			$query->andWhere(['type' => $type]);
		}
		
		if(count($otherParams) > 0)
		{
			foreach($otherParams as $o)
			{
				$query->andWhere($o);
			}
		}
		
		return $query->count();
	    
    }
    
    // Counts total number of expired tests, regardless of whether they've been
    // completed or not.
    
    public static function countExpiredTests($participant_id, $type = null, $otherParams = [])
    {
	    $time = time();
	    $expiration_time = Yii::$app->studyDefinitions->expiration_time;
	    
	    // Since we want to capture both completed AND uncompleted tests, we need to make two checks.
		// If someone completed a test recently (within the past $expiration_time seconds), we still want to
		// include it in this count.
		// So, we need to see if either session_date + expiration_time has passed, OR
		// if completed = 1 and session_date has passed.
	    
	    $query = (new Query())->select('id')
		->from(ParticipantTestSession::tableName())
		->where(['participant' => $participant_id])
		->andWhere("(session_date < $time AND completed = 1) OR (session_date + $expiration_time < $time)");
	    
	    if($type != null)
		{
			$query->andWhere(['type' => $type]);
		}
		
		if(count($otherParams) > 0)
		{
			foreach($otherParams as $o)
			{
				$query->andWhere($o);
			}
		}
		return $query->count();
    }
       
	// Counts the number of completed tests.
	
    public static function countCompletedTests($participant_id, $type = null, $otherParams = [])
    {
	    $time = time();

	    $query = (new Query())->select('id')
		->from(ParticipantTestSession::tableName())
		->where(['participant' => $participant_id])
		->andWhere(["completed" => 1])
		->andWhere(["<", "session_date", $time]);
	    
	    if($type != null)
		{
			$query->andWhere(['type' => $type]);
		}
		
		if(count($otherParams) > 0)
		{
			foreach($otherParams as $o)
			{
				$query->andWhere($o);
			}
		}
		return $query->count();
    }
    
    public static function getFirstTest($participant_id)
    {
	    return ParticipantTestSession::find()->where(['participant' => $participant_id])->orderBy('session_date asc')->one();
    }
    
    public static function getFinalTest($participant_id)
    {
	    return ParticipantTestSession::find()->where(['participant' => $participant_id])->orderBy('session_date desc')->one();	    
    }
    
    
    public static function getLatestCompletedTest($participant_id)
    {
	    return ParticipantTestSession::find()->where(['participant' => $participant_id])->andWhere('completed = 1 AND session_date IS NOT NULL')->andWhere(['<=', 'session_date', time()])->orderBy('start_date desc')->one();	    
    }
    
    // returns the most recent test that the Participant would have taken, based on the session_date.
    
    public static function getLatestTest($participant_id)
    {
	    return ParticipantTestSession::find()->where(['participant' => $participant_id])->andWhere('session_date IS NOT NULL')->andWhere(['<=', 'session_date', time()])->orderBy('session_date desc')->one();
    }

}
