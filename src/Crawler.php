<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Psr\Log\LoggerInterface;

/**
 * Crawler Class
 * Handles fetching URLs and counting images
 */
class Crawler
{
    private Client $httpClient;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->httpClient = new Client([
            'timeout' => 10,
            'allow_redirects' => [
                'max' => 10,
                'track_redirects' => true
            ],
            'verify' => false,
        ]);
    }

    /**
     * Fetch URL and count images
     *
     * @param string $url URL to crawl
     * @return int Number of images found
     * @throws Exception If request fails
     */
    public function countImages(string $url): int
    {
        try {
            // Fetch HTML content
            $response = $this->httpClient->get($url);
            $statusCode = $response->getStatusCode();
            $html = $response->getBody()->getContents();

            // Log redirects if any
            $redirects = $response->getHeader('X-Guzzle-Redirect-History');
            if (!empty($redirects)) {
                $this->logger->warning('URL redirected', [
                    'original_url' => $url,
                    'redirect_chain' => $redirects,
                    'final_status' => $statusCode
                ]);
            }

            // Log successful fetch
            $this->logger->info('Successfully fetched URL', [
                'url' => $url,
                'status_code' => $statusCode
            ]);

            // Parse HTML and count <img> tags
            $crawler = new DomCrawler($html);
            $imageCount = $crawler->filter('img')->count();

            return $imageCount;
        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;

            $this->logger->error('Failed to fetch URL', [
                'url' => $url,
                'status_code' => $statusCode,
                'error' => $e->getMessage()
            ]);

            throw new Exception("Failed to fetch URL: {$url}. Status: {$statusCode}. Error: " . $e->getMessage());
        } catch (GuzzleException $e) {
            $this->logger->error('HTTP client error', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            throw new Exception("Failed to fetch URL: {$url}. Error: " . $e->getMessage());
        }
    }

    /**
     * Get HTTP status code for a URL
     *
     * @param string $url URL to check
     * @return int HTTP status code
     */
    public function getStatusCode(string $url): int
    {
        try {
            $response = $this->httpClient->get($url);
            return $response->getStatusCode();
        } catch (GuzzleException $e) {
            return 0;
        }
    }
}
