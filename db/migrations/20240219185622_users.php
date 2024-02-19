<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Users extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        if (!$this->hasTable('Users')) {
            $table = $this->table('Users');
            $table->addColumn('email', 'string', ['limit' => 128, 'null' => false])
                  ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
                  ->addColumn('ruolo', 'string', ['limit' => 128, 'null' => true])
                  ->addIndex('email', ['unique' => true])
                  ->create();
        }
    }
}