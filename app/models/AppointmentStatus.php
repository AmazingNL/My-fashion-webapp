<?php

namespace App\Models;

enum AppointmentStatus {
    case REQUEST;
    case CONFIRMED;
    case CANCELLED;
    case COMPLETED;
}