<?php

use yii\db\Migration;

/**
 * Class m190124_173954_add_rule_item_table
 */
class m190124_173954_add_rule_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
		
        $this->createTable('auth_role_rule_grant', [
            'id' => $this->primaryKey(),
			'assigned_rule' => $this->string(64),
			'assigned_role' => $this->string(64)->notNull(),
			'granted_role' => $this->string()->notNull(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11)->notNull(),
        ], $tableOptions);
      
        $this->addForeignKey(
			'fk-auth_role_rule_grant-rule',
            'auth_role_rule_grant',
            'assigned_rule',
            'auth_rule',
            'name',
            'NO ACTION'
        );     

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
			'fk-auth_role_rule_grant-rule',
            'auth_role_rule_grant',
            'assigned_rule'
        );

		$this->dropTable('auth_role_rule_grant');
		
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190124_173954_add_rule_item_table cannot be reverted.\n";

        return false;
    }
    */
}
