<?php

use yii\db\Migration;

/**
 * Class m190131_173925_add_thoughts_of_death_table
 */
class m190131_173925_add_thoughts_of_death_table extends Migration
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
		
        $this->createTable('participant_thoughts_of_death', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer()->notNull(),
			'value' => $this->integer(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
            'UNIQUE KEY (participant)',
        ], $tableOptions);

		$this->addForeignKey(
			'fk-participant_thoughts_of_death-participant',
            'participant_thoughts_of_death',
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
        $this->dropForeignKey('fk-participant_thoughts_of_death-participant',
            'participant_thoughts_of_death');
        
        $this->dropTable('participant_thoughts_of_death');

        return true;
    }

}
