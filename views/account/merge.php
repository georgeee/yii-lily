<?php
/* @var $this Controller*/
$this->pageTitle = LilyModule::t('{appName} - Merge users', array('{appName}' => Yii::app()->name));
$this->breadcrumbs = array(
    LilyModule::t('User')=>$this->createUrl('user/view'),
    LilyModule::t('Merge')
);
?><h1><?php echo LilyModule::t('Merge users');?></h1>

<div>
    <form action="" method='post'  >
        <p class="note"><?php echo LilyModule::t('Do you really want to merge your user account with {userLink}?',
                array('{userLink}'=>CHtml::link($user->nameId, $this->createUrl('user/view', array('uid' => $user->uid)))));?></p>
        <?php if ($banWarning) { ?>
            <p class="warning"><?php echo LilyModule::t("Warning! User, you're merging with is banned. If you'll submit this form, your account would be banned also."); ?></p>
        <?php } ?>
        <?php if ($deleteWarning) { ?>
            <p class="warning"><?php echo LilyModule::t("Warning! User, you're merging with is deleted. If you'll submit this form, your account would be deleted also."); ?></p>
        <?php } ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton(LilyModule::t('Yes'), array('name' => 'accept')); ?>
            <?php echo CHtml::link(LilyModule::t('Cancel'), $this->createUrl('account/list')); ?>
        </div>
    </form>
</div>