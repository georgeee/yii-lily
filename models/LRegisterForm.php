<?php

/**
 * LLoginForm class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * This is a model class for login form implemention.
 * @package application.modules.lily.models
 */
class LRegisterForm extends CFormModel {

    /**
     * @var string email field
     */
    public $email;

    /**
     * @var string password field
     */
    public $password;

    /**
     * @var string password field
     */
    public $passwordRepeat;

    /**
     * @var string rememberMe field
     */
    public $rememberMe;
    public $service = "email";

    /**
     * Declares the validation rules.
     * @return array validation rules
     */
    public function rules() {
        return array(
            array('rememberMe', 'boolean'),
            //Special validator. It uses default validators, but on clientvalidation
            //prefixes 'em with JS code, that checks service set to email (otherwise it's incorrect to validate them)
            array('email, password', 'lily.components.LLoginFormValidator'),
            array('passwordRepeat', 'compare', 'compareAttribute' => 'password', 'message' => t('Passwords are not equal!')),
            array('passwordRepeat', 'required'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'rememberMe' => t('Remember me next time'),
        );
    }

}
