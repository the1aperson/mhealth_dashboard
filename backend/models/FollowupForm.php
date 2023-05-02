<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\Alert;

/**
 * Follow Up form
 */
class FollowupForm extends Model
{
    public $alert_id;
    public $email;
    public $phone_call;
    public $text;
	
	public $no_followup;
	public $password;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {	    
        return [

			['alert_id', 'required'],
			['alert_id', 'exist', 'targetClass' => Alert::class, 'targetAttribute' => ['alert_id' => 'id']],    
    
			['password', 'required'],
			['password', function ($attribute, $params, $validator){
				$user = Yii::$app->user->getIdentity();
				if($user == null || $user->validatePassword($this->password) == false)
				{
					$this->addError($attribute, 'Invalid Password');
				}
			}],
			
			[['email', 'phone_call', 'text', 'no_followup'], 'boolean'],
			['text',  function ($attribute, $params, $validator){
				if($this->email == 0 && $this->phone_call == 0 && $this->text == 0)
				{
					$this->addError($attribute, 'Please select at least one method.');
				}
			}],
			
        ];
    }
    
    public function save($user_id)
    {
	    $alert = Alert::findOne($this->alert_id);
	    
	    $followUpMethods = [];
	    if($this->email)
	    {
		    $followUpMethods[]= "Emailed";
	    }
	    if($this->phone_call)
	    {
		    $followUpMethods[]= "Called";
	    }
	    if($this->text)
	    {
		    $followUpMethods[]= "Texted";
	    }
	    
	    if(count($followUpMethods) > 1)
	    {
	    	$followUpMethods[count($followUpMethods) - 1] = "& " . $followUpMethods[count($followUpMethods) - 1];
	    }
	    
	    $message = implode(", ", $followUpMethods);
	    
	    return $alert->markFollowup($user_id, $message, time());
    }

	public function noFollowup($user_id, $alert_id)
	{
	    $alert = Alert::findOne($alert_id);
		if($this->no_followup)
		{
			$message = 'No Follow Up Needed';
			return $alert->markNoFollowup($user_id, $message, time());
		} 
		else
		{
			$alert->requires_follow_up = false;
			return $alert->markNoFollowup($user_id = null, $message = null, null);
		}
	}
    
}
