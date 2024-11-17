<?php

namespace App\Service\Fixtures;

use App\Entity\Competition;
use App\Entity\Season;
use Exception;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * https://docs.football-data.org/
 */
class FootballDataOrgClient
{
    private string $apiUrl;
    private array $headers = [];

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ParameterBagInterface $params,
    ) {
        $config = $this->params->get('fixtures')['football-data-org'];
        $url = $config['api.url'] ?? null;
        $key = $config['api.key'] ?? null;

        if (empty($url) || empty($key)) {
            throw new InvalidArgumentException('Configs must be provided');
        }

        $this->apiUrl = $url;
        $this->headers['X-Auth-Token'] = $key;
    }

    /**
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    public function getTeams(Competition $competition, Season $season): array
    {
        $code = $competition->getCode();
        $response = $this->client->request('GET', $this->url('/competitions/'.$code.'/teams'), [
            'headers' => $this->headers,
            'query' => [
                'season' => $season->getYear()
            ]
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Failed to fetch teams');
        }

        return json_decode($response->getContent(), true);
    }


    /**
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    public function getMatches(Competition $competition, Season $season): array
        {
            $code = $competition->getCode();
            $response = $this->client->request('GET', $this->url('/competitions/'.$code.'/matches'), [
                'headers' => $this->headers,
                'query' => [
                    'season' => $season->getYear()
                ]
            ]);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new Exception('Failed to fetch teams');
            }

            return json_decode($response->getContent(), true);
        }

    private function url(string $path): string
    {
        return $this->apiUrl.$path;
    }
}