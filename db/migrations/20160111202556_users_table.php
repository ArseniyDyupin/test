<?php

use Phinx\Migration\AbstractMigration;

class UsersTable extends AbstractMigration
{
    public function up()
    {
        $users_table = $this->table('users');
        $users_table->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('surname', 'string', ['limit' => 255])
            ->addColumn('patronymic', 'string', ['limit' => 255])
            ->addColumn('email', 'string', ['unique' => true, 'limit' => 255])
            ->addColumn('phone', 'string', ['unique' => true, 'limit' => 255])
            ->create();
    }

    public function down() {
        $this->dropTable('users');
    }
}
