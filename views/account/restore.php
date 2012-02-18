<h1>Restore controll to account</h1>

<p>Please type your e-mail address:</p>

<div class="form">
    <?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'restore-form',
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
    ),
)); ?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <div class="row">
        <?php echo $form->labelEx($model,'email'); ?>
        <?php echo $form->textField($model,'email'); ?>
        <?php echo $form->error($model,'email'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton('Restore'); ?>
    </div>
    <?php $this->endWidget(); ?>
</div><!-- form -->
