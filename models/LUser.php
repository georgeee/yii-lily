<?php

/**
 * This is the model class for table "{{user}}".
 *
 * The followings are the available columns in table '{{user}}':
 * @property integer $uid
 * @property string $name
 * @property string $birthday
 * @property integer $sex
 */
class LUser extends CActiveRecord {

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

    public function getId() {
        return $this->uid;
    }

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
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('uid, deleted, inited, active', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        $relations = array(
            'accounts' => array(self::HAS_MANY, 'LAccount', 'uid'),
            'emailActivations' => array(self::HAS_MANY, 'LAccount', 'uid'),
        );
        return array_merge($relations, LilyModule::instance()->userRelations);
    }

    //TODO What will happen if id==null
    public function getAccountIds($uid = null) {
        if (!isset($uid))
            $uid = $this->uid;
//        LAccount::model()->
        $ids = $this->getDbConnection()->createCommand()->select('aid')->from(LAccount::model()->tableName())->where('uid=:uid', array(':uid' => $this->uid))->queryColumn();
        return $ids;
    }

    public function appendUid($uid) {
        $count = count($this->getAccountIds($uid));
        $affected = $this->getDbConnection()->createCommand()->update(LAccount::model()->tableName(), array('uid' => $this->uid), 'uid=:oid', array(':oid' => $uid));
        return $count == $affected;
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'uid' => 'User id',
            'deleted' => 'Deleted status',
            'active' => 'Active',
            'inited' => 'Inited status',
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
        $criteria->compare('deleted', $this->deleted);
        $criteria->compare('active', $this->active);
        $criteria->compare('inited', $this->inited);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

}