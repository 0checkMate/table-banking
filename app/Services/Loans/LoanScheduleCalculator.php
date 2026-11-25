<?php

declare(strict_types=1);

namespace App\Services\Loans;

use App\Enums\InterestMethod;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

final class LoanScheduleCalculator
{
    private const BPS_DIVISOR = 10_000;

    /**
     * @param  int  $principal  Amount lent, in minor units (cents)
     * @param  int  $monthlyRateBps  Monthly rate in basis points (1000 = 10.00%)
     * @return list<Installment>
     */
    public function calculate(
        int $principal,
        int $monthlyRateBps,
        int $termMonths,
        InterestMethod $method,
        CarbonImmutable $disbursedOn,
    ): array {
        if ($principal <= 0) {
            throw new InvalidArgumentException('Principal must be positive.');
        }
        if ($monthlyRateBps < 0) {
            throw new InvalidArgumentException('Rate cannot be negative.');
        }
        if ($termMonths < 1) {
            throw new InvalidArgumentException('Term must be at least one month.');
        }

        $basePrincipal = intdiv($principal, $termMonths);
        $remainder = $principal % $termMonths;
        $balance = $principal;
        $installments = [];

        for ($number = 1; $number <= $termMonths; $number++) {
            $principalPart = $number === $termMonths
                ? $basePrincipal + $remainder
                : $basePrincipal;

            $interestBase = $method === InterestMethod::Flat ? $principal : $balance;
            $interest = $this->roundedDivide($interestBase * $monthlyRateBps, self::BPS_DIVISOR);

            $installments[] = new Installment(
                $number,
                $disbursedOn->addMonthsNoOverflow($number),
                $principalPart,
                $interest,
            );

            $balance -= $principalPart;
        }

        return $installments;
    }

    private function roundedDivide(int $numerator, int $denominator): int
    {
        return intdiv(2 * $numerator + $denominator, 2 * $denominator);
    }
}
