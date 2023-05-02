<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "participant".
 *
 * @property int $id
 * @property string $participant_id
 * @property int $study_id
 * @property string $password_hash
 * @property int $created_at
 * @property int $updated_at
 *
 * @property ParticipantDevice[] $participantDevices
 * @property TestData[] $testDatas
 */
class Participant extends AuditableModel implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participant';
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
            [['participant_id', 'password_hash', 'study_id'], 'required'],
            [['enabled', 'hidden'], 'integer'],
            [['study_id', 'created_at', 'updated_at'], 'integer'],
            [['participant_id', 'password_hash'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'participant_id' => 'Participant ID',
            'study_id' => 'Study ID',
            'enabled' => 'Enabled',
            'hidden' => 'Hidden',
            'password_hash' => 'Password Hash',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public static function getParticipantByParticipantId($participant_id)
    {
	    return Participant::find()->where(['participant_id' => $participant_id, 'enabled' => 1, 'hidden' => 0])->one();
    }
    
    public static function getParticipantByDeviceId($deviceId)
    {
	    $device = ParticipantDevice::find()->where(['active' => 1, 'device_id' => $deviceId])->one();
	    if($device == null)
	    {
		    return null;
	    }
	    
	    $participant = Participant::find()->where(['id' => $device->participant, 'enabled' => 1, 'hidden' => 0])->one();
	    return $participant;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticipantDevices()
    {
        return $this->hasMany(ParticipantDevice::className(), ['participant' => 'id']);
    }
  
    
    public function getNotes()
    {
	    return $this->hasMany(ParticipantNote::className(), ['participant' => 'id']);
    }
    
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
    
    
    public function getActiveDevice()
    {
	    return ParticipantDevice::find()->where(['active' => 1, 'participant' => $this->id])->one();
    }
    
    public function getMostRecentDevice()
    {
	    return ParticipantDevice::find()->where(['participant' => $this->id])->orderBy('created_at desc')->one();
    }
    
    
    public function getInstallCount()
    {
	    return ParticipantDevice::find()->where(['participant' => $this->id])->count();
    }
    
    public function isFlagged()
    {
	    $user_id = Yii::$app->user->getId();
	    return ParticipantUserFlag::find()->where(['participant_id' => $this->id, 'user_id' => $user_id])->count() > 0;
    }
    
    public function dropFromStudy()
    {
	    if($this->enabled == 0)
	    {
		    return false;
	    }
	    
	    $this->enabled = 0;
	    if($this->save())
	    {
		    $user_id = Yii::$app->user->getId();
		    $dropRecord = new ParticipantDroppedBy();
		    $dropRecord->participant = $this->id;
		    $dropRecord->dropped_by = $user_id;
		    if($dropRecord->save())
		    {
			    $device = $this->getActiveDevice();
			    if($device != null)
			    {
				    $device->setToInactive();
			    }
			    return true;
		    }
		    else
		    {
			    return false;
		    }
	    }
	    else
	    {
		    return false;
	    }
    }
    
    // Marking a user as hidden doesn't delete them, or any of their data, but it
    // hides them from the dashboard, and prevents staff members from exporting their data.
    
    public function markAsHidden()
    {
	    $this->dropFromStudy();
	    $this->hidden = 1;
	    return $this->save();
    }
    
    //! IdentityInterface
    
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::getParticipantByDeviceId($token);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return "";
    }

    public function validateAuthKey($authKey)
    {
        return false;
    }
    
    //! AuditableModel
    
    
    public function attributesToSkip()
    {
	    $toSkip = parent::attributesToSkip();
	    $toSkip []= ["password_hash"];
	    return $toSkip;
    }
}
