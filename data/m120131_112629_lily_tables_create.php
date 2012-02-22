<?php

class m120131_112629_lily_tables_create extends CDbMigration {

    public function up() {
        $options = null;
        if($this->dbConnection->driverName == 'mysql') $options = 'ENGINE=InnoDB';

        $this->createTable('{{lily_user}}', array(
            'uid' => 'pk',
            'deleted' => 'integer',
            'active' => 'boolean',
            'inited' => 'boolean',
        ), $options);
        $this->createTable('{{lily_account}}', array(
            'aid' => 'pk',
            'uid' => 'integer',
            'service' => 'string NOT NULL',
            'id' => 'string NOT NULL',
            'hidden' => 'boolean',
            'data' => 'binary',
            'created' => 'integer',
        ), $options);
        $this->createTable('{{lily_email_account_activation}}', array(
            'code_id' => 'pk',
            'uid' => 'integer',
            'email' => 'string NOT NULL',
            'password' => 'string NOT NULL',
            'code' => 'string NOT NULL',
            'created' => 'integer',
        ), $options);
        $this->createTable('{{lily_session}}', array(
            'sid' => 'pk',
            'aid' => 'integer',
            'data' => 'binary',
            'ssid' => 'string NOT NULL',
            'created' => 'integer',
        ), $options);
        $this->createTable('{{lily_onetime}}', array(
            'tid' => 'pk',
            'uid' => 'integer',
            'token' => 'string NOT NULL',
            'created' => 'integer',
        ), $options);
        $this->createIndex('service_id', '{{lily_account}}', 'service,id', true);
    }

    public function down() {
        $this->dropTable('{{lily_user}}');
        $this->dropTable('{{lily_account}}');
        $this->dropTable('{{lily_email_account_activation}}');
        $this->dropTable('{{lily_session}}');
        $this->dropTable('{{lily_onetime}}');
    }

}