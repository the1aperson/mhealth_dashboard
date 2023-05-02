<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "participant_note".
 *
 * @property int $id
 * @property int $participant
 * @property int $created_by
 * @property string $note
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Participant $participant0
 * @property User $createdBy
 */
class ParticipantNote extends AuditableModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participant_note';
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
            [['participant', 'created_by', 'created_at', 'updated_at'], 'integer'],
            [['note'], 'string'],
            [['participant'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['participant' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'created_by' => 'Created By',
            'note' => 'Note',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by'])->one();
    }
    
    public static function getNotes($participant_id)
    {
	    return ParticipantNote::find()->where(['participant' => $participant_id])->orderBy('created_at desc')->all();
    }
}
