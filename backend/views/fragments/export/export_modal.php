<?php
	use yii\helpers\Url;
	use yii\helpers\Html;
	
	$modalId = $modalId ?? "export-data-modal";

	$filterModel = $filterModel ?? null;
	$additionalParams = $additionalParams ?? [];
	
	$formatOptions = Yii::$app->params['export_types'];
	$selectedFormat = $formatOptions[0];
	
	$this->registerJsFile(Url::base() . '/js/export_modal.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
	
?>
<div class="modal fade export-data-modal" id="<?= $modalId; ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
		        <h2>Export Data</h2>
	        </div>
	        <div class="modal-body">
		        <div class="form-group form-radio-list">
					<label class="control-label">Scope</label>
					<?php foreach($scopeOptions as $val => $label): ?>
					<?php if($val == "filters"){ continue; } ?>
					<label><input type="radio" name="export-scope" value="<?= $val; ?>" <?= $val == $selectedScope ? 'checked=""' : ''; ?>><span class="form-radio-item"></span><?= $label; ?></label>
					
					<?php endforeach; ?>
					
					<?php if($filterModel != null && isset($scopeOptions["filters"])):
						$filterLabels = [];
						foreach($filterModel->attributes() as $attribute)
						{
				        
							if($attribute != "study_id" && isset($filterModel->$attribute) && $filterModel->$attribute !== "")
							{
								
								$filterLabels []= $filterModel->getFilterOptionName($attribute, true);
								
							}
						}
						if(count($filterLabels) > 0): ?>
						<label><input type="radio" name="export-scope" value="filters" <?= $selectedScope == "filters" ? 'checked=""' : ''; ?>><span class="form-radio-item"></span>As Shown With Filters:</label>
				        <div id="export-modal-filter-list">
					        <?php foreach($filterLabels as $label): ?>
					        	<span class="export-modal-filter-item rounded-filter-label"><?= $label; ?></span>
					        <?php endforeach; ?>
						</div>
						<div id="export-modal-filter-options">
						<?php 
							foreach($filterModel->attributes() as $attribute):
							if($attribute != "study_id" && isset($filterModel->$attribute) && $filterModel->$attribute !== ""): ?>
							<input type="hidden" name="filterOptions[<?= $attribute; ?>]" value="<?= $filterModel->$attribute; ?>" class="export-modal-additional-param" />
						<?php 
							endif;
						endforeach; ?>
						</div>
						<?php endif; ?>
			        <?php endif; ?>
		        </div>
		        
		        
		        
		        <div class="form-group form-radio-list">
					<label class="control-label">Format</label>
					<?php foreach($formatOptions as $val): ?>
					
					<label><input type="radio" name="export-format" value="<?= $val; ?>" <?= $val == $selectedFormat ? 'checked=""' : ''; ?>><span class="form-radio-item"></span><?= strtoupper($val); ?></label>
					
					<?php endforeach; ?>
		        </div>
		        
		        <div class="form-group">
			        <p class="text-error-red form-error"></p>
		        </div>
		        
		        <?php foreach($additionalParams as $key => $val): ?>
		        	<input type="hidden" name="<?= $key; ?>" value="<?= $val; ?>" class="export-modal-additional-param" />
		        <?php endforeach; ?>

	        </div>
	        <div class="modal-footer">
		        <div class="button-list">
		           <button data-url="<?= Url::to('/export-data', true); ?>" id="export-submit-button" type="button" class="button-blue pull-left">Export</button>
				   <button type="button" class="button-light pull-left" data-dismiss="modal">Cancel</button>
		       </div>
	        </div>
        </div>
    </div>
</div>
