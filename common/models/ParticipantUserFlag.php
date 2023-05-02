<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "participant_user_flag".
 *
 * @property int $id
 * @property int $participant_id
 * @property int $user_id
 *
 * @property Participant $participant
 * @property User $user
 */
class ParticipantUserFlag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participant_user_flag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['participant_id', 'user_id'], 'required'],
            [['participant_id', 'user_id'], 'integer'],
            [['participant_id'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['participant_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => 'User ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticipant()
    {
        return $this->hasOne(Participant::className(), ['id' => 'participant_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public static function flag($participant_id, $user_id)
    {
	    return Yii::$app->db->createCommand('INSERT IGNORE INTO participant_user_flag (participant_id, user_id) VALUES (:participant_id, :user_id)', [':participant_id' => $participant_id, ':user_id' => $user_id])->execute();
    }
    
    public static function unflag($participant_id, $user_id)
    {
	    return ParticipantUserFlag::deleteAll(['participant_id' => $participant_id, 'user_id' => $user_id]);
    }
}
