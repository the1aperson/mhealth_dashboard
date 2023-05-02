<?php

use yii\db\Migration;

/**
 * Class m181102_184114_add_table_participant_user_flag
 */
class m181102_184114_add_table_participant_user_flag extends Migration
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
		
        $this->createTable('participant_user_flag', [
            'id' => $this->primaryKey(),
			'participant_id' => $this->integer()->notNull(),
			'user_id' => $this->integer()->notNull(),
            'UNIQUE KEY (participant_id, user_id)',
        ], $tableOptions);
        
        $this->addForeignKey(
			'fk-puf_participant',
            'participant_user_flag',
            'participant_id',
            'participant',
            'id',
            'CASCADE'
        );
        
        $this->addForeignKey(
			'fk-puf_user',
            'participant_user_flag',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropForeignKey('fk-puf_user',
            'participant_user_flag');
		$this->dropForeignKey('fk-puf_participant',
            'participant_user_flag');
		$this->dropTable('participant_user_flag');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181102_184114_add_table_participant_user_flag cannot be reverted.\n";

        return false;
    }
    */
}
