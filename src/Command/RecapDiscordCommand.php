<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\PredictionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecapDiscordCommand extends Command
{
    protected static $defaultName = 'app:recap-discord';
    private HttpClientInterface $httpClient;
    private GameRepository $gameRepository;
    private PredictionRepository $predictionRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $em;
    private string $discordWebhookUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        GameRepository $gameRepository,
        PredictionRepository $predictionRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        string $discordWebhookUrl,
    ) {
        parent::__construct();

        $this->httpClient = $httpClient;
        $this->gameRepository = $gameRepository;
        $this->predictionRepository = $predictionRepository;
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->discordWebhookUrl = $discordWebhookUrl;
    }

    protected function configure(): void
    {
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        /** @var Game[] $gamesOfTheDay */
        $gamesOfTheDay = $this->gameRepository->findGameOfTheDay();

        dump($gamesOfTheDay);

        /*$this->httpClient->request(
            'POST',
            $this->discordWebhookUrl,
            [
                'headers' => [
                    'Content-type' => 'application/json',
                ],
                'body' => \json_encode(['content' => $message]),
            ],
        );*/


        return Command::SUCCESS;
    }
}
