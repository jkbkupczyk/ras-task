<?php

namespace App\Connectors;

use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PhoenixHttpClient implements PhoenixClient
{
    private const HEADER_ACCESS_TOKEN = 'access-token';
    private const ENDPOINT_PHOTOS = '/api/photos';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string              $baseUrl
    )
    {
    }

    #[\Override]
    public function getPhotos(string $userAccessToken): array
    {
        $url = $this->baseUrl . self::ENDPOINT_PHOTOS;
        $opts = [
            'headers' => [self::HEADER_ACCESS_TOKEN => $userAccessToken],
        ];

        try {
            $response = $this->httpClient->request('GET', $url, $opts);

            $responseBody = $response->toArray();
            if (!$responseBody) {
                return [];
            }

            if (!array_key_exists('photos', $responseBody)) {
                return [];
            }

            return array_map(
                $this->mapToPhoto(...),
                $responseBody['photos']
            );
        } catch (HttpExceptionInterface|DecodingExceptionInterface|TransportExceptionInterface $e) {
            throw new PhoenixClientException(
                message: "Could not get photos from Phoenix client",
                previous: $e
            );
        }
    }

    private function mapToPhoto(array $item): PhoenixPhoto
    {
        return new PhoenixPhoto($item["id"], $item["photo_url"]);
    }

}
