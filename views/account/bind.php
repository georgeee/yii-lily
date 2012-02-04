<?php
$this->pageTitle = Yii::app()->name . ' - Account bind';
$this->breadcrumbs = array(
    'Bind account',
);
?><h1>Bind new account</h1>
<p>Please fill out the following form with your login credentials:</p>
<?php $this->widget('LAuthWidget', array('model' => $model, 'services' => $services, 'showRememberMe' => false, 'submitLabel' => 'Bind', 'action' => '')); ?>
