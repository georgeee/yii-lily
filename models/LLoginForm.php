<?php
/**
 * LLoginForm class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LLoginForm is a model class for login form implemention.
 * @package application.modules.lily.models
 */
class LLoginForm extends CFormModel
{
    /**
     * @var string email field
     */
    public $email;
    /**
     * @var string password field
     */
    public $password;
    /**
     * @var string rememberMe field
     */
    public $rememberMe;
    /**
     * @var string service name field
     */
    public $service;
    /**
     * @var string Form HTML id attribute.
     * It's used by LAuthWidget in order to pass html attribute to validator.
     */
    public $id;
    /**
     * @var array services, available for selection (in order to use by validator)
     */
    public $services;

    public function __construct($scenario = '', $services = null)
    {
        parent::__construct($scenario);
        if (!isset($services)) $services = array_keys(LilyModule::instance()->services);
        $this->services = $services;
    }

/**
* Declares the validation rules.
* @return array validation rules
*/
    public function rules()
    {
        return array(
            array('service', 'in', 'range' => $this->services),
            array('rememberMe', 'boolean'),
            //Special validator. It uses default validators, but on clientvalidation
            //prefixes 'em with JS code, that checks service set to email (otherwise it's incorrect to validate them)
            array('email, password', 'lily.components.LLoginFormValidator'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'rememberMe' => LilyModule::t('Remember me next time'),
        );
    }

}
