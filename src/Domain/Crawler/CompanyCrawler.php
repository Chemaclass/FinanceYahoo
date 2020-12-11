<?php

declare(strict_types=1);

namespace Chemaclass\FinanceYahoo\Domain\Crawler;

use Chemaclass\FinanceYahoo\Domain\ReadModel\Company;
use Chemaclass\FinanceYahoo\Domain\ReadModel\Site;
use Chemaclass\FinanceYahoo\Domain\ReadModel\Ticker;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CompanyCrawler
{
    private HttpClientInterface $httpClient;

    /** @var SiteCrawlerInterface[] */
    private array $siteCrawlers;

    public function __construct(
        HttpClientInterface $httpClient,
        SiteCrawlerInterface ...$siteCrawlers
    ) {
        $this->httpClient = $httpClient;
        $this->siteCrawlers = $siteCrawlers;
    }

    public function crawlStock(string ...$tickerSymbols): CrawlResult
    {
        $result = [];
        $tickers = $this->mapTickersFromSymbols($tickerSymbols);

        foreach ($tickers as $ticker) {
            $sites = $this->crawlAllSitesForTicker($ticker);

            $result[$ticker->symbol()] = new Company(
                $ticker,
                $this->flat(...$sites)
            );
        }

        return new CrawlResult($result);
    }

    /**
     * @param string[] $tickerSymbos
     *
     * @return Ticker[]
     */
    private function mapTickersFromSymbols(array $tickerSymbos): array
    {
        return array_map(
            static fn (string $symbol) => Ticker::withSymbol($symbol),
            $tickerSymbos
        );
    }

    /**
     * @return Site[]
     */
    private function crawlAllSitesForTicker(Ticker $ticker): array
    {
        return array_map(
            fn (SiteCrawlerInterface $crawler): Site => $crawler->crawl($this->httpClient, $ticker),
            $this->siteCrawlers
        );
    }

    private function flat(Site ...$sites): array
    {
        return array_merge(...$this->normalizeSites(...$sites));
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    private function normalizeSites(Site ...$sites): array
    {
        return array_map(
            static fn (Site $site): array => $site->crawled(),
            array_values($sites)
        );
    }
}