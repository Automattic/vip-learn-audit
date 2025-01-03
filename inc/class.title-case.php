<?php
namespace VIP\Learn\Audit;

class TitleCase
{
    // https://titlecaseconverter.com/rules/#:~:text=letters%20or%20more-,Do%20not%20capitalize%20articles%2C%20conjunctions%2C%20and%20prepositions%20of%20three%20letters%20or%20fewer,-Source
    private $lower_case = [
        'a', 'an', 'and', 'as', 'at', 'by', 'for', 'in', 'of', 'on', 'to', 'up', 'the',
        'or', 'nor', 'yet', 'but', 'so', 'per', 'via', 'is', 'it', 'be', 'vs'
    ];
    

    private $upper_case = [
        'HTML', 'PHP', 'AJAX', 'SSH', 'WP', 'CLI', 'GUI', 'HTTP', 'API', 'VVV', 'CSS', 'WSOD', 
        'ELK', 'VIP', 'MU', 'PHPCS', 'IDE', 'PHPMD', 'SQL', 'PEAR', 'BOM', 'APM', 'JS', 'OOM',
        'SSRF', 'IP', 'URL', 'WAF', 'TLS', 'XSS', 'DOM', 'OWASP', 'XML', 'RPC', 'CMS', 'TTL', 'TTFB', 'CDN',
        'SEO'
    ];

    private $special_case = [
        'WordPress', 'phpMyAdmin', 'Xdebug', 'VirtualBox', 'MySQL', 'MariaDB', 'JavaScript', 'PHPStan',
        'TablePlus', 'DevTools', 'MySQLi', 'PHP_CodeSniffer', 'jQuery', 'SQLite', 'vs.', 'WP_Query', 'PhpStorm', 'URLs',
        'Elasticsearch', 'DOMPurify', 'DDoS', 'DoS', 'wp_options', 'WebDriver', 'WordPress.com', 'ETag'
    ];

    private $conditional_upper_case = [
        'REST' => 'API', // Capitalize REST only if followed by API
        'VS' => 'Code',
    ];

    private $trailing_chars = [
        ',', ':', '?'
    ];

    public function to_title_case($text)
    {
        $words = explode(' ', $text);
        $last_index = count($words) - 1;

        foreach ($words as $index => &$word) {
            $lower_word = strtolower($word);

            // Check for conditional uppercase rules.
            if (array_key_exists($word, $this->conditional_upper_case)) {
                if (isset($words[$index + 1]) && $words[$index + 1] === $this->conditional_upper_case[$word]) {
                    $word = strtoupper($word);
                    continue;
                }
            }

            // Handle hyphenated words.
            if (strpos($word, '-') !== false) {
                $sub_words = explode('-', $word);
                foreach ($sub_words as &$sub_word) {
                    $this->process_word($sub_word, $index, $last_index);
                }
                $word = implode('-', $sub_words);
                continue;
            }

            // Handle words separated by a slash.
            if (strpos($word, '/') !== false) {
                $sub_words = explode('/', $word);
                foreach ($sub_words as &$sub_word) {
                    $this->process_word($sub_word, $index, $last_index);
                }
                $word = implode('/', $sub_words);
                continue;
            }

            // Handle words inside parentheses.
            $word_last_char = substr($word, -1);
            if (substr($word, 0, 1) === '(' && $word_last_char === ')') {
                $inside_word = ltrim($word, '(');
                $inside_word = rtrim($inside_word, ')');
                $this->process_word($inside_word, $index, $last_index);
                $word = '(' . $inside_word . ')';
                continue;
            }

            if (substr($word, 0, 1) === '(') {
                $inside_word = ltrim($word, '(');
                $this->process_word($inside_word, $index, $last_index);
                $word = '(' . $inside_word;
                continue;
            }

            // Handle words immediately followed by a trailing characters
            $word_last_char = substr($word, -1);
            $trailing_char_match = false;
            foreach( $this->trailing_chars as $char ) {
                if ( $word_last_char === $char ) {
                    $trailing_char_match = true;
                    $inside_word = rtrim( $word, $char );
                    $this->process_word( $inside_word, $index, $last_index );
                    $word = $inside_word . $char;
                    continue;
                }
            }
            if($trailing_char_match === true){
                continue;
            }

            // Handle words beginning with a "
            if (substr($word, 0, 1) === '"') {
                $inside_word = ltrim($word, '"');
                $this->process_word($inside_word, $index, $last_index);
                $word = '"' . $inside_word;
                continue;
            }

            // Capitalize first and last words regardless of lower_case.
            if ($index === 0 || $index === $last_index) {
                // if($word === "DOMPurify"){
                //     echo "yes";
                // }
                if (!in_array($word, $this->special_case) && !in_array($word, $this->upper_case)) {
                    $word = ucfirst(strtolower($word));
                    continue;
                }
            }

            $this->process_word($word, $index, $last_index);
        }

        return implode(' ', $words);
    }

    private function process_word(&$word, $index, $last_index)
    {
        $lower_word = strtolower($word);

        // Special case.
        if (in_array($word, $this->special_case)) {
            $word = $word;
            return;
        }

        // Upper case.
        $upper_word = strtoupper($word);
        if (in_array($upper_word, $this->upper_case)) {
            $word = strtoupper($word);
            return;
        }

        // Lower case (unless last word).
        if (in_array($lower_word, $this->lower_case)) {
            $word = strtolower($word);
            return;
        }

        // Default capitalize.
        $word = ucfirst(strtolower($word));
    }

    public function add_lower_case(array $words)
    {
        foreach ($words as $word) {
            $word = strtolower($word);
            if (!in_array($word, $this->lower_case)) {
                $this->lower_case[] = $word;
            }
        }
    }

    public function remove_lower_case(array $words)
    {
        $this->lower_case = array_diff($this->lower_case, array_map('strtolower', $words));
    }

    public function add_upper_case(array $words)
    {
        foreach ($words as $word) {
            $word = strtoupper($word);
            if (!in_array($word, $this->upper_case)) {
                $this->upper_case[] = $word;
            }
        }
    }

    public function remove_upper_case(array $words)
    {
        $this->upper_case = array_diff($this->upper_case, array_map('strtoupper', $words));
    }

    public function add_special_case(array $words)
    {
        foreach ($words as $word) {
            if (!in_array($word, $this->special_case)) {
                $this->special_case[] = $word;
            }
        }
    }

    public function remove_special_case(array $words)
    {
        $this->special_case = array_diff($this->special_case, $words);
    }
}

// Example usage:
// $title_case = new TitleCase();
// $title_case->add_special_case([ 'WordPress' ]);
// $title_case->add_upper_case([ 'NASA' ]);

// $text = "this is a test for fast-forward wordpress and REST API";
// echo $title_case->to_title_case($text);
// Output: "This is a Test for Fast-Forward WordPress and REST API"
