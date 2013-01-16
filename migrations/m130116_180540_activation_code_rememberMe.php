<?php

class m130116_180540_activation_code_rememberMe extends CDbMigration {

    public function safeUp() {
        $this->addColumn('{{lily_email_account_activation}}', 'rememberMe', 'boolean');
    }

    public function safeDown() {
        $this->dropColumn('{{lily_email_account_activation}}', 'rememberMe');
    }

}