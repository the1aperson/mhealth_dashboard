<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "hidden_alert".
 *
 * @property int $id
 * @property int $alert_id
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Alert $alert
 * @property User $user
 */
class HiddenAlert extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hidden_alert';
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
            [['alert_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['alert_id'], 'exist', 'skipOnError' => true, 'targetClass' => Alert::className(), 'targetAttribute' => ['alert_id' => 'id']],
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
            'alert_id' => 'Alert ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlert()
    {
        return $this->hasOne(Alert::className(), ['id' => 'alert_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public static function hideAlert($alert_id, $user_id)
    {
	    return Yii::$app->getDb()->createCommand("INSERT IGNORE INTO " . HiddenAlert::tableName() . " (alert_id, user_id, created_at, updated_at) VALUES(:alert_id, :user_id, :created_at, :updated_at)", 
	    [':alert_id' => $alert_id,
	    ':user_id' => $user_id,
	    ':created_at' => time(),
	    ':updated_at' => time()])
	    ->execute();
	    
    }
}
