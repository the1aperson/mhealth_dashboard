<?php

use yii\db\Migration;

/**
 * Class m181023_231410_add_explicit_metadata_columns
 */
class m181023_231410_add_explicit_metadata_columns extends Migration
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
		
        $this->createTable('participant_adherence', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer()->notNull(),
			'test_type' => $this->string()->notNull(),
			'adherence' => $this->float(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
            'UNIQUE KEY (participant, test_type)',
        ], $tableOptions);
        
        $this->addForeignKey(
			'fk-participant_adherence-participant',
            'participant_adherence',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
        
        $this->createTable('participant_test_finished_count', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer()->notNull(),
			'test_type' => $this->string()->notNull(),
			'count' => $this->integer(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
            'UNIQUE KEY (participant, test_type)',
        ], $tableOptions);

		$this->addForeignKey(
			'fk-participant_test_finished_count-participant',
            'participant_test_finished_count',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
                
        $this->createTable('participant_test_missed_count', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer()->notNull(),
			'test_type' => $this->string()->notNull(),
			'count' => $this->integer(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
            'UNIQUE KEY (participant, test_type)',
        ], $tableOptions);
        
        $this->addForeignKey(
			'fk-participant_test_missed_count-participant',
            'participant_test_missed_count',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
                
        
        $this->createTable('participant_last_seen', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer()->notNull()->unique(),
			'last_seen' => $this->integer(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
		$this->addForeignKey(
			'fk-participant_last_seen-participant',
            'participant_last_seen',
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
			'fk-participant_adherence-participant',
            'participant_adherence');
            
		$this->dropForeignKey(
			'fk-participant_test_finished_count-participant',
            'participant_test_finished_count');

		$this->dropForeignKey(
			'fk-participant_test_missed_count-participant',
            'participant_test_missed_count');
                        
        $this->dropForeignKey(
			'fk-participant_last_seen-participant',
            'participant_last_seen');
            
        $this->dropTable('participant_adherence');
        $this->dropTable('participant_test_finished_count');            
		$this->dropTable('participant_test_missed_count');
		$this->dropTable('participant_last_seen');
		
        return true;
    }
}
