<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\UserForm */

$studies = $model->getStudies();
$roles = $model->getRoles();


$this->registerJsFile(Url::base() . '/js/staff_form.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
$this->registerJsFile(Url::base() . '/js/change_background.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);

?>

<div class="user-create">
    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
		<div class="row">
			<div class="col-sm-4">	 
				<?= $form->field($model, 'first_name') ?>
				<?= $form->field($model, 'last_name') ?>
				<?= $form->field($model, 'email') ?>
			</div>
		</div>
		<hr/>

	    <div id="user-roles">
		    <?php foreach($model->auth_roles as $i => $role): ?>
		    
			    <?= $this->render('auth_role_form', ['model' => $role, 'index' => $i, 'form' => $form, 'roles' => $roles, 'studies' => $studies]); ?>
		   <?php endforeach; ?>
	    </div>
	   
	    <div class="row">
			<span id="user-form-add-study" class="cursor text-blue" data-url="<?= Url::to("/staff/render-role-form", true); ?>" >Add Another Study <span class="icon-add-solid_blue" style="vertical-align: middle; margin-left: 10px;"></span></span>
		</div>
		<hr/>
	    
	    <div class="row">
		    <div class="col-sm-4">
		
		        <div class="form-group button-list">
			        <!-- <?php $buttonLabel = $model->scenario == 'create' ? 'Create User' : 'Update'; ?> -->
		            <?= Html::submitButton('Submit', ['class' => 'button-blue', 'name' => 'signup-button']) ?>
		            <a href="<?= Url::to('/staff', true); ?>" class="button-light">Cancel</a>
		        </div>
		    </div>
	    </div>

    <?php ActiveForm::end(); ?>
</div>
