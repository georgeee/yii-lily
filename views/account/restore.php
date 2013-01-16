<?php
/* @var $this Controller */
$this->pageTitle = LilyModule::t('{appName} - Restore e-mail account', array('{appName}' => Yii::app()->name));
$this->breadcrumbs = array(
    LilyModule::t('Restore e-mail account')
);
?><h1><?php echo LilyModule::t('Restore controll to account'); ?></h1>

<p><?php echo LilyModule::t('Please type your e-mail address:'); ?></p>

<div class="form">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'restore-form',
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
            ));
    ?>

    <p class="note"><?php echo LilyModule::t('Fields with {requiredSign} are required.', array('{requiredSign}' => '<span class="required">*</span>')); ?></p>

    <div class="row">
        <?php echo $form->labelEx($model, 'email'); ?>
<?php echo $form->textField($model, 'email'); ?>
<?php echo $form->error($model, 'email'); ?>
    </div>

    <div class="row buttons">
    <?php echo CHtml::submitButton(LilyModule::t('Restore')); ?>
    <?php echo CHtml::link(LilyModule::t('Cancel'), $this->createUrl(Yii::app()->user->isGuest ? 'user/login' : 'account/list')); ?>
    </div>
<?php $this->endWidget(); ?>
</div><!-- form -->
