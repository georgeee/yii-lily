<?php
/* @var $this Controller*/
$this->pageTitle = LilyModule::t('{appName} - User initialization', array('{appName}' => Yii::app()->name));
$this->breadcrumbs = array(
    LilyModule::t('User initialization')
);
?><h1><?php echo LilyModule::t('Account initialization');?></h1>
<?php
switch ($action) {
    case 'start':
        ?>
        <div>
            <?php echo LilyModule::t('Please fill in next few forms in order to initialize your account.');?>
            <br/> <?php echo CHtml::link(LilyModule::t('Start'), $this->createUrl('', array('action' => 'next'))); ?>
        </div>
        <?php
        break;
    case 'finish':
        ?>
        <div>
            <?php echo LilyModule::t('Account was successfully initialized.'); ?>
            <br/> <?php echo CHtml::link(LilyModule::t('Finish'), $this->createUrl('', array('action' => 'next'))); ?>
        </div>
        <?php
        break;
}