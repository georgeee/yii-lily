<?php
Yii::app()->getModule('lily')->registerCss('authForm');
?>
<div id="accountList">
<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$accountProvider,
	'itemView'=>'_view',
)); ?>
</div>