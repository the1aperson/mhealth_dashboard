<?php
	
/* @var $alertCounts */

use yii\helpers\Url;
	
?>

<div class="section">
	<h3 class="section-header section-header-margin">
		Alerts
	</h3>
	
	<?php foreach($alertCounts as $alertCount): ?>

    <?php

        $icon = "";
        if ($alertCount["alert_name"] == "danger") {
            $icon = "icon-error-solid_red";
        } else if ($alertCount["alert_name"] == "warning") {
            $icon = "icon-warning-solid_yellow";
        } else {
            $icon = "icon-checkmark-solid_green";
        }

    ?>


	<div class="alert-overview-section alert-overview-<?= $alertCount["alert_name"]; ?>">
		<div class="ao-level-label"><div class="<?= $icon; ?> ao-level-label-icon"></div><?= $alertCount["alert_label"]; ?></div>
		
		<div class="ao-count">
			<span><b><?= $alertCount["new"]; ?></b> New</span>
			<span> | </span>
			<span><b><?= $alertCount["all"]; ?></b> Total</span>
		</div>
		<a href="<?= Url::to(["/alerts", "type" => $alertCount["alert_level"]], true); ?>" class="view-data-link">
			View
			<div class="icon-arrow-right_blue" style="position: relative; top: 3px;"></div>
		</a>
	</div>
	
	
	<?php endforeach; ?>
</div>
