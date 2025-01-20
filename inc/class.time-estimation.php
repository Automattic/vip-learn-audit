<?php

namespace VIP\Learn\Audit;

class TimeEstimation {

    public function course_estimate(int $course_id ){

        $data = [];

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

        foreach($lessons as $lesson){

            $lesson_title     = $lesson->post_title;
            $lesson_content   = $lesson->post_content;

            // get word count
            $word_count = str_word_count(strip_tags($lesson_content));

            // Get completion times
            $comments = get_comments([
                'post_id' => $lesson->ID,
                'status' => 'complete', // Only approved comments
                'type' => 'sensei_lesson_status'
            ]);
            
            // get all comments for this post, where comment_approved = complete
            
            $completion_times = [];
            $comment_times = [];
            foreach ($comments as $comment) {
                
                $start_time = get_comment_meta($comment->comment_ID, 'start', true);
                $end_time = $comment->comment_date;

                $comment_times[] = [
                    'comment_id' => $comment->comment_ID,
                    //'comment' => $comment,
                    'start' => $start_time,
                    'end' => $end_time
                ];
    
                if ($start_time && $end_time) {
                    $start_timestamp = strtotime($start_time);
                    $end_timestamp = strtotime($end_time);
    
                    if ($end_timestamp > $start_timestamp) {
                        $completion_times[] = $end_timestamp - $start_timestamp;
                    }
                }
            }

            //print_r($completion_times);

            // calculate lesson titles
            $lesson_time = $this->calculate_lesson_times( $word_count, $completion_times );

            $data['lesson_data'][] = [
                'lesson_id' => $lesson->ID,
                'lesson_title' => $lesson_title,
                'lesson_time' => $lesson_time,
                'completion_times' => $completion_times,
                'comment_times' => $comment_times,
                'word_count' => $word_count,
                //'lesson_comments' => $comments
            ];
        }
        $data['total_estimate'] = 0;
        // get total time based on estimate
        foreach( $data['lesson_data'] as $item){
            $data['total_estimate'] += $item['lesson_time']['estimated_time'];
        }

        return $data;
    }

    private function calculate_lesson_times(int $word_count, array $completion_times): array {

        // Constants
        $words_per_minute = 225; // Average reading speed
        $min_valid_time = 30; // Minimum valid time in seconds
        $max_valid_time_hours = 2; // Maximum valid time in hours
        
        // Step 1: Calculate reading time
        $reading_time = ceil($word_count / $words_per_minute * 60); // Convert minutes to seconds

        // Step 2: Filter completion times to remove outliers
        $filtered_times = [];
        foreach ($completion_times as $time) {
            if ($time >= $min_valid_time && $time <= ($max_valid_time_hours * 3600)) {
                $filtered_times[] = $time;
            }
        }

        if (empty($filtered_times)) {
            return [
                "reading_time" => $reading_time,
                "max_time" => 0,
                "min_time" => 0,
                "estimated_time" => $reading_time,
            ];
        }

        // Step 3: Calculate statistical metrics
        sort($filtered_times);
       
        $min_time = min($filtered_times);
        $max_time = max($filtered_times);
        
        $median_time = $this->calculate_median($filtered_times);

        // Step 4: Calculate the estimated time
        // Weight: 80% behaviour-based (median), 20% content-based (reading time)
        $estimated_time = round(0.8 * $median_time + 0.2 * $reading_time);

        // Return the results
        return [
            "reading_time" => $reading_time,
            "max_time" => $max_time,
            "min_time" => $min_time,
            "estimated_time" => $estimated_time,
        ];
    }

    private function calculate_median(array $numbers): float {
        $count = count($numbers);
        $middle = floor($count / 2);

        if ($count % 2) {
            return $numbers[$middle];
        }

        return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
    }


}