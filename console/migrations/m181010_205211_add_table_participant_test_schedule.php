<?php

use yii\db\Migration;

/**
 * Class m181010_205211_add_table_participant_test_schedule
 */
class m181010_205211_add_table_participant_test_schedule extends Migration
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
		
        $this->createTable('participant_test_session', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer(),
			'session_date' => $this->integer(11),
			'start_date' => $this->integer(11),
			'type' => $this->string(),
			'session_identifier' => $this->string(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        
        $this->addForeignKey(
			'fk-participant_test_session-participant',
            'participant_test_session',
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
		$this->dropForeignKey('fk-participant_test_session-participant',
		'participant_test_session');
		
		$this->dropTable('participant_test_session');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181010_205211_add_table_participant_test_schedule cannot be reverted.\n";

        return false;
    }
    */
}
