<?php

use App\Enums\InterestMethod;
use App\Services\Loans\LoanScheduleCalculator;
use Carbon\CarbonImmutable;

function buildSchedule(
    int $principal,
    int $bps,
    int $term,
    InterestMethod $method,
    string $start = '2026-01-15',
): array {
    return (new LoanScheduleCalculator)->calculate(
        $principal,
        $bps,
        $term,
        $method,
        CarbonImmutable::parse($start),
    );
}

it('charges flat interest on the original principal every month', function () {
    $installments = buildSchedule(1_000_000, 1000, 4, InterestMethod::Flat);

    expect(array_map(fn ($i) => $i->interest, $installments))
        ->toBe([100_000, 100_000, 100_000, 100_000])
        ->and(array_map(fn ($i) => $i->total(), $installments))
        ->toBe([350_000, 350_000, 350_000, 350_000]);
});

it('charges reducing balance interest on the outstanding principal', function () {
    $installments = buildSchedule(1_000_000, 1000, 4, InterestMethod::Reducing);

    expect(array_map(fn ($i) => $i->interest, $installments))
        ->toBe([100_000, 75_000, 50_000, 25_000])
        ->and(array_map(fn ($i) => $i->total(), $installments))
        ->toBe([350_000, 325_000, 300_000, 275_000]);
});

it('repays exactly the principal with any remainder in the last installment', function (InterestMethod $method) {
    $installments = buildSchedule(1_000_000, 1000, 3, $method);

    expect(array_map(fn ($i) => $i->principal, $installments))
        ->toBe([333_333, 333_333, 333_334])
        ->and(array_sum(array_map(fn ($i) => $i->principal, $installments)))
        ->toBe(1_000_000);
})->with([InterestMethod::Flat, InterestMethod::Reducing]);

it('rounds interest half up to the nearest minor unit', function () {
    $installments = buildSchedule(1_000_005, 1000, 1, InterestMethod::Flat);

    expect($installments[0]->interest)->toBe(100_001);
});

it('sets monthly due dates without overflowing short months', function () {
    $installments = buildSchedule(900_000, 1000, 3, InterestMethod::Flat, '2026-01-31');

    expect(array_map(fn ($i) => $i->dueDate->toDateString(), $installments))
        ->toBe(['2026-02-28', '2026-03-31', '2026-04-30']);
});

it('rejects invalid loan terms', function (int $principal, int $bps, int $term) {
    buildSchedule($principal, $bps, $term, InterestMethod::Flat);
})->with([
    'zero principal' => [0, 1000, 3],
    'negative rate' => [1_000_000, -1, 3],
    'zero term' => [1_000_000, 1000, 0],
])->throws(InvalidArgumentException::class);
