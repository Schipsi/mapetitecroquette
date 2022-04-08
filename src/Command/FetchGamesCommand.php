<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Game;
use App\Repository\GameRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FetchGamesCommand extends Command
{
    private HttpClientInterface $httpClient;
    private GameRepository $gameRepository;

    public function __construct(
        HttpClientInterface $httpClient,
        GameRepository $gameRepository
    ) {
        parent::__construct();

        $this->httpClient = $httpClient;
        $this->gameRepository = $gameRepository;
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
            if (\in_array($event['match']['id'], $existingGamesId)) {
                $progressBar->advance();

                continue;
            }

            $game = new Game(
                $event['match']['id'],
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
