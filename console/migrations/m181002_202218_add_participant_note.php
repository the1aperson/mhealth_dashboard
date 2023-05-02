<?php

use yii\db\Migration;

/**
 * Class m181002_202218_add_participant_note
 */
class m181002_202218_add_participant_note extends Migration
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
		
        $this->createTable('participant_note', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer(),
			'created_by' => $this->integer(),
			'note' => $this->text(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        $this->addForeignKey(
			'fk-participant_note-participant',
            'participant_note',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
        $this->addForeignKey(
			'fk-participant_note-user',
            'participant_note',
            'created_by',
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
		'fk-participant_note-user',
            'participant_note');
        
        $this->dropForeignKey(
	        'fk-participant_note-participant',
            'participant_note');
            
		$this->dropTable("participant_note");

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181002_202218_add_participant_note cannot be reverted.\n";

        return false;
    }
    */
}
