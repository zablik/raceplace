<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebDownloader
{
    const RACE_RESULTS_PAGE_PATH = 'race_results';

    private string $resultsDirPath;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $client, string $staticDataPath, string $kernelRootDir)
    {
        $this->httpClient = $client;
        $this->resultsDirPath = sprintf('%s/%s/%s', $kernelRootDir, $staticDataPath, self::RACE_RESULTS_PAGE_PATH);
    }

    public function getHtml(string $link): string
    {
        $filesystem = new Filesystem();
        $filePath = sprintf('%s/%s', $this->resultsDirPath, self::generateKey($link));

        if (!$filesystem->exists($filePath)) {
            $response = $this->httpClient->request('GET', $link);
            $html = $response->getContent();;
            $filesystem->dumpFile($filePath, $html);
        } else {
            $html = file_get_contents($filePath);
        }

        return $html;
    }

    private static function generateKey(string $link): ?string
    {
        return md5($link);
    }
}