<?php

/**
 * An example of extending the provider class.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class LYandexService extends YandexOpenIDService {

    protected $jsArguments = array('popup' => array('width' => 900, 'height' => 620));
    protected $requiredAttributes = array(
        'name' => array('fullname', 'namePerson'),
        'username' => array('nickname', 'namePerson/friendly'),
        'email' => array('email', 'contact/email'),
        'sex' => array('gender', 'person/gender'),
        'birthday' => array('birthday', 'birthDate'),
    );

    protected function fetchAttributes() {
        $this->attributes['sex'] = ($this->attributes['sex']=='M');
        $this->attributes['url'] = $this->id;
        if (isset($this->attributes['name']) && !empty($this->attributes['name']))
            $this->attributes['displayId'] = $this->attributes['name'];
        else if (isset($this->attributes['email']) && !empty($this->attributes['email']))
            $this->attributes['displayId'] = $this->attributes['email'];
        else if (isset($this->attributes['username']) && !empty($this->attributes['username']))
            $this->attributes['displayId'] = $this->attributes['username'];
        else
            $this->attributes['displayId'] = $this->id;
        if (isset($this->attributes['birthday']) && !empty($this->attributes['birthday']))
            $this->attributes['birthday'] = Yii::app()->dateFormatter->formatDateTime(CDateTimeParser::parse($this->attributes['birthday'], 'yyyy-MM-dd'), 'medium', NULL);
    }

}