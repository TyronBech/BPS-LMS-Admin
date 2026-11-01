<?php

namespace App\Enum;

enum AcquisitionModeEnum: string
{
    case PURCHASE = 'Purchase';
    case DONATION = 'Donation';
    case EXCHANGE = 'Exchange';
    case GIFTS = 'Gifts';
}
