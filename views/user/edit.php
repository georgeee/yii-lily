<div class="form">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'user-edit-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),)
    );
    ?>
    <div class="row">
        <?php echo $form->labelEx($user, 'name'); ?>
        <?php echo $form->textField($user, 'name'); ?>
        <?php echo $form->error($user, 'name'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($user, 'sex'); ?>
        <?php echo $form->dropDownList($user, 'sex', $user->sexOptions); ?>
        <?php echo $form->error($user, 'sex'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($user, 'birthday'); ?>
        <?php
        $attr = 'birthday';
        $this->widget('zii.widgets.jui.CJuiDatePicker', array(
            'name' => CHtml::resolveName($user, $attr),
            'value' => $user->birthday,
            'language' => Yii::app()->locale->id,
        ));
        ?>
        <?php echo $form->error($user, 'birthday'); ?>
    </div>
    <div class="row buttons">
    <?php echo CHtml::submitButton('Save'); ?>
    </div>
<?php $this->endWidget(); ?>
</div><!-- form -->


