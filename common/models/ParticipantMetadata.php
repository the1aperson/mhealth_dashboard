<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "participant_metadata".
 *
 * @property int $id
 * @property int $participant
 * @property string $name
 * @property string $value
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Participant $participant0
 */
class ParticipantMetadata extends \yii\db\ActiveRecord
{
		
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'participant_metadata';
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
            [['participant'], 'integer'],
            [['name', 'value'], 'string', 'max' => 255],
            [['participant'], 'exist', 'skipOnError' => true, 'targetClass' => Participant::className(), 'targetAttribute' => ['participant' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'participant' => 'Participant',
            'name' => 'Name',
            'value' => 'Value',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

	
    public static function getMetadataForParticipant($participant_id, $associative = true)
    {
	    $metadataObjects = ParticipantMetadata::find()->where(['participant' => $participant_id])->all();
	    
	    $metadata = [];
	    
	    if($associative)
	    {
		    foreach($metadataObjects as $m)
		    {
			    $metadata[$m->name] = $m;
		    }
		    
		    return $metadata;
		}
		else
		{
			return $metadataObjects;
		}
		   
    }
    
    
    public static function updateMetadata($participant_id, $name, $value)
    {
	    $metadata = ParticipantMetadata::find()->where(['participant' => $participant_id, 'name' => $name])->one();
	    
	    if($metadata == null)
	    {
		    $metadata = new ParticipantMetadata();
		    $metadata->participant = $participant_id;
		    $metadata->name = $name;
	    }
	    
	    $metadata->value = strval($value);
	    return $metadata->save();
    }
    
    public static function getMetadata($participant_id, $name)
    {
	    return ParticipantMetadata::find()->where(['participant' => $participant_id, 'name' => $name])->one();
    }
    
    public static function incrementMetadata($participant_id, $name, $by = 1)
    {
	    $metadata = self::getMetadata($participant_id, $name);
	    if($metadata == null)
	    {
		    $metadata = new ParticipantMetadata();
		    $metadata->participant = $participant_id;
		    $metadata->name = $name;
		    $metadata->value = 0;
	    }
	    
	    $value = intval($metadata->value);
	    $value += $by;
	    
	    $metadata->value = strval($value);
	    return $metadata->save();
    }
    
    public static function decrementMetadata($participant_id, $name, $by = 1)
    {
	    return ParticipantMetadata::incrementMetadata($participant_id, $name, $by * -1);
    }
}
