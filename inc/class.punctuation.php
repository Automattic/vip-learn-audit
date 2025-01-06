<?php
namespace VIP\Learn\Audit;

class Punctuation {

     /**
     * List of words to always keep in uppercase.
     * 
     * @var array
     */
    protected array $upper_case = [
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
    protected array $special_case = [
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
    protected $conditional_upper_case = [
        'REST' => 'API', // Capitalize REST only if followed by API
        'VS' => 'Code',
    ];

    public function get_incorrect_word_instances( string $text, array $word_instances ): array
    {
        if( empty( $text ) ) return [];
        $results = [];
        $words = explode( ' ', $text );
        foreach( $words as $index => $word ) {
            $word_parts = $this->extract_word_parts( $word );

            // check against provided words_instances array
            $incorrect_special_case = $this->check_word_instance( $word_parts['word'], $word_instances );

            if( !empty( $incorrect_special_case ) ) {
                $results[] = [
                    'position' => $index,
                    'instance' => $incorrect_special_case,
                ];
            }
        }
        return $results;
    }

    protected function check_word_instance( string $word, array $word_instances ) {
        $lcase_word = strtolower( $word );
        $result =[];
        foreach( $word_instances as $wi ){
            if( $lcase_word === strtolower( $wi ) ){
                if( $word !== $wi ){
                    $result['correct_usage'] = $wi;
                    $result['incorrect_usage'] = $word;
                    return $result;
                }
            }
        }
        return $result;
    }

    /**
     * Extract parts of a word including leading and trailing characters.
     * 
     * @param string $input_string
     * @return array
     */
    protected function extract_word_parts( string $input_string ): array
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

    public function get_special_case_array() {
        return $this->special_case;
    }

    /**
     * Check if word parts look like code.
     * 
     * @param array $word_parts
     * @return bool
     */
    protected function word_parts_look_like_code( array $word_parts ): bool
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
    protected function word_parts_look_like_file_name( array $word_parts ): bool
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

}