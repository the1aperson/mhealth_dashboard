<?php
	
	use yii\helpers\Url;
	
	$filterModel = $filterModel ?? null;
	$additionalParams = $additionalParams ?? [];
	$modal = $modal ?? "site";
	$permissionParams = $permissionParams ?? null;
	$modalId = $modalId ?? "export-data-modal";
	$buttonStyle = $buttonStyle ?? "blue";

	// Different modals may have different possible permissions associated with them.
	// These different permissions allow them to view different export options.
	// If we've been given a list of permissions to check, check each until we find 
	// one that the current user has.
	// They need to have at least one permission in order to see the Export button.
		
	$permissions = Yii::$app->params['export_modal_permissions'][$modal] ?? ["viewTestData"];

	$scopeOptions = [];		
	$hasPermission = false;
	foreach($permissions as $permission)
	{
		if(Yii::$app->user->can($permission))
		{
			$hasPermission = true;
			$permittedScopes = Yii::$app->params['export_scope_permissions'][$permission];
			foreach($permittedScopes as $scope)
			{
				$scopeOptions[$scope] = Yii::$app->params['export_scope_descriptions'][$scope];
			}
		}
	}
	
	if($hasPermission == false)
	{
		return;
	}
	
	$selectedScope = array_keys($scopeOptions)[0];
?>

<?php if($buttonStyle == "blue"): ?>
<div id="header-export-button">
	<span data-toggle="modal" data-target="#<?= $modalId; ?>" class="button-blue">Export<span class='icon-download_white'></span></span>	
</div>
<?php else: ?>
	<a data-toggle="modal" href="#<?= $modalId; ?>"><span class="icon-download_blue" data-html="true" data-toggle="tooltip" data-placement="top" title="Export Study Data"></span></a>
<?php endif; ?>

<?php $this->beginBlock('modal-export-data'); ?>
<?= $this->render('export_modal', ['scopeOptions' => $scopeOptions, 'selectedScope' => $selectedScope, 'filterModel' => $filterModel, 'additionalParams' => $additionalParams, 'modalId' => $modalId]); ?>
<?php $this->endBlock(); ?>