API Definitions
---------------

### Some Parameter Definitions

Some new stuff that has been introduced since DIAN:

##### Test Sessions

A given test session may be comprised of multiple test types (for instance, the EMA and Cognitive tests are taken consecutively, and they're considered part of the same test session).
Aside from a test session's session_date, we also define the following values:

"session_id": (string) The session's index within the total count of sessions for the study. This is used to make sure that the applications and the server can easily agree on which sessions they're talking about.
"week" : (integer) 0-indexed week of the study that this session takes place in
"day" : (integer) 0-indexed day within the current week
"session" : (integer) 0-indexed session within the current day



### Endpoints

All data sent to, and received from, the API endpoints should be of content-type application/json. The general response structure for each endpoint is:

{
	"response": {
		"success": (boolean, true or false)
		(optionally, other parameters)
		...
	},
	"errors": {
		"some_parameter_name": [ "array of strings describing errors"],
		...
	}
}

The response parameter will always contain a "success" parameter, with a boolean true or false. If other data is expected to be returned by the endpoint, it will be in the response parameter as well.

If any errors occurred, the response code will be non-200, and the "errors" parameter will likely contain keys referencing the specific request parameter, and an array of strings describing the error.

Common Response Codes:
200 - Participant successfully registered

401 - Invalid Participant ID, Device ID, or Authorization Code
409 - Participant already has an Active Device
400 - Other errors, see the "errors" parameter


#### Device Registration

POST /device-registration

Expected request body:
{
	"participant_id": (string, the participant id entered by the user)
	"authorization_code": (string, the authorization code entered by the user)
	"device_id": (a unique id identifying this device)
	"device_info" : (a string with format "OS name|device model|OS version", ie "iOS|iPhone8,4|10.1.1")
	"app_version": (string, the version of the application)
	"override": (optional boolean, set to true if re-registering an existing user)
}

Response:
On success, simply returns a value of `true`.


#### Submitting Test Schedule

POST /submit-test-schedule
Query Parameters: device_id

Expected request body:

{ 
  "app_version" : (string, version of the app),
  "device_info": (a string with format "OS name|device model|OS version", ie "iOS|iPhone8,4|10.1.1")
  "participant_id" : (string, the user's participant id),
  "device_id" : (string, the unique id for this device),
"model_version" : "0", (string, the model version of this data object. For now, just set this to "0". Although this isn't used currently, if we ever need to make a meaningful change to the structure of this data, this will help us differentiate between versions)

  "sessions" : [] (an array of objects that define each session),
}

A session object is defined as:
{
	"session_id" : (string, an identifier for this specific session w/r/t the entire test. On iOS, we're just using the sessions "index", so to speak)
	"week" : (integer, 0-indexed week that this session takes place in),
	"day" : (integer, 0-indexed day within the current week),
	"session" : (integer, 0-indexed session within the current day),
	"session_date" : (a Time Interval, the  date/time when this session is scheduled to start),
	"types" : [] (an array of strings, indicating the test types taken during this session)
}

The list of sessions is expected to include all of the upcoming sessions for the participant, from the current moment until the end of the study.

Response:
On success, returns an "md5" field in the response object, with a calculated md5 hash of the received data.


#### Submitting Wake/Sleep Schedule

POST /submit-wake-sleep-schedule
Query Parameters: device_id

Expected request body:

{ 
  "app_version" : (string, version of the app),
  "device_info": (a string with format "OS name|device model|OS version", ie "iOS|iPhone8,4|10.1.1")
  "participant_id" : (string, the user's participant id),
  "device_id" : (string, the unique id for this device),
"model_version" : "0", (string, the model version of this data object. For now, just set this to "0". Although this isn't used currently, if we ever need to make a meaningful change to the structure of this data, this will help us differentiate between versions)

	......... and whatever we're currently sending in the DIAN version.
}


Response:
On success, returns an "md5" field in the response object, with a calculated md5 hash of the received data.

#### Submitting Test Data

POST /submit-test
Query Parameters: device_id

Expected request body:

{
  "session_id" : (string, an identifier for this specific session w/r/t the entire test. On iOS, we're just using the sessions "index", so to speak)
  "session_date" : (a Time Interval, the  date/time when this session is scheduled to start),
  "start_time": (optional) (a Time Interval, the date/time when the user began the test),
  "week" : (integer, 0-indexed week that this session takes place in),
  "day" : (integer, 0-indexed day within the current week),
  "session" : (integer, 0-indexed session within the current day),
  "finished_session" :  (1 or 0, whether or not the user finished the test),
  "missed_session" : (1 or 0, whether or not the user did not finish the test. Yes this is kind of redundant.),
  "model_version" : "0", (string, the model version of this data object. For now, just set this to "0". Although this isn't used currently, if we ever need to make a meaningful change to the structure of this data, this will help us differentiate between versions)
  "app_version" : (string, version of the app),
  "device_info": (a string with format "OS name|device model|OS version", ie "iOS|iPhone8,4|10.1.1")
  "participant_id" : (string, the user's participant id),
  "device_id" : (string, the unique id for this device),
  "tests" : [],	(An array of test data objects)
}

The test data objects are pretty arbitrary, the only common field that they share is a "type" field, which denotes what type of test it is (currently, "ema", "edna", "cognitive", or "mindfulness").

Response:
On success, returns an "md5" field in the response object, with a calculated md5 hash of the received data.





#### Device Heartbeat

POST /device-heartbeat
Query Parameters: device_id

Expected request body:
{
  "device_id" : (string, the unique id for this device),
  "participant_id" : (string, the user's participant id),
  "device_info": (a string with format "OS name|device model|OS version", ie "iOS|iPhone8,4|10.1.1")
  "app_version" : (string, version of the app),
}

Response:
On success, simply returns a value of `true`.


#### Retrieving Session Info

GET /get-session-info
Query Parameters: device_id


Response:
On success, returns two session objects named 'first_test' and 'latest_test'.

