<?php

namespace App\Models;

enum OrderStatus {
    case PENDING;   
    case PAID;
    case SHIPPED;
    case CANCELLED;
}