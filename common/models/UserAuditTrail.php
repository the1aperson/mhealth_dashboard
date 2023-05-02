<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_audit_trail".
 *
 * @property int $id
 * @property int $user_id
 * @property string $client_ip_address
 * @property string $action
 * @property string $item
 * @property resource $data
 * @property int $created_at
 *
 * @property User $user
 */
class UserAuditTrail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_audit_trail';
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
            [['user_id', 'item_id', 'created_at'], 'integer'],
            [['data'], 'string'],
            [['client_ip_address', 'action', 'item'], 'string', 'max' => 255],
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
            'user_id' => 'User ID',
            'client_ip_address' => 'Client Ip Address',
            'action' => 'Action',
            'item' => 'Item',
            'item_id' => 'Item ID',
            'data' => 'Data',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    
    public static function addAuditLog($action, $item, $item_id = null, $data = null)
    {
	    $request = yii::$app->getRequest();
		$auditItem = new UserAuditTrail();
		
		$auditItem->client_ip_address = Yii::$app->ipAnonymizer->hashedIp();
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
			$auditItem->user_id = Yii::$app->user->getIdentity()->id;
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
