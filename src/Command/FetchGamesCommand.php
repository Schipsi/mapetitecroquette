<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Service\ProcessGameCompletion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchGamesCommand extends Command
{
    private HttpClientInterface $httpClient;
    private GameRepository $gameRepository;
    private EntityManagerInterface $em;
    private ProcessGameCompletion $processGameCompletion;

    public function __construct(
        HttpClientInterface $httpClient,
        GameRepository $gameRepository,
        EntityManagerInterface $em,
        ProcessGameCompletion $processGameCompletion,
    ) {
        parent::__construct();

        $this->httpClient = $httpClient;
        $this->gameRepository = $gameRepository;
        $this->em = $em;
        $this->processGameCompletion = $processGameCompletion;
    }

    protected static $defaultName = 'app:fetch-games';

    protected function configure(): void
    {
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int
    {
        $response = $this->httpClient->request(
            'GET',
            'https://esports-api.lolesports.com/persisted/gw/getSchedule?hl=fr-FR&leagueId=98767991302996019',
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
            $matchId = $event['match']['id'];

            if (\in_array($matchId, $existingGamesId)) {
                $game = $existingGames[\array_search($matchId, $existingGamesId)];

                // If game is not completed, we update the game info to make sure it is fresh
                if (Game::STATE_COMPLETED !== $game->getState()) {
                    $game->setDate(new \DateTimeImmutable($event['startTime']));
                    $game->setNameTeam1($event['match']['teams'][0]['name']);
                    $game->setNameTeam2($event['match']['teams'][1]['name']);
                    $game->setCodeTeam1($event['match']['teams'][0]['code']);
                    $game->setCodeTeam2($event['match']['teams'][1]['code']);
                    $game->setImageTeam1($event['match']['teams'][0]['image']);
                    $game->setImageTeam2($event['match']['teams'][1]['image']);
                    $game->setState($event['state']);

                    if (Game::STATE_COMPLETED === $event['state']) {
                        $game->setOutComeTeam1($event['match']['teams'][0]['result']['outcome']);
                        $game->setOutComeTeam2($event['match']['teams'][1]['result']['outcome']);
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

            $game = new Game(
                $matchId,
                new \DateTimeImmutable($event['startTime']),
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
            }

            $this->gameRepository->add($game);
            $progressBar->advance();
        }

        $progressBar->finish();

        return Command::SUCCESS;
    }
}
