<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "study".
 *
 * @property int $id
 * @property string $name
 * @property int $start_date
 * @property int $end_date
 * @property int $created_at
 * @property int $updated_at
 * @property integer $status

 * @property StudyMetadata[] $studyMetadatas
 * @property StudyUser[] $studyUsers
 */
class Study extends AuditableModel
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'study';
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
	        
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['name', 'required', 'message' => 'Study Name cannot be blank.'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],

            // try to format the incoming start_date and end_date values. If they're a unix timestamp, we need to
            // convert them to a date format.
            
            [['start_date', 'end_date'], 'filter', 'filter' => function($value){
	            if(is_numeric($value))
	            {
		            return date("n/j/Y g:i A", $value);
	            }
	            return $value;
            }],
            // validate the date and overwrite start and end date with the unix timestamp
            ['start_date', 'datetime', 'format' => 'php:n/j/Y g:i A', 'timestampAttribute' => 'start_date'],
            ['end_date', 'datetime', 'format' => 'php:n/j/Y g:i A', 'timestampAttribute' => 'end_date'],
            ['start_date', 'required', 'message' => 'Start Date cannot be blank.'],
            ['end_date', 'required', 'message' => 'End Date cannot be blank.'],

            // validation for start date and end date
            ['end_date', function ($attribute, $model, $validator){
                $start = $this->start_date;
                $end = $this->end_date;
                if ($start != null && $end != null && $end < $start){
                    $this->addError($attribute, 'End date cannot precede start date.');
                }
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudyMetadatas()
    {
        return $this->hasMany(StudyMetadata::className(), ['study_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudyUsers()
    {
        return $this->hasMany(StudyUser::className(), ['study_id' => 'id']);
    }

    public function getStatusLabel()
    {
        if($this->status == Study::STATUS_ACTIVE)
        {
            return "Active";
        }
        return "Inactive";
    }
    /*
            
    Since we don't want to actually delete studies, we have to override this and
    just change the status.
        
    */
    public function delete()
    {
        $this->status = Study::STATUS_DELETED;
        return $this->save();

    }
        
    public function undelete()
    {
        $this->status = Study::STATUS_ACTIVE;
        return $this->save();
    }
}