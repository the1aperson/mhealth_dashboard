<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use common\models\AuthAvailablePermissions;

/* @var $this yii\web\View */
/* @var $roles yii\rbac\Role[] */

// $dataProvider = new ArrayDataProvider([
// 'allModels' => $roles]);

// $this->title = 'Roles';
$auth = Yii::$app->authManager;
$totalUsers = 0;
$permissionCount = 0;

foreach($roles as $r => $role){
	$users[$r] = count($auth->getUserIdsByRole($r));
	$totalUsers += $users[$r];
}
?>
<?php $this->beginBlock('header-content'); ?>
	<button class="back-button" id="roles-back" onclick="goBack()"><a class="icon-arrow-left_blue"></a>BACK</button>
	<h1 class='page-title'>Dashboard Roles</h1>
	<p class="button-list pull-right" id="roles-buttons">
		<?= Html::a('New Role<span class="icon-add-outline_white" id="new-role-icon"></span>', ['create'], ['class' => 'button-blue', 'id' => 'new-role-button']) ?>
	</p>
<?php $this->endBlock(); ?>

<div class="study-index">

<div class="section" id="role-container">
		<p class="section-header">Permissions Per Role</p>
		<div id="role-grid">
			<table class="roles-table">
				<thead>
					<tr class="role-row">
						<th></th>
						<?php foreach ($roles as $role):?>
							<?php if($role->name != 'siteAdmin'): ?> 
								<th class="table-role"><?= $role->name; ?><br/>
									<?= Html::a('EDIT', ['update', 'id' => $role->name], ['class' => 'role-edit border-right']) ?>
									<?= Html::a('DELETE', ['delete', 'id' => $role->name], [
										'class' => 'role-delete',
										'data' => [
											'confirm' => 'Are you sure you want to delete this role? Any users associated with this role will lose their privileges.',
											'method' => 'post',
										],
									]) ?></th>

							<?php endif;?>
						<?php endforeach;?>
					</tr>
				</thead>
				<tbody>
					<tr class="role-white">
						<td class="table-permission">Number of Users</td>
						<?php foreach($roles as $r => $role):?>
							<?php $users[$r] = count($auth->getUserIdsByRole($r)); ?>
							<td class="role-count"><?= $users[$r]; ?></td>
						<?php endforeach;?>
					</tr>
					<tr class="role-row">
						<td class="table-permission">Percentage of Total Users</td>
						<?php foreach($roles as $r => $role):?>
							<?php $users[$r] = count($auth->getUserIdsByRole($r)); ?>
							<td class="role-count"><?= number_format((($users[$r]/$totalUsers) * 100));  ?>%</td>
						<?php endforeach;?>
					</tr>

					<?php foreach(Yii::$app->params['staff_permission_settings']['permission_groups'] as $group_name => $permission_list): ?>
						<tr class="group-name">
							<td class="group-label"><?= rtrim($group_name, 's'); ?> Permissions</td>
							<?php foreach($roles as $role):?>
								<td class="group-label"></td>
							<?php endforeach;?>
						</tr>
							 <?php foreach($permission_list as $p): ?>
                                <tr class="<?= $permissionCount % 2 == 0 ? "role-white" : "role-row"; ?>">
								<?php if(array_key_exists($p, $permissions)): ?>
									<td class="table-permission"><?= $permissions[$p]; ?></td>
								<?php endif; ?>
								<?php foreach($roles as $role):?>
										<?php $role_permits = $auth->getPermissionsByRole($role->name);
										if(array_key_exists($p,$role_permits )):?>
											<td class="role-check"><span class="icon-checkmark-only_blue"></span></td>
										<?php else:?>
										<td class="role-check"></td>
										<?php endif; ?>
								<?php endforeach;?>
						        </tr>
                            <?php $permissionCount += 1; ?>
				        <?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
			</table>	
		</div>
	</div>
	<!-- <hr/> -->

</div>
