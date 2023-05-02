<?php
	
	/* $filter */
	/* $model */
	
	$options = $filterModel->getFilterOptions($attribute, false);
	// $choice = $filterModel->getFilterChoice($attribute, false);
?>

<div class="search-filter-item">
	<p class="search-filter-title"><?= $displayName; ?></p>
	
	<div class="search-filter-bar">
		<?php
		$valueKeys = array_keys($options);
		$count_array = [];
		$value = null;
		$count = null;
		$sum = null;

		for($k = 0; $k < count($valueKeys); $k++): 
			$value = $valueKeys[$k];
			$count = $filterModel->getCount([$attribute => $value], Yii::$app->user->getId());
			array_push($count_array, $count); ?>
		<?php endfor; ?>
		
		<?php $sum = array_sum($count_array);
			
				for($i = 0; $i < count($valueKeys); $i++):
					if($sum != 0):
					$width = ($count_array[$i] / $sum) * 100; ?>
						<?php if($width == 100): ?>
						<div class="search-bar-option" style="width:<?= $width?>%; border-radius: 100px"></div>
						<?php else: ?>
						<div class="search-bar-option" style="width:<?= $width?>%"></div>
						<?php endif; ?>
					<?php else: 
						$width = (100/count($valueKeys));?>
						<div class="search-bar-option" style="width:<?= $width?>%"></div>
					<?php endif;?>
				<?php endfor; ?>
	</div>

	<?= $this->render('search_filter_options', ['options' => $options, 'attribute' => $attribute, 'filterModel' => $filterModel]); ?>

</div>
