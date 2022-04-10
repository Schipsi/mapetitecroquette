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

class PingDiscordCommand extends Command
{
    protected static $defaultName = 'app:ping-discord';
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
        $users = $this->userRepository->findAll();
        $usersToPing = \array_filter(
            $users,
            fn (User $user): bool => $user->getDiscordId() !== null,
        );

        // If there is no user to ping, exit.
        if (!\count($usersToPing)) {
            return Command::SUCCESS;
        }

        $notPingedGames = $this->gameRepository->findBy(['pingSent' => false]);
        /** @var Game[] $incomingGames */
        $incomingGames = \array_filter(
            $notPingedGames,
            fn (Game $game): bool => $game->getDate()->format('Y-m-d H-i') < (new \DateTime('-1 hour'))->format('Y-m-d H-i'),
        );

        // If there is no match in the next 1 hour, exit.
        if (!\count($incomingGames)) {
            return Command::SUCCESS;
        }

        // If there is at least one match, get all match of the day.
        $matchOfTheDays = \array_filter(
            $notPingedGames,
            fn (Game $game): bool => $game->getDate()->format('Y-m-d') === (new \DateTime())->format('Y-m-d'),
        );

        /** @var Game $firstMatch */
        $firstMatch = $matchOfTheDays[0];
        foreach ($matchOfTheDays as $matchOfTheDay) {
            $matchOfTheDay->setPingSent(true);

            if ($matchOfTheDay->getDate()->format('Y-m-d H-i') < $firstMatch->getDate()->format('Y-m-d H-i')) {
                $firstMatch = $matchOfTheDay;
            }
        }


        // Remove users from ping list if they have entered all their predictions
        foreach ($usersToPing as $key => $user) {
            $missingMatch = false;
            foreach ($matchOfTheDays as $matchOfTheDay) {
                if (null === $this->predictionRepository->findOneBy(['user' => $user, 'game' => $matchOfTheDay])) {
                    $missingMatch = true;

                    break;
                }
            }

            if (!$missingMatch) {
                unset($usersToPing[$key]);
            }
        }

        // If all users have their predictions, do not ping
        if (!\count($usersToPing)) {
            return Command::SUCCESS;
        }

        $message = \sprintf(
            '%s matchs prévus aujourd\'hui (premier à %s), n\'oubliez pas de faire vos paris',
            \count($matchOfTheDays),
            $firstMatch->getDate()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('G\\hi')
        );

        foreach ($usersToPing as $user) {
            $message = sprintf('<@%s> %s', $user->getDiscordId(), $message);
        }

        $this->httpClient->request(
            'POST',
            $this->discordWebhookUrl,
            [
                'headers' => [
                    'Content-type' => 'application/json',
                ],
                'body' => \json_encode(['content' => $message]),
            ],
        );

        $this->em->flush();

        return Command::SUCCESS;
    }
}
