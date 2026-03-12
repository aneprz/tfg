<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUniqueAchievementNamePerGame extends AbstractMigration
{
    public function change(): void
    {
        $this->table('Logros')
            ->addIndex(
                ['id_videojuego', 'nombre_logro'],
                [
                    'unique' => true,
                    'name' => 'unique_game_achievement_name'
                ]
            )
            ->update();
    }
}