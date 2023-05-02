<?php

use yii\db\Migration;
use yii\db\Query;


/**
 * Class m190114_192735_workaround_android_test_finished_session
 */
class m190114_192735_workaround_android_test_finished_session extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    // This is a workaround for a bug in the Android app, that doesn't always mark finished_session correctly.
		// In both apps, missed_session is only set to 0 if the participant completed the test, so in reality, finished_session
		// probably isn't needed. The two shouldn't actually have the same value.

		$androidUsers = new Query();
		
		$androidUsers->select("participant")->from("participant_device")
		->where(["active" => 1])
		->andWhere(["os_type" => "Android"]);
		
		$test_session_query = new Query();
		$test_session_query->select("test_data_id, participant")->distinct()->from("participant_test_session")
		->where(["participant" => $androidUsers])
		->andWhere("test_data_id IS NOT NULL")
		->andWhere("start_date IS NOT NULL")
		->andWhere([">", "start_date", 0])
		->andWhere(["completed" => 0]);
		
		$testSessionIds = $test_session_query->all();

		echo "Found " . count($testSessionIds) . " test sessions to update...\n";
		$participantsToUpdate = [];
		
		foreach($testSessionIds as $testSession)
		{
			$test_data_id = $testSession["test_data_id"];
			$participant_id = $testSession["participant"];
			
			$blob_data = (new Query())->select("blob_data")->from("test_session_data")->where(["id" => $test_data_id])->andWhere(['participant' => $participant_id])->one();
			
			$blob_data = $blob_data["blob_data"];
			$jsonData = json_decode($blob_data, true);
			
			if(isset($jsonData["missed_session"]) && $jsonData["missed_session"] == 0)
			{
				echo "Updating test_data_id $test_data_id, participant_id $participant_id \n";
				$this->execute("UPDATE participant_test_session SET completed = 1 WHERE participant = :participant AND test_data_id = :test_data_id", [":participant" => $participant_id, ":test_data_id" => $test_data_id]);
				
				$participantsToUpdate []= $participant_id;
			}
		}
		
		$participantsToUpdate = array_unique($participantsToUpdate);
		
		echo "Found " . count($participantsToUpdate) . " participants to update metadata...\n";
		foreach($participantsToUpdate as $participant_id)
		{
			Yii::$app->participantMetadataHandler->updateAdherence($participant_id);
		}	
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190114_192735_workaround_android_test_finished_session cannot be reverted.\n";

        return false;
    }
    */
}
