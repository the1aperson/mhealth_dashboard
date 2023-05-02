<?php

use yii\db\Migration;

/**
 * Class m181108_165441_add_table_participant_dropped_by
 */
class m181108_165441_add_table_participant_dropped_by extends Migration
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
		
        $this->createTable('participant_dropped_by', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer(),
			'dropped_by' => $this->integer(),
            'created_at' => $this->integer(11)->notNull(),
        ], $tableOptions);
        
        $this->addForeignKey(
			'fk-participant_dropped_by-participant',
            'participant_dropped_by',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
        $this->addForeignKey(
			'fk-participant_dropped_by-user',
            'participant_dropped_by',
            'dropped_by',
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
		$this->dropForeignKey('fk-participant_dropped_by-user',
            'participant_dropped_by');
            
		$this->dropForeignKey('fk-participant_dropped_by-participant',
            'participant_dropped_by');
            
        $this->dropTable('participant_dropped_by');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181108_165441_add_table_participant_dropped_by cannot be reverted.\n";

        return false;
    }
    */
}
