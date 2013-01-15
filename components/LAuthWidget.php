<?php
/**
 * LAuthWidget class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LAuthWidget is a widget, that you can use to display login form.
 *
 * @package application.modules.lily
 */
class LAuthWidget extends CWidget {

    /**
     * @var array the services.
     * @see EAuth::getServices() 
     */
    public $services = null;

    /**
     * @var boolean whether to use popup window for authorization dialog. Javascript required.
     */
    public $popup = null;

    /**
     * @var string the action to use for dialog destination. Default: the current route.
     */
    public $action = null;
    /**
     * @var string submit button label
     */
    public $submitLabel = null;
    /**
     * @var bool Whether to show rememberMe checkbox
     */
    public $showRememberMe = true;
    /**
     * @var LLoginForm login form model instance
     */
    public $model = null;

    /**
     * Initializes the widget.
     */
    public function init() {
        parent::init();
        if(!isset($this->submitLabel)) $this->submitLabel = LilyModule::t('Login');

        // Some default properties from component configuration
        if (!isset($this->services))
            $this->services = LilyModule::instance()->services;
        if (!isset($this->popup))
            $this->popup = Yii::app()->eauth->popup;

        // Set the current route, if it is not set.
        if (!isset($this->action))
            $this->action = Yii::app()->urlManager->parseUrl(Yii::app()->request);

        //If model isn't present, we just create one
        if (!isset($this->model))
            $this->model = new LLoginForm('', $this->services);
    }

    /**
     * Executes the widget.
     */
    public function run() {
        parent::run();
        $this->setId('LAuthWidget-form-' . $this->getId());
        $this->model->id = $this->getId();
        $this->registerAssets();
        return $this->controller->renderPartial('../user/authForm', array(
            'id' => $this->getId(),
            'services' => $this->services,
            'action' => $this->action,
            'submitLabel' => $this->submitLabel,
            'showRememberMe' => $this->showRememberMe,
            'model' => $this->model,
        ));
    }
    /**
     * Registers JS an CSS files, that are used for login form displaying
     */
    public function registerAssets() {
        $assetsUrl = LilyModule::instance()->getAssetsUrl();
        Yii::app()->clientScript->registerCssFile($assetsUrl . "/lily.css");
        Yii::app()->clientScript->registerCoreScript('jquery');
        Yii::app()->clientScript->registerScriptFile($assetsUrl . "/lily.js");
        $_services = $this->services;
        unset($_services['email']);
        //We have to run EAuthWidget to make it register it's assets
        $this->widget('EAuthWidget', array('popup' => $this->popup, 'services' => $_services), true);
    }

}
