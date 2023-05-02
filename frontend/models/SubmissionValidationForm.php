<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/*

This form is really just used to make sure that the submitted data has these basic fields.
Pretty much all of the API endpoints expect that these fields will exist, so this form can be
used to simplify catching errors in missing or mis-named data.	
	
*/


class SubmissionValidationForm extends Model
{
    public $participant_id;
    public $device_info;
    public $app_version;
    public $device_id;
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
			[['participant_id', 'device_info', 'app_version', 'device_id'], 'required'],
			[['participant_id', 'device_info', 'app_version', 'device_id'], 'string'],
			['device_info', function ($attribute, $params, $validator){
				$infoParts = explode("|", $this->$attribute);
				// We just need to make sure the device_info has at least 3 parts to it.
				// iOS sends "iOS"|model no|OS version
				// Android send some additional information: "Android|"+name+"|"+ Build.VERSION.RELEASE+"|"+Build.VERSION.SDK_INT+"|"+Build.FINGERPRINT;
				
				if(count($infoParts) < 3)
				{
					$this->addError($attribute, 'device_info improperly formatted.');
				}
			}],
        ];
    }
        
}