<?php

/**
 * LUser class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LUser is a model class. It's the main class to manage with users (relations from module configurations will be applied here).
 *
 * @property integer $uid User id
 * @property integer $state State of user. '0' means not deleted, '-1' - deleted, '-2' - banned and >=1 - id of the user,
 * to which this user was appended to (see docs on user merging)
 * @property bool $inited Is user inited or not
 * 
 * @property string $nameId String representation of user in format "%name% (id %id)"
 * @property integer $id Alias for $uid property
 * @property string $name Name of the user, if speciefed (see docs on relations property of the module) or it's uid if not
 * @property array $accountIds Array of integers - account ids, refered to the user
 *
 *
 * @package application.modules.lily.models
 */
class LUser extends CActiveRecord {

    const DELETED_STATE = -1;
    const BANNED_STATE = -2;
    const ACTIVE_STATE = 0;

    /**
     * Getter for nameId property
     * @return string
     */
    public function getNameId() {
        $result = '';
        if (isset($this->name)) {
            $result .= $this->name;
        } else {
            $result .= '<' . LilyModule::t('Name not set') . '>';
        }
        $result .= ' (';
        $result .= $this->getAttributeLabel('id');
        $result .= ' ';
        if (isset($this->id)) {
            $result .= $this->id;
        } else {
            $result .= '<' . LilyModule::t('User id not set') . '>';
        }
        $result .= ')';
        return $result;
    }

    /**
     * Getter for id property
     * @return int
     */
    public function getId() {
        return $this->uid;
    }

    /**
     * Getter for name property
     * @return mixed
     */
    public function getName() {
        $userNameFunction = LilyModule::instance()->userNameFunction;
        if (isset($userNameFunction))
            return call_user_func($userNameFunction, $this);
        return $this->uid;
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return LUser the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{lily_user}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
            array('state, inited', 'safe', 'on' => 'search'),
            array('state, inited', 'default', 'value' => 0),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        //Empty array of default relations, possibly later it will contain something...
        $relations = array(
            'reciever' => array(self::BELONGS_TO, 'LUser', 'state'),
        );
        return array_merge($relations, LilyModule::instance()->userRelations);
    }

    public function getAccountIds($uid = null) {
        if (!isset($uid))
            $uid = $this->uid;
        $ids = $this->getDbConnection()->createCommand()->select('aid')->from(LAccount::model()->tableName())->where('uid=:uid', array(':uid' => $uid))->queryColumn();
        return $ids;
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'uid' => LilyModule::t('User id'),
            'state' => LilyModule::t('State of user'),
            'inited' => LilyModule::t('Initialization status'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('uid', $this->uid);
        $criteria->compare('state', $this->state);
        $criteria->compare('inited', $this->inited);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Perform the creation of new user
     * Return created user or null if creation failed
     * @return LUser created user instance
     */
    public static function create() {
        $user = new LUser;
        if (!$user->save()) {
            throw new CDbException("failed to create new user");
        }
        $account = LAccount::create('onetime', $user->uid, null, $user->uid);
        Yii::log("Created new user with uid {$user->uid}", CLogger::LEVEL_INFO, 'lily');
        return $user;
    }

    /**
     * Returns the state label for user
     * @param LUser $user
     * @return string label
     */
    public static function getStateLabel($user) {
        if (!isset($user))
            return null;
        switch ($user->state) {
            case self::ACTIVE_STATE:
                return LilyModule::t("Active");
                break;
            case self::DELETED_STATE:
                return LilyModule::t("Deleted");
                break;
            case self::BANNED_STATE:
                return LilyModule::t("Banned");
                break;
            default:
                return LilyModule::t ("Appended to {user}", array("{user}" => CHtml::link(CHtml::encode($user->reciever->name), array("user/view", "uid" => $user->state)))
                );
                break;
        }
    }

    /**
     * Returns the initialization state label for user
     * @param LUser $user
     * @return string label
     */
    public static function getInitedLabel($user) {
        if (!isset($user))
            return null;
        return $user->inited ? LilyModule::t("Initialized") : LilyModule::t("Not initialized");
    }

}