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
    
    public $module = 'lily';
    
    public function init() {
        parent::init();
        Yii::app()->getModule($this->module);
    }

}

?>
