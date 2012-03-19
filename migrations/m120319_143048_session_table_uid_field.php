<?php

class m120319_143048_session_table_uid_field extends CDbMigration
{
	public function up()
	{
        $this->addColumn('{{lily_session}}', 'uid', 'integer');
	}

	public function down()
	{
		$this->dropColumn('{{lily_session}}', 'uid');
	}

}