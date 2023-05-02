<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\Study;
use common\models\StudyUserAuth;

/**
 * User form
 */
class UserForm extends Model
{
	public $first_name;
	public $last_name;
	public $username;
    public $email;
    public $password;
	public $auth_roles;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
	    
        return [
            [['first_name', 'last_name', 'username', 'email', 'password'], 'required'],
            
            [['first_name', 'last_name', 'username', 'email'], 'trim'],

			[['first_name', 'last_name'], 'string'],
						
			['email', 'email'],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.', 'on' => 'create'],
            
			['password', 'string', 'min' => 8],
			
			['auth_roles', function($attribute, $params, $validator){
				
				// we need to make sure that the user isn't somehow passing in study ids that they're
				// not able to use.
				
				if(count($this->auth_roles) == 0)
				{
					return;
				}
				
				$studyIds = array_keys($this->getStudies());
				$roles = $this->getRoles();
				foreach($this->auth_roles as $auth_role)
				{
					// Check that we have a valid study id and role
					if(in_array($auth_role->study_id, $studyIds) == false)
					{
						$this->addError('auth_roles', 'Invalid Study Id');
						$auth_role->addError('auth_roles', 'Invalid Study Id');
					}
					
					if(in_array($auth_role->auth_item_name, $roles) == false)
					{
						$this->addError('auth_roles', 'Invalid Role');
						$auth_role->addError('auth_roles', 'Invalid Role');
					}
				}
			}],
            
        ];
    }
    
    public function init()
    {
	    parent::init();
		$this->auth_roles = [new StudyUserAuth()];
    }
    
    public function scenarios()
    {
	    $scenarios = parent::scenarios();
	    
	    $scenarios['create'] = ['first_name', 'last_name', 'email', 'auth_roles'];
	    $scenarios['update'] = ['first_name', 'last_name', 'email', 'auth_roles'];
	    
	    return $scenarios;
    }
	public function validate($attributeNames = null, $clearErrors = true)
	{
		if(parent::validate($attributeNames, $clearErrors) && (count($this->auth_roles) == 0 || StudyUserAuth::validateMultiple($this->auth_roles)))
		{
			return true;
		}
				
		return false;
	}
	public function load ( $data, $formName = null )
	{
		if(parent::load($data, $formName))
		{
			if(isset($data['StudyUserAuth']) && is_array($data['StudyUserAuth']))
			{
				$this->auth_roles = [];
				$auth_roles = [];
				
				foreach($data['StudyUserAuth'] as $d)
				{
					$auth_role = new StudyUserAuth();
					if($auth_role->load($d, ''))
					{
						$auth_roles []= $auth_role;
					}
					else
					{
						return false;
					}
				}
				$this->auth_roles = $auth_roles;
			}
			else
			{
				$this->auth_roles = [];
			}
			return true;
		}
		return false;
	}
    
    public function save($id = null)
    {
	    if (!$this->validate()) {
            return null;
        }
        
        if($this->scenario == "update")
        {
	        $user = User::find()->where(['id'=> $id])->one();
        }
        else if($this->scenario == "create")
        {
	        $user = new User();	        
	        $user->username = $this->email;
	        // if we're creating a new user, we force them to reset their password before login.
	        // So let's just set an unnecessarily long character sequence for their password temporarily.
	        // This value isn't displayed anywhere, and isn't intended to actually be used.
	        $user->setPassword(bin2hex(random_bytes(16)));	
	        $user->markRequiresPasswordReset();
	        
	    }
	    
	    if($user == null)
	    {
		    $this->addError('username', 'User with id ' . $id . ' not found');
		    return null;
	    }
	    
        $user->email = $this->email;
        $user->first_name = $this->first_name;
        $user->last_name = $this->last_name;
        $user->auth_key = "";
                
		if($user->save())
		{
			// For each of the roles assigned to this user, we have to do two things:
			// - Make sure the user is assigned to the given role in the AuthManager
			// - Make sure the user is assigned the role on the given study in StudyUserAuth.
			// After we make the new assignments, we have to revoke any roles that the user
			// is no longer assigned to.
			
			$auth = Yii::$app->authManager;
			
			$existingRoles = $auth->getRolesByUser($user->getId());
			StudyUserAuth::removeAssignmentsForUser($user->getId());
			
			$newRoleNames = [];
			// add new/existing roles			
			foreach($this->auth_roles as $auth_role)
			{
				if(in_array($auth_role->auth_item_name, array_keys($existingRoles)) == false)
				{
					$role = $auth->getRole($auth_role->auth_item_name);
					$auth->assign($role, $user->getId());
					$existingRoles[$role->name] = $role;
				}
				StudyUserAuth::makeAssignment($auth_role->study_id, $user->getId(), $auth_role->auth_item_name);
				$newRoleNames[]=$auth_role->auth_item_name;
			}
			
			// Now, we need to figure out which roles to remove
			
			$existingRoles = $auth->getRolesByUser($user->getId());
			
			foreach($existingRoles as $name => $role)
			{
				if(in_array($name, $newRoleNames) == false)
				{
					$auth->revoke($role, $user->getId());
				}
			}
			
	        return $user;
	    }
	    else
	    {
		    $this->addErrors($user->getErrors());
	    }
	    
		return null;
    }
    
    public function preload($user)
    {
	    $this->first_name = $user->first_name;
	    $this->last_name = $user->last_name;
	    $this->username = $user->username;
	    $this->email = $user->email;
	    
	    $this->auth_roles = StudyUserAuth::getAssignmentsForUser($user->id);
	    if(count($this->auth_roles) == 0)
	    {
		    $this->auth_roles = [new StudyUserAuth()];
	    } 
    }
    
    public function getRoles()
    {
	    $roles = array_keys(Yii::$app->authManager->getRoles());
	    $user = Yii::$app->user;
	    
	    $roleArray = [];
	    foreach($roles as $role)
	    {
		    // we have to check that the user can actually assign this role to a user
			if($user->can('assignRole', ['role_name' => $role]))
			{	
		    	$roleArray[$role] = $role;
		    }
	    }
	    
	    // don't make the siteAdmin role available.
	    if(isset($roleArray['siteAdmin']))
	    {
		    unset($roleArray['siteAdmin']);
	    }
	    
	    return $roleArray;
    }
    
    
    public function getStudies()
    {
	    // The only studies that the current user should be able to setup are ones
	    // that they have 'modifyUsers' permissions for.
	    $availableStudies = [];
	    
	    $availableStudies = StudyUserAuth::getAvailableStudiesForUserAndAuth(Yii::$app->user->getId(), 'modifyUsers', true);
	    
// 	    $studies = Study::find()->all();
	    $studyArray = [];
	    
	    foreach($availableStudies as $study)
	    {
		    $studyArray[$study->id] = $study->name;
	    }
	    
	    return $studyArray;
    }
    
    public function sendWelcomeEmail($user)
    {

        if (!$user) {
            return false;
        }
        
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'welcome-html', 'text' => 'welcome-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
            ->setTo($this->email)
            ->setSubject('New Account Created for ' . Yii::$app->name)
            ->send();
    }
}
