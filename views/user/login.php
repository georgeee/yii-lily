<?php
/* @var $this Controller*/
$this->pageTitle = LilyModule::t('{appName} - Login', array('{appName}' => Yii::app()->name));
$this->breadcrumbs = array(
    LilyModule::t('Login')
);
?><h1><?php echo LilyModule::t('Login');?></h1>
<p><?php echo LilyModule::t('Please fill out the following form with your login credentials:');?></p>
<?php $this->widget('LAuthWidget', array('model' => $model, 'services' => $services, 'showRememberMe' => true, 'submitLabel' => LilyModule::t('Login'), 'action' => '')); ?>
