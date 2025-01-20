<?php
namespace VIP\Learn\Audit;

use WP_CLI;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WP CLI command class for auditing Sensei courses.
 * 
 * @package VIP\Learn\Audit
 * @category CLI
 * @author Automattic
 * @license GPL-2.0-or-later
 * @link     https://github.com/Automattic/vip-learn-audit
 */
class Command {
    /**
     * Title case converter instance.
     *
     * @var TitleCase
     */
    private $title_case;

    /**
     * Sentence case converter instance.
     *
     * @var SentenceCase
     */
    private $sentence_case;

    /**
     * Initialize the command.
     */
    public function __construct() {
        $this->title_case = new TitleCase();
        $this->sentence_case = new SentenceCase();
    }

    /**
     * Audit a specified Sensei course for title case issues in lesson titles and headings 
     * 
     * @param array $args Command arguments.
     *
     * ## OPTIONS
     *
     * <course_id>
     * : The ID of the Sensei course to audit.
     *
     * ## EXAMPLES
     *
     * wp course-title-audit 123
     *
     * @return void
     */
    public function course_title_case_audit( $args ) {
        list( $course_id ) = $args;

        // Get lessons for the course.
        $lessons = get_posts([
            'post_type'   => 'lesson',
            'posts_per_page' => -1,
            'meta_query'  => [
                [
                    'key'   => '_lesson_course',
                    'value' => $course_id,
                ],
            ],
        ]);

        if ( empty( $lessons ) ) {
            WP_CLI::error( 'No lessons found for this course.' );
        }

        $rows = [];

        foreach ( $lessons as $lesson ) {
            $lesson_title     = $lesson->post_title;
            $lesson_content   = $lesson->post_content;
            $title_case_title = $this->title_case->to_title_case( $lesson_title );

            if ( $lesson_title !== $title_case_title ) {
                $rows[] = [
                    'Current Title' => $lesson_title,
                    'Title Case'    => $title_case_title,
                    'Edit Link'     => admin_url( "post.php?post={$lesson->ID}&action=edit" ),
                ];
            }

            // Extract headings from content.
            preg_match_all( '/<h[1-6][^>]*>(.*?)<\/h[1-6]>/', $lesson_content, $headings );

            foreach ( $headings[1] as $heading ) {

                // pre-process headings
                $heading = str_replace( '<strong>', '', $heading );
                $heading = str_replace( '</strong>', '', $heading );
                $heading = str_replace( '<br>', '', $heading );
                $heading = str_replace( '&nbsp;', '', $heading );
                $heading = str_replace( '</code>', ' ', $heading ); 
                $heading = str_replace( '<code>', '', $heading ); 

                $title_case_heading = $this->title_case->to_title_case( $heading );
                if ( $heading !== $title_case_heading ) {
                    $rows[] = [
                        'Current Title' => $heading,
                        'Title Case'    => $title_case_heading,
                        'Edit Link'     => admin_url( "post.php?post={$lesson->ID}&action=edit" ),
                    ];
                }
            }
        }

        if ( empty( $rows ) ) {
            WP_CLI::success( 'All titles and headings are properly title cased.' );
            return;
        }

        // Display the results in a table.
        WP_CLI\Utils\format_items( 'table', $rows, [ 'Current Title', 'Title Case', 'Edit Link' ] );
    }

    /**
     * Audit a specified Sensei course for for sentence case issues in lesson titles and headings 
     * 
     * @param array $args Command arguments.
     *
     * ## OPTIONS
     *
     * <course_id>
     * : The ID of the Sensei course to audit.
     */
    public function course_sentence_case_audit( $args ) {
        list( $course_id ) = $args;

        // Get lessons for the course.
        $lessons = get_posts([
            'post_type'   => 'lesson',
            'posts_per_page' => -1,
            'meta_query'  => [
                [
                    'key'   => '_lesson_course',
                    'value' => $course_id,
                ],
            ],
        ]);

        if ( empty( $lessons ) ) {
            WP_CLI::error( 'No lessons found for this course.' );
        }

        $rows = [];

        foreach ( $lessons as $lesson ) {
            $lesson_content = $lesson->post_content;
            $lesson_title = $lesson->post_title;
            $sentence_case_title = $this->title_case->to_title_case( $lesson_title );

            if ( $lesson_title !== $sentence_case_title ) {
                $rows[] = [
                    'Current Title' => $lesson_title,
                    'Sentence Case'    => $sentence_case_title,
                    'Edit Link'     => admin_url( "post.php?post={$lesson->ID}&action=edit" ),
                ];
            }

            // Extract headings from content.
            preg_match_all( '/<h[1-6][^>]*>(.*?)<\/h[1-6]>/', $lesson_content, $headings );

            foreach ( $headings[1] as $heading ) {

                // pre-process headings
                $heading = str_replace( '<strong>', '', $heading );
                $heading = str_replace( '</strong>', '', $heading );
                $heading = str_replace( '<br>', '', $heading );
                $heading = str_replace( '&nbsp;', '', $heading );
                $heading = str_replace( '</code>', ' ', $heading ); 
                $heading = str_replace( '<code>', '', $heading ); 

                $sentence_case_heading = $this->sentence_case->to_sentence_case( $heading );
                if( $heading !== $sentence_case_heading ){
                    $rows[] = [
                        'Current Title' => $heading,
                        'Sentence Case' => $sentence_case_heading,
                        'Edit Link' => admin_url( "post.php?post={$lesson->ID}&action=edit" ),
                    ];
                }
            }
        }

        if ( empty( $rows ) ) {
            WP_CLI::success( 'All headings is properly sentence cased.' );
            return;
        }

        WP_CLI\Utils\format_items( 'table', $rows, [ 'Current Title', 'Sentence Case', 'Edit Link' ] );

    }

    /**
     * Audit a Sensei course for special capitalization instances e.g. trademarks.
     * 
     * @param array $args Command arguments
     *
     * ## OPTIONS
     *
     * <course_id>
     * : The ID of the Sensei course to audit.
     *
     * ## EXAMPLES
     *
     * wp vip-learn audit word-instances 123
     *
     */
    public function word_instance_audit( $args ) {
        list( $course_id ) = $args;

        // Get lessons for the course.
        $lessons = get_posts([
            'post_type'   => 'lesson',
            'posts_per_page' => -1,
            'meta_query'  => [
                [
                    'key'   => '_lesson_course',
                    'value' => $course_id,
                ],
            ],
        ]);

        if ( empty( $lessons ) ) {
            WP_CLI::error( 'No lessons found for this course.' );
        }

        $rows = [];

        foreach ( $lessons as $lesson ) {

            // Parse the content using DOMDocument.
            $dom = new \DOMDocument();
            @$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $lesson->post_content );

            $text = $lesson->post_title . ' ' . $dom->textContent;

            $punctuation = new Punctuation;

            $results = $punctuation->get_incorrect_word_instances( $text, $punctuation->get_special_case_array() );
            foreach( $results as $result ) {
                
                $rows[] = [
                    'Page Title' => $lesson->post_title,
                    'Word Instance'    => $result['instance']['incorrect_usage'],
                    'Correct Word Instance' => $result['instance']['correct_usage'],
                    'Edit Link'     => admin_url( "post.php?post={$lesson->ID}&action=edit" ),
                ];
            }
        }

        if ( empty( $rows ) ) {
            WP_CLI::success( 'No incorrect word instances found.' );
            return;
        }

        // Display the results in a table.
        WP_CLI\Utils\format_items( 'table', $rows, [ 'Page Title', 'Word Instance', 'Correct Word Instance', 'Edit Link' ] );
    }

    /**
     * Audit a Sensei course for basic punctuation issues, e.g. missing full-stops
     * 
     * @param array $args Command arguments.
     *
     * ## OPTIONS
     *
     * <course_id>
     * : The ID of the Sensei course to audit.
     */
    public function course_punctuation_audit( $args ) {
        list( $course_id ) = $args;
    
        // Get lessons for the course.
        $lessons = get_posts([
            'post_type'   => 'lesson',
            'posts_per_page' => -1,
            'meta_query'  => [
                [
                    'key'   => '_lesson_course',
                    'value' => $course_id,
                ],
            ],
        ]);
    
        if ( empty( $lessons ) ) {
            WP_CLI::error( 'No lessons found for this course.' );
        }
    
        $rows = [];
    
        foreach ( $lessons as $lesson ) {
            $lesson_content = $lesson->post_content;

            // strip out some characters which confuse the string comparison later
            $lesson_content = str_replace('&nbsp;', '', $lesson_content);
            $lesson_content = str_replace("\u{00A0}", " ", $lesson_content);
    
            // Parse the content using DOMDocument.
            $dom = new \DOMDocument();
            @$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $lesson_content );

            $allowed_characters = [ '.', '!', '?', ':', '"', '”' ];
    
            // Extract paragraphs, list items, and blockquotes.
            foreach ( ['p', 'li', 'blockquote'] as $tag ) {
                $elements = $dom->getElementsByTagName( $tag );
                foreach ( $elements as $element ) {

                    $text = trim( $element->textContent );
                    
                    if( !empty( $text) ){

                        $last_char = substr( $text, -1 );

                        if ( ! in_array( $last_char, $allowed_characters ) ) {
                            $rows[] = [
                                'Text'      => $text,
                                'LastChar' => $last_char. ' [' . bin2hex($last_char) . ']',
                                'Edit Link' => admin_url( "post.php?post={$lesson->ID}&action=edit" ),
                            ];
                        }
                    }
                }
            }
        }
    
        if ( empty( $rows ) ) {
            WP_CLI::success( 'All paragraphs, list items, and blockquotes are properly punctuated.' );
            return;
        }
    
        // Display the results in a table.
        WP_CLI\Utils\format_items( 'table', $rows, [ 'Text', 'LastChar', 'Edit Link' ] );
    }

    public function link_audit( $args ) {
        list( $course_id ) = $args;
    
        $site_url = get_site_url();
        
        // Get lessons for the course.
        $lessons = get_posts([
            'post_type'   => 'lesson',
            'posts_per_page' => -1,
            'meta_query'  => [
                [
                    'key'   => '_lesson_course',
                    'value' => $course_id,
                ],
            ],
        ]);
    
        if ( empty( $lessons ) ) {
            WP_CLI::error( 'No lessons found for this course.' );
        }
    
        $rows = [];
    
        foreach ( $lessons as $lesson ) {
            $lesson_content = $lesson->post_content;
            $lesson_url = get_permalink( $lesson->ID );
    
            // Parse the content using DOMDocument.
            $dom = new \DOMDocument();
            @$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $lesson_content );
            $links = $dom->getElementsByTagName('a');
    
            foreach ( $links as $link ) {
                $href = $link->getAttribute('href');
                $target = $link->getAttribute('target');
                $link_text = trim( $link->nodeValue );
    
                // Check if the link is external and does not open in a new tab.
                if ( !empty( $href ) && strpos( $href, $site_url ) === false && $target !== '_blank' ) {
                    $rows[] = [
                        'Lesson URL' => $lesson_url,
                        'Link Text'  => $link_text,
                        'Link URL'   => $href,
                        'Edit Link'  => admin_url( "post.php?post={$lesson->ID}&action=edit" ),
                    ];
                }
            }
        }
    
        if ( empty( $rows ) ) {
            WP_CLI::success( 'No external links without target="_blank" found.' );
            return;
        }
    
        // Display the results in a table.
        WP_CLI\Utils\format_items( 'table', $rows, [ 'Lesson URL', 'Link Text', 'Link URL', 'Edit Link' ] );
    }

    public function completion_times_audit( $args ) {
        list( $course_id ) = $args;
        $course_id = intVal( $course_id );
        if( 0 === $course_id ) {
            WP_CLI::error( "Please specify a course id" );
        }
        $te = new TimeEstimation;
        $data = $te->course_estimate( $course_id );
        $rows = [];
        $total_reading_time = 0;
        $total_estimated_time = 0;
        foreach($data['lesson_data'] as $lesson){
            $rows[] = [
                'Name' => $lesson['lesson_title'],
                'Reading time' => $lesson['lesson_time']['reading_time'],
                'Estimated time' => $lesson['lesson_time']['estimated_time'],
            ];
            $total_reading_time += $lesson['lesson_time']['reading_time'];
            $total_estimated_time += $lesson['lesson_time']['estimated_time'];
        }

        $rows[] = [
            'Name' => 'Totals',
            'Reading time' => $total_reading_time . ' (' . gmdate( "H:i", $total_reading_time ) . ' hours)',
            'Estimated time' => $total_estimated_time . ' (' . gmdate( "H:i", $total_estimated_time ) . ' hours)'
        ];

        if ( empty( $rows ) ) {
            WP_CLI::error( 'No lesson timing data found' );
        } else {
            // Display the results in a table.
            WP_CLI\Utils\format_items( 'table', $rows, [ 'Name', 'Reading time', 'Estimated time' ] );
        }

    }

    /**
     * Check provided string is title case
     *
     * ## OPTIONS
     *
     * <title>
     * : The ID of the Sensei course to audit.
     *
     * ## EXAMPLES
     *
     * wp vip-learn audit check-title-case "this is my title"
     *
     */
    public function check_title_case( $args ) {
        list( $title ) = $args;

        if( empty( $title) ){
            WP_CLI::error("Title is empty");
        }

        $title = trim( $title );

        $title_case_title = $this->title_case->to_title_case( $title );
        if( $title_case_title === $title ){
            WP_CLI::success("Provided title looks like title case");
        } else {
            WP_CLI::error("Provided title should probably be: " . $title_case_title );
        }

    }

    /**
     * Check provided string is sentence case
     *
     * ## OPTIONS
     *
     * <title>
     * : The ID of the Sensei course to audit.
     */
    public function check_sentence_case( $args ) {
        list( $title ) = $args;

        if( empty( $title) ){
            WP_CLI::error("Title is empty");
        }

        $title = trim( $title );

        $sentence_case_title = $this->sentence_case->to_sentence_case( $title );
        if( $sentence_case_title === $title ){
            WP_CLI::success("Provided title looks like sentence case");
        } else {
            WP_CLI::error("Provided title should probably be: " . $sentence_case_title );
        }
    }

    private function get_text_content( $dom ) {
        $text = '';
    
        $body = $dom->getElementsByTagName('body')->item(0);
        if ( $body === null ) {
            return $text; // Return an empty string if the body tag is not present.
        }
    
        foreach ( $body->childNodes as $node ) {
            $text .= $this->node_to_text( $node );
        }
    
        return trim( $text );
    }    

    private function node_to_text( $node ) {
        if ( $node->nodeType === XML_TEXT_NODE ) {
            return $node->nodeValue;
        }

        $text = '';

        if ( $node->hasChildNodes() ) {
            foreach ( $node->childNodes as $child ) {
                $text .= $this->node_to_text( $child );
            }
        }

        return $text;
    }
}

// Register the commands with WP-CLI.
if ( class_exists( 'WP_CLI' ) ) {
    WP_CLI::add_command( 'vip-learn audit course-title-case', [ new Command(), 'course_title_case_audit' ] );
    WP_CLI::add_command( 'vip-learn audit course-sentence-case', [ new Command(), 'course_sentence_case_audit' ] );
    WP_CLI::add_command( 'vip-learn audit course-punctuation', [ new Command(), 'course_punctuation_audit' ] );
    WP_CLI::add_command( 'vip-learn audit check-title-case-string', [ new Command(), 'check_title_case' ] );
    WP_CLI::add_command( 'vip-learn audit check-sentence-case-string', [ new Command(), 'check_sentence_case' ] );
    WP_CLI::add_command( 'vip-learn audit word-instances', [ new Command(), 'word_instance_audit' ] );
    WP_CLI::add_command( 'vip-learn audit links', [ new Command(), 'link_audit' ] );
    WP_CLI::add_command( 'vip-learn audit completion-times', [ new Command(), 'completion_times_audit' ] );
}
