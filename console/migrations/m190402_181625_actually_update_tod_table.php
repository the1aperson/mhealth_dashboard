<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Class m190402_181625_actually_update_tod_table
 */
class m190402_181625_actually_update_tod_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {		
		$participant_ids = (new Query())->select('max(id) as id, participant')->from('participant_thoughts_of_death')->groupBy('participant')->all();
		
		foreach($participant_ids as $pid_pairing)
		{
			$pid = $pid_pairing["participant"];
			$id = $pid_pairing["id"];

			$this->execute("DELETE FROM participant_thoughts_of_death WHERE participant = :participant AND id < :id", [":participant" => $pid, ":id" => $id]);
		}
		
		// Drop the foreign key, delete the index, and then recreate it all.
		
        $this->dropForeignKey('fk-participant_thoughts_of_death-participant',
            'participant_thoughts_of_death');
 		$this->execute("DROP INDEX participant on participant_thoughts_of_death");
		$this->execute("ALTER TABLE participant_thoughts_of_death add unique `participant` (`participant`)");
		
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
	    // We don't really need to do anything here, because the original migration for this table has been updated to only create the one unique key.
        echo "m190402_181625_actually_update_tod_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190402_181625_actually_update_tod_table cannot be reverted.\n";

        return false;
    }
    */
}
