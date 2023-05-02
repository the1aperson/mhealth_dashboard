<?php

use yii\db\Migration;

/**
 * Class m181003_211414_add_user_audit_table
 */
class m181003_211414_add_user_audit_table extends Migration
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

		// setup tables for participant, device, and test data
		
        $this->createTable('user_audit_trail', [
            'id' => $this->primaryKey(),
			'user_id' => $this->integer(),
			'client_ip_address' => $this->string(),
			'action' => $this->string(),
			'item' => $this->string(),
			'item_id' => $this->integer(),
			'data' => $this->binary(),
            'created_at' => $this->integer(11)->notNull(),
        ], $tableOptions);
      
        
		$this->addForeignKey(
			'fk-user_audit_trail-user',
            'user_audit_trail',
            'user_id',
            'user',
            'id',
            'NO ACTION'
        );
        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropForeignKey(
			'fk-user_audit_trail-user',
            'user_audit_trail'
		);
		
		$this->dropTable('user_audit_trail');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181003_211414_add_user_audit_table cannot be reverted.\n";

        return false;
    }
    */
}
