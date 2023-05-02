<?php

use kartik\datetime\DateTimePicker;
use kartik\time\TimePicker;
use kartik\widgets\DatePicker;

use yii\helpers\BaseUrl;
use yii\helpers\Html;

/*
$form - form to add input
$model - the model for form
$name - the attribute name

$date - n/j/Y
$time - h:i
*/

$words = explode('_', $name);
$label = ucwords(join(' ', $words));

$className = $model::className();
$classParts = explode('\\', $className);
$class = lcfirst(array_pop($classParts));

$layout = <<< HTML
    {picker}
    {input}
    {remove}
HTML;

$showTime = isset($showTime) ? $showTime : true;

$this->registerJsFile(BaseUrl::base() . '/js/datetime.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD], '');
?>

<div class="datetime-widget form-group" data-class="<?= $class ?>" data-name="<?= $name ?>">

    <!-- hidden field - actual value used by model -->
    <div class="hidden-field">
        <?= $form->field($model, $name)->widget(DateTimePicker::classname(), [
                'removeButton' => false,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'mm/dd/yyyy H:ii P',
                    'showMeridian' => true
                ]
            ]
        ); ?>
    </div>

    <!-- displayed field - values displayed for input --->
    <div class="displayed-field">
    <label><?= $label ?> (MM/DD/YYYY)</label>
        <div class="row">
            <div class="col-md-8">
                <?= DatePicker::widget([
                    'options' => ['placeholder' => '_ _ / _ _ / _ _ _ _','autocomplete'=>'off'],
                    'name' => $name . '-date',
                    'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                    'value' => $date,
                    'id' => $class . '-' . $name . '-date',
                    'class' => 'form-control date',
                    'layout' => $layout,
                    'pluginOptions' => [
                        'autoclose'=> true,
                        'format' => 'mm/dd/yyyy',
                        'assumeNearbyYear' => true,
                        'todayHighlight' => true,
                    ]
                ]);?>
            </div>
            
        <?php if($showTime): ?>
            <div class="col-md-4">
                <?= Html::dropDownList($name, $time, $timeOptions, [
                    'class' => 'form-control time',
                    'id' => $class . '-' . $name . '-time',
                    'value' => $time,
                    'style' => 'width: 100%;'
                ]); ?>
            </div>
        <?php else: ?>
        <input type="hidden" id="<?= $class . '-' . $name . '-time'; ?>" value="" />
        <?php endif; ?>
        </div>
    </div>

</div>
