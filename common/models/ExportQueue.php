<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "export_queue".
 *
 * @property int $id
 * @property int $created_by
 * @property string $status
 * @property string $item_ids
 * @property string $filepath
 * @property int $created_at
 * @property int $updated_at
 * @property int $study_id
 * @property string $options_json
 *
 * @property User $createdBy
 */
class ExportQueue extends \yii\db\ActiveRecord
{
	const STATUS_NEW = "new";
	const STATUS_PROCESSING = "processing";
	const STATUS_FINISHED = "finished";
	const STATUS_ERROR = "error";
	
	
	private $_options;
	
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'export_queue';
    }

	/**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_by', 'status'], 'required'],
            [['created_by', 'created_at', 'updated_at', 'study_id'], 'integer'],
            [['item_ids', 'filepath', 'export_type', 'progress_msg', 'item_type'], 'string'],
            [['status'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['study_id'], 'exist', 'skipOnError' => true, 'targetClass' => Study::className(), 'targetAttribute' => ['study_id' => 'id']],
            ['options_json', 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Created By',
            'status' => 'Status',
            'item_ids' => 'Item Ids',
            'filepath' => 'Filepath',
            'export_type' => 'Export Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'progress_msg' => 'Progress Message',
            'item_type' => 'Item Type',
            'study_id' => 'Study ID',
            'options' => 'Options',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    
    public static function getNewlyEnqueuedItem()
    {
	    return ExportQueue::find()->where(['status' => ExportQueue::STATUS_NEW])->orderBy('created_at asc')->one();
    }
    
    public function markProcessing()
    {
	    $this->status = ExportQueue::STATUS_PROCESSING;
	    return $this->save();
    }
    
    public function markFinished()
    {
	    $this->status = ExportQueue::STATUS_FINISHED;
	    return $this->save();
    }
    
    public function markError()
    {
	    $this->status = ExportQueue::STATUS_ERROR;
	    return $this->save();
    }
    
    public function setProgressMessage($message)
    {
	    $this->progress_msg = $message;
	    return $this->save();
    }
    
   public function getOptions()
   {
	   return $this->_options;
   }
   
   public function setOptions($options)
   {

	   $this->options_json = json_encode($options);
	   $this->_options = $options;
   }
   
   public function afterFind()
   {
	   parent::afterFind();
	   $this->refreshOptions();
   }
   
   public function afterRefresh()
   {
	   parent::afterRefresh();
	   $this->refreshOptions();
   }
   
   private function refreshOptions()
   {
	   if($this->options_json != null)
	   {
		   $this->_options = json_decode($this->options_json, true);
	   }
   }
    
}
