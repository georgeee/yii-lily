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
class LUser extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return LUser the static model class
	 */
	public static function model($className=__CLASS__)
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
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
//			array('name', 'required'),
//			array('sex', 'numerical', 'integerOnly'=>true),
//			array('name', 'length', 'max'=>255),
//			array('birthday', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('uid, name, birthday, sex', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
                    'accounts' => array(self::HAS_MANY, 'LAccount', 'uid'),
                    'emailActivations' => array(self::HAS_MANY, 'LAccount', 'uid'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'uid' => 'Uid',
			'name' => 'Name',
			'birthday' => 'Birthday',
			'sex' => 'Sex',
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

		$criteria=new CDbCriteria;

		$criteria->compare('uid',$this->uid);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('birthday',$this->birthday,true);
		$criteria->compare('sex',$this->sex);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}