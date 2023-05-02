<?php

use yii\db\Migration;

/**
 * Class m180913_165211_create_initial_tables
 */
class m180913_165211_create_initial_tables extends Migration
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
		
        $this->createTable('participant', [
            'id' => $this->primaryKey(),
			'participant_id' => $this->string()->notNull(),
			'password_hash' => $this->string()->notNull(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        $this->createTable('participant_device', [
            'id' => $this->primaryKey(),
            'participant' => $this->integer(),
			'device_id' => $this->string()->notNull(),
			'device_type' => $this->text()->notNull(),
			'active' => $this->boolean(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        $this->createTable('test_data', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer(),
			'device' => $this->integer(),
			'test_type' => $this->string(),
			'blob_data' => "MEDIUMBLOB",
			'md5_hash' => $this->string(),
            'created_at' => $this->integer(11)->notNull(),
        ], $tableOptions);
        
        $this->createTable('schedule_data', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer(),
			'device' => $this->integer(),
			'test_type' => $this->string(),
			'blob_data' => "MEDIUMBLOB",
			'md5_hash' => $this->string(),
            'created_at' => $this->integer(11)->notNull(),
        ], $tableOptions);
        
        $this->createTable('participant_metadata', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer(),
			'name' => $this->string(),
			'value' => $this->string(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        $this->createTable('participant_heartbeat', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer(),
			'device' => $this->integer(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        // Now setup the relationships between them
                
        $this->addForeignKey(
			'fk-device-participant',
            'participant_device',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
		$this->addForeignKey(
			'fk-test_data-participant',
            'test_data',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
		$this->addForeignKey(
			'fk-test_data-device',
            'test_data',
            'device',
            'participant_device',
            'id',
            'NO ACTION'
        );
        
         $this->addForeignKey(
			'fk-schedule_data-participant',
            'schedule_data',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
        $this->addForeignKey(
			'fk-schedule_data-device',
            'schedule_data',
            'device',
            'participant_device',
            'id',
            'NO ACTION'
        );
        
        $this->addForeignKey(
			'fk-participant_metadata-participant',
            'participant_metadata',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
        
         $this->addForeignKey(
			'fk-participant_heartbeat-participant',
            'participant_heartbeat',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
        $this->addForeignKey(
			'fk-participant_heartbeat-device',
            'participant_heartbeat',
            'device',
            'participant_device',
            'id',
            'NO ACTION'
        );
        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
	    $this->dropForeignKey('fk-test_data-device',
            'test_data');
            
        $this->dropForeignKey('fk-test_data-participant',
            'test_data');
            
           
        $this->dropForeignKey('fk-device-participant',
            'participant_device');
            
	    $this->dropForeignKey('fk-schedule_data-device',
            'schedule_data');
            
        $this->dropForeignKey('fk-schedule_data-participant',
            'schedule_data');

	    $this->dropForeignKey('fk-participant_heartbeat-device',
            'participant_heartbeat');
            
        $this->dropForeignKey('fk-participant_heartbeat-participant',
            'participant_heartbeat');
        
		$this->dropForeignKey('fk-participant_metadata-participant',
            'participant_metadata');            
        
        $this->dropTable("participant_heartbeat");
        $this->dropTable("participant_metadata");
        $this->dropTable("schedule_data");
        $this->dropTable("test_data");
        $this->dropTable("participant_device");
        $this->dropTable("participant");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180913_165211_create_initial_tables cannot be reverted.\n";

        return false;
    }
    */
}
