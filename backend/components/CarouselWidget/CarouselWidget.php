<?php

namespace backend\components\CarouselWidget;

use yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\AssetBundle;

class CarouselWidget extends Widget
{
	public $id = null;
	public $container_class = "carousel-container";
	public $content_class = "carousel-wrapper";
	public $inner_class = "carousel-inner-wrapper";
	public $item_class = "carousel-item";
	public $top_class = "carousel-top-section";
	public $items = [];
	public $title = "";
	
   	public function init()
    {
        parent::init();
        if($this->id == null)
        {
	        $this->id = "carousel-" . time();
        }


    }

    public function run()
    {	
		CarouselWidgetAssetBundle::register($this->view);
		return $this->render('carousel', [ 'widget' => $this]);
    }
    
    public function addItem($html)
    {
	    $this->items []= $html;
    }

}


class CarouselWidgetAssetBundle extends AssetBundle
{
    public $sourcePath = '@app/components/CarouselWidget/assets';

    public $js = [
	    'js/vendor/siema.min.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    
}