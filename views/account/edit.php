<h1>Edit an account</h1>

<p>Please type the password, you want to have and repeat in next field:</p>

<div class="form">
    <?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'password-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
    ),
)); ?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

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
        In password you can use lowercase and uppercase latin letters, characters (excluding quotes) &quot;-.,;=+~/\[]{}!@#$%^*&amp;()_|&quot; and simple whitespace.
        <br /> Password's length must be from 8 to 32 characters.
    </p>
    <div class="row buttons">
        <?php echo CHtml::submitButton('Save'); ?>
    </div>
    <?php $this->endWidget(); ?>
</div><!-- form -->
