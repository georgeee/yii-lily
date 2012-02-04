<?php

class m120131_112629_lily_tables_create extends CDbMigration {

    public function up() {
        $this->createTable('{{lily_user}}', array(
            'uid' => 'pk',
            'name' => 'string',
            'birthday' => 'date',
            'sex' => 'boolean'
        ), 'ENGINE=InnoDB');
        $this->createTable('{{lily_account}}', array(
            'aid' => 'pk',
            'uid' => 'integer',
            'service' => 'string NOT NULL',
            'id' => 'string NOT NULL',
            'data' => 'binary',
            'created' => 'integer',
        ), 'ENGINE=InnoDB');
        $this->createTable('{{lily_email_account_activation}}', array(
            'code_id' => 'pk',
            'uid' => 'integer',
            'email' => 'string NOT NULL',
            'password' => 'string NOT NULL',
            'code' => 'string NOT NULL',
            'created' => 'integer',
        ), 'ENGINE=InnoDB');
        $this->createTable('{{lily_session}}', array(
            'sid' => 'pk',
            'aid' => 'integer',
            'data' => 'binary',
            'ssid' => 'string NOT NULL',
            'created' => 'integer',
        ), 'ENGINE=InnoDB');
        $this->createIndex('email', '{{lily_email_account}}', 'email', true);
        $this->createIndex('service_user', '{{lily_account}}', 'service_id,user_id', true);
    }

    public function down() {
        $this->dropTable('{{lily_user}}');
        $this->dropTable('{{lily_account}}');
        $this->dropTable('{{lily_session}}');
        $this->dropTable('{{lily_email_account_activation}}');
    }

}