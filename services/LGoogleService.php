<?php
/**
 * LGoogleService class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LGoogleService is a eauth service class.
 * It provides properties and fetching method for eauth extension to authenticate through Google OpenId service.
 *
 * @package application.modules.lily.services
 */

class LGoogleService extends GoogleOpenIDService
{

    /**
     * @var array the OpenID required attributes.
     */
    protected $requiredAttributes = array(
        'firstname' => array('firstname', 'namePerson/first'),
        'lastname' => array('lastname', 'namePerson/last'),
        'email' => array('email', 'contact/email'),
        'language' => array('language', 'pref/language'),
    );

    /**
     * Fetch attributes array.
     * @return boolean whether the attributes was successfully fetched.
     */
    protected function fetchAttributes()
    {
        $this->attributes['name'] = $this->attributes['firstname'] . ' ' . $this->attributes['lastname'];
        $this->attributes['displayId'] = $this->attributes['email'];
        return true;
    }
}