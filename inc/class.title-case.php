<?php
namespace VIP\Learn\Audit;

class TitleCase
{
    /**
     * List of words to always keep in lowercase.
     * 
     * @var array
     */
    private $lower_case = [
        'a', 'an', 'and', 'as', 'at', 'by', 'for', 'in', 'of', 'on', 'to', 'up', 'the',
        'or', 'nor', 'yet', 'but', 'so', 'per', 'via', 'is', 'it', 'be', 'vs'
    ];

    /**
     * List of words to always keep in uppercase.
     * 
     * @var array
     */
    private $upper_case = [
        'HTML', 'PHP', 'AJAX', 'SSH', 'WP', 'CLI', 'GUI', 'HTTP', 'API', 'VVV', 'CSS', 'WSOD', 
        'ELK', 'VIP', 'MU', 'PHPCS', 'IDE', 'PHPMD', 'SQL', 'PEAR', 'BOM', 'APM', 'JS', 'OOM',
        'SSRF', 'IP', 'URL', 'WAF', 'TLS', 'XSS', 'DOM', 'OWASP', 'XML', 'RPC', 'CMS', 'TTL', 'TTFB', 'CDN',
        'SEO'
    ];

    /**
     * List of words with special case formatting.
     * 
     * @var array
     */
    private $special_case = [
        'WordPress', 'phpMyAdmin', 'Xdebug', 'VirtualBox', 'MySQL', 'MariaDB', 'JavaScript', 'PHPStan',
        'TablePlus', 'DevTools', 'MySQLi', 'PHP_CodeSniffer', 'jQuery', 'SQLite', 'vs.', 'WP_Query', 'PhpStorm', 'URLs',
        'Elasticsearch', 'DOMPurify', 'DDoS', 'DoS', 'WebDriver', 'WordPress.com', 'ETag', 'wp-env', 'wp-admin',
        'fopen'
    ];

    /**
     * Conditional uppercase rules.
     * 
     * @var array
     */
    private $conditional_upper_case = [
        'REST' => 'API', // Capitalize REST only if followed by API
        'VS' => 'Code',
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
     * Extract parts of a word including leading and trailing characters.
     * 
     * @param string $input_string
     * @return array
     */
    private function extract_word_parts( string $input_string ): array
    {
        $leading_chars = '';
        $trailing_chars = '';
        $word = '';

        $length = strlen( $input_string );
        $i = 0;

        while ( $i < $length && !ctype_alnum( $input_string[ $i ] ) ) {
            $leading_chars .= $input_string[ $i ];
            $i++;
        }

        $j = $length - 1;
        while ( $j >= 0 && !ctype_alnum( $input_string[ $j ] ) ) {
            $trailing_chars = $input_string[ $j ] . $trailing_chars;
            $j--;
        }

        $word = substr( $input_string, $i, $j - $i + 1 );

        return [
            'leading_chars' => $leading_chars,
            'trailing_chars' => $trailing_chars,
            'word' => $word
        ];
    }

    /**
     * Check if word parts look like code.
     * 
     * @param array $word_parts
     * @return bool
     */
    private function word_parts_look_like_code( array $word_parts ): bool
    {
        if ( strpos( $word_parts['word'], '_' ) !== false ) {
            return true;
        }
        if ( $word_parts['trailing_chars'] === '()' ) {
            return true;
        }

        return false;
    }

    /**
     * Check if word parts look like a file name.
     * 
     * @param string $word
     * @return bool
     */
    private function word_parts_look_like_file_name( array $word_parts ): bool
    {
        $word = $word_parts['word'];

        $extensions = [ 
            'txt', 'csv', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'png', 'jpg', 
            'jpeg', 'gif', 'zip', 'rar', 'tar', 'gz', 'html', 'php', 'js', 'css', 
            'json', 'xml', 'md', 'yml' 
        ];

        $parts = explode( '.', $word );
        if ( count( $parts ) > 1 ) {
            $extension = strtolower( end( $parts ) );
            if ( in_array( $extension, $extensions ) ) {
                return true;
            }
        }

        return false;
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

    /**
     * Add words to the list of uppercase words.
     * 
     * @param array $words
     * @return void
     */
    public function add_upper_case( array $words ): void
    {
        foreach ( $words as $word ) {
            $word = strtoupper( $word );
            if ( !in_array( $word, $this->upper_case ) ) {
                $this->upper_case[] = $word;
            }
        }
    }

    /**
     * Remove words from the list of uppercase words.
     * 
     * @param array $words
     * @return void
     */
    public function remove_upper_case( array $words ): void
    {
        $this->upper_case = array_diff( $this->upper_case, array_map( 'strtoupper', $words ) );
    }

    /**
     * Add words to the list of special case words.
     * 
     * @param array $words
     * @return void
     */
    public function add_special_case( array $words ): void
    {
        foreach ( $words as $word ) {
            if ( !in_array( $word, $this->special_case ) ) {
                $this->special_case[] = $word;
            }
        }
    }

    /**
     * Remove words from the list of special case words.
     * 
     * @param array $words
     * @return void
     */
    public function remove_special_case( array $words ): void
    {
        $this->special_case = array_diff( $this->special_case, $words );
    }
}
