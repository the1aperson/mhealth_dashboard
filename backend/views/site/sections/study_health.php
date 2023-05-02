
<div id="study-health-section" class="section">
	<h3 class="section-header">
		Study Health
	</h3>
	
	<div id="sh-total-participants" class="light-blue-subsection">
		<span class="big-numbers">
		    <?= $total_participants; ?>
        </span>
		<span class="big-number-sub">Total Participants</span>
		<span class="icon-info-outline_blue big-number-sub" data-html="true" data-toggle="tooltip" data-placement="top" title="Total participants who have enrolled in the study."></span>
        <div style="float: right;">
            <?= $this->render('/fragments/view_all_data_button'); ?>
	    </div>
    </div>
	
	<?= $this->render("sh_med_sections", ["items" => $med_metadata_items]); ?>
	
	<hr />
	
	<?= $this->render("sh_small_sections", ["items" => $small_metadata_items]); ?>
</div>
