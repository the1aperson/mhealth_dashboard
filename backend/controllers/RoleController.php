<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use backend\models\RoleForm;


class RoleController extends AuditableController
{
	
	public function behaviors()
    {
	    $behaviors = parent::behaviors();
        
		$behaviors['access'] = [
            'class' => \yii\filters\AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'permissions' => ['modifyRoles'],
                ],            
            ]
        ];
        
        return $behaviors;
    }
	
	public function actionIndex()
	{
		$auth = Yii::$app->authManager;
		
		$roles = $auth->getRoles();
		$permissions = $this->getPermissionList();
		if(isset($roles['siteAdmin']))
		{
			unset($roles['siteAdmin']);
		}
		
		return $this->render("index", ['roles' => $roles, 'permissions' => $permissions]);
	}
	
	public function actionView($id)
	{
		if($id == 'siteAdmin')
		{
			return $this->redirect('/roles');
		}
		$auth = Yii::$app->authManager;
		
		$role = $auth->getRole($id);
		$permissions = $auth->getPermissionsByRole($id);
		return $this->render("view", ['role' => $role, 'permissions' => $permissions]);
	}
	
	public function actionCreate()
	{
		$auth = Yii::$app->authManager;
		$permissions = $this->getPermissionList();
		
		$model = new RoleForm();
		$model->scenario = "create";
		if($model->load(Yii::$app->request->post()) && $model->validate())
		{
			$role = $model->save();
			if($role != false)
			{
				return $this->redirect(['/role/view', 'id' => $model->name]);
			}
		}
		
		return $this->render("create", ['model' => $model, 'permissions' => $permissions]);
	}
	
	public function actionUpdate($id)
	{
		if($id == 'siteAdmin')
		{
			return $this->redirect('/roles');
		}
		
		$auth = Yii::$app->authManager;
		$permissions = $this->getPermissionList();
		
		$model = new RoleForm();
		$model->scenario = "update";
		
		$role = $auth->getRole($id);
		if($role == null)
		{
			return $this->redirect("/role");
		}
		
		if($model->preload($id) && $model->load(Yii::$app->request->post()) && $model->validate())
		{
			$role = $model->save();
			if($role != false)
			{
				return $this->redirect(['/role/view', 'id' => $model->name]);
			}
		}
		
		return $this->render("update", ['model' => $model, 'permissions' => $permissions]);
	}
	
	public function actionDelete($id)
	{
		if($id == 'siteAdmin')
		{
			return $this->redirect('/roles');
		}
		
		$auth = Yii::$app->authManager;
		$userId = Yii::$app->user->getId();
		$userRoles = $auth->getRolesByUser($userId);
		
		
		if(array_key_exists($id, $userRoles))
		{
	        Yii::$app->session->setFlash('warning', 'You cannot delete a role that is assigned to you.');
			return $this->redirect("/role");
		}
		
		$role = $auth->getRole($id);
		$auth->remove($role);
		return $this->redirect("/role");
	}
	
	
	private function getPermissionList()
	{
		$permissions = [];
        $available_permissions = \common\models\AuthAvailablePermissions::getAvailablePermissionNames();
        
        $descriptions = Yii::$app->params['staff_permission_settings']['permission_descriptions'];
        foreach($available_permissions as $p)
        {	
			if(array_key_exists($p, $descriptions) == false)
			{
				continue;
			}
		    $permissions[$p] = $descriptions[$p];
        }
        return $permissions;	
    }
}