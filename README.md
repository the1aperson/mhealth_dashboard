Setup and Installation
-----------------

1. Download composer [https://getcomposer.org/download/](https://getcomposer.org/download/)
2. Install dependencies `composer install`
3. Initialize Yii `./init`
4. Create database and db user
5. Update common/config/main-local.php with database information
6. Create RBAC tables `./yii migrate --migrationPath=@yii/rbac/migrations`
7. Run other migrations `./yii migrate`
   During this, you will be asked to setup an email and password for the dashboard admin user. Don't forget your password.
8. Setup study-specific data in common/config/studyDefinitions.php

If you're setting up a non-local server, you will likely need to make the following changes as well:

9. Override the '@exports' alias to point to a tmp folder `'@exports' => sys_get_temp_dir() . "/temp_exports",` for example,
   in common/config/main-local.php
10. Set `adminEmail` and  `supportEmail` in common/config/params-local.php
11. Set the application name (like `'name' => 'Arc Core Dashboard'`) in common/config/main-local.php
12. Configure VirtualHost entries for Apache
13. set the `hostInfo` property on the `urlManager` component for frontend/config/main-local.php, backend/config/main-local.php, and console/config/main-local.php, like:
```
'urlManager' => [
	'hostInfo' => '[base url goes here]',
],
```
   The url for console should just be the backend url.
   
14. Make sure the application's directories and files are accessible by the web server:
   - Figure out what group the web server runs the application as (probably 'apache', or 'web' or something like that)
   - cd to the application directory
   - `chgrp -R <group name> .` Recursively changes the group associated with all of the files and directories 
   - `chmod -R g+w .` Recursively grants the write permission to the group
   - `chmod g-w .git/objects/pack/*` Git pack files should be immutable
   - `find -type d exec chmod g+s {} +` Sets the group id of all application directories (this means that any new files and subdirectories will inherit that group id)


####Other Configuration Things

Some other configurable parameters that you might need to know:
- You can change the expiration time of password reset tokens with 'user.passwordResetTokenExpire'
- On Development installations, you can toggle 'allow_duplicate_devices' to make the server not care about duplicate device tokens

The Yii debug module throws a lot of unnecessary warnings, which make viewing the logs kind of annoying.
- You can disable the Yii debug bar by removing the config section for it in main-local.php
- If you want to keep the debugger bar available, but just keep it from spitting warnings, you can add `'disableIpRestrictionWarning' => true`


Defining Study-specific Data
-----------------


### StudyDefinitions

`common\studyDefinitions\StudyDefinition`

The Study Definitions component houses all study-specific details. These details are primarily used for providing context for the metadata displayed in the dashboard.
This can be accessed by `Yii::$app->studyDefinitions`, and is configured in `common/config/studyDefinitions.php`.

#### Configuration Parameters:
- test_types
   An associative array of shorthand types and full names, like  
   [  
      'cognitive' => 'Cognitive Test'  
   ]
   The type keys will be used to reference different test types.
   
- test_formatters
   Maps test types to data formatters, which should be subclasses of `common\dataFormatters\BaseDataFormatter`. 
   Example:  
   [  
	   'cognitive' => 'common\dataFormatters\CognitiveFormatter'  
   ]

- test_exporters
  Map of different export types, which are subclasses of common\dataExporters\BaseDataExporter.  
  Example:  
  [  
	  'csv' => 'common\dataExporters\CSVExporter'  
  ]
  
- study_schedule
   Array of configuration options for `common\studyDefinitions\StudySection`
   
- participant_id_rules
   These are rules that will get used by forms when setting up new participants. Currently the only used values are `min` and `max` for defining the length of a participant_id.
- participant_password_rules
   These are rules that will get used by forms when setting up new participants. Currently the only used values are `min` and `max` for defining the length of a password.


#### Methods

- testTypes()
   Returns the array keys of test_types
   
- testTypeLabel($type)
   Returns the associated label for the given $type.
   
- StudySection()
   Returns the array of `StudySection` objects.
   
- getTodaysStudySection($first_test_date, $today = null)
   Determines the $day value to call `getStudySectionForDay()`, based on the days between the given $first_test_date and today's date.  
   If you want to change the reference point of "today", pass in a timestamp of the day you want it to be.  
   Returns the `common\studyDefinitions\StudySection` object corresponding
   
- getStudySectionForDay($day)
   Returns the `common\studyDefinitions\StudySection` object corresponding to the given $day.


### Study Section

`common\studyDefinitions\StudySection`

A StudySection can be thought of as one section or phase of a study. It contains information about the starting day and length of the section, as well as which tests are expected to be taken. This information is used mostly for displaying metadata in the dashboard.

#### Configuration Parameters:

- name
   A human-readable value, used mostly for display purposes.

- start
   Number of days that this section or phase starts after the previous section ends.  
   A value of 0 means that it begins the next day. For example, if the first section runs 3 days, and the second has a `start` value of 0, it will begin on day 4.
   
- length
   Number of days that this section runs.
   
- tests
   Array of configuration options for `common\studyDefinitions\TestDefinition`


### Test Definition

`common\studyDefinitions\TestDefinition`

A TestDefinition defines a test's frequency within a given `StudySection`. They're not meant to be a general definition of a test type, which the name might imply.

##### Configuration Parameters:

- type
	A test type, as described in `StudyDefinitions` above.
	
- frequency
   The number of times a day this test is taken.
   
- frequency_label
   A human-readable description of the test's frequency.




Formatting and Exporting Data
-----------------------------

(TODO: This needs to be rewritten)


Roles and Permissions
---------------------

The Roles and Permissions system is based on Yii's [Role Based Access Control (RBAC)](https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#rbac), with several small additions to allow for more granular control. Most of these additions are in the form of RBAC Rules.

The core RBAC is defined by several database tables:
- auth_item
- auth_item_child
- auth_rule
- auth_assignment

These handle the basic job of assigning users to Roles, and pairing Roles with Permissions.

The permissions are listed in `common/config/permissionSettings.php`, and are added to the authManager on initial setup of the application.


### Additional Rules

#### CanViewStudy

`common\rules\StudyRule`

This rule is applied to all Roles, and controls the association between a User and the role (or roles) they've been assigned for a Study. It uses the database table `study_user_auth` to store this association between a User, a Study, and a Role.


#### CanAssignRole, CanRemoveUsers, CanViewUsers

`common\rules\BaseGrantRule`

These three rules are all based on the same core idea. This Rule is applied to a Permission, and controls what roles a given Role can view/modify/assign. It uses the database table `auth_role_rule_grant` to store this association between a Rule, an assigned Role, and the roles to which it is granted access. The naming conventions used in this are less than ideal, but I've tried to name them as clearly as I can to hopefully reduce ambiguity. A user is granted access to the `granted_role` if they are assigned the `assigned_role`, and are attempting to access an action that requires a permission that uses the `assigned_rule`.

For instance, if we're creating or updating a Role, the permission option  "View CMS Users" displays an additional drop-down when selected. This drop-down is a list of other roles that, when selected, grant the given Role access to. So a user with the assigned Role would then be able to see other users with the granted roles.


### Controlling Available Permissions

As mentioned above, `common/config/permissionSettings.php` holds the list of all permissions that have been defined for this dashboard, but we don't always need or want all of these permissions to be accessible when creating or updating a Role. To achieve this, we use a table called `auth_available_permissions` to control what permissions are available. Doing it in this way ensures that we don't have to add or remove permissions from the core RBAC's `auth_item` table, and risk losing information about rule assignments, or creating SQL foreign key issues (a brief sidenote: there is a known issue with Yii's RBAC tables, the `auth_item` foreign key which relates the `rule_name` column to the `auth_rule` table is set to cascade on Update, so it's basically impossible to remove a permission or role's rule once it's been set).

To change what permissions are available, you'll first need to make sure that in `backend/config/params-local.php`, you've set `enable-update-permissions` to true. Then, you need to login to the dashboard with the siteAdmin account, and go to `/update-available-permissions`.


### Adding/Removing Permissions

### General Notes

Creating or removing permissions mostly involve modifying the `permissionSettings.php` file, and enabling/disabling the permission with the `/update-available-permissions` page. If it can be avoided, you should try not to make modifications to permissions or roles through Migrations. In my experience, whenever I tried to use a migration to update permissions, it solved the problem for any existing installations, but made initializing new installations more complicated. Some of this is unavoidable, such as adding a permission that is associated with a Rule.

If you're performing a somewhat complicated action that needs to be repeated on several existing installations, there is a console action for performing "temporary" migrations (migrations that don't get added to the `migration` db table) called `arc-manager/run-temp-migration`.

#### Adding New Permissions

Adding a new permission to the system is fairly straightforward. You have to define the new permission, make use of it in the dashboard code, enable it as an available permission, and then assign it to one or more roles.

- You first need to add the permission to `common/config/permissionsSettings.php`. Make sure to add it to the `all_permissions` array, add a description for in in `permission_descriptions`, and add it to one of the groups in `permission_groups`. If you want this permission to be available to the siteAdmin in future installations, also add it to the `admin_permissions` array.

- Then you need to make use of it in code somewhere. This is not strictly necessary of course, if you're simply adding permissions with the intent of using them later.

- Then, if you're updating an existing installation, you'll need to go to `/update-available-permissions` and enable it.

- Finally, assign it to one or more Roles.

#### Removing Permissions

If you want to permanently remove a permission from the application, you'll need to remove references to it from the dashboard code, and you'll first need to disable it from the `/update-available-permissions` page before removing it from `permissionSettings.php`. Trying to do it the other way around will just remove the option from displaying, but the permission will remain assigned to any roles that already have it. You could also use a Migration to automate removing it from  any roles that have it assigned.