<?php

namespace backend\components;

use yii;
use yii\widgets\InputWidget;

class DateTimeWidget extends InputWidget
{

    public $model;
    public $form;
    public $name;
    public $datetime;
	public $showTime;
	
    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $date = null;
        $time = null;

        if (!empty($this->datetime)){
            $date = date('n/j/Y', $this->datetime);
            $time = date('g:i A', $this->datetime);
        }

        // create time drop down array
        $timeOptions = array('null' => '');
        $timeInterval = new \DateTime('today midnight');
        $timeIncrement = new \DateInterval('PT15M');
        $endInterval = new \DateTime('today 11:59 PM');

        while ($timeInterval < $endInterval){
            $value = $timeInterval->format('g:i A');
            $timeOptions[$value] = $value;
            $timeInterval->add($timeIncrement);
        }

        $value = $endInterval->format('g:i A');
        $timeOptions[$value] = $value;

        // make sure time value is listed in the time options (15 min increments)
        if ($time != null && !in_array($time, $timeOptions)){
            $times = array_keys($timeOptions);
            $compareTime = new \DateTime($time); // need to compare time as datetime object
            array_shift($times); // get rid of first null value in time options

            while (count($times) > 1){
                $timeBefore = new \DateTime(array_shift($times));
                $timeAfter = new \DateTime($times[0]);

                if ($compareTime < $timeAfter && $compareTime > $timeBefore){
                    $time = $timeBefore->format('g:i A');
                    break;
                }
            }
        }

        return $this->render('dateTime', [
            'form' => $this->form,
            'model' => $this->model,
            'name' => $this->name,
            'date' => $date,
            'time' => $time,
            'timeOptions' => $timeOptions,
            'showTime' => isset($this->showTime) ? $this->showTime : true,
        ]);

    }

}
