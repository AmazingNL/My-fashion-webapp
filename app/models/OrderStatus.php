<?php

namespace App\Models;

enum OrderStatus {
    case PENDING;
    case PROCESSING;   
    case PAID;
    case SHIPPED;
    case CANCELLED;
    case DELIVERED;

}