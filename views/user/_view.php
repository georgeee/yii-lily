<?php

$this->widget('zii.widgets.CDetailView', array(
    'data' => $data,
    'attributes' => array(
        'uid', // title attribute (in plain text)
        'name', // an attribute of the related object "owner"
        'birthday', // description attribute in HTML
        array(
            'label' => $data->getAttributeLabel('sex'),
            'type' => 'text',
            'value' => $data->sexOption,
        ), // description attribute in HTML
        array(// related city displayed as a link
            'label' => LilyModule::t('Accounts'),
            'type' => 'raw',
            'value' => $this->widget('zii.widgets.grid.CGridView', array(
                'dataProvider' => new CActiveDataProvider('LAccount', array(
                    'criteria' => array(
                        'condition' => 'uid=' . $data->uid,
                        'order' => 'created ASC',
                    ),
                )),
                'enablePagination' => false,
                'summaryText' => '',
                'columns' => array(
                    array(
                        'name' => 'service',
                        'value' => '$data->serviceName',
                    ),
                    'id',
                    array(
                        'name' => 'created',
                        'value' => 'Yii::app()->dateFormatter->formatDateTime($data->created)',
                    ),
                ),
                    ), true)
        ,
        ),
    ),
));