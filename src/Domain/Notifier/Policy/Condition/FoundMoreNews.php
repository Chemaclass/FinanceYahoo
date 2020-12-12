<?php

declare(strict_types=1);

namespace Chemaclass\FinanceYahoo\Domain\Notifier\Policy\Condition;

use Chemaclass\FinanceYahoo\Domain\Notifier\Policy\PolicyConditionInterface;
use Chemaclass\FinanceYahoo\Domain\ReadModel\Company;

final class FoundMoreNews implements PolicyConditionInterface
{
    /**
     * @var array<string,string>
     * For example ['TickerSymbol' => 'fmtPubtime']
     */
    private static $cacheOldestDateTimeBySymbol = [];

    public function __construct()
    {
        // TODO: Add as optional arg: the datetime from where to start looking for news.
    }

    public function __invoke(Company $company): bool
    {
        $current = $this->findLatestPubtimeFromNews($company);
        $previous = self::$cacheOldestDateTimeBySymbol[$company->ticker()->symbol()] ?? '';

        self::$cacheOldestDateTimeBySymbol[$company->ticker()->symbol()] = $current;

        return $current !== $previous;
    }

    private function findLatestPubtimeFromNews(Company $company): string
    {
        $reduced = array_reduce(
            (array) $company->info('news')->get(),
            static function (?array $carry, array $current): array {
                if (null === $carry) {
                    return $current;
                }

                return $carry['fmtPubtime'] > $current['fmtPubtime']
                    ? $carry
                    : $current;
            }
        );

        return $reduced['fmtPubtime'];
    }
}