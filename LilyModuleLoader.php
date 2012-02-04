<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LModuleLoader
 *
 * @author georgeee
 */
class LilyModuleLoader extends CApplicationComponent implements IApplicationComponent {

    public function init() {
        parent::init();
        Yii::app()->setModules(
                array(
                    'lily' => array(
                        'class' => 'lily.LilyModule',
                        'defaultController' => 'user',
                    ),
                )
        );
        Yii::app()->getModule('lily');
    }

}

?>
