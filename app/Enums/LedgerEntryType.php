<?php

declare(strict_types=1);

namespace App\Enums;

enum LedgerEntryType: string
{
    case Contribution = 'contribution';
    case Disbursement = 'disbursement';
    case Repayment = 'repayment';
    case Interest = 'interest';
    case Penalty = 'penalty';
    case Dividend = 'dividend';
    case Reversal = 'reversal';
}
