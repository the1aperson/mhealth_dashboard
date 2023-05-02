<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "participant_device".
 *
 * @property int $id
 * @property int $participant
 * @property string $device_id
 * @property string $device_type
 * @property int $active
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Participant $participant0
 * @property TestData[] $testDatas
 */
class ParticipantDevice extends AuditableModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participant_device';
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
        $rules = [
            [['participant', 'active', 'created_at', 'updated_at'], 'integer'],
            [['device_id', 'device_type'], 'required'],
            [['device_type', 'os_version', 'app_version', 'os_type', 'raw_device_info'], 'string'],
            [['device_id'], 'string', 'max' => 255],
            [['participant'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['participant' => 'id']],
        ];
        
        if(YII_DEBUG && (Yii::$app->params['allow_duplicate_devices'] ?? false) == true)
        {
	        // do nothing?
        }
        else
        {
            $rules []= ['device_id', 'unique'];
        }
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'participant' => 'Participant',
            'device_id' => 'Device ID',
            'device_type' => 'Device Type',
			'app_version' => 'App Version',
			'os_version' => 'OS Version',
            'raw_device_info' => 'Raw Device Info',
			'os_type' => 'OS Type',
            'active' => 'Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',

        ];
    }

    
    public function setToActive()
    {
	    if($this->participant == null)
	    {
		    return false;
	    }

		$activeDevices = ParticipantDevice::find()->where(['participant' => $this->participant, 'active' => 1])->all();
		foreach($activeDevices as $device)
		{
			$device->active = 0;
			$device->save();
		}

		$this->active = 1;
		return $this->save();
		
    }
    
    public function setToInactive()
    {
	    $this->active = 0;
	    return $this->save();
    }
    
    public function setDeviceInfo($appVersion, $deviceInfo)
    {
	    if($appVersion != null)
	    {
		    $this->app_version = $appVersion;
	    }
	    
	    if($deviceInfo != null)
	    {
		    $this->raw_device_info = $deviceInfo;
		    
		    $deviceInfo = \common\components\DeviceNameHelper::parseDeviceInfoString($deviceInfo);
		    $this->os_type = $deviceInfo['os_type'];
		    $this->device_type = $deviceInfo['device_type'];
		    $this->os_version = $deviceInfo['os_version'];
	    }
    }
}
