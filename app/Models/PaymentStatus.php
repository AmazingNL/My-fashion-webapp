<?php

namespace App\Models;

enum PaymentStatus: string {
    case PENDING = 'pending';
    case COMPLETED = 'completed';   
    case FAILED = 'failed';

}