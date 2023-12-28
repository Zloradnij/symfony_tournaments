<?php


namespace App\Handlers;

use App\Entity\Games;
use App\Entity\Tournaments;

class SchedulerShow
{
    public function getGrid(Tournaments $tournament): array
    {
        $grid = [];
        $games = $tournament->getGames();

        /** @var Games $game */
        foreach ($games as $game) {
            $grid[$game->getDate()->format('Y-m-d')][] = $game;
        }

        return $grid;
    }
}
