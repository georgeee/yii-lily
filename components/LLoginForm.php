<?php

class LLoginForm extends CFormModel {

    public $email;
    public $password;
    public $rememberMe;
    public $service;
    public $id;
    
    public $services;
    
    public function __construct($scenario = '', $services = null) {
        parent::__construct($scenario);
        if(!isset($services)) $services = array_keys(Yii::app()->eauth->getServices());
        $this->services = $services;
    }
    
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules() {
        return array(
            array('service', 'in', 'range' => $this->services),
            // username and password are required
            // rememberMe needs to be a boolean
            array('rememberMe', 'boolean'),
            // password needs to be authenticated
            array('email, password', 'lily.components.LLoginFormValidator'),
        );
    }
    
    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'rememberMe' => 'Remember me next time',
        );
    }

}
