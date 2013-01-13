<?php
/**
 * LUserIniter class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LUserIniter is a component class, that contains functionality to process user initialization (processing init of all related models).
 *
 * @property integer $count Step count (excluding start and finish steps, even if $showStartStep or $showFinishStep are true)
 * @property array $steps Array, containing information of all steps. Each step is an object with such properties:
 * <ul>
 * <li>page - url, where user will be redirected in order to pass the step</li>
 * <li>name - relation name, see relations property of LilyModule class</li>
 * <li>allowed - array of routes, that are allowed to use during step processing</li>
 * </ul>
 * @property integer $stepId id of current running step
 * @property object $step current step object, also could be accessed as $steps[$stepId]
 * @property bool $isStarted is userInit component started or not (if not, lily assumes that user is already inited)
 *
 * @package application.modules.lily.components
 */
class LUserIniter extends CApplicationComponent
{

    /**
     * @var bool Whether to show initial step page with common information about next actions
     */
    public $showStartStep = true;
    /**
     * @var bool Whether to show finish step page with common information about site using, registration results or etc.
     */
    public $showFinishStep = true;
    /**
     * @var string Url, to which user will be redirected after initing process (last step) step
     */
    public $finishRedirectUrl = '';

    protected $_steps;
    protected $_stepId;
    protected $_count;
    protected $_isStarted = false;

    /**
     * This function just inits LUserIniter component
     */
    public function init()
    {
        parent::init();
        $this->_steps = isset(LilyModule::instance()->session->data->userInitData->steps) ? LilyModule::instance()->session->data->userInitData->steps : null;
        $this->_stepId = isset(LilyModule::instance()->session->data->userInitData->stepId) ? LilyModule::instance()->session->data->userInitData->stepId : null;
        $this->_count = isset(LilyModule::instance()->session->data->userInitData->count) ? LilyModule::instance()->session->data->userInitData->count : null;
    }

    /**
     * Getter for $count property
     * @return integer
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * Getter for $steps property
     * @return array
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    /**
     * Getter for $stepId property
     * @return integer
     */
    public function getStepId()
    {
        return $this->_stepId;
    }

    /**
     * Getter for $step property
     * @return object
     */
    public function getStep()
    {
        return $this->steps[$this->stepId];
    }

    /**
     * This function sets Lily to next step, this function should be called by step controller after processing the form.
     */
    public function nextStep()
    {
        if (LilyModule::instance()->enableLogging)
            Yii::log("userIniter: passed step $this->stepId", CLogger::LEVEL_INFO, 'lily');
        if ($this->stepId < $this->count -1 ) {
            LilyModule::instance()->session->data->userInitData->stepId++;
            LilyModule::instance()->session->save();
            Yii::app()->request->redirect($this->steps[$this->stepId + 1]->page);
        } else {
            $this->finish();
        }
    }

    /**
     * This function adds route to allowed list of current step (if it isn't there yet)
     * @param string $route
     */
    public function allow($route)
    {
        if (!in_array($route, $this->step->allowed)) {
            $this->step->allowed[] = $route;
            LilyModule::instance()->session->data->userInitData->steps[$this->stepId]->allowed[] = $route;
            LilyModule::instance()->session->save();
        }
    }

    /**
     * Getter for isStarted property
     * @return bool
     */
    public function getIsStarted()
    {
        return $this->_isStarted;
    }

    /**
     * This function starts userIniter. It gets called by LilyModule::init if it assumes that current user isn't yet inited
     */
    public function start()
    {
        if ($this->isStarted) return;
        $this->_isStarted = true;
        if (LilyModule::instance()->enableLogging)
            Yii::log("userIniter started", CLogger::LEVEL_INFO, 'lily');
        $initRoute = LilyModule::route('user/init');
        if (!isset($this->steps)) {
            $count = 0;
            $steps = array();
            if($this->showStartStep) $steps[$count++] = (object)array(
                'page' => Yii::app()->createUrl($initRoute, array('action'=>'start')),
                'name' => "Start",
                'allowed' => array($initRoute),
            );
            foreach (LilyModule::instance()->relations as $name => $relation) {
                if (isset($relation['onRegister'])) {
                    $onRegister_route = is_array($relation['onRegister']) ? $relation['onRegister'][0] : $relation['onRegister'];
                    $onRegister_query = is_array($relation['onRegister']) ? array_slice($relation['onRegister'], 1) : array();
                    $steps[$count++] = (object)array(
                        'page' => Yii::app()->createUrl($onRegister_route, $onRegister_query),
                        'name' => $name,
                        'allowed' => array($onRegister_route),
                    );
                }
            }
            if($this->showFinishStep) $steps[$count++] = (object)array(
                'page' => Yii::app()->createUrl($initRoute, array('action'=>'finish')),
                'name' => "Finish",
                'allowed' => array($initRoute),
            );
            LilyModule::instance()->session->data->userInitData = new stdClass;
            LilyModule::instance()->session->data->userInitData->steps = $this->_steps = $steps;
            LilyModule::instance()->session->data->userInitData->stepId = $this->_stepId = 0;
            LilyModule::instance()->session->data->userInitData->count = $this->_count = $count;
            LilyModule::instance()->session->save();
        }
        $route = Yii::app()->urlManager->parseUrl(Yii::app()->request);
        Yii::log("userIniter started with route $route", CLogger::LEVEL_INFO, 'lily');
        if (!in_array($route, $this->step->allowed)
            && !in_array($route, LilyModule::instance()->allowedRoutes)
            && !in_array($route, array(LilyModule::route('user/logout')))
        ) {
            Yii::app()->request->redirect($this->step->page);
        }
    }

    /**
     * This function is called after last step gets executed and it finishes user init process
     */
    protected function finish()
    {
        if (LilyModule::instance()->enableLogging)
            LilyModule::instance()->user->inited = true;
        LilyModule::instance()->user->save();
        if (is_string($this->finishRedirectUrl))
            $redirectUrl = Yii::app()->createUrl($this->finishRedirectUrl);
        else
            $redirectUrl = Yii::app()->createUrl($this->finishRedirectUrl[0], array_slice($this->finishRedirectUrl, 1));
        Yii::log("userIniter finished", CLogger::LEVEL_INFO, 'lily');
        Yii::app()->request->redirect($redirectUrl);
    }

}

