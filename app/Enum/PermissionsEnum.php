<?php

namespace App\Enum;

enum PermissionsEnum : string
{
    case CREATE_USERS   = 'Add Users';
    case EDIT_USERS     = 'Edit Users';
    case DELETE_USERS   = 'Delete Users';
    case CREATE_BOOKS   = 'Add Books';
    case EDIT_BOOKS     = 'Edit Books';
    case DELETE_BOOKS   = 'Delete Books';
    case MODIFY_ADMIN   = 'Modify Admin';
}
