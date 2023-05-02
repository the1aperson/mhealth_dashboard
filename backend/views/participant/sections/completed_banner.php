<?php
	
	use common\components\DateFormatter;
?>

<div class="row">
    <div class="col-sm-12">
	    <div class="participant-banner message">
		    <div class="clearfix">
			    <div class="participant-banner-section">
				    <p><b>This participant has completed the study!</b></p>
				    <p>All data on this page appears in the same state as it did on the complete date. </p>
			    </div>
			    <div class="pull-right">
				    <div class="participant-banner-section">
					    <p>Complete Date:</p>
						<p><?= DateFormatter::abbreviatedDate($final_test->session_date); ?></p>
				    </div>
			    </div>
		    </div>
	    </div>
	</div>
</div>
