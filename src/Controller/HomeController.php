<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(HttpClientInterface $httpClient): Response
    {
        $response = $httpClient->request(
            'GET',
            'https://esports-api.lolesports.com/persisted/gw/getLeagues?hl=fr-FR',
            [
                'headers' => [
                    'x-api-key' => '0TvQnueqKa5mxJntVWt0w4LpLfEkrV1Ta8rQBb9Z',
                ],
            ]
        );

        dump(\json_decode($response->getContent(), true));

        $response = $httpClient->request(
            'GET',
            'https://esports-api.lolesports.com/persisted/gw/getEventList?hl=fr-FR&leagueId=98767991302996019,98767991310872058,98767991325878492,98767975604431411',
            [
                'headers' => [
                    'x-api-key' => '0TvQnueqKa5mxJntVWt0w4LpLfEkrV1Ta8rQBb9Z',
                ],
            ]
        );

        dump(\json_decode($response->getContent(), true));

        $response = $httpClient->request(
            'GET',
            'https://esports-api.lolesports.com/persisted/gw/getLive?hl=fr-FR',
            [
                'headers' => [
                    'x-api-key' => '0TvQnueqKa5mxJntVWt0w4LpLfEkrV1Ta8rQBb9Z',
                ],
            ]
        );

        dump(\json_decode($response->getContent(), true));

        $response = $httpClient->request(
            'GET',
            'https://esports-api.lolesports.com/persisted/gw/getSchedule?hl=fr-FR&leagueId=98767991302996019',
            [
                'headers' => [
                    'x-api-key' => '0TvQnueqKa5mxJntVWt0w4LpLfEkrV1Ta8rQBb9Z',
                ],
            ]
        );

        dump(\json_decode($response->getContent(), true));die;

        return $this->render('base.html.twig');
    }
}
