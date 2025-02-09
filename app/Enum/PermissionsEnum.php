<?php

namespace App\Enum;

enum PermissionsEnum : string
{
    case CREATE_USERS   = 'Create Users';
    case EDIT_USERS     = 'Edit Users';
    case DELETE_USERS   = 'Delete Users';
    case MODIFY_ADMIN   = 'Modify Admin';
}
