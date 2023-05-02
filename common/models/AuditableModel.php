<?php

namespace common\models;

use Yii;

class AuditableModel extends \yii\db\ActiveRecord
{
	
	public function afterSave($insert, $changedAttributes)
	{
		if(defined('YII_ENV') && YII_ENV == 'test')
		{
			return;
		}
		
		$auditClass = Yii::$app->params["auditClass"];
		
		$action = $insert ? "create" : "update";
		$id = isset($this->id) ? $this->id : null;
		$auditClass::addAuditLog($action, $this->tableName(), $id);
		$attributesToSkip = $this->attributesToSkip();
		
		foreach($changedAttributes as $name => $oldValue)
		{
			if(in_array($name, $attributesToSkip))
			{
				continue;
			}
			
			$itemName = $this->tableName() . "." . $name;
			$newValue = $this->$name;
			$auditClass::addAuditLog($action, $itemName, $id, $newValue);
		}
		parent::afterSave($insert, $changedAttributes);
	}
	
	public function afterDelete()
	{
		if(defined('YII_ENV') && YII_ENV == 'test')
		{
			return;
		}
		
		$auditClass = Yii::$app->params["auditClass"];
		
		$id = isset($this->id) ? $this->id : null;
		$auditClass::addAuditLog("delete", $this->tableName(), $id, json_encode($this));
		parent::afterDelete();
	}
	
	public function attributesToSkip()
	{
		return ['updated_at', 'created_at'];
	}
}

?>