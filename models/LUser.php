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
 * @property integer $deleted Deleted status. '0' means not deleted, '-1' - completely deleted and >=1 - id of the user,
 * to which this user was appended to (see docs on user merging)
 * @property bool $active Whether user active or not
 * @property bool $inited Is user inited or not
 * @property string $nameId String representation of user in format "%name% (id %id)"
 * @property integer $id Alias for $uid property
 * @property string $name Name of the user, if speciefed (see docs on relations property of the module) or it's uid if not
 * @property array $accountIds Array of integers - account ids, refered to the user
 *
 *
 * @package application.modules.lily.models
 */
class LUser extends CActiveRecord
{
    /**
     * Getter for nameId property
     * @return string
     */
    public function getNameId()
    {
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
    public function getId()
    {
        return $this->uid;
    }

    /**
     * Getter for name property
     * @return mixed
     */
    public function getName()
    {
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
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{lily_user}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('deleted, inited, active', 'safe', 'on' => 'search'),
            array('deleted, inited', 'default', 'value' => 0),
            array('active', 'default', 'value' => 1),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        //Empty array of default relations, possibly later it will contain something...
        $relations = array();
        return array_merge($relations, LilyModule::instance()->userRelations);
    }

    //TODO What will happen if id==null
    public function getAccountIds($uid = null)
    {
        if (!isset($uid))
            $uid = $this->uid;
        $ids = $this->getDbConnection()->createCommand()->select('aid')->from(LAccount::model()->tableName())->where('uid=:uid', array(':uid' => $uid))->queryColumn();
        return $ids;
    }


    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'uid' => LilyModule::t('User id'),
            'deleted' => LilyModule::t('Deleted status'),
            'active' => LilyModule::t('Active'),
            'inited' => LilyModule::t('Inited status'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('uid', $this->uid);
        $criteria->compare('deleted', $this->deleted);
        $criteria->compare('active', $this->active);
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
    public static function create()
    {
        $user = new LUser;
        if (!$user->save()) {
            return null;
        }
        $account = LAccount::create('onetime', $user->uid, null, $user->uid);
        if (!isset($account)) return null;
        $account->hidden = 1;
        if (!$account->save()) return null;

        return $user;
    }
}