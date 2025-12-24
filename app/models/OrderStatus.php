<?php

namespace app\models;

enum OrderStatus {
    case PENDING;   
    case PAID;
    case SHIPPED;
    case CANCELLED;
}