<?php

namespace App\Models;

enum OrderStatus: string {
    case PENDING = 'pending';
    case PROCESSING = 'processing';   
    case PAID = 'paid';
    case SHIPPED = 'shipped';
    case CANCELLED = 'cancelled';
    case DELIVERED = 'delivered';

}