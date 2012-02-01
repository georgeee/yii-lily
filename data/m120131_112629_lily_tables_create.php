<?php

class m120131_112629_lily_tables_create extends CDbMigration {

    public function up() {
        $this->createTable('{{user}}', array(
            'uid' => 'pk',
            'name' => 'string NOT NULL',
            'birthday' => 'date',
            'sex' => 'boolean'
        ), 'ENGINE=InnoDB');
        $this->createTable('{{account}}', array(
            'aid' => 'pk',
            'uid' => 'integer',
            'service_id' => 'string NOT NULL',
            'user_id' => 'string NOT NULL',
            'service_data' => 'binary',
            'created' => 'timestamp',
        ), 'ENGINE=InnoDB');
        $this->createTable('{{email_account}}', array(
            'email_id' => 'pk',
            'email' => 'string NOT NULL',
            'password' => 'string NOT NULL',
        ), 'ENGINE=InnoDB');
        $this->createTable('{{email_account_activation}}', array(
            'code_id' => 'pk',
            'email' => 'string NOT NULL',
            'password' => 'string NOT NULL',
            'code' => 'string NOT NULL',
            'created' => 'integer',
        ), 'ENGINE=InnoDB');
        $this->createIndex('email', '{{email_account}}', 'email', true);
        $this->createIndex('service_user', '{{account}}', 'service_id,user_id', true);
    }

    public function down() {
        $this->dropTable('{{user}}');
        $this->dropTable('{{account}}');
        $this->dropTable('{{email_account}}');
        $this->dropTable('{{email_account_activation}}');
    }

}