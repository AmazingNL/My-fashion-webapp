<?php

namespace App\Models;

enum AppointmentStatus: string
{
    case PENDING   = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case CANCELLED = 'CANCELLED';
    case COMPLETED = 'COMPLETED';

}
