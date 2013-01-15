<?php
/* @var $this Controller */
$this->pageTitle = LilyModule::t('{appName} - Delete account', array('{appName}' => Yii::app()->name));
$this->breadcrumbs = array(
    LilyModule::t('Accounts') => $this->createUrl('account/list'),
    LilyModule::t('Delete')
);
?><h1><?php echo LilyModule::t('Delete account'); ?></h1>
<div>
    <form action="" method="POST">
        <p class="note"><?php echo LilyModule::t('Do you really want to delete your account {displayId} (service {serviceName})?', array('{displayId}' => $account->displayId, '{serviceName}' => $account->serviceName)); ?></p>
        <div class="row buttons">
            <?php echo CHtml::submitButton(LilyModule::t('Yes'), array('name' => 'accept')); ?>
            <?php echo CHtml::link(LilyModule::t('Cancel'), $this->createUrl('account/list')); ?>
        </div>
    </form>
</div>