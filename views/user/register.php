<?php
/* @var $this Controller*/
$this->pageTitle = LilyModule::t('{appName} - E-mail registration', array('{appName}' => Yii::app()->name));
$this->breadcrumbs = array(
    LilyModule::t('E-mail registration')
);
?><h1><?php echo LilyModule::t('E-mail registration');?></h1>
<p><?php echo LilyModule::t('Please fill out the following form:');?></p>
<div class="form">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => "LRegisterForm-form",
        'htmlOptions' => array('class'=>'regForm'),
        'action'=>'',
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),)
    );
    ?>
    <div class="formDiv">
            <div class="emailFieldsDiv">              

                <p class="note"><?php echo LilyModule::t('If you forgot your password, you can restore it using {restorePageLink}', array('{restorePageLink}' => CHtml::link(LilyModule::t('this page'), Yii::app()->createUrl('/'.LilyModule::route('account/restore'))))); ?>.</p>

                <div class="row">
                    <?php echo $form->labelEx($model, 'email'); ?>
                    <?php echo $form->textField($model, 'email'); ?>
                    <?php echo $form->error($model, 'email'); ?>
                </div>

                <div class="row">
                    <?php echo $form->labelEx($model, 'password'); ?>
                    <?php echo $form->passwordField($model, 'password'); ?>
                    <?php echo $form->error($model, 'password'); ?>
                    <p class="hint">
                            <?php echo LilyModule::t('In password you can use lowercase and uppercase latin letters, characters (excluding quotes) {passwordSymbols} and simple whitespace.
        <br /> Password\'s length must be from 8 to 32 characters.', array('{passwordSymbols}' => '&quot;-.,;=+~/\[]{}!@#$%^*&amp;()_|&quot;')); ?>
                        </p>
                </div>
                <div class="row">
                    <?php echo $form->labelEx($model, 'passwordRepeat'); ?>
                    <?php echo $form->passwordField($model, 'passwordRepeat'); ?>
                    <?php echo $form->error($model, 'passwordRepeat'); ?>
                    
                </div>
            </div>
        <?php if(LilyModule::instance()->accountManager->loginAfterRegistration){ ?>
        <div class="row rememberMeFieldDiv">
            <?php echo $form->checkBox($model, 'rememberMe', array('class' => 'authMethodRememberMe')); ?>
            <?php echo $form->label($model, 'rememberMe', array('class' => 'authMethodRememberMeLabel')); ?>
            <?php echo $form->error($model, 'rememberMe'); ?>
        </div>
        <?php } ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton(LilyModule::t("Register me"), array('class'=>'submitButton')); ?>
        </div>
    </div>
    <?php $this->endWidget(); ?>
</div><!-- form -->


