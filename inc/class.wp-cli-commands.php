<?php
namespace VIP\Learn\Audit;

use WP_CLI;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Command {
    private $title_case;

    public function __construct() {
        $this->title_case = new TitleCase();
    }

    /**
     * Audit a Sensei course for title case issues.
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
     */
    public function course_title_audit( $args ) {
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
                                'LastChar' => $last_char . ' [' . bin2hex($last_char) . ']',
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
    WP_CLI::add_command( 'vip-learn audit course-title', [ new Command(), 'course_title_audit' ] );
    WP_CLI::add_command( 'vip-learn audit course-punctuation', [ new Command(), 'course_punctuation_audit' ] );
    WP_CLI::add_command( 'vip-learn audit check-title-case', [ new Command(), 'check_title_case' ] );
}
