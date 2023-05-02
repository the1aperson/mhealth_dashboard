<?php
namespace console\optionalMigrations;

use Yii;
use yii\db\Migration;
use yii\db\Query;
use common\components\RBACHelper;
use common\models\AuthAvailablePermissions;

class add_view_note_permission extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {	    	    
	    RBACHelper::extendNewPermission("viewParticipants", "viewParticipantNotes", Yii::$app->params['staff_permission_settings']['permission_descriptions']["viewParticipantNotes"]);	
	    AuthAvailablePermissions::addPermission("viewParticipantNotes");
    }
    

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		echo "There's nothing to undo for this migration.\n";
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190221_173655_temp_add_new_participant_permissions cannot be reverted.\n";

        return false;
    }
    */
}
