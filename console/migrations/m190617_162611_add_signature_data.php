<?php

use yii\db\Migration;

/**
 * Class m190617_162611_add_signature_data
 */
class m190617_162611_add_signature_data extends Migration
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
        
         $this->createTable('signature_data', [
            'id' => $this->primaryKey(),
			'participant' => $this->integer(),
			'device' => $this->integer(),
			'blob_data' => "MEDIUMBLOB",
			'md5_hash' => $this->string(),
			'raw_type' => $this->integer(),
            'created_at' => $this->integer(11)->notNull(),
        ], $tableOptions);
        
        $this->addForeignKey(
			'fk-signature_data-participant',
            'signature_data',
            'participant',
            'participant',
            'id',
            'NO ACTION'
        );
        
		$this->addForeignKey(
			'fk-signature_data-device',
            'signature_data',
            'device',
            'participant_device',
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
			'fk-signature_data-participant',
            'signature_data'
        );
        
		$this->dropForeignKey(
			'fk-signature_data-device',
            'signature_data'
        );
        
		$this->dropTable('signature_data');


        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190617_162611_add_signature_data cannot be reverted.\n";

        return false;
    }
    */
}
