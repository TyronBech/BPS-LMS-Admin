<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Exception;

abstract class Controller
{
    /**
     * Translate generic or database exceptions into user-friendly error messages.
     *
     * @param Exception $e
     * @param string $default Fallback message if specific error cannot be determined
     * @return string
     */
    protected function friendlyErrorMessage(Exception $e, string $default = 'An unexpected error occurred. Please try again.'): string
    {
        if ($e instanceof QueryException) {
            $errorCode = $e->errorInfo[1] ?? null;
            $sqlMessage = $e->getMessage();

            // Duplicate Entry Error
            if ($errorCode == 1062) {
                // Format usually: Duplicate entry '123' for key 'table_accession_unique'
                if (preg_match("/Duplicate entry '(.+?)' for key '(.+?)'/", $sqlMessage, $matches)) {
                    $entry = $matches[1];
                    $key = strtolower($matches[2]);

                    if (str_contains($key, 'accession')) {
                        return "The accession number '{$entry}' already exists. Please enter a unique accession number.";
                    }
                    if (str_contains($key, 'call_number')) {
                        return "The call number '{$entry}' already exists. Please enter a unique call number.";
                    }
                    if (str_contains($key, 'email')) {
                        return "The email address '{$entry}' already exists. Please use a different email.";
                    }
                    if (str_contains($key, 'rfid')) {
                        return "The RFID '{$entry}' already exists in the system.";
                    }
                    if (str_contains($key, 'isbn')) {
                        return "The ISBN '{$entry}' already exists.";
                    }
                    
                    return "The value '{$entry}' already exists in our records. Please provide a unique value.";
                }

                return "A duplicate entry was detected. Please ensure all unique fields (like accession or call number) are not already in use.";
            }

            // Foreign Key Constraint Violation
            if ($errorCode == 1451 || $errorCode == 1452) {
                return "This record cannot be modified or deleted because it is currently in use or referenced by other records.";
            }

            // Other Database Errors
            return "A database error occurred. Please verify your inputs and try again.";
        }

        $message = $e->getMessage();
        if (str_contains($message, 'SQLSTATE') || str_contains($message, 'Call to undefined')) {
            return $default;
        }

        return (!empty($message) && strlen($message) < 200) ? $message : $default;
    }
}
