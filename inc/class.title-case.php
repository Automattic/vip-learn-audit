<?php
/**
 * Title case conversion functionality.
 *
 * Handles converting text to title case following specified rules for capitalization,
 * including handling of lowercase words, hyphenation, and special cases.
 *
 * @package VIP\Learn\Audit
 */

namespace VIP\Learn\Audit;

/**
 * Class for converting text to title case with customizable rules.
 *
 * Extends the Punctuation class to handle title case conversion while respecting
 * punctuation rules. Maintains lists of words that should remain lowercase and
 * handles special cases like hyphenated words.
 */
class TitleCase extends Punctuation
{
    /**
     * List of words to always keep in lowercase.
     * 
     * @var array
     */
    private $lower_case = [
        'a', 'an', 'and', 'as', 'at', 'by', 'for', 'in', 'of', 'on', 'up', 'the',
        'or', 'nor', 'yet', 'but', 'so', 'per', 'via', 'is', 'it', 'be', 'vs'
    ];

    /**
     * Trailing characters to handle separately.
     * 
     * @var array
     */
    private $trailing_chars = [
        ',', ':', '?'
    ];

    /**
     * Convert text to title case following specified rules.
     * 
     * @param string $text
     * @return string
     */
    public function to_title_case( string $text ): string
    {
        $words = explode( ' ', $text );
        $last_index = count( $words ) - 1;

        foreach ( $words as $index => &$word ) {
            $lower_word = strtolower( $word );

            // Check for conditional uppercase rules.
            if ( array_key_exists( $word, $this->conditional_upper_case ) ) {
                if ( isset( $words[ $index + 1 ] ) && $words[ $index + 1 ] === $this->conditional_upper_case[ $word ] ) {
                    $word = strtoupper( $word );
                    continue;
                }
            }

            // Handle hyphenated words.
            if ( strpos( $word, '-' ) !== false ) {
                $word_parts = $this->extract_word_parts( $word );
                if ( !in_array( $word_parts['word'], $this->special_case ) && !$this->word_parts_look_like_file_name( $word_parts ) ) {
                    $sub_words = explode( '-', $word );
                    foreach ( $sub_words as &$sub_word ) {
                        $this->process_word( $sub_word, $index, $last_index );
                    }
                    $word = implode( '-', $sub_words );
                    continue;
                }
            }

            // Handle words separated by a slash.
            if ( strpos( $word, '/' ) !== false ) {
                $sub_words = explode( '/', $word );
                foreach ( $sub_words as &$sub_word ) {
                    $this->process_word( $sub_word, $index, $last_index );
                }
                $word = implode( '/', $sub_words );
                continue;
            }

            $this->process_word( $word, $index, $last_index );
        }

        return implode( ' ', $words );
    }

    /**
     * Process individual word for title case conversion.
     * 
     * @param string $word
     * @param int $index
     * @param int $last_index
     * @return void
     */
    private function process_word( string &$word, int $index, int $last_index ): void
    {
        $word_parts = $this->extract_word_parts( $word );
        $lower_word = strtolower( $word_parts['word'] );
        $upper_word = strtoupper( $word_parts['word'] );

        // Special case.
        if ( in_array( $word_parts['word'], $this->special_case ) ) {
            $word = $word_parts['leading_chars'] . $word_parts['word'] . $word_parts['trailing_chars'];
            return;
        }

        // Upper case.
        if ( in_array( $upper_word, $this->upper_case ) ) {
            $word = $word_parts['leading_chars'] . strtoupper( $word_parts['word'] ) . $word_parts['trailing_chars'];
            return;
        }

        // Don't format words that look like code.
        if ( $this->word_parts_look_like_code( $word_parts ) ) {
            $word = $word_parts['leading_chars'] . $word_parts['word'] . $word_parts['trailing_chars'];
            return;
        }

        // Don't format words that look like a filename.
        if ( $this->word_parts_look_like_file_name( $word_parts ) ) {
            $word = $word_parts['leading_chars'] . $word_parts['word'] . $word_parts['trailing_chars'];
            return;
        }

        // Capitalize first and last words regardless of lower_case.
        if ( $index === 0 || $index === $last_index ) {
            $word = $word_parts['leading_chars'] . ucfirst( strtolower( $word_parts['word'] ) ) . $word_parts['trailing_chars'];
            return;
        }

        // Lower case.
        if ( in_array( $lower_word, $this->lower_case ) ) {
            $word = $word_parts['leading_chars'] . strtolower( $word_parts['word'] ) . $word_parts['trailing_chars'];
            return;
        }

        // Default capitalize.
        $word = $word_parts['leading_chars'] . ucfirst( strtolower( $word_parts['word'] ) ) . $word_parts['trailing_chars'];
    }

    /**
     * Add words to the list of lowercase words.
     * 
     * @param array $words
     * @return void
     */
    public function add_lower_case( array $words ): void
    {
        foreach ( $words as $word ) {
            $word = strtolower( $word );
            if ( !in_array( $word, $this->lower_case ) ) {
                $this->lower_case[] = $word;
            }
        }
    }

    /**
     * Remove words from the list of lowercase words.
     * 
     * @param array $words
     * @return void
     */
    public function remove_lower_case( array $words ): void
    {
        $this->lower_case = array_diff( $this->lower_case, array_map( 'strtolower', $words ) );
    }

}
