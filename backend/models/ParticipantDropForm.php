<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\Participant;
use common\models\ParticipantDevice;

use common\validators\ModifiedValidator;

class ParticipantDropForm extends Model
{
    public $participant_id;
    public $updated_at;
	public $password;
	public function init()
	{
		parent::init();
		$this->updated_at = ModifiedValidator::getTimestamp();
	}
    /**
     * {@inheritdoc}
     */
    public function rules()
    {	    
        return [

			['participant_id', 'required'],
			['password', 'required'],

			['password', function(){
				if(Yii::$app->user->getIdentity()->validatePassword($this->password) == false)
				{
					$this->addError('password', 'Password is incorrect.');
				}
			}],
			['participant_id', 'exist', 'targetClass' => Participant::className(), 'targetAttribute' => ['participant_id' => 'id']],
            ['updated_at', ModifiedValidator::className(), 'targetClass' => Participant::className(), 'targetAttribute' => 'updated_at', 
            	'filter' => function($query){
	            	$query->andWhere(['id' => $this->participant_id]);
            	},
            	'message' => 'This participant has been modified since the last time you loaded this page.'
            ],
        ];
    }
    
        
    public function drop()
    {
	    $participant = Participant::findOne(['id' => $this->participant_id]);
		$participant->dropFromStudy();
    }
    
    public function hide()
    {
	    $participant = Participant::findOne(['id' => $this->participant_id]);
	    $participant->markAsHidden();
    }
    
	    
}
