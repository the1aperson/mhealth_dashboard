<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "participant_dropped_by".
 *
 * @property int $id
 * @property int $participant
 * @property int $dropped_by
 * @property int $created_at
 *
 * @property Participant $participant0
 * @property User $droppedBy
 */
class ParticipantDroppedBy extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participant_dropped_by';
    }
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
			'timestamp'  => [
	            'class' => TimestampBehavior::className(),
	            'attributes' => [
	                 \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
	            ],
	        ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['participant', 'dropped_by', 'created_at'], 'integer'],
            [['participant'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['participant' => 'id']],
            [['dropped_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['dropped_by' => 'id']],
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
            'dropped_by' => 'Dropped By',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticipant0()
    {
        return $this->hasOne(Participant::className(), ['id' => 'participant']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDroppedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'dropped_by']);
    }
    
    public static function getDroppedRecord($participant_id)
    {
	    return ParticipantDroppedBy::find()->where(['participant' => $participant_id])->orderBy('created_at')->one();
    }
}
