<?php
namespace frontend\tests\unit\models;
// https://phpunit.de/manual/6.5/en/appendixes.assertions.html
// https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/start-testing.md
// http://docs.guzzlephp.org/en/stable/

use Yii;
use GuzzleHttp\Client;
use common\models\Participant;

class RestTestSubmitTest extends \Codeception\Test\Unit
{

	private $http;
	private $registration_data;
	private $test_session_data;
	
    public function setUp()
    {
        $this->http = new Client(['base_uri' => 'http://localhost:6010/']);
        $this->registration_data = require codecept_data_dir() . 'registration_data.php';
        $this->test_session_data = require codecept_data_dir() . 'test_session_data.php';
    }
    
    public function tearDown() {
        $this->http = null;
    }

	public function testSuccessfulSubmit()
	{
		
		$json = $this->test_session_data;
		
	    try
	    {	
		    $json = json_encode($this->test_session_data);
		    $response = $this->http->request('POST', 'submit-test', ['body' => $json, 'headers' => [
			    'content-type' => 'application/json',
		    ],'query' => ['device_id' => $this->registration_data['device_id']]]);
		    
    	    $response_data = json_decode($response->getBody(), true);
	        $this->assertEquals(200, $response->getStatusCode());
	        
	        $our_md5 = md5($json);
	        $this->assertEquals($our_md5, $response_data["response"]["md5"]);
	        
		}
		catch (\GuzzleHttp\Exception\RequestException $e)
		{
			if ($e->hasResponse()) 
		    {
		        $response = $e->getResponse();
		        $this->assertEquals(401, $response->getStatusCode());
        	    $response_data = json_decode($response->getBody(), true);
			    $this->assertEquals(false, $response_data["response"]["success"]);
			    $this->assertArrayHasKey('Unauthorized', $response_data["errors"]);
		    }
		}
	}

	public function testMismatchedParticipantId()
	{
		$this->checkBadParameter('participant_id', "66666", 401, 'Unauthorized');
	}


	public function testMismatchedDeviceId()
	{
		$json = $this->test_session_data;
		
	    try
	    {
		    $response = $this->http->request('POST', 'submit-test', ['json' => $json, 'query' => ['device_id' => "obviously_bad_device_id"]]);
	    	$this->assertTrue(false, 'This http request should have resulted in a RequestException.');
		}
		catch (\GuzzleHttp\Exception\RequestException $e)
		{
			if ($e->hasResponse()) 
		    {
		        $response = $e->getResponse();
		        $this->assertEquals(401, $response->getStatusCode());
        	    $response_data = json_decode($response->getBody(), true);
			    $this->assertEquals(false, $response_data["response"]["success"]);
			    $this->assertArrayHasKey('Unauthorized', $response_data["errors"]);
		    }
		}
	}
	
	public function testBadParticipantId()
	{
		$this->checkBadParameter('participant_id', 11111, 400, 'participant_id');
	}
	
	public function testBadDeviceId()
	{
		$this->checkBadParameter('device_id', 1234555, 400, 'device_id');
	}
	
	public function testMissingDeviceId()
	{
		$this->checkBadParameter('device_id', null, 400, 'device_id');
	}
	
	public function testMissingSessionDate()
	{
		$this->checkBadParameter('session_date', null, 400, 'session_date');
	}
	
	
    
    private function checkBadParameter($parameter, $bad_value, $expected_status, $check_param = null)
    {
	    $json = $this->test_session_data;
		
		if($bad_value == null)
		{
			unset($json[$parameter]);
		}
		else
		{
			$json[$parameter] = $bad_value;			
		}
		$check_param = $check_param ?? $parameter;
	
	    try
	    {
		    $response = $this->http->request('POST', 'submit-test', ['json' => $json, 'query' => ['device_id' => $this->registration_data["device_id"]]]);
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
