<?php

use yii\helpers\Url;
/* @var $widget backend\components\CarouselWidget */


$carousel_id = "#" . $widget->id;
$item_class = "." . $widget->item_class;
$selector = "#" . $widget->id . " ." . $widget->inner_class;
$js = <<<EOT
(function(){
const next = $('$carousel_id .carousel-next');
const prev = $('$carousel_id .carousel-prev');
const perPage = 2;

var siema = new Siema({selector:'$selector',
perPage: 2,
draggable: false,
onInit: onInit,
onChange, onChange,
loop: false});

function onInit(){
	prev.disabled = true;
	prev.css('opacity','.5');
	}
	
	function onChange(){
		const index = siema.currentSlide;
		if(index == 0)
		{
			prev.disabled = true;
			prev.css('opacity','.5');
		} 
		else
		{
			prev.disabled = false;
			prev.css('opacity','1');
		};
		if(index === siema.innerElements.length + 1 || index + perPage >= siema.innerElements.length)
		{
			next.disabled = true;
			next.css('opacity', '.5');
		} 
		else
		{
			next.disabled = false;
			next.css('opacity', '1');
		}
	}
	
	prev.click(function(){
		siema.prev();
});

	next.click(function(){
		siema.next();
});

})();
EOT;
$this->registerJs($js);

?>

<div class="<?= $widget->container_class; ?>" id="<?= $widget->id;?>">
	<div class="<?= $widget->top_class; ?> clearfix">
		<div class="pull-left">
			<p class="section-header"><?= $widget->title; ?></p>
		</div>
		<div class="pull-right">
			<div class="carousel-button carousel-prev"></div>
			<div class="carousel-button carousel-next"></div>
		</div>
	</div>
	<div class="<?= $widget->content_class; ?>">
		<div class="<?= $widget->inner_class; ?>">
			<?php foreach($widget->items as $i => $item): ?>
				<div class="<?= $widget->item_class; ?>" id="carousel-item-<?= $i; ?>"><?= $item; ?></div>
			<?php endforeach; ?>
		</div>
	</div>
</div>