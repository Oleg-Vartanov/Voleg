<?php

namespace App\FixturePredictions\Command;


use App\Core\Service\CsvReader;
use App\FixturePredictions\Entity\FixturePrediction;
use App\FixturePredictions\Repository\FixturePredictionRepository;
use App\FixturePredictions\Repository\FixtureRepository;
use App\FixturePredictions\Repository\SeasonRepository;
use App\FixturePredictions\Repository\TeamRepository;
use App\FixturePredictions\Service\PredictionsService;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'import:predictions')]
class ImportResultsCommand extends Command
{
    public function __construct(
        private CsvReader $csvReader,
        private KernelInterface $kernel,
        private FixturePredictionRepository $fixturePredictionRepository,
        private UserRepository $userRepository,
        private TeamRepository $teamRepository,
        private FixtureRepository $fixtureRepository,
        private EntityManagerInterface $entityManager,
        private SeasonRepository $seasonRepository,
        private PredictionsService $predictionsService,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $oleg = $this->userRepository->findOneBy(['displayName' => 'Voleg']) ?? throw new Exception();
        $artem = $this->userRepository->findOneBy(['displayName' => 'Artem']) ?? throw new Exception();

        $folder = $this->kernel->getProjectDir().'/data/';
        $rows = $this->csvReader->read($folder.'fixtures.csv');
        $headers = [
            'home_team_id' => 0,
            'away_team_id' => 1,
            'artem_home_score' => 2,
            'artem_away_score' => 3,
            'oleg_home_score' => 4,
            'oleg_away_score' => 5,
        ];

        unset($rows[0]);

        $progressBar = new ProgressBar($output, count($rows));
        $progressBar->start();

        $season = $this->seasonRepository->findOneBy(['year' => 2023]) ?? throw new Exception('No season');

        foreach ($rows as $row) {
            if ($row[0] === null) continue;

            $homeTeam = $this->teamRepository->findOneBy(['id' => $row[$headers['home_team_id']]]) ?? throw new Exception($row[$headers['home_team_id']]);
            $awayTeam = $this->teamRepository->findOneBy(['id' => $row[$headers['away_team_id']]]) ?? throw new Exception($row[$headers['away_team_id']]);
            $fixture = $this->fixtureRepository->findOneBy(['homeTeam' => $homeTeam, 'awayTeam' => $awayTeam, 'season' => $season]) ?? throw new Exception();

            // Artem's prediction.
            $fixturePrediction = $this->fixturePredictionRepository->findOneBy(['fixture' => $fixture, 'user' => $artem]);
            if ($fixturePrediction === null) {
                $fixturePrediction = new FixturePrediction();
                $fixturePrediction->setFixture($fixture);
                $fixturePrediction->setUser($artem);
            }
            $fixturePrediction->setHomeScore($row[$headers['artem_home_score']]);
            $fixturePrediction->setAwayScore($row[$headers['artem_away_score']]);
            $this->entityManager->persist($fixturePrediction);

            // Oleg's prediction.
            if ($row[$headers['oleg_home_score']] != 100) {
                $fixturePrediction = $this->fixturePredictionRepository->findOneBy(['fixture' => $fixture, 'user' => $oleg]);
                if ($fixturePrediction === null) {
                    $fixturePrediction = new FixturePrediction();
                    $fixturePrediction->setFixture($fixture);
                    $fixturePrediction->setUser($oleg);
                }
                $fixturePrediction->setHomeScore($row[$headers['oleg_home_score']]);
                $fixturePrediction->setAwayScore($row[$headers['oleg_away_score']]);
                $this->entityManager->persist($fixturePrediction);
            }

            $this->predictionsService->updatePoints($fixture);
            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();
        $output->writeln(' Done');

        return Command::SUCCESS;
    }
}