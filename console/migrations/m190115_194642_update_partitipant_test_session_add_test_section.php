<?php

use yii\db\Migration;

use yii\db\Query;
/**
 * Class m190115_194642_update_partitipant_test_session_add_test_section
 */
class m190115_194642_update_partitipant_test_session_add_test_section extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
 		$this->addColumn("participant_test_session", "study_section", $this->integer());
		
		$query = (new Query())->select('id, session_identifier')->from('participant_test_session')->limit(100)->offset(0);
		$offset = 0;
		
		// first, update session_identifier
		
		$this->execute("UPDATE participant_test_session SET session_identifier = SUBSTRING_INDEX(session_identifier, '-', 1)");
		
		$previous_end = 0;
		$studySections = Yii::$app->studyDefinitions->studySchedule();
		
		foreach($studySections as $s => $section)
		{
			$start = $previous_end + $section->start;
			$length = $section->length;
			// Like a 0-based array, the length here would put the end past the end of this section. Instead of subtracting 1, though,
			// we need to make our comparison non-inclusive (< instead of <=). We can't subtract one because it will mess up other assumptions
			// made about the resulting value of $end.
			$end = $start + $length;
			
			echo "start: $start end: $end\n";
			$this->execute("UPDATE participant_test_session SET study_section = :section_id, updated_at = :updated_at WHERE ((week * 7) + day) >= :begin AND ((week * 7) + day) < :end", [":section_id" => $s, ":begin" => $start, ":end" => $end, ":updated_at" => time()]);

			$previous_end = $end;
		}
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {	
		
		$this->dropColumn("participant_test_session", "study_section");
		$this->execute("UPDATE participant_test_session SET session_identifier = CONCAT(session_identifier, '-', type)");
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190115_194642_update_partitipant_test_session_add_test_section cannot be reverted.\n";

        return false;
    }
    */
}
