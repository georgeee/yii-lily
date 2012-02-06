<?php

$this->widget('zii.widgets.CDetailView', array(
    'data' => $data,
    'itemCssClass' => array(),
    'attributes' => array(
        'uid',
        'name',
        'birthday',
        array(
            'label' => $data->getAttributeLabel('sex'),
            'type' => 'text',
            'value' => $data->sexOption,
        ),
        array(
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
                    array(
                        'name' => 'id',
                        'value' => '$data->displayId',
                    ),
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