<?php

use yii\db\Migration;

/**
 * Class m181113_182042_add_table_participant_completed_study
 */
class m181113_182042_add_table_participant_completed_study extends Migration
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
		
        $this->createTable('participant_completed_study', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer()->notNull()->unique(),
			'completed_study' => $this->boolean(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        $this->addForeignKey(
			'fk-participant_completed_study-participant',
            'participant_completed_study',
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
        $this->dropForeignKey('fk-participant_completed_study-participant',
            'participant_completed_study');
		$this->dropTable('participant_completed_study');
		
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181113_182042_add_table_participant_completed_study cannot be reverted.\n";

        return false;
    }
    */
}
