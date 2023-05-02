<?php

namespace common\rules;

use yii;
use yii\rbac\Rule;
use yii\db\Query;
use common\models\AuthItemRuleGrant;

//NOTE! This class is most likely going to get deleted, but due to the way Yii stores Rule information in the database,
// I can't delete it until all of the existing applications have been updated and ran migrations successfully.

/* This Rule is the counterpart to the AssignRole permission, which controls what roles
	A user is allowed to assign.
	This is based on the relationship between the Role that contains this permission and the
	Roles that it is allowed to granted.
*/


class AssignRoleRule extends BaseGrantRule
{
    public $name = 'CanAssignRole';
    public $description = "Can Assign Roles";
	
	/* 
	Checks to make sure the given user has permission for the given role/permission
	for current Study.	
	The way the default RBAC is setup makes it kind of complicated to incorporate this
	relationship more directly.
	*/
	
    public function execute($user, $item, $params)
    {	    
	    return parent::execute($user, $item, $params);
    }
}

	
?>