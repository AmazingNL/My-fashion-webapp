<?php

namespace app\models;

enum AppointmentStatus {
    case REQUEST;
    case CONFIRMED;
    case CANCELLED;
    case COMPLETED;
}