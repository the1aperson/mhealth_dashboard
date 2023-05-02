<?php

namespace console\controllers;

use yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;

use backend\models\RoleForm;
use common\models\UserAuditTrail;

/**
 * This controller will contain actions to help in the process of managing an Arc installation
 *
 */
class ArcManagerController extends \yii\console\Controller
{
	
	public function beforeAction($action)
	{
		UserAuditTrail::addAuditLog('view', $action->uniqueId);
		return parent::beforeAction($action);
	}
	
	//! Importing Roles
	
	public function actionImportRoles($filepath = null)
	{
		$auth = Yii::$app->authManager;
		$example_json = <<<EOT
{
	"Role Name" : [
		"description" : "A description of the role (optional)",
		"permissions" : [
			"permission 1",
			"permission 2",
			...
		],
		"grants" (optional) : {
			"rule name" : [
				"Role name",
				"AUTH_ALL_ROLES" (if you want to let this role apply the given rule to all roles)
			],
			...
		}
	},
	...
}
EOT;
		// Check to make sure the file actually exists, and that it's valid json
		if($filepath == null || file_exists($filepath) == false)
		{
			$this->stdout("This action requires a filepath to a settings json file, following the format:\n");
			
			echo $example_json;
			echo "\n";
			return;
		}
		
		$json_string = file_get_contents($filepath);
		$roles_to_create = json_decode($json_string, true);
		if($roles_to_create == null)
		{
			$this->stdout("There was an error parsing the provided settings file. Double check that it is a valid json format\n");
			return;
		}


		// Now, create a RoleForm for each Role, and validate it.
		// If a role of the same name already exists, warn the user about it.

		$this->stdout("Validating Roles...\n");
		
		if(array_key_exists("siteAdmin", $roles_to_create))
		{
			$this->stdout("Error: You cannot update siteAdmin from this command!\n\n Import cancelled.\n");
			return;
		}
		
		$forms = [];
		foreach($roles_to_create as $role_name => $role_info)
		{
			$form = new RoleForm();
			$form->attributes = $role_info;
			$form->name = $role_name;
			$form->scenario = 'create';
			
			$maybeExistingRole = $auth->getRole($role_name);
			if($maybeExistingRole != null)
			{
				$this->stdout("Role $role_name already exists!\n");
				$this->stdout("Modifying this role will overwrite all existing settings for that Role.\n");
				if(Console::confirm("Are you sure you want to overwrite Role $role_name ?") == false)
				{
					$this->stdout("Role import cancelled. Please remove $role_name from your settings file if you want to continue creating the remaining Roles.\n");
					return;
				}
				
				$form->scenario = 'update';
			}
			
			// If we get an invalid Role, let's just bail.
			
			if($form->validate() == false)
			{
				$this->stdout("Error validating role $role_name :\n");
				print_r($form->getErrors());
				return;
			}
			
			$this->stdout("Role $role_name is valid, with the following settings:\n");
			print_r($role_info);
			
			$forms [$role_name] = $form;
			echo "\n";
		}
		
		// One last confirmation before we create the roles.
		
		if(Console::confirm("Confirm creating/updating the above Roles?") == false)
		{
			$this->stdout("Role import cancelled. No Roles have been created or modified.\n");
			return;
		}
		
		$this->stdout("Creating " . count($forms) . " Roles...\n");
		foreach($forms as $role_name => $form)
		{
			$role = $form->save();
			if($role == false)
			{
				$this->stdout("Error creating Role $role_name: ");
				print_r($form->getErrors());
				return;
			}
		}
		
		$this->stdout("Success!\n");
	}
	
	
	//! Exporting Roles
	
	public function actionExportRoles($filepath = null)
	{
		$auth = Yii::$app->authManager;
		
		if($filepath == null)
		{
			$this->stdout("This action requires a filepath to where you want Roles exported.\n");
		}
		
		$pathInfo = pathinfo($filepath);
		
		if(file_exists($filepath))
		{
			if(Console::confirm("File " . $pathInfo['filename'] . " already exists. Overwrite it? ") == false)
			{
				$this->stdout("Role export cancelled.\n");
				return;
			}
		}
		
		$roles = $auth->getRoles();
		echo "\n\n";
		
		$exportRoles = [];
		foreach($roles as $role)
		{
			if($role->name == "siteAdmin")
			{
				continue;
			}
			
			$this->stdout("Adding Role '" . $role->name . "'\n");	
			$form = new RoleForm();
			$form->preload($role->name);
			$newRole = $form->attributes;
			unset($newRole['name']);
			$exportRoles[$form->name] = $newRole;
		}
		
		$json = json_encode($exportRoles, JSON_PRETTY_PRINT);
		if(file_put_contents($filepath, $json) === false)
		{
			$this->stdout("There was an error saving data to '$filepath'.\n");
		}
		else
		{
			$this->stdout("Successfully wrote " . count($exportRoles) . " Roles to '$filepath'.\n");
		}
		
	}
	
	//! Importing Participant Ids
	
	/*
		Takes a tab-delimited list of participant ids and passwords
	*/
	
	public function actionImportParticipants($study_id, $pid_filepath, $delimter = "\t")
	{
		if(is_file($pid_filepath) == false)
		{
			$this->stdout("filename '$pid_filename' is invalid.\n");
			return;
		}
		
		$study = \common\models\Study::findOne($study_id);
		if($study == null)
		{
			$this->stdout("Could not find a study with id $study_id \n");
			return;
		}
		
		$pids = file($pid_filepath);
		
		$this->stdout("Validating participant ids... \n");
		
		$pids_to_create = [];
		
		// Validate that the given ids match the format we want, and 
		// check that they're not already taken.
		foreach($pids as $pid)
		{
			$pid_split = explode($delimter, trim($pid));
			if(count($pid_split) != 2 || !is_numeric($pid_split[0]) || !is_numeric($pid_split[1]))
			{
				$this->stdout("participant id '$pid' is not formatted correctly!\n");
				return;
			}
			
			$participant_id = trim(strval($pid_split[0]));
			$password = trim(strval($pid_split[1]));
			
			// check if the participant already exists
			$form = new \backend\models\ParticipantForm();
					
			$pData = [
				"study" => $study->id,
				"participant_id" => $participant_id,
				"password" => $password,
			];

			$form->attributes = $pData;
			
			if($form->validate())
			{
				$pids_to_create[$participant_id] = $password;				
			}
			else
			{
				$this->stdout("Error validating participant $participant_id:\n");
				print_r($form->getErrors());
				return;
			}

		}
		
		$count = count($pids_to_create);
		
		// Display the list of id's we're going to create, and ask the user to confirm.
		
		$this->stdout("Creating $count participants for study " . $study->name . ":\n");
		$col = 0;
		$colCount = min(4, ceil(count($pids_to_create) / 25));
		foreach($pids_to_create as $participant_id => $password)
		{
			$this->stdout("$participant_id");
			$col += 1;
			if($col == $colCount)
			{
				$col = 0;
				$this->stdout("\n");
			}
			else
			{
				$this->stdout("\t");
			}
		}
		$this->stdout("\n");
		if(Console::confirm("Confirm creating the above participants:") == false)
		{
			$this->stdout("Participant creation cancelled.\n");
			return;
		}	
		
		
		Console::startProgress(0, $count, "Creating participants");
		$i = 0;
		$success = 0;
		$error = 0;
		
		foreach($pids_to_create as $participant_id => $password)
		{
			$form = new \backend\models\ParticipantForm();
					
			$pData = [
				"study" => $study->id,
				"participant_id" => strval($participant_id),
				"password" => strval($password),
			];

			$form->attributes = $pData;
			$participant = $form->createParticipant();
			if($participant == null)
			{
				$this->stdout("Error creating participant '$participant_id': \n");
				print_r($form->getErrors());
				$error += 1;
			}
			$i += 1;
			$success += 1;
			Console::updateProgress($i, $count);
		}
		
		Console::endProgress();
		
		$this->stdout("Successfully created $success participants\n");
		if($error > 0)
		{
			$this->stdout("There were $error errors while creating participants. Scroll up to look for the errors that happened.\n");
		}
		
		
	}
	
	//! Run temporary migrations.
	// These migrations are not added to Yii's migration table.
	// This is only meant to be used if you're absolutely sure that you need to run a complicated action,
	// but only on some installations.
	
	public function actionRunOptionalMigration($migration_name = null, $upOrDown = "up", $namespace = 'console\optionalMigrations')
	{
		if($migration_name == null)
		{
			$this->stdout("Pass the migration name!\n");
			return;
		}
		
		$migration = Yii::createObject($namespace . '\\' . $migration_name);
		if($migration == null)
		{
			$this->stdout("Couldn't create object $migration_name\n");
			return;
		}
		$confirmAction = $upOrDown == "up" ? "Apply" : "Revert";
		if(Console::confirm("$confirmAction $migration_name?") == false)
		{
			$this->stdout("Migration cancelled.\n");
			return;
		}
		
		
		if($upOrDown == "up")
		{
			$this->stdout("Running migration $migration_name...\n");
			
			if($migration->up() === false)
			{
				$this->stdout("Migration failed.\n");
			}
		}
		else
		{
			$this->stdout("Reverting migration $migration_name...\n");
			if($migration->down() === false)
			{
				$this->stdout("Migration revert failed.\n");
			}
		}

	}
}