<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "participant_audit_trail".
 *
 * @property int $id
 * @property int $participant
 * @property string $action
 * @property string $item
 * @property int $item_id
 * @property resource $data
 * @property int $created_at
 *
 * @property Participant $participant0
 */
class ParticipantAuditTrail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participant_audit_trail';
    }

	public function behaviors()
    {
        return [
			'timestamp'  => [
	            'class' => TimestampBehavior::className(),
	            'attributes' => [
	                ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
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
            [['participant', 'item_id', 'created_at'], 'integer'],
            [['data'], 'string'],
            [['action', 'item'], 'string', 'max' => 255],
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
            'action' => 'Action',
            'item' => 'Item',
            'item_id' => 'Item ID',
            'data' => 'Data',
            'created_at' => 'Created At',
        ];
    }

    
    public static function addAuditLog($action, $item, $item_id = null, $data = null)
    {
	    $request = yii::$app->getRequest();
		$auditItem = new ParticipantAuditTrail();
		
		$auditItem->action = $action;
		$auditItem->item = $item;
		$auditItem->item_id = $item_id;
		
		if(is_null($data) == false && is_string($data) == false)
		{
			$data = json_encode($data);
		}
		
		$auditItem->data = $data;
		
		if($request->getIsConsoleRequest() == false && isset(Yii::$app->user) && Yii::$app->user->isGuest == false)
		{
			$auditItem->participant = Yii::$app->user->getIdentity()->id;
		}
		
		if($auditItem->save() == false)
		{
			Yii::error($auditItem->getErrors());
			return false;
		}
		return true;
    }
    
    public function beforeDelete()
    {
	    return false;
    }
    
    public function beforeSave($insert)
    {
	    if($insert)
	    {
		    return parent::beforeSave($insert);
	    }
	    
	    return false;
    }
    
}
