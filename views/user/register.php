<?php
$this->pageTitle = Yii::app()->name . ' - Registration';
$this->breadcrumbs = array(
    'Registration',
);
?><h1>Registration</h1>
<p>Please fill out the following form:</p>
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

                <p class="note">If you forgot your password, you can restore it using <a href="<?php echo Yii::app()->urlManager->createUrl(LilyModule::route('account/restore'));?>">this page</a>.</p>

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
                        In password you can use lowercase and uppercase latin letters, characters (excluding quotes) &quot;-.,;=+~/\[]{}!@#$%^*&amp;()_|&quot; and simple whitespace.
                        <br /> Password's length must be from 8 to 32 characters.
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
            <?php echo CHtml::submitButton("Register me", array('class'=>'submitButton')); ?>
        </div>
    </div>
    <?php $this->endWidget(); ?>
</div><!-- form -->


