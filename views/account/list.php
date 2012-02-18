<h1>Accounts</h1>

<?php
Yii::app()->clientScript->registerCssFile(LilyModule::instance()->getAssetsUrl() . "/lily.css");
?>
<div id="accountList">
<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$accountProvider,
	'itemView'=>'_view',
)); ?>
</div>