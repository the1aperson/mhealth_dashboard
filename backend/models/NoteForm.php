<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\Participant;
use common\models\ParticipantNote;

use common\validators\ModifiedValidator;

/**
 * Note form
 */
class NoteForm extends Model
{
    public $participant_id;
    public $message;
    public $updated_at;

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
            ['message', 'required'],
            ['message', 'string'],            
            ['updated_at', ModifiedValidator::className(), 'targetClass' => ParticipantNote::className(), 'targetAttribute' => 'updated_at', 
            	'filter' => function($query){
	            	$query->andWhere(['participant' => $this->participant_id]);
            	},
            	'message' => 'This participant has been modified since last viewing.'
            ],
        ];
    }
    
    public function afterValidate()
    {
	    $this->updated_at = ModifiedValidator::getTimestamp();
    }

	public function addNote($user_id)
	{
		$note = new ParticipantNote();
		$note->participant = $this->participant_id;
		$note->created_by = $user_id;
		$note->note = $this->message;
		
		$note->save();
		return $note;
	}
    
}
