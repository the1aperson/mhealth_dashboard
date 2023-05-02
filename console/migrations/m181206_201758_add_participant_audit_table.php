<?php

use yii\db\Migration;

/**
 * Class m181206_201758_add_participant_audit_table
 */
class m181206_201758_add_participant_audit_table extends Migration
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
		
        $this->createTable('participant_audit_trail', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer(),
			'action' => $this->string(),
			'item' => $this->string(),
			'item_id' => $this->integer(),
			'data' => $this->binary(),
            'created_at' => $this->integer(11)->notNull(),
        ], $tableOptions);
      
        
		$this->addForeignKey(
			'fk-participant_audit_trail-participant',
            'participant_audit_trail',
            'participant',
            'participant',
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
			'fk-participant_audit_trail-participant',
            'participant_audit_trail'
		);
		
		$this->dropTable('participant_audit_trail');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181206_201758_add_participant_audit_table cannot be reverted.\n";

        return false;
    }
    */
}
