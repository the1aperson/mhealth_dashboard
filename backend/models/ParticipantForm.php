<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\Participant;
use common\models\Study;
use common\models\StudyUserAuth;
/**
 * Participant form
 */
class ParticipantForm extends Model
{
    public $participant_id;
    public $study;
    public $password;
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
	    $studyIds = array_keys($this->getStudies());
	    
	    $participant_id_rules = Yii::$app->studyDefinitions->participant_id_rules;
	    $participant_password_rules = Yii::$app->studyDefinitions->participant_password_rules;
	    
        return [

			['participant_id', 'required'],
            ['participant_id', 'string', 'min' => $participant_id_rules["min"], 'max' => $participant_id_rules["max"]],
            ['participant_id', 'unique', 'targetClass' => '\common\models\Participant', 'message' => 'This Participant ID has already been taken.'],
            ['participant_id', 'match', 'pattern' => '/^\d+$/'],
            
            ['study', 'required'],
            ['study', 'in', 'range' => $studyIds],
            
            ['password', 'required'],
            ['password', 'string', 'min' => $participant_password_rules["min"], 'max' => $participant_password_rules["max"]],
            
            
        ];
    }


    public function createParticipant()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $participant = new Participant();
        $participant->participant_id = $this->participant_id;
        $participant->setPassword($this->password);
        $participant->enabled = 1;
        $participant->study_id = $this->study;
        if($participant->save())
        {
	        return $participant;
        }
        
        return null;
    }
    
    public function getStudies()
    {
	    $request = yii::$app->getRequest();
		
		$studies = [];
		if($request->getIsConsoleRequest())
		{
			$studies = Study::find()->all();
		}
		else
		{
		    $studies = StudyUserAuth::getAvailableStudiesForUserAndAuth(Yii::$app->user->getId(), 'createParticipants');
		}		

	    $studyArray = [];
	    
	    foreach($studies as $study)
	    {
		    $studyArray[$study->id] = $study->name;
	    }
	    
	    return $studyArray;
    }
    
}
