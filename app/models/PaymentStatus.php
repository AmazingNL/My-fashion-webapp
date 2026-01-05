<?php

namespace App\Models;

enum PaymentStatus {
    case PENDING;
    case COMPLETED;   
    case FAILED;

}