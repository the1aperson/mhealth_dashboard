<?php

use yii\db\Migration;

/**
 * Class m181024_232215_add_export_queue_table
 */
class m181024_232215_add_export_queue_table extends Migration
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
		
        $this->createTable('export_queue', [
            'id' => $this->primaryKey(),
			'created_by' => $this->integer()->notNull(),
			'status' => $this->string()->notNull(),
			'test_data_ids' => "MEDIUMBLOB",
			'filepath' => $this->text(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        $this->addForeignKey('fk-export_queue-user',
        'export_queue',
        'created_by',
        'user',
        'id',
        'NO ACTION');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropForeignKey('fk-export-queue-user', 'export_queue');
		$this->dropTable('export_queue');
		

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181024_232215_add_export_queue_table cannot be reverted.\n";

        return false;
    }
    */
}
