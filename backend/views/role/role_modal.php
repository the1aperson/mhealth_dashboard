<?php
	use yii\helpers\Url;
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
    use backend\models\RoleForm;
    use yii\widgets\Pjax;

    $this->registerJsFile(Url::base() . '/js/role.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
    $this->registerJsFile(Url::base() . '/js/show_password.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
?>
<div class="modal fade" id='role-modal' tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

                <div class="modal-header">
                    <p class="modal-title">Enter Password to Save</p>	        
                </div>
                <div id="role-modal-main" class="collapse in no-transition">

                        <div class="modal-body">
                            <p>Please <b>enter your password to confirm</b> your edits to this role.</p>
                            <p>Editing this role will <b>change dashboard access for all users</b> currently assigned this role.  Each user will receive an email notifying them that there have been changes.</p>
                            <div class="row">
                                <div class="col-sm-6">
                                <?= $form->field($model, 'password', ['template' => "<div class='password_reveal'><p class='show_password' onclick='role_password()'>Show</p><span id='eye-icon' class='icon-visibility-on_blue' onclick='role_password()'></span></div>{label}{input}{error}"])->passwordInput()->label("Password"); ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="button-list pull-left">
                                    <?= Html::submitButton('Save Changes', ['class' => 'button-blue', 'id' => 'role-button']) ?>
                                    <button type="button" class="button-light" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
            </div>

        </div>

    </div>
</div>

