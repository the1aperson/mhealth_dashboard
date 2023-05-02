
<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\PermissionForm */

$this->title = 'Update Permissions';
?>


<div class="permission-form">
	
	<div class="bg-danger container">
		<div class="row">
			<div class="col-sm-12">
			<h2>This is a very destructive action.<br /><br />Be sure you only add or remove EXACTLY what you intend to.<br /><br />
				Removing a permission will immediately revoke it from every Role and User.<br /><br />
			</h2>
			</div>
		</div>
	</div>
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
	    <div class="col-sm-6">
			<?= $form->field($model, 'permission_names')->checkboxList($model->getPermissionList(), ["separator" => "<br />", "encode" => false
			]); ?>
	    </div>
    </div>

    <div class="form-group button-list">
        <?= Html::submitButton('Save', ['class' => 'button-blue', 'data-confirm' => "Are you ABSOLUTELY CERTAIN that you know what you're doing? Removing permissions will immediately revoke this permission from every user."]); ?>
        <a href="<?= Url::to('/roles', true); ?>" class="button-light">Cancel</a>
    </div>

    <?php ActiveForm::end(); ?>

</div>
