<div class="form">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => $id,
        'htmlOptions' => array('class'=>'authForm'),
        'action'=>$action,
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),)
    );
    ?>
    <script type="text/javascript">/*<![CDATA[*/ jQuery(function($){$('#<?php echo $id; ?>').lily();}); /*]]>*/</script>
    <h2>Choose your auth method:</h2>

    <div class="authMethodSwitcherDiv">
        <ul class="authMethodSwitcher">
            <?php foreach ($services as $service => $options) { ?>
                <li class="authMethod <?php echo $options->id; ?>" service="<?php echo $options->id; ?>">
                    <a class="authMethodLink" href="#">
                        <div class="authMethodIcon"><i></i></div>
                        <div class="authMethodTitle"><?php echo $options->title; ?></div>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
    <div class="authMethodSelectDiv row">
        <?php
        $data = $options = array();
        foreach ($services as $service => $opts) {
            $data[$service] = $opts->title;
            $options[$service] = array('class' => 'option_' . $service);
        }
        echo $form->dropDownList($model, 'service', $data, array('class' => 'authMethodSelect', 'options' => $options));
        ?>

    </div>
    <div class="eauthHandlers">
        <?php
        foreach ($services as $name => $service) {
            if ($name == 'email')
                continue;
            echo '<div class="auth-service ' . $service->id . '">'
            . CHtml::link('-', array($action, 'service' => $name), array('class' => 'auth-link ' . $service->id,))
            . '</div>';
        }
        ?>
    </div>
    <div class="formDiv">
        <?php if (isset($services['email'])) { ?>
            <div class="emailFieldsDiv">
                <p class="note emailFieldHint">Use fields Email, Password when method "E-mail" was selected</p>                
                
                <?php if(LilyModule::instance()->accountManager->registerEmail){ ?>
                <p class="note">If you're not yet registered, just fill in E-mail and password fields with your e-mail address and a password you want to use. You'll be automaticaly registrated.</p>
<?php }else{ ?>
                <p class="note">If you're not yet registered, just go to <?php echo CHtml::link("registration page", array("user/register")); ?> and pass the registration. Or you can choose another authentication method.</p>
                <?php }?>

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
<!--                    <p class="hint">
                        In password you can use lowercase and uppercase latin letters, characters (excluding quotes) &quot;-.,;=+~/\[]{}!@#$%^*&amp;()_|&quot; and simple whitespace.
                        <br /> Password's length must be from 8 to 32 characters.
                    </p>-->
                </div>
            </div>
        <?php } ?>
        <div class="row rememberMeFieldDiv" <?php if(!$showRememberMe){ ?>style="display:none;"<?php } ?>>
            <?php echo $form->checkBox($model, 'rememberMe', array('class' => 'authMethodRememberMe')); ?>
            <?php echo $form->label($model, 'rememberMe', array('class' => 'authMethodRememberMeLabel')); ?>
            <?php echo $form->error($model, 'rememberMe'); ?>
        </div>
        <div class="row buttons">
            <?php echo CHtml::submitButton($submitLabel, array('class'=>'submitButton')); ?>
        </div>
    </div>
    <?php $this->endWidget(); ?>
</div><!-- form -->


