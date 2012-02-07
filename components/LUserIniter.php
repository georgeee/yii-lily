<?php

/**
 * Helper component for email account managment
 *
 * @author georgeee
 */
class LUserIniter extends CApplicationComponent {

    public $showStartStep = true;
    public $showFinishStep = true;
    public $finishRedirectUrl = '';
    protected $_steps;
    protected $_stepId;
    protected $_count;
    protected $_isStarted = false;

    public function init() {
        parent::init();
        $this->_steps = isset(LilyModule::instance()->session->data->userInitData->steps) ? LilyModule::instance()->session->data->userInitData->steps : null;
        $this->_stepId = isset(LilyModule::instance()->session->data->userInitData->stepId) ? LilyModule::instance()->session->data->userInitData->stepId : null;
        $this->_count = isset(LilyModule::instance()->session->data->userInitData->count) ? LilyModule::instance()->session->data->userInitData->count : null;
    }

    public function getCount() {
        return $this->_count;
    }

    public function getSteps() {
        return $this->_steps;
    }

    public function getStepId() {
        return $this->_stepId;
    }

    public function getStep() {
        return $this->steps[$this->stepId];
    }

    public function nextStep() {
        if ($this->stepId < count($this->steps)) {
            LilyModule::instance()->session->data->userInitData->stepId++;
            LilyModule::instance()->session->save();
            Yii::app()->request->redirect($this->steps[$this->stepId + 1]->page);
        } else {
            $this->finish();
        }
    }

    public function allow($route) {
        if (!in_array($route, $this->step->allowed)) {
            $this->step->allowed[] = $route;
            LilyModule::instance()->session->data->userInitData->steps[$this->stepId]->allowed[] = $route;
            LilyModule::instance()->session->save();
        }
    }
    
    public function getIsStarted(){
        return $this->_isStarted;
    }
    
    public function start() {
        if($this->isStarted) return;
        $this->_isStarted = true;
        if (!isset($this->steps)) {
            $count = 0;
            $steps = array();
            foreach (LilyModule::instance()->relations as $name => $relation) {
                if (isset($relation['onRegister'])) {
                    $onRegister_route = is_array($relation['onRegister']) ? $relation['onRegister'][0] : $relation['onRegister'];
                    $onRegister_query = is_array($relation['onRegister']) ? array_slice($relation['onRegister'], 1) : array();
                    $steps[++$count] = (object) array(
                                'page' => Yii::app()->createUrl($onRegister_route, $onRegister_query),
                                'name' => $name,
                                'allowed' => array($onRegister_route),
                    );
                }
            }
            LilyModule::instance()->session->data->userInitData = new stdClass;
            LilyModule::instance()->session->data->userInitData->steps = $this->_steps = $steps;
            LilyModule::instance()->session->data->userInitData->stepId = $this->_stepId = 1;
            LilyModule::instance()->session->data->userInitData->count = $this->_count = $count;
            LilyModule::instance()->session->save();
        }
        $route = Yii::app()->urlManager->parseUrl(Yii::app()->request);
        if (!in_array($route, $this->step->allowed)) {
            Yii::app()->request->redirect($this->step->page);
        }
    }

    protected function finish() {
        LilyModule::instance()->user->inited = true;
        LilyModule::instance()->user->save();
        if(is_string($this->finishRedirectUrl))
            $redirectUrl = Yii::app()->createUrl($this->finishRedirectUrl);
        else
            $redirectUrl = Yii::app()->createUrl ($this->finishRedirectUrl[0], array_slice ($this->finishRedirectUrl, 1));
        Yii::app()->request->redirect($redirectUrl);
    }

}

