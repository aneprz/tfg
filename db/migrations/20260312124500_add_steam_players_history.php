<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSteamPlayersHistory extends AbstractMigration
{
    public function change(): void
    {
        $this->table('Steam_Players_History', ['id' => 'id_steam_players'])
            ->addColumn('steam_appid', 'integer', ['signed' => false])
            ->addColumn('current_players', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('peak_today', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('captured_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['steam_appid', 'captured_at'])
            ->addIndex(['captured_at'])
            ->create();
    }
}

