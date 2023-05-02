<?php

use yii\db\Migration;

/**
 * Class m190204_174713_add_available_permissions_table
 */
class m190204_174713_add_available_permissions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
     
     
	 /* 
		 Creating a table called auth_available_permissions, so that we don't have to keep adding/deleting items from
		 auth_item every time the siteAdmin changes the available permission set. This way we don't have to worry about
		 losing rule and child/parent assignments.
	 */
	 
    public function safeUp()
    {
		$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

		
        $this->createTable('auth_available_permissions', [
			'name' => $this->string()->notNull()->unique(),
			'created_at' => $this->integer(),
			'updated_at' => $this->integer(),
        ], $tableOptions);
        
        $auth = Yii::$app->authManager;
        
        // Add all of the currently existing permissions to this table.
        // Since we don't want to add children permissions, we need to  first go through
        // the $existingPermissions and get a list of all of the children names.
        // Then, while we're adding the existing permissions to our new table, we'll skip
        // any in $namesToSkip.
        
        $existingPermissions = $auth->getPermissions();
        $existingPermissionNames = [];
        $namesToSkip = [];
        
        foreach($existingPermissions as $permission)
        {
	        $children = $auth->getChildren($permission->name);
	        
	        foreach($children as $c)
	        {
		        $namesToSkip []= $c->name;
	        }
        }
        
        foreach($existingPermissions as $permission)
        {
	        if(in_array($permission->name, $namesToSkip))
	        {
		        continue;
	        }
	        
	        $this->execute("INSERT INTO auth_available_permissions (name, created_at, updated_at) VALUES (:name, :created_at, :updated_at)", [":name" => $permission->name, ":created_at" => time(), ":updated_at" => time()]);
	        $existingPermissionNames []= $permission->name;
        }
        
        // Now, go through the remaining permissions and add them to the authManager.
        
        $allPermissions = Yii::$app->params['staff_permission_settings']['all_permissions'];
        
        $newPermissions = array_diff($allPermissions, $existingPermissionNames);
        
        foreach($newPermissions as $new_name)
        {
	        echo "Adding $new_name...\n";
	        $desc = Yii::$app->params['staff_permission_settings']['permission_descriptions'][$new_name];
	        $perm = $auth->createPermission($new_name);
	        $perm->description = $desc;
	        $auth->add($perm);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable("auth_available_permissions");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190204_174713_add_available_permissions_table cannot be reverted.\n";

        return false;
    }
    */
}
