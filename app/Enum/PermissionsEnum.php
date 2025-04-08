<?php

namespace App\Enum;

enum PermissionsEnum : string
{
    case ADD_USERS      = 'Add Users';
    case EDIT_USERS     = 'Edit Users';
    case DELETE_USERS   = 'Delete Users';
    case ADD_BOOKS      = 'Add Books';
    case EDIT_BOOKS     = 'Edit Books';
    case DELETE_BOOKS   = 'Delete Books';
    case MODIFY_ADMIN   = 'Modify Admin';
    case VIEW_REPORTS   = 'View Reports';
    case CREATE_REPORTS = 'Create Reports';
    case BOOK_INVENTORY = 'Book Inventory';
    case IMPORT_USERS   = 'Import Users';
    case IMPORT_BOOKS   = 'Import Books';
}
