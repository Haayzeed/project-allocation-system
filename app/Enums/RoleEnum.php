<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case SUPERVISOR = 'supervisor';
    case STUDENT = 'student';
}