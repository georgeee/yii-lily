<?php
/**
 * LOneTimeService class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LOneTimeService is a eauth service class.
 * It provides authentication using one-time login urls.
 *
 * @package application.modules.lily.services
 */
class LOneTimeService extends EAuthServiceBase implements IAuthService
{
    public function getServiceType(){
        return 'hidden';
    }

    public function getServiceTitle(){
        return 'One Time Login';
    }

    public function getServiceName(){
        return 'onetime';
    }


    /**
     * @var string Login token
     */
    public $token;

    /**
     * This function simply tries to authenticate auth identity (see docs about user authenticating behaviour)
     * @return bool is user identity authenticated or authentication failed
     */
    public function authenticate()
    {
        $this->authenticated = false;

        if (!isset($this->token)) {
            return false;
        }
        $token = LOneTime::model()->findByAttributes(array('token'=>$this->token));

        if (isset($token)) {
            $this->attributes['id'] = $token->uid;
            $this->authenticated = true;
        }
        return $this->authenticated;
    }

}

?>
