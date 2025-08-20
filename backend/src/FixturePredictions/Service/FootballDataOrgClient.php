<?php

namespace App\FixturePredictions\Service;

use App\FixturePredictions\Entity\Competition;
use App\FixturePredictions\Entity\Season;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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
     * @return array{
     *     seasons: array<int, array{
     *         startDate: string,
     *         endDate: string
     *     }>
     * }
     *
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

        return $this->decodeResponse($response); // @phpstan-ignore-line Ignore mixed to avoid copying return type.
    }

    /**
     * @return array{
     *     teams: array<int, array{
     *         id: int,
     *         shortName: string,
     *     }>
     * }
     *
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    public function getTeams(Competition $competition, Season $season): array
    {
        $code = $competition->getCode();
        $response = $this->client->request('GET', $this->url('/competitions/' . $code . '/teams'), [
            'headers' => $this->headers,
            'query' => [
                'season' => $season->getYear(),
            ],
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Failed to fetch teams. Response code: ' . $response->getStatusCode());
        }

        return $this->decodeResponse($response); // @phpstan-ignore-line Ignore mixed to avoid copying return type.
    }

    /**
     * @return array{
     *     matches: array<int, array{
     *         id: int,
     *         utcDate: string,
     *         status: string,
     *         matchday: int,
     *         homeTeam: array{id: int, name: string},
     *         awayTeam: array{id: int, name: string},
     *         score: array{
     *             fullTime: array{home: int|null, away: int|null}
     *         }
     *     }>
     * }
     *
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    public function getMatches(
        Competition $competition,
        Season $season,
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
    ): array {
        $timezone = new DateTimeZone('UTC');

        $filters = ['season' => $season->getYear()];
        if (!is_null($from)) {
            $filters['dateFrom'] = $from->setTimezone($timezone)->format('Y-m-d');
        }
        if (!is_null($to)) {
            $filters['dateTo'] = $to->setTimezone($timezone)->format('Y-m-d');
        }

        $code = $competition->getCode();
        $response = $this->client->request('GET', $this->url('/competitions/' . $code . '/matches'), [
            'headers' => $this->headers,
            'query' => $filters,
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Failed to fetch matches. Response code: ' . $response->getStatusCode());
        }

        return $this->decodeResponse($response); // @phpstan-ignore-line Ignore mixed to avoid copying return type.
    }

    /**
     * @return mixed[]
     *
     * @throws Exception|TransportExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|ClientExceptionInterface
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        $data = json_decode($response->getContent(), true);

        if (!is_array($data)) {
            throw new Exception('Invalid API response: expected array.');
        }

        return $data;
    }

    private function url(string $path): string
    {
        return $this->apiUrl . $path;
    }
}
