<?php
$this->pageTitle = Yii::app()->name . ' - Login';
$this->breadcrumbs = array(
    'Login',
);
?><h1>Login</h1>
<p>Please fill out the following form with your login credentials:</p>
<?php $this->widget('LAuthWidget', array('model' => $model, 'services' => $services, 'showRememberMe' => true, 'submitLabel' => 'Login', 'action' => '')); ?>
