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
    case CREATE_REPORTS = 'Create Reports';
    case BOOK_INVENTORY = 'Book Inventory';
    case IMPORT_USERS   = 'Import Users';
    case IMPORT_BOOKS   = 'Import Books';
    case VIEW_USER_REPORTS              = 'View User Reports';
    case VIEW_SUMMARY_REPORTS           = 'View Summary Reports';
    case VIEW_INVENTORY_REPORTS         = 'View Inventory Reports';
    case VIEW_TRANSACTION_REPORTS       = 'View Transaction Reports';
    case VIEW_BOOK_CIRCULATION_REPORTS  = 'View Book Circulation Reports';
}
