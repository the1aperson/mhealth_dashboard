<?php

use yii\db\Migration;

/**
 * Class m181004_192919_add_alert_tables
 */
class m181004_192919_add_alert_tables extends Migration
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

		// setup Alert table
		
        $this->createTable('alert', [
            'id' => $this->primaryKey(),
			'alert_level' => $this->integer(),
			'participant' => $this->integer(),
			'message' => $this->text(),
			'requires_follow_up' => $this->boolean() . " DEFAULT 0",
			'follow_up_by' => $this->integer(),
			'follow_up_message' => $this->text(),
			'expires' => $this->integer(11),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
      

		$this->addForeignKey(
			'fk-alert-participant',
            'alert',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
		$this->addForeignKey(
			'fk-alert-user',
            'alert',
            'follow_up_by',
            'user',
            'id',
            'NO ACTION'
        );
        
        
        // Setup table for mapping alerts to dashboard users
        
         $this->createTable('hidden_alert', [
            'id' => $this->primaryKey(),
			'alert_id' => $this->integer(),
			'user_id' => $this->integer(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        $this->addForeignKey(
			'fk-hidden_alert-alert',
            'hidden_alert',
            'alert_id',
            'alert',
            'id',
            'NO ACTION'
        );
        
        $this->addForeignKey(
			'fk-hidden_alert-user',
            'hidden_alert',
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
			'fk-hidden_alert-user',
            'hidden_alert'
		);
		
		 $this->dropForeignKey(
			'fk-hidden_alert-alert',
            'hidden_alert'
        );
        
        $this->dropTable('hidden_alert');


		$this->dropForeignKey(
			'fk-alert-user',
            'alert'
        );
        
        $this->dropForeignKey(
			'fk-alert-participant',
            'alert'
        );
        
        $this->dropTable('alert');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181004_192919_add_alert_tables cannot be reverted.\n";

        return false;
    }
    */
}
