<?php

namespace App\Enums;

enum QueueStatus: string
{
    case WAITING = 'waiting';
    case SERVING = 'serving';
    case COMPLETED = 'completed';
    case HOLD = 'hold';
}
