<?php

use yii\db\Migration;

/**
 * Class m181008_205347_add_study_tables
 */
class m181008_205347_add_study_tables extends Migration
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

		// setup Alert table
		
        $this->createTable('study', [
            'id' => $this->primaryKey(),
			'name' => $this->string(),
			'start_date' => $this->integer(11),
			'end_date' => $this->integer(11),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
                
        $this->createTable('study_user_auth', [
            'id' => $this->primaryKey(),
			'user_id' => $this->integer(),
			'study_id' => $this->integer(),
			'auth_item_name' => $this->string(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        
        $this->createTable('study_metadata', [
            'id' => $this->primaryKey(),
			'study_id' => $this->integer(),
			'name' => $this->string(),
			'value' => $this->string(),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
        ], $tableOptions); 
        
        $this->addColumn('participant', 'study_id', $this->integer()->notNull());
        
        $this->addForeignKey(
			'fk-study_user_auth-user',
            'study_user_auth',
            'user_id',
            'user',
            'id',
            'NO ACTION'
        );
        
        $this->addForeignKey(
			'fk-study_user_auth-study',
            'study_user_auth',
            'study_id',
            'study',
            'id',
            'NO ACTION'
        );
        
        $this->addForeignKey(
			'fk-study_user_auth-auth_item',
            'study_user_auth',
            'auth_item_name',
            'auth_item',
            'name',
            'CASCADE'
        );
        
        
        $this->addForeignKey(
			'fk-study_metadata-study',
            'study_metadata',
            'study_id',
            'study',
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
			'fk-study_metadata-study',
            'study_metadata');
		$this->dropForeignKey(
			'fk-study_user-study',
            'study_user');
            
        $this->dropForeignKey(
			'fk-study_user-user',
            'study_user');
        $this->dropForeignKey(
			'fk-study_user_auth-study',
            'study_user_auth');
            
		$this->dropTable('study_metadata');
		$this->dropTable('study_user_auth');
		$this->dropTable('study');
		
		$this->dropColumn('participant', 'study_id');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181008_205347_add_study_tables cannot be reverted.\n";

        return false;
    }
    */
}
