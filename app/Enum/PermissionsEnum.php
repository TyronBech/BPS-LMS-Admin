<?php

namespace App\Enum;

enum PermissionsEnum : string
{
    case ADD_USERS                          = 'Add Users';
    case EDIT_USERS                         = 'Edit Users';
    case DELETE_USERS                       = 'Delete Users';
    case ADD_BOOKS                          = 'Add Books';
    case EDIT_BOOKS                         = 'Edit Books';
    case DELETE_BOOKS                       = 'Delete Books';
    case MODIFY_ADMIN                       = 'Modify Admins';
    case CREATE_REPORTS                     = 'Create Reports';
    case BOOK_INVENTORY                     = 'Book Inventory';
    case IMPORT_USERS                       = 'Import Users';
    case IMPORT_BOOKS                       = 'Import Books';
    case IMPORT_FACULTIES_AND_STAFFS        = 'Import Faculties & Staffs';
    case ADD_PRIVILEGES                     = 'Add Privileges';
    case EDIT_PRIVILEGES                    = 'Edit Privileges';
    case DELETE_PRIVILEGES                  = 'Delete Privileges';
    case ADD_CATEGORIES                     = 'Add Categories';
    case EDIT_CATEGORIES                    = 'Edit Categories';
    case DELETE_CATEGORIES                  = 'Delete Categories';
    case VIEW_USER_REPORTS                  = 'View User Reports';
    case VIEW_SUMMARY_REPORTS               = 'View Summary Reports';
    case VIEW_INVENTORY_REPORTS             = 'View Inventory Reports';
    case VIEW_TRANSACTION_REPORTS           = 'View Transaction Reports';
    case VIEW_BOOK_CIRCULATION_REPORTS      = 'View Book Circulation Reports';
    case VIEW_PENALTY_REPORTS               = 'View Penalty Reports';
    case VIEW_USER_AUDIT_REPORTS            = 'View User Audit Reports';
    case VIEW_BOOK_AUDIT_REPORTS            = 'View Book Audit Reports';
    case VIEW_TRANSACTION_AUDIT_REPORTS     = 'View Transaction Audit Reports';
    case ADD_PENALTY_RULES                  = 'Add Penalty Rule';
    case EDIT_PENALTY_RULES                 = 'Edit Penalty Rule';
    case DELETE_PENALTY_RULES               = 'Delete Penalty Rule';
    case EDIT_TRANSACTIONS                  = 'Edit Transactions';
    case VIEW_USERS_MAINTENANCE             = 'View Users Maintenance';
    case VIEW_BOOKS_MAINTENANCE             = 'View Books Maintenance';
    case VIEW_BOOK_CATEGORIES_MAINTENANCE   = 'View Book Categories Maintenance';
    case VIEW_PRIVILEGES_MAINTENANCE        = 'View Privileges Maintenance';
    case VIEW_PENALTY_RULES_MAINTENANCE     = 'View Penalty Rules Maintenance';
    case VIEW_TRANSACTIONS_MAINTENANCE      = 'View Transactions Maintenance';
    case VIEW_DASHBOARD                     = 'View Dashboard';
    case CREATE_BACKUPS                     = 'Create Backups';

}
