<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\AuthItemRuleGrant;

$auth = Yii::$app->authManager;
$groupId = str_replace(" ", "_", $group_name);

$this->registerJsFile(Url::base() . '/js/change_background.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);

?>
<label class='group-permission' style="color: white;"><?= $group_name; ?></label>
<div id="<?= $groupId; ?>" class="collapse in">
<?php foreach($permission_list as $p): ?>
	<?php if(array_key_exists($p, $permissions)): ?>
	<?php
		$permission = $auth->getPermission($p);
		$rule = AuthItemRuleGrant::getGrantRuleForAuthItem($permission);
		$isPermissionSelected = in_array($p, $model->permissions);
		$labelOptions = [];
		{	$grantListId = "grants_" . $p;
			if($rule != null)
			$labelOptions["data-target"] = "#" . $grantListId;
			$labelOptions["data-toggle"] = "collapse";
		}
	?>
	<div class="role-permit">
		<?= Html::checkbox("RoleForm[permissions][]", $isPermissionSelected, [ "value" => $p, "class" => 'role-checkbox', 'id' => $grantListId ]); ?>
		<label class='role-label' for='<?php echo $grantListId ?>'><span><?php echo $permissions[$p]  ?></span></label>
	</div>
	<!-- <?php
		// If we have grants to show for this permission, create a dropdown and list the available options
		if($rule != null):
			$grantOptions = $rule->grantOptions();
			$grantHtmlName = "RoleForm[grants][" . $rule->name . "][]";
			$hiddenName = "RoleForm[grants][" . $rule->name . "]";
		?>
		<div id="<?= $grantListId; ?>" class="col-sm-12 permission_grant_list collapse <?= $isPermissionSelected ? "in" : "";?>">
			<label class="cursor" data-target="#<?= $grantListId . $rule->name; ?>" data-toggle="collapse" aria-expanded="true"><?= $rule->description; ?>: <span class="icon-toggle-chevron-blue icon-small"></span></label>
			    <?= Html::hiddenInput($hiddenName); ?>
			    <div id="<?= $grantListId . $rule->name; ?>" class="collapse in">
			<?php foreach($grantOptions as $option): 
				$isSelected = isset($model->grants[$rule->name]) && in_array($option, $model->grants[$rule->name]);
			?>
			<?= Html::checkbox($grantHtmlName, $isSelected, ["value" => $option, "label" => $option ]); ?>
			<?php endforeach; ?>
				<br />
		    </div>

		</div>
		
		<?php endif; ?> -->
		<hr class="permission-break"/>

	<?php endif; ?>
<?php endforeach; ?>
</div>
<br />