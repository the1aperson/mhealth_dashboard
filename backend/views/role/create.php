<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\RoleForm */
/* @var $permissions */

$this->title = 'Create Role';
?>
<div class="new-role-header">
	<button class="back-button" onclick="goBack()"><a class="icon-arrow-left_blue"></a>BACK</button>
	<h2 class='page-title'>New Role</h2>
</div>

<div class="role-create">

    <?= $this->render('_form', [
        'model' => $model,
        'permissions' => $permissions,
    ]) ?>

</div>
