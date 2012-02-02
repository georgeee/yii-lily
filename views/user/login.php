<?php
Yii::app()->getModule('lily')->registerCss('loginForm');
Yii::app()->getModule('lily')->registerJs('loginForm');
$_services = $services;
unset($_services['email']);
Yii::app()->getWidgetFactory()->createWidget(null, 'EAuthWidget', array('popup'=>true, 'services'=>$_services))->registerAssets();
$this->pageTitle = Yii::app()->name . ' - Login';
$this->breadcrumbs = array(
    'Login',
);
?>


<h1>Login</h1>

<p>Please fill out the following form with your login credentials:</p>


<div class="form" id="loginForm">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'login-form',
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
            ));
    ?>

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

        //echo $form->labelEx($model, 'service');
        echo $form->dropDownList($model, 'service', $data, array('class' => 'authMethodSelect', 'options' => $options));
        //echo $form->error($model, 'service');
        ?>

    </div>
    <div class="eauthHandlers">
            <?php
            foreach ($services as $name => $service) {
                if($name=='email') continue;
                echo '<div class="auth-service '.$service->id.'">'.CHtml::link('-', array('', 'service' => $name), array(
                            'class' => 'auth-link ' . $service->id,
                        )).'</div>';
            }
            ?>
    </div>
    <div class="formDiv">
        <?php if (isset($services['email'])) { ?>
            <div class="emailFieldsDiv">
                <p class="note">Use fields Email, Password when method "E-mail" was selected</p>

                <p class="note">Fields with <span class="required">*</span> are required.</p>

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
                    </p>
                </div>
            </div>
        <?php } ?>
        <div class="row rememberMeField">
            <?php echo $form->checkBox($model, 'rememberMe', array('class' => 'authMethodRememberMe')); ?>
            <?php echo $form->label($model, 'rememberMe'); ?>
            <?php echo $form->error($model, 'rememberMe'); ?>
        </div>

        <div class="row buttons">
            <?php echo CHtml::submitButton('Login'); ?>
        </div>
    </div>
    <?php $this->endWidget(); ?>
</div><!-- form -->


