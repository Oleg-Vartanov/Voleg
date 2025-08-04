<?php

namespace App\FixturePredictions\Service;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use DateTimeInterface;
use Exception;
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

    /**
     * @var mixed[]
     */
    private array $headers = [];

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ParameterBagInterface $params,
    ) {
        $url = $this->params->get('fixtures.football-data-org.api.url');
        $key = $this->params->get('fixtures.football-data-org.api.key');

        $this->apiUrl = $url;
        $this->headers['X-Auth-Token'] = $key;
    }

    /**
     * @param Competition $competition
     *
     * @return mixed[]
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    public function getSeasons(Competition $competition): array
    {
        $response = $this->client->request('GET', $this->url('/competitions/' . $competition->getCode()), [
            'headers' => $this->headers,
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Failed to fetch seasons. Response code: ' . $response->getStatusCode());
        }

        return json_decode($response->getContent(), true);
    }

    /**
     * @return mixed[]
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    public function getTeams(Competition $competition, Season $season): array
    {
        $code = $competition->getCode();
        $response = $this->client->request('GET', $this->url('/competitions/' . $code . '/teams'), [
            'headers' => $this->headers,
            'query' => [
                'season' => $season->getYear()
            ]
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Failed to fetch teams. Response code: ' . $response->getStatusCode());
        }

        return json_decode($response->getContent(), true);
    }

    /**
     * @return mixed[]
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    public function getMatches(
        Competition $competition,
        Season $season,
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
    ): array {
        $filters = ['season' => $season->getYear()];
        if (!is_null($from)) {
            $filters['dateFrom'] = $from->format('Y-m-d');
        }
        if (!is_null($to)) {
            $filters['dateTo'] = $to->format('Y-m-d');
        }

        $code = $competition->getCode();
        $response = $this->client->request('GET', $this->url('/competitions/' . $code . '/matches'), [
            'headers' => $this->headers,
            'query' => $filters
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Failed to fetch matches. Response code: ' . $response->getStatusCode());
        }

        return json_decode($response->getContent(), true);
    }

    private function url(string $path): string
    {
        return $this->apiUrl . $path;
    }
}
