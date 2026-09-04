<?php

namespace App\FixturePredictions\DataFixture;

use App\FixturePredictions\Entity\Fixture as FpFixture;
use App\FixturePredictions\Entity\Team;
use App\FixturePredictions\Enum\CompetitionCodeEnum;
use App\FixturePredictions\Enum\FixtureStatusEnum;
use App\FixturePredictions\Repository\CompetitionRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Repository\TeamRepository;
use App\FixturePredictions\Service\Seeder\SeasonSeeder;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;
use RuntimeException;

class FixtureFixture extends Fixture implements DependentFixtureInterface
{
    private const array KICKOFF_HOURS = [12, 14, 15, 17];

    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly SeasonRepository $seasonRepository,
        private readonly TeamRepository $teamRepository,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getDependencies(): array
    {
        return [
            TeamFixture::class,
        ];
    }

    /**
     * @throws RandomException|DateMalformedStringException
     */
    public function load(ObjectManager $manager): void
    {
        $competition = $this->competitionRepository->findOneByCode(CompetitionCodeEnum::EPL->value);
        if ($competition === null) {
            throw new RuntimeException('Premier League competition must be seeded before loading fixtures.');
        }

        $seasonYear = SeasonSeeder::CURRENT_SEASON_YEAR;
        $season = $this->seasonRepository->findOneByYear($seasonYear);
        if ($season === null) {
            throw new RuntimeException('Season '.$seasonYear.' must be seeded before loading fixtures.');
        }

        /** @var list<Team> $teams */
        $teams = $this->teamRepository->findBy([], ['id' => 'ASC'], limit: 20);
        if (count($teams) !== 20) {
            throw new RuntimeException('At least 20 teams must exist before loading fixtures.');
        }

        $matchdays = $this->buildDoubleRoundRobin($teams);
        $weekends = $this->weekendDates($seasonYear, count($matchdays));
        // Cut off scores at year-end so results don't depend on when fixtures are loaded.
        $scoresUntil = new DateTimeImmutable(sprintf('%d-12-31 23:59:59', $seasonYear));
        $providerId = 1000;

        foreach ($matchdays as $matchdayIndex => $pairs) {
            $saturday = $weekends[$matchdayIndex];
            $sunday = $saturday->modify('+1 day');
            $gamesPerDay = (int) ceil(count($pairs) / 2);

            foreach ($pairs as $gameIndex => [$home, $away]) {
                $isSunday = $gameIndex >= $gamesPerDay;
                $day = $isSunday ? $sunday : $saturday;
                $slot = $isSunday ? $gameIndex - $gamesPerDay : $gameIndex;
                $hour = self::KICKOFF_HOURS[$slot % count(self::KICKOFF_HOURS)];
                $startAt = $day->setTime($hour, 0);

                $fixture = new FpFixture();
                $fixture->setSeason($season);
                $fixture->setCompetition($competition);
                $fixture->setHomeTeam($home);
                $fixture->setAwayTeam($away);
                $fixture->setMatchday($matchdayIndex + 1);
                $fixture->setProviderFixtureId($providerId++);
                $fixture->setStartAt($startAt);

                if ($startAt <= $scoresUntil) {
                    $fixture->setHomeScore(random_int(0, 4));
                    $fixture->setAwayScore(random_int(0, 4));
                    $fixture->setStatus(FixtureStatusEnum::Finished);
                } else {
                    $fixture->setStatus(FixtureStatusEnum::Scheduled);
                }

                $manager->persist($fixture);
            }
        }

        $manager->flush();
    }

    /**
     * @param list<Team> $teams
     *
     * @return list<list<array{0: Team, 1: Team}>>
     */
    private function buildDoubleRoundRobin(array $teams): array
    {
        $rotation = array_values($teams);
        $teamCount = count($rotation);
        $half = intdiv($teamCount, 2);
        $firstHalf = [];

        for ($round = 0; $round < $teamCount - 1; $round++) {
            $pairs = [];
            for ($i = 0; $i < $half; $i++) {
                $home = $rotation[$i];
                $away = $rotation[$teamCount - 1 - $i];
                if ($round % 2 === 1 && $i === 0) {
                    [$home, $away] = [$away, $home];
                }
                $pairs[] = [$home, $away];
            }
            $firstHalf[] = $pairs;

            $fixed = array_shift($rotation);
            $last = array_pop($rotation);
            array_unshift($rotation, $last);
            array_unshift($rotation, $fixed);
        }

        $secondHalf = [];
        foreach ($firstHalf as $pairs) {
            $secondHalf[] = array_map(
                static fn (array $pair): array => [$pair[1], $pair[0]],
                $pairs,
            );
        }

        return [...$firstHalf, ...$secondHalf];
    }

    /**
     * @return list<DateTimeImmutable>
     * @throws DateMalformedStringException
     */
    private function weekendDates(int $seasonYear, int $matchdayCount): array
    {
        $date = new DateTimeImmutable(sprintf('%d-08-10', $seasonYear));
        while ((int) $date->format('N') !== 6) {
            $date = $date->modify('+1 day');
        }

        $dates = [];
        for ($i = 0; $i < $matchdayCount; $i++) {
            $dates[] = $date;
            $date = $date->modify('+7 days');
        }

        return $dates;
    }
}
