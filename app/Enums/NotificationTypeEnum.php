<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case ALERT                = 'ALERT';
    case INFO                 = 'INFO';
    case DOCUMENT_EXPIRING    = 'DOCUMENT_EXPIRING';
    case MAINTENANCE_REMINDER = 'MAINTENANCE_REMINDER';
    case INCIDENT_UPDATE      = 'INCIDENT_UPDATE';
}
