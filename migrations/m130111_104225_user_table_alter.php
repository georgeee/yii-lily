<?php

class m130111_104225_user_table_alter extends CDbMigration {

    public function safeUp() {
        $this->dropColumn('{{lily_user}}', 'active');
        $this->renameColumn('{{lily_user}}', 'deleted', 'state');
    }

    public function safeDown() {
        $this->addColumn('{{lily_user}}', 'active', 'boolean');
        $this->renameColumn('{{lily_user}}', 'state', 'deleted');
    }

}