<?php
/* @var $this Controller*/
$this->pageTitle = LilyModule::t('{appName} - Bind account', array('{appName}' => Yii::app()->name));
$this->breadcrumbs = array(
    LilyModule::t('Accounts')=>$this->createUrl('account/list'),
    LilyModule::t('Bind')
);
?><h1><?php echo LilyModule::t('Bind new account'); ?></h1>
<p><?php echo LilyModule::t('Please fill out the following form with your login credentials:'); ?></p>
<?php $this->widget('LAuthWidget', array('model' => $model, 'services' => $services, 'showRememberMe' => false, 'submitLabel' => LilyModule::t('Bind'), 'action' => '')); ?>
