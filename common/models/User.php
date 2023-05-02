<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
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
	        [['first_name', 'last_name'], 'string'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            ['requires_password_reset', 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
    
    public static function findByUsernameOrEmail($username)
    {
        return static::find()->where(['or', ['username' => $username], ['email' => $username]])->andWhere(['status' => self::STATUS_ACTIVE])->one();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
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

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    
    public function getRoles()
    {
	    $roles = array_keys(Yii::$app->authManager->getRolesByUser($this->getId()));
	    return $roles;
    }
    
    public function getAccessibleStudies()
    {
	    $availableStudyIds = (new Query())->select('study_id')->from('study_user_auth')->where(['user_id' => $this->id])->column();
	    if(in_array(StudyUserAuth::AUTH_ALL_STUDIES_ID, $availableStudyIds))
	    {
		    return Study::find()->indexBy('id')->all();
	    }
	    return Study::find()->where(['id' => $availableStudyIds])->indexBy('id')->all();
    }
    
    public function isSiteAdmin()
    {
	    $admin_ids = Yii::$app->authManager->getUserIdsByRole('siteAdmin');
	    return in_array($this->id, $admin_ids);
    }
    
    public function getStatusLabel()
    {
	    if($this->status == User::STATUS_ACTIVE)
	    {
		    return "Active";
	    }
	    return "Inactive";
    }
    
    public function displayName()
    {
	    return $this->first_name . " " . $this->last_name;
    }
    
    public function shortDisplayName()
    {
        return substr($this->first_name, 0, 1) . ". " . $this->last_name;
    }

    public function markRequiresPasswordReset()
    {
	    $this->generatePasswordResetToken();
	    $this->requires_password_reset = 1;
    }
    
    public function removeRequiresPasswordReset()
    {
	    $this->requires_password_reset = 0;
    }
    
    /*
	    
	Since we don't want to actually delete users, we have to override this and
	just change the status.
	
	*/
    public function delete()
    {
	    $this->status = User::STATUS_DELETED;
	    return $this->save();

    }
    
    public function undelete()
    {
	    $this->status = User::STATUS_ACTIVE;
	    return $this->save();
    }
}
