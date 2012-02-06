<?php

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
    
    public $submitLabel = 'Login';
    public $showRememberMe = true;
    public $model = null;
    
    /**
     * Initializes the widget.
     * This method is called by {@link CBaseController::createWidget}
     * and {@link CBaseController::beginWidget} after the widget's
     * properties have been initialized.
     */
    public function init() {
        parent::init();


        // Some default properties from component configuration
        if (!isset($this->services))
            $this->services = Yii::app()->eauth->getServices();
        if (!isset($this->popup))
            $this->popup = Yii::app()->eauth->popup;

        // Set the current route, if it is not set.
        if (!isset($this->action))
            $this->action = Yii::app()->urlManager->parseUrl(Yii::app()->request);
        
        if(!isset($this->model)) $this->model = new LLoginForm('', $this->services);
    }

    /**
     * Executes the widget.
     * This method is called by {@link CBaseController::endWidget}.
     */
    public function run() {
        parent::run();
        $this->setId('LAuthWidget-form-'.$this->getId());
        $this->model->id = $this->getId();
        $this->registerAssets();
        $this->render('authForm', array(
            'id' => $this->getId(),
            'services' => $this->services,
            'action' => $this->action,
            'submitLabel' => $this->submitLabel,
            'showRememberMe' => $this->showRememberMe,
            'model' => $this->model,
        ));
    }

    public function registerAssets() {
        LilyModule::instance()->registerCss('authForm');
        LilyModule::instance()->registerJs('authForm');
        $_services = $this->services;
        unset($_services['email']);
        //We have to run EAuthWidget to make it register it's assets
        $this->widget('EAuthWidget', array('popup' => $this->popup, 'services' => $_services),true);
    }

}
