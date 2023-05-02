<?php
namespace frontend\tests\unit\models;
// https://phpunit.de/manual/6.5/en/appendixes.assertions.html
// https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/start-testing.md

use Yii;
use GuzzleHttp\Client;
use common\models\Participant;

class RestRegistrationTest extends \Codeception\Test\Unit
{
	private $http;
	private $registration_data;
	
	public function _before()
	{
		$this->registration_data = require codecept_data_dir() . 'registration_data.php';
		
        $this->http = new Client(['base_uri' => 'http://localhost:6010/']);
	}
	
    
    public function _after() {
        $this->http = null;
    }
    

	// Normal registation
	
    public function testDeviceRegistration()
    {
	    $response = $this->http->request('POST', 'device-registration', ['json' => $this->registration_data]);
	    $this->assertEquals(200, $response->getStatusCode());
	    $response_data = json_decode($response->getBody(), true);
	    $this->assertEquals(true, $response_data["response"]["success"]);
    }

	// Re-registering an existing ID

    public function testDeviceRegistrationConflict()
    {
	    try
	    {
	    	$response = $this->http->request('POST', 'device-registration', ['json' => $this->registration_data]);
	    	$this->assertTrue(false, ['message' => 'This http request should have resulted in a RequestException.']);
	    }
	    catch (\GuzzleHttp\Exception\RequestException $e) 
	    {
		    if ($e->hasResponse()) 
		    {
		        $response = $e->getResponse();
		        $this->assertEquals(409, $response->getStatusCode());
        	    $response_data = json_decode($response->getBody(), true);
			    $this->assertEquals(false, $response_data["response"]["success"]);
			    $this->assertArrayHasKey('participant_id', $response_data["errors"]);
		    }
	    }
    }
    
    // Re-registering, with override flag set to true
    
    public function testDeviceRegistrationOverride()
    {
	    $registration_data = $this->registration_data;
	    $registration_data["override"] = true;
	    
	    $response = $this->http->request('POST', 'device-registration', ['json' => $registration_data]);
	    $this->assertEquals(200, $response->getStatusCode());
	    $response_data = json_decode($response->getBody(), true);
	    $this->assertEquals(true, $response_data["response"]["success"]);
    }
	
	// Valid participant_id but invalid authorization_code
    
    public function testDeviceRegistrationInvalidAuthCode()
    {
	    $this->checkBadRegParameter("authorization_code", "66666", 401, "participant_id");
    }
    
    // invalid participant_id, but valid authorization_code
    
    public function testDeviceRegistrationInvalidParticipantId()
    {
	    $this->checkBadRegParameter("participant_id", "66666", 401);
	}
    
    //! Testing missing parameters
    
    public function testDeviceRegistrationMissingParticipantId()
    {
		$this->checkBadRegParameter('participant_id', null, 400);
    }
    
    
    public function testDeviceRegistrationMissingAuthCode()
    {
	    $this->checkBadRegParameter('authorization_code', null, 400);
    }
    
    
    public function testDeviceRegistrationMissingDeviceId()
    {
	    $this->checkBadRegParameter('device_id', null, 400);
    }
    
    
    public function testDeviceRegistrationMissingDeviceInfo()
    {
	    $this->checkBadRegParameter('device_info', null, 400);
    }
    
    
    public function testDeviceRegistrationMissingAppVersion()
    {
	    $this->checkBadRegParameter('app_version', null, 400);
    }
    
    //! Testing invalid inputs, values that are of the wrong type
    
    public function testDeviceRegistrationBadParticipantId()
    {
		$this->checkBadRegParameter('participant_id', 11111, 400);
    }
    
    
    public function testDeviceRegistrationBadAuthCode()
    {
	    $this->checkBadRegParameter('authorization_code', 11111, 400);
    }
    
    
    public function testDeviceRegistrationBadDeviceId()
    {
	    $this->checkBadRegParameter('device_id', 8675309, 400);
    }
    
    
    public function testDeviceRegistrationBadDeviceInfo()
    {
	    $this->checkBadRegParameter('device_info', 12.1, 400);
    }
    
    
    public function testDeviceRegistrationBadAppVersion()
    {
	    $this->checkBadRegParameter('app_version', 3.5, 400);
    }

    
    private function checkBadRegParameter($parameter, $bad_value, $expected_status, $check_param = null)
    {
	    $registration_data = $this->registration_data;
		
		if($bad_value == null)
		{
			unset($registration_data[$parameter]);
		}
		else
		{
			$registration_data[$parameter] = $bad_value;			
		}
		$check_param = $check_param ?? $parameter;
	
	    try
	    {
		    $response = $this->http->request('POST', 'device-registration', ['json' => $registration_data]);
	    	$this->assertTrue(false, 'This http request should have resulted in a RequestException.');
		}
		catch (\GuzzleHttp\Exception\RequestException $e)
		{
			if ($e->hasResponse()) 
		    {
		        $response = $e->getResponse();
		        $this->assertEquals($expected_status, $response->getStatusCode());
        	    $response_data = json_decode($response->getBody(), true);
			    $this->assertEquals(false, $response_data["response"]["success"]);
			    $this->assertArrayHasKey($check_param, $response_data["errors"]);
		    }
		}
    }
}
