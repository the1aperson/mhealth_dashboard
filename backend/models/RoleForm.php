<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use yii\db\Query;

use common\models\AuthItemRuleGrant;
use common\models\AuthAvailablePermissions;
use common\models\UserAuditTrail;

/**
 * Role form
 */
class RoleForm extends Model
{
    public $name;
    public $description;
    public $permissions = [];
    public $grants = [];
	public $password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {	   
	    $available_permissions = AuthAvailablePermissions::getAvailablePermissionNames();
        return [
			['password', 'required'],		
			['password', function(){
				if(Yii::$app->user->getIdentity()->validatePassword($this->password) == false)
				{
					$this->addError('password', 'Password is incorrect.');
				}
			}],
			['name', 'required'],
            ['name', 'string'],
            ['description', 'string'],
            ['permissions', 'default', 'value' => []],
            ['permissions', 'each', 'rule' => ['string']],
            ['permissions', 'each', 'rule' => ['in', 'range' => $available_permissions]],
            ['permissions', 'required', 'message' => ' Please select at least one permission'],
			['grants', function ($attribute, $params, $validator){
				// catch empty values (meaning no selection was submitted), and make sure it's at least an empty array
				if(is_array($this->grants))
				{
					foreach($this->grants as $name => $grants)
					{
						if(is_array($grants) == false)
						{
							$this->grants[$name] = [];
						}
					}
				}
            } ],
            ['grants', 'default', 'value' => []],
            ['grants', function($attribute, $params, $validator){
	           
	           // Let's check and make sure that each Rule we're trying to grant actually exists,
	           // and that each of the roles we're granting access to exist as well.
	           if(is_array($this->grants))
	           {
		           $ruleNames = array_keys($this->grants);
		           
		           $existingRules = (new Query())->select('name')->from('auth_rule')->where(['name' => $ruleNames])->column();
		           $missingRules = array_diff($ruleNames, $existingRules);

		           foreach($missingRules as $missingRule)
		           {
			           $this->addError('grants', "Grant Rule $missingRule does not exist");
		           }
		           
		           foreach($this->grants as $name => $roles)
		           {
			           $existingRoles = (new Query())->select('name')->from('auth_item')->where(['name' => $roles])->andWhere(['type' => \yii\rbac\Item::TYPE_ROLE])->column();
			           $missingRoles = array_diff($roles, $existingRoles);
			           
			           foreach($missingRoles as $missingRole)
			           {
				           if($missingRole == AuthItemRuleGrant::AUTH_ALL_ROLES)
				           {
					           continue;
				           }
				           
				           $this->addError('grants', "Role $missingRole for Grant $name does not exist");
			           }
		           }
	           } 
            }],

        ];
    }
    
    public function scenarios()
    {
	    $scenarios = parent::scenarios();
	    $scenarios['create'] = ['name', 'description', 'permissions', 'grants'];
	    $scenarios['update'] = ['name', 'description', 'permissions', 'grants'];
	    
	    return $scenarios;
    }
    
    public function preload($role_name)
    {
	    $auth = Yii::$app->authManager;
	    
	    $role = $auth->getRole($role_name);
	    $available_permissions = AuthAvailablePermissions::getAvailablePermissionNames();
	    if($role)
	    {
		    $this->name = $role->name;
		    $this->description = $role->description;
		    
		    $permissions = $auth->getPermissionsByRole($role_name);
		    $this->permissions = [];
		    foreach($permissions as $p)
		    {
			    if(in_array($p->name, $available_permissions))
			    {
			    	$this->permissions[]= $p->name;
			    }
		    }
		    
		    $grants = AuthItemRuleGrant::getAllGrantsForRole($role->name);
		    
		    $this->grants = [];
		    
		    foreach($grants as $grant)
		    {
			    if(!isset($this->grants[$grant->assigned_rule]))
			    {
				    $this->grants[$grant->assigned_rule] = [];
			    }
			    $this->grants[$grant->assigned_rule][] = $grant->granted_role;
		    }
		    
			return true;
	    }
	    
	    return false;
    }

	public function save()
	{
		$auth = Yii::$app->authManager;
		
		$role = $auth->getRole($this->name);
		if($this->scenario == "update" && $role == null)
		{
			return false;
		}
		else if($this->scenario == "create")
		{
			if($role != null)
			{
				$this->addError("name", "Role with name already exists");
				return false;
			}
			$role = $auth->createRole($this->name);
			$auth->add($role);
		}
		
		
		
		$role->ruleName = 'CanViewStudy';
		$role->description = $this->description;
		$auth->removeChildren($role);
		
		// Add each permission to the newly created role
		if(isset($this->permissions) && is_array($this->permissions))
		{
			foreach($this->permissions as $permission)
			{
				$p = $auth->getPermission($permission);
				
				if($p)
				{
					$auth->addChild($role, $p);
				}
			}
		}
		
		// Simiarly, go through the list of grants, and add AuthItemRuleGrants
		// for each (and deleting the old grants beforehand)
		
		if(isset($this->grants) && is_array($this->grants))
		{

			foreach($this->grants as $rule_name => $grants)
			{
				AuthItemRuleGrant::removeAllGrants($rule_name, $role->name);
				if(!is_array($grants))
				{
					continue;
				}
				foreach($grants as $grant)
				{
					AuthItemRuleGrant::createGrant($rule_name, $role->name, $grant);
				}				
			}
		}
				
		$auth->update($role->name, $role);
		
		UserAuditTrail::addAuditLog($this->scenario, 'role', null, $role->name);
		UserAuditTrail::addAuditLog($this->scenario, 'role', null, $auth->getChildren($role->name));
		
		return $role;
	}
    
}
