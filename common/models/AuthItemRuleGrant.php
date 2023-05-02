<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/*
This table maps the relationship a user's assigned Role, a specific Rule assigned to that role,
and the Roles that are then granted permission to. The name of this table is probably not the most informative.
When creating a Role, some Permissions have additional options, which let you choose what other roles the new Role will be able to
affect or change, based on the permission.

For instance, a certain Role may be able to view all of the other users, regardless of their role, but can only modify or delete a small
subset of roles.

Along with being able to grant certain roles, there is also an AUTH_ALL_ROLES value that can be set to indicate that the assigned Role is 
implicitly granted access to all roles.

Calling $user->can('somePermission', [<parameters, see BaseGrantRule>]) causes the RBAC Manager to look through the
user's assigned Roles and permissions:

- Searches auth_assignment for any Roles or Permissions assigned to $user
- Then optionally recurses through auth_item_child to find a path from the $user's assigned permissions to the requested 'somePermission'
- Checks auth_item to see if 'somePermission' has a Rule associated with it
- Assuming that it does, and that it's an instance of BaseGrantRule, it instantiates the Rule, and calls execute()
- The BaseGrantRule then uses auth_role_rule_grant to see if the $user has been assigned a Role that has been granted access to the requested 
  role or other user, based on the parameters passed along when calling can().
*/

/**
 * This is the model class for table "assigned_role_rule_grant".
 *
 * @property int $id
 * @property string $assigned_rule
 * @property string $assigned_role
 * @property string $granted_role
 * @property int $created_at
 * @property int $updated_at
 *
 * @property AuthRule $ruleName
 */
class AuthItemRuleGrant extends \yii\db\ActiveRecord
{
	
	public const AUTH_ALL_ROLES = "AUTH_ALL_ROLES"; // if granted_role == AUTH_ALL_ROLES, the assigned rule/role pair has access to all roles.
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_role_rule_grant';
    }
    
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
            [['assigned_role', 'granted_role',], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['assigned_rule', 'assigned_role'], 'string', 'max' => 64],
            [['granted_role'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'assigned_rule' => 'Assigned Rule',
            'assigned_role' => 'Assigned Role',
            'granted_role' => 'Granted Role',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


	public static function getGrantRuleForAuthItem($auth_item)
 	{
 		$auth = Yii::$app->authManager;

	 	if(isset($auth_item->ruleName) && $auth_item->ruleName != '')
	 	{
	 		$rule = $auth->getRule($auth_item->ruleName);

		 	if($rule instanceof \common\rules\BaseGrantRule)
		 	{
		 		return $rule;
			}
		}
	 	
	 	$children = $auth->getChildren($auth_item->name);
	 	
	 	foreach($children as $child)
	 	{
	 		$rule = AuthItemRuleGrant::getGrantRuleForAuthItem($child);
	 		if($rule != null)
	 		{
	 			return $rule;
			}
		}
	 	
		return null;
 	}
    
    public static function createGrant($assigned_rule, $assigned_role, $granted_role)
    {
	    $grant = AuthItemRuleGrant::find()->where(['assigned_rule' => $assigned_rule, 'assigned_role' => $assigned_role, 'granted_role' => $granted_role])->one();
	    
	    if($grant != null)
	    {
		    return $grant;
		}
		
		$grant = new AuthItemRuleGrant();
		
		$grant->assigned_rule = $assigned_rule;
		$grant->assigned_role = $assigned_role;
		$grant->granted_role = $granted_role;
		
		if($grant->save())
		{
			return $grant;
		}
		return null;
    }
    
    
    public static function removeGrant($assigned_rule, $assigned_role, $granted_role)
    {
	    return AuthItemRuleGrant::deleteAll(['assigned_rule' => $assigned_rule, 'assigned_role' => $assigned_role, 'granted_role' => $granted_role]);
    }
    
    public static function removeAllGrants($assigned_rule, $assigned_role)
    {
	    return AuthItemRuleGrant::deleteAll(['assigned_rule' => $assigned_rule, 'assigned_role' => $assigned_role]);
    }
    
    
    public static function getGrants($assigned_rule, $assigned_role)
    {
	    return AuthItemRuleGrant::find()->where(['assigned_rule' => $assigned_rule, 'assigned_role' => $assigned_role])->all();
    }
    
    public static function getAllGrantsForRole($assigned_role)
    {
	    return AuthItemRuleGrant::find()->where(['assigned_role' => $assigned_role])->all();
    }
    
    public static function checkGrantForRule($assigned_rule, $assigned_role, $granted_role)
    {
	    $query = AuthItemRuleGrant::find()->where([
		    'assigned_rule' => $assigned_rule, 
		    'assigned_role' => $assigned_role,
	    ]);
	    
		// Since an assigned role/rule pair can be granted AUTH_ALL_STUDIES, we need to check that granted_role doesn't also = AUTH_ALL_STUDIES
		// In the event that we're passed a $granted_role that is either empty or null or whatever, we need to at least check for AUTH_ALL_STUDIES.
		
	    if(!empty($granted_role))
	    {
	    	$query->andWhere(['or', ['granted_role' => AuthItemRuleGrant::AUTH_ALL_ROLES], ['granted_role' => $granted_role]]);
	    }
	    else
	    {
	    	$query->andWhere(['granted_role' => AuthItemRuleGrant::AUTH_ALL_ROLES]);
	    }
	    
	    $count = $query->count();
	    return $count > 0;
    }
    
        
}
