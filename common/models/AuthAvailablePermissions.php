<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;

/**
 * This is the model class for table "auth_available_permissions".
 *
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 */
class AuthAvailablePermissions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_available_permissions';
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
            [['name'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public static function getAvailablePermissions()
    {
	    return AuthAvailablePermissions::findAll();
    }
    
    public static function getAvailablePermissionNames()
    {
		return (new Query())->select('name')->from(AuthAvailablePermissions::tableName())->column();
    }
    
    public static function addPermission($name)
    {
	    return Yii::$app->getDb()->createCommand("INSERT IGNORE INTO auth_available_permissions (name, created_at, updated_at) VALUES (:name, :created_at, :updated_at)", [":name" => $name, ":created_at" => time(), ":updated_at" => time()])->execute();
    }
    
    public static function removePermission($name)
    {
	    return AuthAvailablePermissions::deleteAll(['name' => $name]);
    }
}
