<?php
namespace VIP\Learn\Audit;

class TitleCase
{
    private $lower_case = [
        'a',
        'and',
        'as',
        'at',
        'but',
        'by',
        'down',
        'for',
        'from',
        'if',
        'in',
        'into',
        'like',
        'near',
        'nor',
        'of',
        'off',
        'on',
        'once',
        'onto',
        'or',
        'over',
        'past',
        'so',
        'than',
        'that',
        'to',
        'upon',
        'when',
        'with',
        'yet'
    ];

    private $upper_case = [];

    private $special_case = [
        'WordPress',
        'phpMyAdmin',
        'Xdebug'
    ];

    public function to_title_case ( $text )
    {
        $words = explode(' ', $text);
        $last_index = count($words) - 1;

        foreach ($words as $index => &$word) {
            // Handle hyphenated words
            if (strpos($word, '-') !== false) {
                $sub_words = explode('-', $word);
                foreach ($sub_words as &$sub_word) {
                    $lower_sub_word = strtolower($sub_word);

                    // Special case
                    if (in_array($sub_word, $this->special_case)) {
                        $sub_word = $sub_word;
                        continue;
                    }

                    // Upper case
                    if (in_array($lower_sub_word, $this->upper_case)) {
                        $sub_word = strtoupper($sub_word);
                        continue;
                    }

                    // Lower case (unless last word)
                    if (in_array($lower_sub_word, $this->lower_case) && $index !== $last_index) {
                        $sub_word = strtolower($sub_word);
                        continue;
                    }

                    // Default capitalize
                    $sub_word = ucfirst(strtolower($sub_word));
                }
                $word = implode('-', $sub_words);
                continue;
            }

            $lower_word = strtolower($word);

            // Special case
            if (in_array($word, $this->special_case)) {
                $word = $word;
                continue;
            }

            // Upper case
            if (in_array($lower_word, $this->upper_case)) {
                $word = strtoupper($word);
                continue;
            }

            // Lower case (unless last word)
            if (in_array($lower_word, $this->lower_case) && $index !== $last_index) {
                $word = strtolower($word);
                continue;
            }

            // Default capitalize
            $word = ucfirst(strtolower($word));
        }

        return implode(' ', $words);
    }

    public function add_lower_case ( array $words )
    {
        foreach ($words as $word) {
            $word = strtolower($word);
            if (!in_array($word, $this->lower_case)) {
                $this->lower_case[] = $word;
            }
        }
    }

    public function remove_lower_case ( array $words )
    {
        $this->lower_case = array_diff($this->lower_case, array_map('strtolower', $words));
    }

    public function add_upper_case ( array $words )
    {
        foreach ($words as $word) {
            $word = strtoupper($word);
            if (!in_array($word, $this->upper_case)) {
                $this->upper_case[] = $word;
            }
        }
    }

    public function remove_upper_case ( array $words )
    {
        $this->upper_case = array_diff($this->upper_case, array_map('strtoupper', $words));
    }

    public function add_special_case ( array $words )
    {
        foreach ($words as $word) {
            if (!in_array($word, $this->special_case)) {
                $this->special_case[] = $word;
            }
        }
    }

    public function remove_special_case ( array $words )
    {
        $this->special_case = array_diff($this->special_case, $words);
    }
}

// Example usage:
// $title_case = new TitleCase();
// $title_case->add_special_case([ 'WordPress' ]);
// $title_case->add_upper_case([ 'NASA' ]);

// $text = "this is a test for fast-forward wordpress and nasa";
// echo $title_case->to_title_case( $text ); // Output: "This is a Test for Fast-Forward WordPress and NASA"
