<?php
namespace VIP\Learn\Audit;

class SentenceCase extends Punctuation
{
    public function to_sentence_case( string $text ): string
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

        // Capitalize first word
        if ( $index === 0 ) {
            $word = $word_parts['leading_chars'] . ucfirst( strtolower( $word_parts['word'] ) ) . $word_parts['trailing_chars'];
            return;
        }

        // Default lowercase.
        $word = $word_parts['leading_chars'] . strtolower( $word_parts['word'] ) . $word_parts['trailing_chars'];
    }
}
