<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CallNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Book::all()->each(function ($book) {
            $book->update([
                'call_number' => $this->generateCallNumber($book)
            ]); 
        });
    }
    /**
     * Generate a call number based on the book's category, author's last name, and copyright year
     *
     * @param Book $book The book object
     * @return string The generated call number
     */
    private function generateCallNumber(Book $book)
    {
        // 1. Get Classification Code from Category (e.g., "Science" -> "SCI")
        $categoryCode = 'GEN'; // Default to "General"
        if ($book->category && !empty($book->category->name)) {
            $categoryCode = strtoupper(substr($book->category->name, 0, 3));
        }

        // 2. Generate Cutter Number from author's last name
        $cutterNumber = 'U00'; // Default to "Unknown"
        if (!empty($book->author)) {
            $cutterNumber = $this->generateCutterNumber($book->author);
        }

        // 3. Generate work letters (2 lowercase characters from title)
        $workLetters = $this->generateWorkLetters($book->title);

        // 4. Get Copyright Year
        $copyrightYear = !empty($book->copyrights) ? $book->copyrights : date('Y');

        // 5. Combine the parts into the final call number
        $callNumber = "{$categoryCode} {$cutterNumber}{$workLetters} {$copyrightYear}";

        return $callNumber;
    }

    /**
     * Generate work letters from book title (2 lowercase characters)
     * 
     * @param string $title Book title
     * @return string Two lowercase letters from the title
     */
    private function generateWorkLetters($title)
    {
        if (empty($title)) {
            return 'aa';
        }

        // Remove common articles and prepositions at the beginning
        $cleanTitle = preg_replace('/^(the|a|an|el|la|le|un|une|der|die|das)\s+/i', '', trim($title));
        
        // Remove non-alphabetic characters and convert to lowercase
        $cleanTitle = strtolower(preg_replace('/[^a-zA-Z]/', '', $cleanTitle));
        
        if (strlen($cleanTitle) < 2) {
            return 'aa';
        }
        
        // Get first two letters of the cleaned title
        return substr($cleanTitle, 0, 2);
    }

    /**
     * Generate Cutter Number based on Cutter notation system
     * 
     * @param string $author Author's full name
     * @return string Cutter number (e.g., "D63" for "Doe, John")
     */
    private function generateCutterNumber($author)
    {
        // Extract last name (assume format "First Last" or "Last, First")
        $nameParts = preg_split('/[,\s]+/', trim($author));
        $lastName = '';
        
        // If comma exists, last name is first part
        if (strpos($author, ',') !== false) {
            $lastName = $nameParts[0];
        } else {
            // Otherwise, last name is last part
            $lastName = end($nameParts);
        }
        
        $lastName = strtoupper(trim($lastName));
        
        if (empty($lastName)) {
            return 'U00';
        }
        
        $firstLetter = substr($lastName, 0, 1);
        $secondLetter = strlen($lastName) > 1 ? substr($lastName, 1, 1) : '';
        $thirdLetter = strlen($lastName) > 2 ? substr($lastName, 2, 1) : '';
        
        // Start with first letter
        $cutter = $firstLetter;
        
        // Determine second digit based on Cutter notation rules
        $secondDigit = '0';
        
        // After initial vowels (A, E, I, O, U)
        if (in_array($firstLetter, ['A', 'E', 'I', 'O', 'U'])) {
            $secondDigit = $this->getCutterDigitAfterVowel($secondLetter);
        }
        // After initial letter S
        elseif ($firstLetter === 'S') {
            $secondDigit = $this->getCutterDigitAfterS($secondLetter);
        }
        // After initial letter Q
        elseif ($firstLetter === 'Q') {
            if ($secondLetter === 'U') {
                // For Qu, use third letter
                $secondDigit = $this->getCutterDigitAfterQu($thirdLetter);
            } else {
                $secondDigit = '2'; // Qa-Qt default
            }
        }
        // After initial consonants
        else {
            $secondDigit = $this->getCutterDigitAfterConsonant($secondLetter);
        }
        
        $cutter .= $secondDigit;
        
        // Determine third digit if needed
        $thirdDigit = $this->getCutterThirdDigit($thirdLetter);
        $cutter .= $thirdDigit;
        
        return $cutter;
    }

    /**
     * Get Cutter digit after initial vowels (A, E, I, O, U)
     */
    private function getCutterDigitAfterVowel($letter)
    {
        $letter = strtoupper($letter);
        
        if (in_array($letter, ['B', 'C'])) return '2';
        if (in_array($letter, ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'])) return '3';
        if (in_array($letter, ['L', 'M'])) return '4';
        if (in_array($letter, ['N', 'O'])) return '5';
        if (in_array($letter, ['P', 'Q'])) return '6';
        if ($letter === 'R') return '7';
        if (in_array($letter, ['S', 'T'])) return '8';
        if (in_array($letter, ['U', 'V', 'W', 'X', 'Y', 'Z'])) return '9';
        
        return '2'; // Default
    }

    /**
     * Get Cutter digit after initial letter S
     */
    private function getCutterDigitAfterS($letter)
    {
        $letter = strtoupper($letter);
        
        if ($letter === 'A') return '2';
        if (in_array($letter, ['C', 'H'])) return '3';
        if ($letter === 'E') return '4';
        if (in_array($letter, ['H', 'I'])) return '5';
        if (in_array($letter, ['M', 'N', 'O', 'P'])) return '6';
        if (in_array($letter, ['Q', 'R', 'S', 'T'])) return '7';
        if ($letter === 'U') return '8';
        if (in_array($letter, ['W', 'X', 'Y', 'Z'])) return '9';
        
        return '2'; // Default
    }

    /**
     * Get Cutter digit after Qu
     */
    private function getCutterDigitAfterQu($letter)
    {
        $letter = strtoupper($letter);
        
        if ($letter === 'A') return '3';
        if ($letter === 'E') return '4';
        if ($letter === 'I') return '5';
        if ($letter === 'O') return '6';
        if ($letter === 'R') return '7';
        if ($letter === 'Y') return '9';
        
        return '3'; // Default
    }

    /**
     * Get Cutter digit after initial consonants
     */
    private function getCutterDigitAfterConsonant($letter)
    {
        $letter = strtoupper($letter);
        
        if (in_array($letter, ['A', 'B', 'C', 'D'])) return '3';
        if (in_array($letter, ['E', 'F', 'G', 'H'])) return '4';
        if (in_array($letter, ['I', 'J', 'K', 'L', 'M', 'N'])) return '5';
        if (in_array($letter, ['O', 'P', 'Q'])) return '6';
        if (in_array($letter, ['R', 'S', 'T'])) return '7';
        if (in_array($letter, ['U', 'V', 'W', 'X'])) return '8';
        if ($letter === 'Y') return '9';
        
        return '3'; // Default
    }

    /**
     * Get third digit for Cutter number
     */
    private function getCutterThirdDigit($letter)
    {
        $letter = strtoupper($letter);
        
        if (in_array($letter, ['A', 'B', 'C', 'D'])) return '2';
        if (in_array($letter, ['E', 'F', 'G', 'H'])) return '3';
        if (in_array($letter, ['I', 'J', 'K', 'L'])) return '4';
        if ($letter === 'M') return '5';
        if (in_array($letter, ['N', 'O', 'P', 'Q'])) return '6';
        if (in_array($letter, ['R', 'S', 'T'])) return '7';
        if (in_array($letter, ['U', 'V', 'W'])) return '8';
        if (in_array($letter, ['X', 'Y', 'Z'])) return '9';
        
        return '0'; // Default for no third letter
    }
}
