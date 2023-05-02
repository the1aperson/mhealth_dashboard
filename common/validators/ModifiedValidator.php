<?php

namespace common\validators;

use Yii;
use yii\db\Query;
use yii\validators\Validator;



class ModifiedValidator extends Validator
{
	/**
     * @var string the name of the ActiveRecord class that should be used to validate the attribute value. 	
     * @see targetAttribute
     */
    public $targetClass;
    /**
     * @var string|array the name of the [[\yii\db\ActiveRecord|ActiveRecord]] attribute that should be used to
     * validate the attribute value. If not set, it will use the name
     * of the attribute currently being validated. You may use an array to validate the uniqueness
     * of multiple columns at the same time. The array values are the attributes that will be
     * used to validate the uniqueness, while the array keys are the attributes whose values are to be validated.
     */
    public $targetAttribute = "updated_at";
    
    /**
     * @var string|array|\Closure additional filter to be applied to the DB query used to check the uniqueness of the attribute value.
     * This can be a string or an array representing the additional query condition (refer to [[\yii\db\Query::where()]]
     * on the format of query condition), or an anonymous function with the signature `function ($query)`, where `$query`
     * is the [[\yii\db\Query|Query]] object that you can modify in the function.
     */
    public $filter;
    
    /**
     * @var string the user-defined error message.
     *
     * When validating single attribute, it may contain
     * the following placeholders which will be replaced accordingly by the validator:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * When validating mutliple attributes, it may contain the following placeholders:
     *
     * - `{attributes}`: the labels of the attributes being validated.
     * - `{values}`: the values of the attributes being validated.
     */
    public $message;
    
    
    
    public function init()
    {
        parent::init();
        if ($this->message !== null) {
            return;
        }

        $this->message = Yii::t('yii', '{attribute} has been modified since the last viewing.');
    }
    
    
    public function validateAttribute($model, $attribute)
    {
        /* @var $targetClass ActiveRecordInterface */
        $targetClass = $this->targetClass;
        $targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;
        
        $unmaskedValue = Yii::$app->security->unmaskToken($model->$attribute);
        $query = $targetClass::find();
        $query->where([">", $targetAttribute, $unmaskedValue]);
        
        if ($this->filter instanceof \Closure) 
        {
            call_user_func($this->filter, $query);
        } 
        else if ($this->filter !== null) 
        {
            $query->andWhere($this->filter);
        }
                
        $count = $query->count();
        
        if($count > 0)
        {
	        $this->addError($model, $attribute, $this->message);
        }
        
    }
    
    public static function getTimestamp($time = null)
    {
	    $time =  strval($time ?? time());
	    
	    return Yii::$app->security->maskToken($time);
    }
	
}