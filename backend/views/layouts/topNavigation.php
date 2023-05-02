<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use common\models\Study;
use common\models\StudyUserAuth;

$this->registerJsFile(Url::base() . '/js/topnav.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);

$studyName = Yii::$app->name;
$host = $_SERVER['REQUEST_URI'];

	if (Yii::$app->study->isStudySet())
	{
		$study = Yii::$app->study->getStudy();
		if($study != null && $study->status == Study::STATUS_ACTIVE)
		{
			
			if($host == '/select-study' || $host == '/study/create' || $host == '/study/index')
			{
				$studyName = 'All Studies';
			} 
			else 
			{
				$studyName = $study->name;
			}
			$studyUpdate = date('g:i A; F jS, Y',$study->updated_at);
		} else {
			$studyName = 'All Studies';
		} 
	} else 
	{
		$studyName = 'All Studies';
	}
// Build the dropdown
$studyDropdown = array();

if(Yii::$app->user->isGuest == false)
{
	$user = Yii::$app->user->getIdentity();
	$studies = $user->getAccessibleStudies();
	foreach ($studies as $study) { 
		if($study->status != Study::STATUS_ACTIVE){
			continue;
		}
	    $url = Url::to(["/select-study", "study_id" => $study->id], true);
	    $temp = array('label' => $study->name, 'url' => $url, 'options' => ['class' => 'dropdown-view-'.$study->id]);
	    array_push($studyDropdown, $temp);
	}
}

$url = Url::to(["/select-study"], true);
$summary = array('label' => 'All Studies', 'url' => $url, 'options' => ['class' => 'dropdown-view-all-sites']);
array_unshift($studyDropdown, $summary);

$options = [
    'options' => [
        'class' => 'navbar-nav study-dropdown-container',
    ],
    'items' => [
        [
            'label' => "<div class='icon-chevron_down_teal'></div>",
            'url' => '#',
            'items' => $studyDropdown,
        ],
    ],
    'encodeLabels' => false,
];

?>

<div id="top-nav">
	
	<div id="study-dropdown-wrapper" class="top-nav-section pull-left navbar-border" style="max-width:208px; min-width:208px;">
		<p class='navbar-current-study'>Current Study:</p>
		<div class="navbar-study-name"><?= $studyName; ?></div>
		<?= Nav::widget($options); ?>	
	</div>

	<?php if(Yii::$app->study->isStudySet() && $studyName != 'All Studies'): ?>
		<div class="top-nav-section pull-left navbar-update">
		<p class="navbar-updated-time">Last updated: <?= $studyUpdate; ?></p>
			<?php if((strtotime($studyUpdate)) >= (strtotime('- 1 day'))): ?>
				<div class="new-data"><p class="new-data-alert">New Data Available!</p><a class="icon-reload_teal" onclick='document.location.reload(true)'></a><a class="refresh" onclick='document.location.reload(true)'>Refresh</a></div>
			<?php endif;?>
		</div>
	<?php endif; ?>


<?php if (Yii::$app->user->isGuest): ?>
	<div class="top-nav-section pull-right navbar-border-left">
		<a class="top-nav-text" href="<?= Url::to("/login", true); ?>">Login</a>
	</div>
<?php else: ?>
<?php
    $now = new DateTime();
    $later = new DateTime();
    $later->setTimestamp(time() + Yii::$app->user->authTimeout);
    
	$timeInterval = $later->diff($now);
    $expirationTime = $timeInterval->format("%H:%I:%S");    
    $laterTimestamp = $later->getTimestamp();
    
	$options =[
	    'options' => [
	        'class' => 'navbar-nav user-nav',
	    ],
	    'items' => [
	        [
	            'label' => "<div class='icon-chevron_down_teal' id='top-nav-chevron'></div>",
	            'url' => '#',
	            'items' => [['label' => 'Logout', 'url' => Url::to("/logout", true)]],
	            'options' => [
	            	'class' => 'dropdown-menu-right',
				]
	        ],
	    ],
	    'encodeLabels' => false,
	];
?>
	<div class="top-nav-section pull-right">
		<div class="top-nav-section pull-left top-nav-text navbar-border-left" id="session-expiration">Session Expires in <span id="header-countdown" data-expiration-time="<?= $laterTimestamp; ?>"><?= $expirationTime; ?></span>
		</div>
		<div class="top-nav-section pull-left top-nav-text navbar-border-left"><span class='text-white'>Hi, <?=Yii::$app->user->identity->first_name; ?></span></div>
		<?= Nav::widget($options); ?>
	</div>
<?php endif; ?>
</div>