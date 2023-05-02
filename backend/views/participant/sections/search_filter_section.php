<?php
	/* @var $filterModel */
	/*
	Adherence √
	Study Phase
	Last Seen √
	App Version √
	OS Version √
	Flags
	Enabled
	Installed
	Dates of Participation
	*/
	use yii\helpers\Url;
			
	$this->registerJsFile(Url::base() . '/js/search_filters.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
	$filters = $filterModel->getFilters();
	
	$skipDisplay = ["study_id"];
	$showFilterDropdown = false;
?>

<div class="section search-filter-section">
	<div class="search-filter-top">
		<div class="sf-top-left">
				
			<span class="section-header sf-header pull-left">Filters</span>
			<span class="sf-vr pull-left"></span>
			<span class="pull-left text-dark-gray">Filtering by:</span>
			<div class="search-filter-active-list pull-left">
				<?php $count = 0; ?>
				<?php foreach($filterModel->attributes as $attribute => $value): ?>
				<?php if(in_array($attribute, $skipDisplay) || $value == null || $value == ""){continue;} ?>
				<?php //$showFilterDropdown = true; ?>
				<span class="search-filter-active-list-item" data-attribute="<?= $attribute; ?>"> <?= $filterModel->getAttributeLabel($attribute); ?></span>
				<?php $count += 1; ?>
				<?php endforeach; ?>
				<?php $showHideClearAll = ($count > 0) ? "show-clear-all" : "hide-clear-all";   ?>
				<a class="<?= $showHideClearAll ?>" href="<?=Url::to('/participants', true); ?>">CLEAR ALL</a>
			</div>
		</div>
	    	
		<span class="pull-right section-filter-toggle" data-toggle="collapse" data-target="#search-filter-content" 
			aria-expanded="<?= $showFilterDropdown ? "true" : "false"; ?>"></span>
            
	</div>
	
	<div id="search-filter-content" class="search-filter-content clearfix collapse <?= $showFilterDropdown ? "in" : ""; ?>">
		<div class="search-filter-flex">
            <?php foreach($filters as $attribute => $displayName): ?>
            <?= $this->render('search_filter_item', ['attribute' => $attribute, 'displayName' => $displayName, 'filterModel' => $filterModel]); ?>
            <?php endforeach; ?>

            <?php /* enabled/installed filters */ ?>
            <div class="search-filter-item">
                <p class="search-filter-title">Enabled</p>
                
                <?= $this->render('search_filter_options', [
                    'filterModel' => $filterModel, 
                    'attribute' => 'enabled', 
                    'listClass' => 'filter-grouped',
                    'options' => ['' => 'All', '1' => 'Yes', '0' => 'No'] ]); ?>
                    
                <p class="search-filter-title">Installed</p>
                
                <?= $this->render('search_filter_options', [
                    'filterModel' => $filterModel, 
                    'attribute' => 'installed', 
                    'listClass' => 'filter-grouped',
                    'options' => ['' => 'All', '1' => 'Yes', '0' => 'No'] ]); ?>

                
			</div>
			<div class="search-filter-item">
					<p class="search-filter-title">Dates of Participation</p>
					<div class="edit-dates">
					<span class="start-date">Start: <p></p></span>
					<span class="end-date">End: <p></p></span>
					<span><a class="search-filter-edit" data-toggle="modal" href="#date-range-modal">EDIT TIMEFRAME</a><a class='date-clear' href="<?=Url::to('/participants', true); ?>">CLEAR</a></span>
				</div>
				<div class="choose-dates">
					<span class="search-filter-choose" data-toggle="modal" href="#date-range-modal">CHOOSE TIMEFRAME</span>
				</div>
					<?= $this->render('/participant/date_range_modal', ['dataProvider' => $dataProvider, 'filterModel' => $filterModel]); ?>
			</div>
        </div>
	</div>
	
	<div class="search-filter-bottom">
	</div>
</div>
