<h1>User <?php
/* @var $user LUser */
echo CHtml::encode($user->name);
?></h1>
<?php


$this->renderPartial('_view', array('data'=>$user));
?>