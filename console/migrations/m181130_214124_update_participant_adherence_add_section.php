<?php

use yii\db\Migration;

/**
 * Class m181130_214124_update_participant_adherence_add_section
 */
class m181130_214124_update_participant_adherence_add_section extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('participant_adherence', 'study_section', $this->string());
		$this->execute('update participant_adherence set study_section = :all', [":all" => "all"]);
		$this->execute("update participant_adherence set test_type = :all WHERE test_type = 'overall'", [":all" => "all"]);

		$this->addColumn('participant_test_finished_count', 'study_section', $this->string());
		$this->execute('update participant_test_finished_count set study_section = :all', [":all" => "all"]);

		$this->addColumn('participant_test_missed_count', 'study_section', $this->string());
		$this->execute('update participant_test_missed_count set study_section = :all', [":all" => "all"]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropColumn('participant_adherence', 'study_section');
		$this->dropColumn('participant_test_finished_count', 'study_section');
		$this->dropColumn('participant_test_missed_count', 'study_section');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181130_214124_update_participant_adherence_add_section cannot be reverted.\n";

        return false;
    }
    */
}
