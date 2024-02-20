<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Prenotazioni extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('Prenotazioni');
            $table->addColumn('titolo', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('arrivo', 'date', ['null' => false])
            ->addColumn('partenza', 'date', ['null' => false])
            ->addColumn('capo_gruppo', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('telefono', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('numero_allegati', 'integer', ['null' => true])
            ->addColumn('stato', 'integer', ['null' => false])
            ->addColumn('colore', 'integer', ['limit' => 7, 'null' => false])
            ->addColumn('termine_saldo', 'date', ['null' => false])
            ->addColumn('caparre', 'text', ['null' => true])
            ->addColumn('totale', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->create();
    }
}
