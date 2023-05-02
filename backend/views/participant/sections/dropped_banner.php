<?php
	
	use common\components\DateFormatter;
	
	
?>
<div class="row">
    <div class="col-sm-12">
		<div class="participant-banner danger">
		    <div class="clearfix">
			    <div class="participant-banner-section">
				    <p><b>This participant has been dropped from the study.</b></p>
				    <p>All data on this page appears in the same state as it did on the drop date. </p>
			    </div>
			    <div class="pull-right">
				    <div class="participant-banner-section">
					    <p>Drop Date:</p>
						<p><?= DateFormatter::abbreviatedDate($droppedRecord->created_at); ?></p>
				    </div>
				    <div class="participant-banner-section">
					    <p>Dropped By:</p>
						<p><?= $droppedRecord->getDroppedBy()->one()->displayName(); ?></p>
				    </div>
			    </div>
		    </div>
		</div>
    </div>
</div>