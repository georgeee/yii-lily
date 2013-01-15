<?php
/* @var $this Controller*/
$this->pageTitle = LilyModule::t('{appName} - Edit account', array('{appName}' => Yii::app()->name));
$this->breadcrumbs = array(
    LilyModule::t('Accounts')=>$this->createUrl('account/list'),
    LilyModule::t('Edit')
);
?><h1><?php echo LilyModule::t('Edit account');?></h1>

<p><?php echo LilyModule::t('Please type the password, you want to have and repeat in next field:');?></p>

<div class="form">
    <?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'password-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
    ),
)); ?>

    <p class="note"><?php echo LilyModule::t('Fields with {requiredSign} are required.', array('{requiredSign}'=> '<span class="required">*</span>'));?></p>

    <div class="row">
        <?php echo $form->labelEx($model,'password'); ?>
        <?php echo $form->passwordField($model,'password'); ?>
        <?php echo $form->error($model,'password'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'password_repeat'); ?>
        <?php echo $form->passwordField($model,'password_repeat'); ?>
        <?php echo $form->error($model,'password_repeat'); ?>
    </div>
    <p class="hint">
        <?php echo LilyModule::t('In password you can use lowercase and uppercase latin letters, characters (excluding quotes) {passwordSymbols} and simple whitespace.
        <br /> Password\'s length must be from 8 to 32 characters.', array('{passwordSymbols}'=>'&quot;-.,;=+~/\[]{}!@#$%^*&amp;()_|&quot;'));?>
    </p>
    <div class="row buttons">
        <?php echo CHtml::submitButton(LilyModule::t('Save')); ?>
    </div>
    <?php $this->endWidget(); ?>
</div><!-- form -->
