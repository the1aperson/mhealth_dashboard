<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Study */

$this->title = 'New Study';
$this->params['breadcrumbs'][] = ['label' => 'Studies', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="study-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
