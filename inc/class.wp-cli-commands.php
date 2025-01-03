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
     * @when after_wp_load
     */
    public function course_title_audit( $args ) {
        list( $course_id ) = $args;

        // Get lessons for the course.
        $lessons = get_posts([
            'post_type'   => 'lesson',
            'post_status' => 'publish',
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
     * Audit a Sensei course for punctuation issues.
     *
     * ## OPTIONS
     *
     * <course_id>
     * : The ID of the Sensei course to audit.
     *
     * ## EXAMPLES
     *
     * wp course-punctuation-audit 123
     *
     * @when after_wp_load
     */
    public function course_punctuation_audit( $args ) {
        list( $course_id ) = $args;

        // Get lessons for the course.
        $lessons = get_posts([
            'post_type'   => 'lesson',
            'post_status' => 'publish',
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

            // Check paragraphs, list items, and blockquotes.
            preg_match_all( '/<(p|li|blockquote)[^>]*>(.*?)<\/\1>/', $lesson_content, $matches, PREG_SET_ORDER );

            foreach ( $matches as $match ) {
                $text = trim( strip_tags( $match[2] ) );

                if ( ! preg_match( '/[.!?:]$/', $text ) ) {
                    $rows[] = [
                        'Text'      => $text,
                        'Edit Link' => admin_url( "post.php?post={$lesson->ID}&action=edit" ),
                    ];
                }
            }
        }

        if ( empty( $rows ) ) {
            WP_CLI::success( 'All paragraphs, list items, and blockquotes are properly punctuated.' );
            return;
        }

        // Display the results in a table.
        WP_CLI\Utils\format_items( 'table', $rows, [ 'Text', 'Edit Link' ] );
    }
}

// Register the commands with WP-CLI.
if ( class_exists( 'WP_CLI' ) ) {
    WP_CLI::add_command( 'vip-learn course-title-audit', [ new Command(), 'course_title_audit' ] );
    WP_CLI::add_command( 'vip-learn course-punctuation-audit', [ new Command(), 'course_punctuation_audit' ] );
}
