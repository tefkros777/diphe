<?php
/*
 * Plugin Name: i-Ninja Course Plugin
 * Description: Logic for the i-Ninja courses
 * Version: 1.0
 * Author: Constantinos Tefkros Loizou, SEIT Lab
 * Author URI: github.com/tefkros777
 */

function i_ninja_start(){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Include custom CSS file
    $src_css = plugin_dir_url(__FILE__) . 'css/diphe-style.css';
    echo "<link rel='stylesheet' type='text/css' href='$src_css'>";

    // Include external JS
    $src = plugin_dir_url(__FILE__) . 'js/i-ninja_script.js';
    echo "<script type='text/javascript' src='$src'></script>";

    // Get course tier
    if ( isset( $_POST['course_id'] ) ){
        $course_id   = $_POST['course_id'];
        if ($course_id == 3) $course_tier = "Tier 1";
        if ($course_id == 4) $course_tier = "Tier 2";
    } else {
        echo "Could not obtain course_id";
        // Show home button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/' method='post'>
                <button class='session-color-button' type='submit'>Home</button>
            </form>
        ";
        die();
    }

    // Top margin and header
    echo "
    <div class='top-header'>
        <h1>i-Ninja - $course_tier</h1>
    </div>";


    /*
     * If we arrive here from another slide, look at next_slide (also holds value for prev_slide).
     * If we arrive here from select_course, look at user progress table
     */
    if ( isset( $_POST['next_slide'] ) ){
        // Load next slide of this course
        $slide_to_load = $_POST['next_slide'];
    } else {
        // Get last slide for this user

        // MUST GIVE 'slide_number_in_course' and not slide_id

        $user    = wp_get_current_user();
        $user_id = $user->id;
        $sql_last_slide   = "SELECT last_slide_id FROM eplatform_USER_PROGRESS WHERE user_id = '$user_id' AND course_id = '$course_id' ";
        $query_last_slide = mysqli_query($con, $sql_last_slide);
        $last_slide_id    = mysqli_fetch_assoc($query_last_slide)['last_slide_id'];

        // Now that we know the slide id, find the number of this slide in the current course
        $sql_slide_num_in_course   = "SELECT slide_number_in_course FROM eplatform_ALL_SLIDES WHERE slide_id = '$last_slide_id' ";
        $query_slide_num_in_course = mysqli_query($con, $sql_slide_num_in_course);
        $slide_num_in_course       = mysqli_fetch_assoc($query_slide_num_in_course)['slide_number_in_course'];

        // If there is no user progress record, start from the first slide of the course
        if ($last_slide_id == null) {
            if ($course_id == 3) $slide_to_load = '1';
            if ($course_id == 4) $slide_to_load = '1';
        } else {
            // THE BUG IS HERE
            $slide_to_load = $slide_num_in_course;
        }
    }

    // Find slide type
    $sql_slide_type = "SELECT slide_type FROM eplatform_ALL_SLIDES WHERE course_id = '$course_id' AND slide_number_in_course = '$slide_to_load' ";
    $query_slide_type = mysqli_query($con, $sql_slide_type);
    $slide_type = mysqli_fetch_assoc($query_slide_type);

    // Call respective slide template
    switch ($slide_type['slide_type']){
        case '2':
            // Cover
            ninja_video_slide($course_id, $slide_to_load);
            break;
        case '7':
            // Video
            assignment_slide($course_id, $slide_to_load);
            break;
        case '8':
            // Quiz
            submit_assignment_slide($course_id, $slide_to_load);
            break;
        default:
            // Error
            echo "Unknown slide type for this course ($slide_type[slide_type]) <br>";
            echo "Query was: $sql_slide_type";
            break;
    }

}
add_shortcode('i_ninja_start', 'i_ninja_start');

function ninja_video_slide($course_id, $slide_to_load){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get Slide Metadata
    $sql_slide_metadata = "SELECT * FROM eplatform_ALL_SLIDES WHERE course_id = '$course_id' AND slide_number_in_course = '$slide_to_load'";
    $result_slide_metadata = mysqli_query($con,$sql_slide_metadata);
    $slide_metadata = mysqli_fetch_assoc($result_slide_metadata);

    $session_id    = $slide_metadata['session_id'];
    $this_slide_id = $slide_metadata['slide_id'];

    // Get video slide data (content)
    $sql_slide_data = "SELECT * FROM eplatform_VIDEO_SLIDES WHERE slide_id = '$this_slide_id'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    $header      = $slide_data['header'];
    $video_link  = $slide_data['video_link'];

    // Find session colors
    $session_colors  = get_session_colors_ninja($session_id);
    $session_1_color = $session_colors['session_1_color'];
    $session_2_color = $session_colors['session_2_color'];
    $session_3_color = $session_colors['session_3_color'];
    $session_4_color = $session_colors['session_4_color'];
    $session_5_color = $session_colors['session_5_color'];
    $session_color   = $session_colors['session_color'];

    // Find first slide for every session
    $s1 = find_first_slide_of_session_ninja($course_id, '1');
    $s2 = find_first_slide_of_session_ninja($course_id, '2');
    $s3 = find_first_slide_of_session_ninja($course_id, '3');
    $s4 = find_first_slide_of_session_ninja($course_id, '4');
    $s5 = find_first_slide_of_session_ninja($course_id, '5');

    // Create Video Slide Rendering
    echo"
    <div class='outer-container'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_id'/>
                <div id='1'style='background-color: $session_1_color' onclick='sessionClick(this)'>
                    <form id='F1' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s1'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                    
                    Way of Martial Arts
                </div>
                <div id='2' style='background-color: $session_2_color' onclick='sessionClick(this)'>
                    <form id='F2' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s2'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Roles of an I-Ninja Coach
                </div>
                <div id='3' style='background-color: $session_3_color' onclick='sessionClick(this)'>
                    <form id='F3' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s3'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Responsibilities of an I-Ninja Coach
                </div>
                <div id='4' style='background-color: $session_4_color' onclick='sessionClick(this)'>
                    <form id='F4' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s4'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Health and Wellbeing
                </div>
                <div id='5' style='background-color: $session_5_color' onclick='sessionClick(this)'>
                    <form id='F5' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s5'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Inclusive Martial Arts
                </div>
            </div>
            <!-- Header -->
            <div class='video-title' id='video_title_div'>
                <h3 id='video_title_text'>$header</h3>
            </div>
            <!-- Video -->
            <iframe
                class='video-iframe'
                id='video_iframe'
                src='$video_link'
                title='YouTube video player'
                allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
                allowfullscreen>
            </iframe>
        </div>
    </div>
    ";

    // Open button bar
    echo "<div class='button-bar'>";

    ininja_back_button($slide_metadata['slide_id'], $session_color);

    ininja_next_or_home_button($slide_metadata['slide_id'], $session_color);

    // Close button bar
    echo "</div>";

    ininja_save_user_progress($this_slide_id);
}

function assignment_slide($course_id, $slide_to_load){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get Slide Metadata
    $sql_slide_metadata = "SELECT * FROM eplatform_ALL_SLIDES WHERE course_id = '$course_id' AND slide_number_in_course = '$slide_to_load'";
    $result_slide_metadata = mysqli_query($con,$sql_slide_metadata);
    $slide_metadata = mysqli_fetch_assoc($result_slide_metadata);

    $session_id    = $slide_metadata['session_id'];
    $this_slide_id = $slide_metadata['slide_id'];

    // Get assignment slide data (content)
    $sql_slide_data = "SELECT * FROM eplatform_ASSIGNMENT_SLIDES WHERE slide_id = '$this_slide_id'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    $header         = $slide_data['header'];
    $body           = $slide_data['body'];
    $research_link  = $slide_data['research_link'];

    // Find session colors
    $session_colors  = get_session_colors_ninja($session_id);
    $session_1_color = $session_colors['session_1_color'];
    $session_2_color = $session_colors['session_2_color'];
    $session_3_color = $session_colors['session_3_color'];
    $session_4_color = $session_colors['session_4_color'];
    $session_5_color = $session_colors['session_5_color'];
    $session_color   = $session_colors['session_color'];

    // Find first slide for every session
    $s1 = find_first_slide_of_session_ninja($course_id, '1');
    $s2 = find_first_slide_of_session_ninja($course_id, '2');
    $s3 = find_first_slide_of_session_ninja($course_id, '3');
    $s4 = find_first_slide_of_session_ninja($course_id, '4');
    $s5 = find_first_slide_of_session_ninja($course_id, '5');

    // Create Assignment Slide Rendering
    echo"
    <div class='outer-container'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_id'/>
                <div id='1'style='background-color: $session_1_color' onclick='sessionClick(this)'>
                    <form id='F1' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s1'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                    
                    Way of Martial Arts
                </div>
                <div id='2' style='background-color: $session_2_color' onclick='sessionClick(this)'>
                    <form id='F2' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s2'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Roles of an I-Ninja Coach
                </div>
                <div id='3' style='background-color: $session_3_color' onclick='sessionClick(this)'>
                    <form id='F3' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s3'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Responsibilities of an I-Ninja Coach
                </div>
                <div id='4' style='background-color: $session_4_color' onclick='sessionClick(this)'>
                    <form id='F4' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s4'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Health and Wellbeing
                </div>
                <div id='5' style='background-color: $session_5_color' onclick='sessionClick(this)'>
                    <form id='F5' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s5'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Inclusive Martial Arts
                </div>
            </div>
            <!-- Header -->
            <div class='assignment-header'>
                <h3>$header</h3>
            </div>
            <!-- Body -->
            <div class='assignment_body' id='assignment_body'>
                $body
            </div>
        </div>
    </div>
    ";

    // Open button bar
    echo "<div class='button-bar'>";

    ininja_back_button($slide_metadata['slide_id'], $session_color);

    ininja_next_or_home_button($slide_metadata['slide_id'], $session_color);

    // Close button bar
    echo "</div>";

    ininja_save_user_progress($this_slide_id);
}

function submit_assignment_slide($course_id, $slide_to_load){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get Slide Metadata
    $sql_slide_metadata = "SELECT * FROM eplatform_ALL_SLIDES WHERE course_id = '$course_id' AND slide_number_in_course = '$slide_to_load'";
    $result_slide_metadata = mysqli_query($con,$sql_slide_metadata);
    $slide_metadata = mysqli_fetch_assoc($result_slide_metadata);

    $session_id    = $slide_metadata['session_id'];
    $this_slide_id = $slide_metadata['slide_id'];

    // Get submit assignment slide data (content)
    $sql_slide_data = "SELECT * FROM eplatform_SUBMIT_ASSIGNMENT_SLIDES WHERE slide_id = '$this_slide_id'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    $header     = $slide_data['header'];
    $body       = $slide_data['body'];
    $is_complex = $slide_data['is_complex'];

    // Find session colors
    $session_colors  = get_session_colors_ninja($session_id);
    $session_1_color = $session_colors['session_1_color'];
    $session_2_color = $session_colors['session_2_color'];
    $session_3_color = $session_colors['session_3_color'];
    $session_4_color = $session_colors['session_4_color'];
    $session_5_color = $session_colors['session_5_color'];
    $session_color   = $session_colors['session_color'];

    // Find first slide for every session
    $s1 = find_first_slide_of_session_ninja($course_id, '1');
    $s2 = find_first_slide_of_session_ninja($course_id, '2');
    $s3 = find_first_slide_of_session_ninja($course_id, '3');
    $s4 = find_first_slide_of_session_ninja($course_id, '4');
    $s5 = find_first_slide_of_session_ninja($course_id, '5');

    // Create Submit Assignment Slide Rendering
    echo"
    <div class='outer-container'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_id'/>
                <div id='1'style='background-color: $session_1_color' onclick='sessionClick(this)'>
                    <form id='F1' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s1'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                    
                    Way of Martial Arts
                </div>
                <div id='2' style='background-color: $session_2_color' onclick='sessionClick(this)'>
                    <form id='F2' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s2'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Roles of an I-Ninja Coach
                </div>
                <div id='3' style='background-color: $session_3_color' onclick='sessionClick(this)'>
                    <form id='F3' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s3'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Responsibilities of an I-Ninja Coach
                </div>
                <div id='4' style='background-color: $session_4_color' onclick='sessionClick(this)'>
                    <form id='F4' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s4'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Health and Wellbeing
                </div>
                <div id='5' style='background-color: $session_5_color' onclick='sessionClick(this)'>
                    <form id='F5' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                        <input type='hidden' name='next_slide' value='$s5'/>
                        <input type='hidden' name='course_id' value='$course_id'/>
                    </form>                
                    Inclusive Martial Arts
                </div>
            </div>
            <!-- Header -->
            <div class='assignment-header' id='header-row'>
                <h3>$header</h3>
            </div>
            <!-- Body -->
            <div class='question-body' id='body-row'>
                <h3>$body</h3>
            </div>";
        // If this is a simple text input slide
        if ($is_complex != '1'){
            echo "
            <!-- Response Textarea -->
            <div class='assignment-response' id='response-area-div'>
                <textarea name='response' placeholder='Enter your final answer here...'></textarea>
            </div>
            ";
        }
        echo"
        </div>
    </div>
    ";

    // Open button bar
    echo "<div class='button-bar'>";

    ininja_back_button($slide_metadata['slide_id'], $session_color);

    ininja_next_or_home_button($slide_metadata['slide_id'], $session_color);

    // Close button bar
    echo "</div>";

    ininja_save_user_progress($this_slide_id);
}

function find_first_slide_of_session_ninja($course_id, $session_id){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB: Session Num, Header (if exists) Video link
    $sql_first_slide_in_session = "SELECT slide_number_in_course FROM eplatform_ALL_SLIDES WHERE course_id = '$course_id' AND session_id = '$session_id' ORDER BY slide_number_in_course LIMIT 1";
    $result_first_slide_in_session = mysqli_query($con,$sql_first_slide_in_session);
    $slide_first_slide_in_session = mysqli_fetch_assoc($result_first_slide_in_session);
    return $slide_first_slide_in_session['slide_number_in_course'];

}

function get_session_colors_ninja($session_num): array {
    switch ($session_num){
        case '1':
            $session_1_color = "#A30100";
            $session_2_color = "#808080";
            $session_3_color = "#808080";
            $session_4_color = "#808080";
            $session_5_color = "#808080";
            $session_color   = "#A30100";
            break;
        case '2':
            $session_1_color = "#A30100";
            $session_2_color = "#1983E7";
            $session_3_color = "#808080";
            $session_4_color = "#808080";
            $session_5_color = "#808080";
            $session_color   = "#1983E7";
            break;
        case '3':
            $session_1_color = "#A30100";
            $session_2_color = "#1983E7";
            $session_3_color = "#702EA0";
            $session_4_color = "#808080";
            $session_5_color = "#808080";
            $session_color   = "#702EA0";
            break;
        case '4':
            $session_1_color = "#A30100";
            $session_2_color = "#1983E7";
            $session_3_color = "#702EA0";
            $session_4_color = "#1A7B00";
            $session_5_color = "#808080";
            $session_color   = "#1A7B00";
            break;
        case '5':
            $session_1_color = "#A30100";
            $session_2_color = "#1983E7";
            $session_3_color = "#702EA0";
            $session_4_color = "#1A7B00";
            $session_5_color = "#F18601";
            $session_color   = "#F18601";
            break;
    }
    $result['session_1_color'] = $session_1_color;
    $result['session_2_color'] = $session_2_color;
    $result['session_3_color'] = $session_3_color;
    $result['session_4_color'] = $session_4_color;
    $result['session_5_color'] = $session_5_color;
    $result['session_color']   = $session_color;
    return $result;
}

function ininja_back_button($slide_id, $session_color){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get Slide info from DB
    $sql_slide_info = "SELECT * FROM eplatform_ALL_SLIDES WHERE slide_id = '$slide_id'";
    $result_slide_info = mysqli_query($con, $sql_slide_info);
    $slide_info = mysqli_fetch_assoc($result_slide_info);

    // Compute previous slide number if it exists
    $prev_slide_num = intval($slide_info['slide_number_in_course']) - 1;
    $course_id = $slide_info['course_id'];

    // Attempt to query previous slide number from DB
    $sql_prev_slide = "SELECT * FROM eplatform_ALL_SLIDES WHERE course_id = '$course_id' AND slide_number_in_course = '$prev_slide_num' ";
    $result_prev_slide = mysqli_query($con, $sql_prev_slide);
    $prev_slide_data   = mysqli_fetch_assoc($result_prev_slide);

    // Check if query was successful
    if ( $prev_slide_data != NULL ){
        // Print back button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: solid 1px $session_color;' type='submit'>Back</input>
                <input class='session-color-button' type='hidden' name='next_slide' value='$prev_slide_num'/>
                <input class='session-color-button' type='hidden' name='course_id' value='$course_id'/>
            </form>";
    } else {
        // Previous slide doesn't exist - we must be at the beginning of the course
        // Button is disabled
        echo "<button class='disabled-button' disabled>Back</button>";
    }
}

function ininja_next_or_home_button($slide_id, $session_color){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get Slide info from DB
    $sql_slide_info = "SELECT * FROM eplatform_ALL_SLIDES WHERE slide_id = '$slide_id'";
    $result_slide_info = mysqli_query($con, $sql_slide_info);
    $slide_info = mysqli_fetch_assoc($result_slide_info);

    // Find next slide of this course if it exists
    $next_slide_num = intval($slide_info['slide_number_in_course']) + 1;
    $course_id = $slide_info['course_id'];

    $sql_next_slide    = "SELECT * FROM eplatform_ALL_SLIDES WHERE course_id = '$course_id' AND slide_number_in_course = '$next_slide_num' ";
    $result_next_slide = mysqli_query($con, $sql_next_slide);
    $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

    // Check if query was successful
    if ( $next_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: solid 1px $session_color;' type='submit'>Next</input>
                <input class='session-color-button' type='hidden' name='next_slide' value='$next_slide_num'/>
                <input class='session-color-button' type='hidden' name='course_id' value='$course_id'/>
            </form>";
    } else {
        // There is no next slide - we must be at the end of the course
        // Home button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: solid 1px $session_color;' type='submit'>Home</button>
            </form>
        ";
    }
}

function ininja_save_user_progress($slide_id){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get current user
    $user = wp_get_current_user();
    $id = $user->id;

    // Get current slide info from DB
    $sql_slide_info = "SELECT * FROM eplatform_ALL_SLIDES WHERE slide_id = '$slide_id'";
    $result_slide_info = mysqli_query($con, $sql_slide_info);
    $slide_info = mysqli_fetch_object($result_slide_info);

    // Check if user has any existing progress for this course
    $sql_user_has_progress = "SELECT * FROM eplatform_USER_PROGRESS WHERE user_id = '$id' AND course_id = '$slide_info->course_id'";
    $query_user_has_progress = mysqli_query($con, $sql_user_has_progress);
    $result_user_has_progress = mysqli_fetch_object($query_user_has_progress);

    // If user has no progress record on this course, create one
    if ($result_user_has_progress == null){
        $sql_create_progress_record = "INSERT INTO eplatform_USER_PROGRESS (user_id, course_id, last_slide_id) VALUES ('$id', '$slide_info->course_id', '$slide_info->slide_id')";
        return mysqli_query($con, $sql_create_progress_record);
    }

    // Update slide left at for this course
    $sql_update_user_progress = "UPDATE eplatform_USER_PROGRESS SET last_slide_id = '$slide_info->slide_id' WHERE user_id = '$id' AND course_id = '$slide_info->course_id' ";
    return mysqli_query($con, $sql_update_user_progress);
}