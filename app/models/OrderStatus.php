<?php

enum OrderStatus {
    case PENDING;   
    case PAID;
    case SHIPPED;
    case CANCELLED;
}