<?php
	use yii\helpers\Url;
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
	use common\models\Participant;
	use kartik\widgets\DatePicker;
	use backend\models\ParticipantSearch;

	$model = $filterModel;
	// $model->enrolled_start = $enrolled_start;
	// $model->enrolled_end = $enrolled_end;
	/* @var $participant */
?>
<div class="modal fade" id="date-range-modal" tabindex="-1" role="dialog" aria-labelledby="date-range-modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="date-range-content">
	        <div class="modal-header">
				<h2>Dates of Participation: Choose Date Range</h2>
				<p>Please select the date range you'd like to filter by.</p>
	        </div>

	            <div id="date-range-main" class="collapse in no-transition">
	                <div class="modal-body">						
						<div class="row">
							<div class="col-sm-4">
						<p style="font-weight:bold">Start Date</p>
						<?= 			
						 DatePicker::widget([
							'model' => $model,
							'attribute' => 'enrolled_start',
							'layout' => "{picker}{input}{remove}",
							'options' => ['placeholder' => '_ _ /_ _ /_ _', 'class' => 'form-control', 'autocomplete' => 'off'],
							'pickerIcon' => '<i class="icon-calendar_white"></i>',
							'removeIcon' => '<i class="icon-close_blue remove-icon"></i>',
							'pluginOptions' => [
								'todayHighlight' => true,
								'format' => 'd M, yyyy'
							]
						]); ?>
						<br/>
						<p style="font-weight:bold">End Date</p>
						<?= 						
						 DatePicker::widget([
							'model' => $model,
							'attribute' => 'enrolled_end',
							'layout' => "{picker}{input}{remove}",
							'options' => ['placeholder' => '_ _ /_ _ /_ _', 'class' => 'form-control', 'autocomplete' => 'off'],
							'pickerIcon' => '<i class="icon-calendar_white"></i>',
							'removeIcon' => '<i class="icon-close_blue remove-icon"></i>',
							'pluginOptions' => [
								'todayHighlight' => true,
								'format' => 'd M, yyyy'
							]
						]); ?>
							</div>

						</div>
						<hr/>
						<div class="modal-footer">
							<div class="button-list">
								<?= Html::submitButton('Submit', ['class' => 'button-blue pull-left', 'id' => 'enrolled_submit']) ?>
								<button class="button-light pull-left" data-dismiss="modal">Cancel</button>
							</div>
						</div>
	                </div>
                </div>      
        </div>
    </div>
</div>
