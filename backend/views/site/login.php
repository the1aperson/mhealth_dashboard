<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;


$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile(Url::base() . '/js/show_password.js', ['depends' => [yii\web\JqueryAsset::className()], 'position' => $this::POS_HEAD]);
$js = <<<EOT
    $(document).ready(function(){  
    
        $("#login-form input[type='checkbox']").prop('checked', true);
         
       $("#login-form input[type='checkbox']").change(function(){
           if ($("#login-form input[type='checkbox']").is(':checked')){
               if($("#loginform-username").val() != "" && $("#loginform-password").val() != "") {
                   $("#login-form button").removeAttr('disabled');  
               }
           } else
           {
               $("#login-form button").attr('disabled', 'disabled'); 
           }
   
       });
   
           function getCookie(cname) {
               var name = cname + "=";
               var ca = document.cookie.split(';');
               for(var i = 0; i < ca.length; i++) {
                 var c = ca[i];
                 while (c.charAt(0) == ' ') {
                   c = c.substring(1);
                 }
                 if (c.indexOf(name) == 0) {
                   return c.substring(name.length, c.length);
                 }
               }
               return "";
             }
             
             function checkCookie() {
               var user = getCookie("arc-cookies");
               if (user != "") {
                 return true;
               } else {
                   return false;
                 }
               }      
   });
EOT;
$this->registerJs($js);

?>
<div class="site-login col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 section">


    <div class="row">
        <div class="col-sm-offset-3 col-sm-6">
	        <div class="text-center">
	            <p class="hello-please-log-in">Hello! Please log in to access the dashboard.</p>
	            <p>If you don’t have a username and password yet, contact the study coordinator to set up your account.</p>
	        </div>
	        
	        
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true])->label('Username/email'); ?>

                <?= $form->field($model, 'password', ['template' => "<div class='password_reveal'><p class='show_password' onclick='login_password()'>Show</p><span id='eye-icon' class='icon-visibility-on_blue' onclick='login_password()'></span></div>{label}{input}{error}"])->passwordInput()->label('Password');; ?>
                <?php if(!isset($_COOKIE['arc-cookies'])):?>
                    <?= $form->field($model, 'accept_privacy',['template' => "<div class='policy_check'>{input}{label}{error}</div>"])->checkbox(['checked'=>false], false)->label("I agree to the <a class='login_policy' target='_blank' href=".Url::to("privacy-policy", true).">PRIVACY POLICY</a>, including the use of cookies and collection of my IP address, which are required for meeting study regulatory standards.",['class' => 'policy_label']); ?>
                <?php endif;?>
                <div class="form-group">
	                <div class="row">
                    <?= Html::submitButton('Log In', ['class' => 'button-blue col-sm-6 col-sm-offset-3', 'name' => 'login-button']) ?>
	                </div>
                </div>

            <?php ActiveForm::end(); ?>
            <div class="text-center login-links">
                <hr/>
                <a href="<?= Url::to("request-password-reset", true); ?>" >FORGOT PASSWORD?</a>
                <a href="<?= Url::to("request-rater-id-reset", true); ?>" >RESET RATER ID</a>
                <a href="<?= Url::to("contact-us", true); ?>" >CONTACT US</a>
            </div>
        </div>
    </div>
    
</div>
<div class="clearfix"></div>