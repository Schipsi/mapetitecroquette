<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Repository\LeagueRepository;
use App\Service\ProcessGameCompletion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchGamesCommand extends Command
{
    protected static $defaultName = 'app:fetch-games';
    private HttpClientInterface $httpClient;
    private GameRepository $gameRepository;
    private LeagueRepository $leagueRepository;
    private EntityManagerInterface $em;
    private ProcessGameCompletion $processGameCompletion;

    public function __construct(
        HttpClientInterface $httpClient,
        GameRepository $gameRepository,
        LeagueRepository $leagueRepository,
        EntityManagerInterface $em,
        ProcessGameCompletion $processGameCompletion,
    ) {
        parent::__construct();

        $this->httpClient = $httpClient;
        $this->gameRepository = $gameRepository;
        $this->leagueRepository = $leagueRepository;
        $this->em = $em;
        $this->processGameCompletion = $processGameCompletion;
    }

    protected function configure(): void
    {
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $leagues = $this->leagueRepository->findBy(['active' => true]);

        foreach ($leagues as $league) {
            $output->writeln(\sprintf('Fetching game for %s', $league->getFullName()));
            $requestUrl = \sprintf('https://esports-api.lolesports.com/persisted/gw/getSchedule?hl=fr-FR&leagueId=%s', $league->getId());

            $response = $this->httpClient->request(
                'GET',
                $requestUrl,
                [
                    'headers' => [
                        'x-api-key' => '0TvQnueqKa5mxJntVWt0w4LpLfEkrV1Ta8rQBb9Z',
                    ],
                ]
            );

            $decodedResponse = \json_decode($response->getContent(), true);
            $events = $decodedResponse['data']['schedule']['events'];

            $progressBar = new ProgressBar($output, \count($events));
            $progressBar->start();

            $existingGames = $this->gameRepository->findAll();
            $existingGamesId = \array_map(fn (Game $game): string => $game->getId(), $existingGames);

            foreach ($events as $event) {
                if (!\array_key_exists('match', $event)) {
                    continue;
                }

                $matchId = $event['match']['id'];
                $gameDate = new \DateTimeImmutable($event['startTime'], new \DateTimeZone('UTC'));

                if (\in_array($matchId, $existingGamesId, true)) {
                    $game = $existingGames[\array_search($matchId, $existingGamesId, true)];

                    // If game is not completed, we update the game info to make sure it is fresh
                    if (Game::STATE_COMPLETED !== $game->getState()) {
                        $game->setDate($gameDate);
                        $game->setNameTeam1($event['match']['teams'][0]['name']);
                        $game->setNameTeam2($event['match']['teams'][1]['name']);
                        $game->setCodeTeam1($event['match']['teams'][0]['code']);
                        $game->setCodeTeam2($event['match']['teams'][1]['code']);
                        $game->setImageTeam1($event['match']['teams'][0]['image']);
                        $game->setImageTeam2($event['match']['teams'][1]['image']);
                        $game->setState($event['state']);

                        if (Game::STATE_COMPLETED === $event['state']) {
                            // prevent empty result update
                            if (null === $event['match']['teams'][0]['result']['outcome']
                                || null === $event['match']['teams'][1]['result']['outcome']
                                || null === $event['match']['teams'][0]['result']['gameWins']
                                || null === $event['match']['teams'][1]['result']['gameWins']
                            ) {
                                continue;
                            }

                            $game->setOutComeTeam1($event['match']['teams'][0]['result']['outcome']);
                            $game->setOutComeTeam2($event['match']['teams'][1]['result']['outcome']);
                            $game->setScoreTeam1($event['match']['teams'][0]['result']['gameWins']);
                            $game->setScoreTeam2($event['match']['teams'][1]['result']['gameWins']);
                        }

                        $this->em->flush();

                        // If the game just completed, process game completion actions
                        if (Game::STATE_COMPLETED === $event['state']) {
                            $this->processGameCompletion->process($game);
                        }
                    }

                    $progressBar->advance();

                    continue;
                }

                // Prevent games older than two month to load, as the LoL Esport API also expose games from previous years
                if ($gameDate->format('Y-m-d') < (new \DateTimeImmutable('-2 month'))->format('Y-m-d')) {
                    continue;
                }

                $game = new Game(
                    $matchId,
                    $league,
                    new \DateTimeImmutable($event['startTime'], new \DateTimeZone('UTC')),
                    $event['match']['teams'][0]['name'],
                    $event['match']['teams'][1]['name'],
                    $event['match']['teams'][0]['code'],
                    $event['match']['teams'][1]['code'],
                    $event['match']['teams'][0]['image'],
                    $event['match']['teams'][1]['image'],
                    $event['state'],
                );

                if (Game::STATE_COMPLETED === $event['state']) {
                    $game->setOutComeTeam1($event['match']['teams'][0]['result']['outcome']);
                    $game->setOutComeTeam2($event['match']['teams'][1]['result']['outcome']);
                    $game->setScoreTeam1($event['match']['teams'][0]['result']['gameWins']);
                    $game->setScoreTeam2($event['match']['teams'][1]['result']['gameWins']);
                }

                // Avoid pinging for already completed games
                if (Game::STATE_COMPLETED === $event['state']) {
                    $game->setPingSent(true);
                }

                $this->gameRepository->add($game);
                $progressBar->advance();
            }

            $progressBar->finish();
        }

        return Command::SUCCESS;
    }
}
