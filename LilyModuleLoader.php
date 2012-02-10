<?php
/**
 * LilyModuleLoader class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LilyModuleLoader is an application component class.
 * Pu it in application components and then in preload. I know it's not a Yii way, but we have to do it,
 * because Lily needs to do some check right before app running.
 *
 * @package application.modules.lily
 */
class LilyModuleLoader extends CApplicationComponent implements IApplicationComponent {
    /**
     * @var string module name
     */
    public $module = 'lily';

    /**
     * This function inites the component. By the way it simply calls
     * Yii::app()->getModule() in order to make sure, that module was inited.
     */
    public function init() {
        parent::init();
        Yii::app()->getModule($this->module);
    }

}

?>
