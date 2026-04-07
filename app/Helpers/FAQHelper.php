<?php

namespace App\Helpers;

class FAQHelper
{

    /**
     * Retrieves an array containing FAQs about the dashboard section.
     *
     * The returned array contains the following keys:
     * - title: The title of the FAQs section.
     * - gif: The URL of the GIF associated with the FAQs section.
     * - description: A brief description of the FAQs section.
     * - questions: An array of questions asked about the FAQs section.
     * - answers: An array of answers corresponding to the FAQs section.
     *
     * @return array The FAQs section array.
     */
    public static function getDashboardFAQs()
    {
        return [
            'title' => "Dashboard FAQs",
            'gif' => asset('gif/Dashboard.gif'),
            'description' => "Frequently asked questions about the dashboard functionalities.",
            'questions' => [
                "What is the dashboard?",
                "How to navigate through the dashboard?",
                "What information is displayed on the dashboard?",
                "Can I generate reports from the dashboard?",
            ],
            'answers' => [
                "The dashboard is the main interface where users can view important information at a glance.",
                "You can navigate through the dashboard using the cards provided.",
                "The dashboard typically displays currently timed in users, monthly counts of all users went to the library, the total number of books in the system,
                monthly transactions of borrowed books, yearly aquired books, number of registered users, most visited students, most borrowed students, top books borrowed, and top categories borrowed.",
                "No, reports cannot be generated directly from the dashboard. Reports can be accessed and generated from the reports section of the system.",
            ]
        ];
    }
    /**
     * Retrieves an array containing FAQs about the inventory management section.
     *
     * The returned array contains the following keys:
     * - title: The title of the FAQs section.
     * - gif: The URL of the GIF associated with the FAQs section.
     * - description: A brief description of the FAQs section.
     * - questions: An array of questions asked about the FAQs section.
     * - answers: An array of answers to the FAQs section.
     *
     * @return array The FAQs section array.
     */
    public static function getInventoryFAQs()
    {
        return [
            'title' => "Inventory FAQs",
            'gif' => asset('gif/Inventory.gif'),
            'description' => "Frequently asked questions about inventory management.",
            'questions' => [
                "How to add the book to the inventory?",
                "How do I track inventory records?",
                "What are the best practices for inventory control?",
            ],
            'answers' => [
                "To add a book to the inventory, you need to enter the book barcode into the inventory management system, update it's remarks and condition status, and save the changes.",
                "You can find the records of inventory under the inventory reports section in the inventory management system.",
                "Best practices for inventory control include proper inventory tracking, accurate inventory reporting, and regular inventory reviews.",
            ]
        ];
    }

    /**
     * Retrieves an array containing FAQs about report generation and management.
     *
     * The returned array contains the following keys:
     * - title: The title of the FAQs section.
     * - gif: The URL of the GIF associated with the FAQs section.
     * - description: A brief description of the FAQs section.
     * - questions: An array of questions asked about the FAQs section.
     * - answers: An array of answers to the FAQs section.
     *
     * @return array The FAQs section array.
     */
    public static function getReportFAQs()
    {
        return [
            'title' => "Report FAQs",
            'gif' => asset('gif/Report.gif'),
            'description' => "Frequently asked questions about report generation and management.",
            'questions' => [
                "How to generate a report?",
                "What types of reports are available?",
                "Why some reports are not visible or accessible?",
                "How can I filter reports?",
            ],
            'answers' => [
                "To generate a report, you can click on the Export button, you can choose if the report is on PDF or Excel format.",
                "Available report types include attendance monitoring, online research, summary of collections, inventory, circulation records, accession list, overdue fines, and audit trails.",
                "Some reports may not be visible or accessible due to user permission settings or if the report type is restricted to certain user roles.",
                "You can filter reports by date range, type, or specific criteria relevant to the report type.",
            ]
        ];
    }

    /**
     * Retrieves an array containing FAQs about data import processes.
     *
     * The returned array contains the following keys:
     * - title: The title of the FAQs section.
     * - gif: The URL of the GIF associated with the FAQs section.
     * - description: A brief description of the FAQs section.
     * - questions: An array of questions asked about the FAQs section.
     * - answers: An array of answers to the FAQs section.
     *
     * @return array The FAQs section array.
     */
    public static function getImportFAQs()
    {
        return [
            'title' => "Import FAQs",
            'gif' => asset('gif/Import.gif'),
            'description' => "Frequently asked questions about data import processes.",
            'questions' => [
                "How to import data?",
                "What file formats are supported for import?",
                "What should I do if the import fails?",
                "Can I edit the preview of Excel data before importing?",
            ],
            'answers' => [
                "To import data, create an Excel file with the required fields, then use the import function in the system to upload the file. You can use the available Excel format template provided in the system.",
                "Supported file formats for import typically include only Excel (.xlsx) files.",
                "If the import fails, check the error messages provided by the system, ensure that your file format is correct, and verify that all required fields are included. You may also need to contact support for further assistance.",
                "Yes, all cells in the preview are editable before importing. You can click on any cell to modify its content as needed."
            ]
        ];
    }
    /**
     * Retrieves an array containing FAQs about system maintenance and updates.
     *
     * The returned array contains the following keys:
     * - title: The title of the FAQs section.
     * - gif: The URL of the GIF associated with the FAQs section.
     * - description: A brief description of the FAQs section.
     * - questions: An array of questions asked about the FAQs section.
     * - answers: An array of answers to the FAQs section.
     *
     * @return array The FAQs section array.
     */
    public static function getMaintenanceFAQs()
    {
        return [
            'title' => "Maintenance FAQs",
            'gif' => asset('gif/Maintenance.gif'),
            'description' => "Frequently asked questions about system maintenance and updates.",
            'questions' => [
                "What can I do in maintenance panel?",
                "Why some maintenance are not visible or accessible?",
                "How can I choose which books I want to generate barcode?",
                "Can I perform all maintenance capabilities?",
                "How are system updates communicated?",
                "How can I generate backups?",
                "Is backups automatically generated?",
            ],
            'answers' => [
                "The maintenance panel allows you to perform system maintenance tasks such as CRUD operations (Create, Read, Update, Delete), managing backups, Modifying roles and permissions, and reservation management.",
                "Some maintenance features may not be visible or accessible due to user permission settings or if the feature is restricted to certain user roles.",
                "To generate barcodes to specific books, you can search for it by name, accession number (it can be mutiple accession numbers just separate it by comma), or filter it by category. After that, you can select the books you want to generate by checking the checkboxes.",
                "Not all users can perform all maintenance capabilities. Access to certain maintenance functions may be restricted based on user roles and permissions.",
                "System updates are usually communicated through email notifications.",
                "You can generate backups through by creating a backup manually in the maintenance panel and downloading it to your local device using the generated password sent to your email.",
                "Yes, backups are automatically generated daily but it will automatically deleted after 7 days.",
            ]
        ];
    }
}
