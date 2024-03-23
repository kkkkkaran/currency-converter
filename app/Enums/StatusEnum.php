<?php

namespace App\Enums;

enum StatusEnum: string
{
    case Pending = 'Pending';
    case Processing = 'Processing';
    case Completed = 'Completed';
    case Failed = 'Failed';
}
