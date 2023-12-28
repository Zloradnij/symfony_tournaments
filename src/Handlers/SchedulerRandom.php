<?php


namespace App\Handlers;

use App\Entity\Games;
use App\Entity\Teams;
use App\Entity\Tournaments;
use App\EntityListener\TournamentsEntityListener;
use Doctrine\Persistence\ObjectManager;

class SchedulerRandom
{
    private ObjectManager $entityManager;

    public function create(Tournaments $tournament, ObjectManager $entityManager): void
    {
        $this->entityManager = $entityManager;

        $games = [];
        $teams = $tournament->getTeams();
        $teamsCount = $teams->count();

        $gamesLine = $this->getGamesLine($teamsCount);
        $i = 0;

        while (!empty($gamesLine)) {
            $date = (new \DateTime())->modify("+{$i} days")->format('Y-m-d');
            $games[$date] = [];

            for ($j = 1; $j <= TournamentsEntityListener::MAX_MATCH_COUNT; $j++) {
                if (empty($gamesLine)) {
                    continue;
                }

                $gameKey = $this->findGameKey($gamesLine, $games[$date]);

                if ($gameKey < 0) {
                    continue;
                }

                $games[$date][] = $gamesLine[$gameKey];

                unset($gamesLine[$gameKey]);
            }

            $i++;
        }

        $games = array_filter($games);

        $this->fillSchedule($tournament, $games);
    }

    protected function fillSchedule(Tournaments $tournament, array $gameData)
    {
        $teams = $tournament->getTeams();

        foreach ($gameData as $date => $gameInDay) {
            foreach ($gameInDay as $singleGame) {
                /** @var Teams $teamFirst */
                $teamFirst = $teams[$singleGame[0]];
                /** @var Teams $teamSecond */
                $teamSecond = $teams[$singleGame[1]];

                $gameItem = new Games();

                $gameItem->setDate(new \DateTime($date))
                    ->setTournament($tournament)
                    ->setFirstTeam($teamFirst)
                    ->setSecondTeam($teamSecond)
                ;

                $this->entityManager->persist($gameItem);
                $this->entityManager->flush();
            }
        }
    }

    protected function getGamesLine(int $teamsCount): array
    {
        $gamesLine = [];

        for ($i = 0; $i < $teamsCount; $i++) {
            for ($j = $i + 1; $j < $teamsCount; $j++) {
                $gamesLine[] = [
                    $i,
                    $j,
                ];
            }
        }

        return $gamesLine;
    }

    protected function findGameKey(array $gamesLine, array $gamesInDay): int
    {
        if (empty($gamesInDay)) {
            return array_rand($gamesLine);
        }

        $gamesLineCopy = $gamesLine;

        while (true) {
            $isset = false;

            if (count($gamesLineCopy) == 0) {
                return -1;
            }

            $gameKey = array_rand($gamesLineCopy);
            $currentGame = $gamesLineCopy[$gameKey];

            foreach ($gamesInDay as $item) {
                if (!empty(array_intersect($item, $currentGame))) {
                    $isset = true;

                    unset($gamesLineCopy[$gameKey]);
                }
            }

            if (!$isset) {
                return $gameKey;
            }
        }

        return -2;
    }
}
