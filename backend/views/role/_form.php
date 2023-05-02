<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model backend\models\RoleForm */
/* @var $form yii\widgets\ActiveForm */
/* @var $permissions */
$this->registerJsFile(Url::base() . '/js/change_background.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);

?>

<div class="role-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
	    <div class="col-sm-6">
		<?php if($model->scenario == "update"): ?>
		<!-- <div class="form-group">
			<label class="control-label">Name: <?= $model->name; ?></label>
		</div> -->
		<?php else: ?>
	    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
		<?php endif; ?>
	    <!-- <?= $form->field($model, 'description')->textarea(); ?> -->
	    </div>
    </div>

	    <?php $field = $form->field($model, 'permissions'); ?>

	    <div id="roleform-permissions" aria-required="true" class="row">
	    <?= $field->begin(); ?>
		    <?php foreach(Yii::$app->params['staff_permission_settings']['permission_groups'] as $group_name => $permission_list): ?>
		    <?= $this->render("_form_permission_list", ["group_name" => $group_name, "permission_list" => $permission_list, "permissions" => $permissions, "model" => $model]); ?>
			<?php endforeach; ?>
			<?= $field->end(); ?>
	    </div>



    <div class="form-group button-list">
		<hr/>
		<button type="button" data-target="#role-modal" data-toggle="modal" class="button-blue">Save</button>
        <a href="<?= Url::to('/roles', true); ?>" class="button-light">Cancel</a>
    </div>
	<?php Pjax::begin(['linkSelector' => false, 'clientOptions' => ['history' => false]]); ?>
		<?= $this->render('role_modal',['model' => $model,'permissions' => $permissions, 'form' => $form]); ?>
	<?php Pjax::end(); ?>
    <?php ActiveForm::end(); ?>

</div>
