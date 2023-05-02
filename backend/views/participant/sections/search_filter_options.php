<?php 
	/* @var $filterModel */
	/* @var $attribute */
	/* @var $options */
	/* @var $listClass */

	$listClass = isset($listClass) ? $listClass : "default-filter-list";	
?>
<ul class="search-filter-options <?= $listClass; ?>">
<?php 
	$valueKeys = array_keys($options);
	for($k = 0; $k < count($valueKeys) && $k < 3; $k++): ?>
	<?php
		$value = $valueKeys[$k];
		$label = $options[$value];
		$selected = ($filterModel->$attribute != '' && $filterModel->$attribute == $value) ? "selected" : ""; 	
//			$count = $filterModel->getCount([$attribute => $value], Yii::$app->user->getId());
	?>
	<li class="search-filter-option <?= $selected; ?>" data-value="<?= $value; ?>" data-attribute="<?= $attribute; ?>" >
		<span class="sfo-label"><?= $label; ?></span>
	</li>
<?php endfor; ?>
</ul>