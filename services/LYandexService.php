<?php
/**
 * LYandexService class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LYandexService is a eauth service class.
 * It provides properties and fetching method for eauth extension to authenticate through Yandex OAuth service.
 *
 * @package application.modules.lily.services
 */

class LYandexService extends YandexOpenIDService
{
    /**
     * @var array arguments for the jQuery.eauth() javascript function.
     */
    protected $jsArguments = array('popup' => array('width' => 900, 'height' => 620));
    /**
     * @var array the OpenID required attributes.
     */

    protected $requiredAttributes = array(
        'name' => array('fullname', 'namePerson'),
        'username' => array('nickname', 'namePerson/friendly'),
        'email' => array('email', 'contact/email'),
        'sex' => array('gender', 'person/gender'),
        'birthday' => array('birthday', 'birthDate'),
    );

    /**
     * Fetch attributes array.
     * @return boolean whether the attributes was successfully fetched.
     */
    protected function fetchAttributes()
    {
        $this->attributes['sex'] = ($this->attributes['sex'] == 'M');
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
            $this->attributes['birthday'] = CDateTimeParser::parse($this->attributes['birthday'], 'yyyy-MM-dd');
    }

}