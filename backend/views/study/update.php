<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Study */

$this->title = 'Edit Study: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Studies', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="study-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
