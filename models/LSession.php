<?php

/**
 * This is the model class for table "{{account}}".
 *
 * The followings are the available columns in table '{{account}}':
 * @property integer $uid
 * @property string $service_id
 * @property string $user_id
 * @property string $service_data
 * @property string $created
 */
class LSession extends CActiveRecord {

    protected $unserialized = false;

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
        return '{{lily_session}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
//			array('service_id, user_id, created', 'required'),
//			array('uid', 'numerical', 'integerOnly'=>true),
//			array('service_id, user_id', 'length', 'max'=>255),
//			array('service_data', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('sid, aid, created', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'account' => array(self::BELONGS_TO, 'LAccount', 'aid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'sid' => 'Session id',
            'aid' => 'Account id',
            'created' => 'Created',
        );
    }

    public function checkUnserialized() {
        if (!$this->unserialized) {
            $this->data = unserialize($this->data);
            $this->unserialized = true;
        }
    }

    public function checkSerialized() {
        if ($this->unserialized) {
            $this->data = serialize($this->data);
            $this->unserialized = false;
        }
    }

    protected function afterFind() {
        parent::afterFind();
        $this->checkUnserialized();
    }

    protected function afterSave() {
        parent::afterSave();
        $this->checkUnserialized();
    }

    protected function beforeSave() {
        parent::beforeSave();
        $this->checkSerialized();
        return true;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('sid', $this->sid);
        $criteria->compare('aid', $this->aid, true);
        $criteria->compare('created', $this->created, true);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Launchs new session
     * @param string $aid Account id (or instance)
     * @return LSession
     */
    public static function create($aid = null, $data = null) {
        if (!isset($aid)) {
            return null;
        }
        if (is_object($aid))
            $aid = $aid->aid;
        $session = new LSession;
        $session->aid = $aid;
        $session->created = time();
        $session->unserialized = true;
        $session->data = $data;
        $session->ssid = LilyModule::instance()->generateRandomString();
        return $session->save() ? $session : null;
    }

}