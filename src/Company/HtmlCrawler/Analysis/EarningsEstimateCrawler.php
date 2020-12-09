<?php

declare(strict_types=1);

namespace App\Company\HtmlCrawler\Analysis;

use App\Company\HtmlCrawler\CrawlerInterface;
use Symfony\Component\DomCrawler\Crawler;

final class EarningsEstimateCrawler implements CrawlerInterface
{
    public function crawlHtml(string $html): string
    {
        $text = (new Crawler($html))
            ->filter('table:nth-child(2) tbody tr:nth-child(1) td:nth-child(2)')
            ->last()
            ->text();

        return 'No. of Analysts: ' . $text;
    }
}