<?php

/**
 * EAuthUserIdentity class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * EAuthUserIdentity is a base User Identity class to authenticate with EAuth.
 * @package application.extensions.eauth
 */
class LUserIdentity extends CBaseUserIdentity {

    const ERROR_NOT_AUTHENTICATED = 3;
    const ERROR_UNRECOGNIZED = 4;

    /**
     * @var EAuthServiceBase the authorization service instance.
     */
    protected $service;

    /**
     * @var string the unique identifier for the identity.
     */
    protected $id;

    /**
     * @var string the display name for the identity.
     */
    protected $name;

    /**
     * Constructor.
     * @param EAuthServiceBase $service the authorization service instance.
     */
    public function __construct($service) {
        $this->service = $service;
    }

    /**
     * Authenticates a user based on {@link service}.
     * This method is required by {@link IUserIdentity}.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate() {
        if ($this->service->isAuthenticated) {
            $id = $this->service->id;
            $service = $this->service->serviceName;
            $account = LAccount::model()->findByAttributes(array('id' => $id, 'service' => $service));
            Yii::log("LUserIdentity launched with service=$service, id=$id", 'info', 'lily.LUserIdentity.info');
            if (!isset($account))
                $account = LAccount::create($service, $id);
            if (!isset($account)) {
                $this->errorCode = self::ERROR_UNRECOGNIZED;
            } else {
                $session = LSession::create($account, (object) $this->service->getAttributes());
                if (!isset($account)) {
                    $this->errorCode = self::ERROR_UNRECOGNIZED;
                } else {
                    $this->id = $account->uid;
                    $this->name = $account->user->name;
                    $account->data = (object)array_merge((array)$account->data, (array)$session->data);
                    $this->setState('ssid', $session->ssid);
                    $this->setState('sid', $session->sid);
                    $this->errorCode = self::ERROR_NONE;
                }
            }
        } else {
            $this->errorCode = self::ERROR_NOT_AUTHENTICATED;
        }
        Yii::log("LUserIdentity finished with code $this->errorCode", 'info', 'lily.LUserIdentity.info');
        return !$this->errorCode;
    }

    /**
     * Returns the unique identifier for the identity.
     * This method is required by {@link IUserIdentity}.
     * @return string the unique identifier for the identity.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Returns the display name for the identity.
     * This method is required by {@link IUserIdentity}.
     * @return string the display name for the identity.
     */
    public function getName() {
        return $this->name;
    }

}