<?php
	
	use yii\helpers\Html;
	use yii\helpers\Url;

?>
<div class="row user-role-item">
  <div class="col-sm-4">
    <?= $form->field($model, "[$index]study_id")->dropdownList($studies,['prompt'=>'Select a Study', 'class'=>'form-control select-studies'])->label('Study'); ?>    
  </div>
  <div class="col-sm-4">    
    <a class="role-compare" target="_blank" href="<?= Url::to("/roles", true); ?>">VIEW ROLE COMPARISON</a>
    <?= $form->field($model, "[$index]auth_item_name")->dropdownList($roles,['prompt'=>'Select a Role', 'class'=>'form-control select-roles'])->label('Role'); ?>
  </div>
    <div id="col-sm-1">
      <span style="display:none;"class="user-role-item-remove icon-close_blue cursor"></span>
      </div>
</div>