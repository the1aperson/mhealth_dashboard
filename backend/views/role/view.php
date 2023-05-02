<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $role yii\rbac\Role */
/* @var $permissions yii\rbac\Permission[] */

$this->title = $role->name;
?>
<div class="study-view">

    <p>
        <?= Html::a('Update', ['update', 'id' => $role->name], ['class' => 'button-blue']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $role->name], [
            'class' => 'button-light',
            'data' => [
                'confirm' => 'Are you sure you want to delete this role? Any users associated with this role will lose their privileges.',
                'method' => 'post',
            ],
        ]) ?>
    </p>

	<p><b>Description:</b><br /><?= $role->description; ?></p>
	<p>
		<b>Permissions:</b><br />
		
		<div class="col-sm-6">
		    <?php foreach(Yii::$app->params['staff_permission_settings']['permission_groups'] as $group_name => $permission_list): ?>
			<label><?= $group_name; ?></label><br />
			<ul>
				<?php foreach($permission_list as $p): ?>
					<?php if(array_key_exists($p, $permissions)): ?>
					<li><span><?= $permissions[$p]->description; ?></span></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		    <?php endforeach; ?>
	    </div>
	</p>

</div>
