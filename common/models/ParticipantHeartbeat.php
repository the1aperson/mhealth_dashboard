<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "participant_heartbeat".
 *
 * @property int $id
 * @property int $participant
 * @property int $device
 * @property int $created_at
 * @property int $updated_at
 *
 * @property ParticipantDevice $device0
 * @property Participant $participant0
 */
class ParticipantHeartbeat extends AuditableModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participant_heartbeat';
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
            [['participant', 'device'],'integer'],
            [['device'], 'exist', 'skipOnError' => true, 'targetClass' => ParticipantDevice::className(), 'targetAttribute' => ['device' => 'id']],
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
            'device' => 'Device',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevice0()
    {
        return $this->hasOne(ParticipantDevice::className(), ['id' => 'device']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticipant0()
    {
        return $this->hasOne(Participant::className(), ['id' => 'participant']);
    }
}
