<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async_priority_high')]
final class FetchAllPrices
{
    /*
     * Add whatever properties and methods you need
     * to hold the data for this message class.
     */

    // public function __construct(
    //     public readonly string $name,
    // ) {
    // }
}
