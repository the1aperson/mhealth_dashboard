<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\Participant;
use common\models\ParticipantDevice;

use common\validators\ModifiedValidator;

class DeviceToggleForm extends Model
{
    public $participant_id;
    public $device_id;
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
            ['device_id', 'required'],
			['device_id', 'exist', 'targetClass' => ParticipantDevice::className(), 'targetAttribute' => ['device_id' => 'id', 'participant_id' => 'participant']],
            ['updated_at', ModifiedValidator::className(), 'targetClass' => ParticipantDevice::className(), 'targetAttribute' => 'updated_at', 
            	'filter' => function($query){
	            	$query->andWhere(['participant' => $this->participant_id]);
            	},
            	'message' => 'This participant has been modified since the last time you loaded this page.'
            ],
        ];
    }
    
    public function afterValidate()
    {
	    $this->updated_at = ModifiedValidator::getTimestamp();
    }

	public function disable()
	{
		$device = ParticipantDevice::find()->where(['id' => $this->device_id, 'participant' => $this->participant_id])->one();
	    if($device != null)
	    {
		    return $device->setToInactive();
	    }
	    return false;
	}
	
	public function enable()
	{
		$device = ParticipantDevice::find()->where(['id' => $this->device_id, 'participant' => $this->participant_id])->one();
	    if($device != null)
	    {
		    return $device->setToActive();
	    }
	    return false;
	}
	    
}
