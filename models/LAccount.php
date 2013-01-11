<?php

/**
 * LAccount class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LAccount is a model class, that serves to manage with service accounts.
 *
 * Two words about handling data:
 * Data is contained in unserialized way. After retrieving from database or saving, it gets unserialized and before saving it gets serialized back.
 * Maybe later it should be rewrited to minimize count of serialize/unserialize actions
 *
 *
 * @property string $displayId String representation of the account, optimized for displaying to user
 * @property string $serviceName Name of service (e.g. not google, but Google)
 * @property integer $aid Account id, null if account isn't saved to DB yet
 * @property integer $uid User id, to which this account belogns to
 * @property string $service Name of the service, used by this account
 * @property string $id Identifer of the user in service. User is authenticated by this identifer (and service of course)
 * @property integer $created Timestamp of the moment, this account was created
 * @property object $data Account data object
 *
 * @package application.modules.lily.models
 */
class LAccount extends CActiveRecord {

    /**
     * Getter for displayId property
     * @return string
     */
    public function getDisplayId() {
        if (isset($this->data->displayId))
            return $this->data->displayId;
        else
            return $this->id;
    }

    /**
     * Getter for serviceName property
     * @return string
     */
    public function getServiceName() {
        $services = LilyModule::instance()->services;
        return $services[$this->service]->title;
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return LAccount the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{lily_account}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
            array('hidden', 'default', 'value' => 0),
            array('uid, service, id, created', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'LUser', 'uid'),
            'sessions' => array(self::HAS_MANY, 'LSession', 'aid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'aid' => LilyModule::t("Account id"),
            'uid' => LilyModule::t("User id"),
            'service' => LilyModule::t("Service"),
            'id' => LilyModule::t("Service user id"),
            'created' => LilyModule::t('Created'),
        );
    }

    /**
     * This method simply unserializes data attribute
     */
    protected function unserializeData() {
        $this->data = unserialize($this->data);
    }

    /**
     * This method simply serializes data attribute
     */
    protected function serializeData() {
        $this->data = serialize($this->data);
    }

    /**
     * After find handler, gets executed after model instance being retrieved from database
     */
    protected function afterFind() {
        parent::afterFind();
        $this->unserializeData();
    }

    /**
     * After save handler, gets executed after model instance being saved to database
     */
    protected function afterSave() {
        parent::afterSave();
        $this->unserializeData();
    }

    /**
     * Before save handler, gets executed before model instance being saved to database
     * @return bool true, we haven't to disallow saving action
     */
    protected function beforeSave() {
        parent::beforeSave();
        $this->serializeData();
        return true;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria = new CDbCriteria;
        $criteria->compare('uid', $this->uid);
        $criteria->compare('service', $this->service, true);
        $criteria->compare('id', $this->id, true);
        $criteria->compare('created', $this->created, true);
        $criteria->compare('hidden', 0);
        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Perform the creation of new account
     * Return created account or null if creation failed
     * @param string $service Service id
     * @param string $id User's id in the service
     * @param object $data Optional service data
     * @param integer $uid User id (account instance), null if it's new account
     * @return LAccount created account instance
     */
    public static function create($service, $id, $data = null, $uid = null) {
        if (!isset($uid)) {
            $uid = LUser::create();
            if (!isset($uid)) {
                if (LilyModule::instance()->enableLogging)
                    Yii::log("LAccount::create($service, $id,..) failed on new User creation", CLogger::LEVEL_ERROR, 'lily');
                return null;
            }
        }
        if (is_object($uid))
            $uid = $uid->uid;
        $account = new LAccount;
        $account->uid = $uid;
        $account->service = $service;
        $account->id = $id;
        $account->data = $data;
        $account->created = time();
        $account->hidden = LilyModule::instance()->allServices[$service]->type == 'hidden';
        if ($account->save()) {
            if (LilyModule::instance()->enableLogging)
                Yii::log("LAccount::create($service, $id,..) successfully created new account aid={$account->aid}", CLogger::LEVEL_INFO, 'lily');
            return $account;
        } else {
            if (LilyModule::instance()->enableLogging)
                Yii::log("LAccount::create($service, $id,..) failed on account saving:\n\$account:\n" . print_r($account->getAttributes(), 1) . "\nerrors:\n" . print_r($account->getErrors(), 1), CLogger::LEVEL_WARNING, 'lily');
            return null;
        }
    }

    function getDeletedStateLabel($data, $row, $obj) {
        switch ($data->deleted) {
            case 0:
                return LilyModule::t("Not deleted");
                break;
            case -1:
                return LilyModule::t("Totally deleted");
                break;
            default:
                return LilyModule::t("Appended to user {user}", array("{user}" => CHtml::link(CHtml::encode($data->reciever->name), array("user/view", "uid" => $data->deleted)))
                );
                break;
        }
    }

}