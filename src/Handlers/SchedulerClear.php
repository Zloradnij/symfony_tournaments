<?php


namespace App\Handlers;

use App\Entity\Tournaments;
use Doctrine\Persistence\ObjectManager;

class SchedulerClear
{
    public function clear(Tournaments $tournament, ObjectManager $entityManager): void
    {
        $games = $tournament->getGames();

        foreach ($games as $game) {
            $tournament->removeGame($game);
        }

        $entityManager->flush();
    }
}
