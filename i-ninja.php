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

    // Get logged in user
    $user = wp_get_current_user();
    $user_id = $user->id;

    // Get user notes
    $sql_user_notes    = "SELECT * FROM eplatform_iN_USER_NOTES WHERE user_id = '$user_id' AND slide_id = '$slide_metadata[slide_id]' ";
    $query_user_notes  = mysqli_query($con, $sql_user_notes);
    $result_user_notes = mysqli_fetch_object($query_user_notes);

    // If user has no notes on this slide, create entry
    if ($result_user_notes == null){
        $sql_create_notes_entry = "INSERT INTO eplatform_iN_USER_NOTES (user_id, slide_id, note_body) VALUES ('$user_id', '$slide_metadata[slide_id]', NULL)";
        mysqli_query($con, $sql_create_notes_entry);
        $notes = "Type your notes here...";
    } else {
        $notes = $result_user_notes->note_body;
    }

    // Notes area (hidden by default)
    ininja_notes_area($user_id, $slide_metadata[slide_id], $notes, $session_color);

    // Open button bar
    echo "<div class='button-bar'>";

    ininja_back_button($slide_metadata['slide_id'], $session_color);

    // I think video slides have no research button
//    if ( $research_link != null )
//        ininja_research_button($research_link, $session_color);

    ininja_notes_button($slide_metadata['slide_id'], $session_color);

    ininja_chat_button($session_color);

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

    // Get logged in user
    $user = wp_get_current_user();
    $user_id = $user->id;

    // Get user notes
    $sql_user_notes    = "SELECT * FROM eplatform_iN_USER_NOTES WHERE user_id = '$user_id' AND slide_id = '$slide_metadata[slide_id]' ";
    $query_user_notes  = mysqli_query($con, $sql_user_notes);
    $result_user_notes = mysqli_fetch_object($query_user_notes);

    // If user has no notes on this slide, create entry
    if ($result_user_notes == null){
        $sql_create_notes_entry = "INSERT INTO eplatform_iN_USER_NOTES (user_id, slide_id, note_body) VALUES ('$user_id', '$slide_metadata[slide_id]', NULL)";
        mysqli_query($con, $sql_create_notes_entry);
        $notes = "Type your notes here...";
    } else {
        $notes = $result_user_notes->note_body;
    }

    // Notes area (hidden by default)
    ininja_notes_area($user_id, $slide_metadata[slide_id], $notes, $session_color);

    // Open button bar
    echo "<div class='button-bar'>";

    ininja_back_button($slide_metadata['slide_id'], $session_color);

    if ( $research_link != null )
        ininja_research_button($research_link, $session_color);

    ininja_notes_button($slide_metadata['slide_id'], $session_color);

    ininja_chat_button($session_color);

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
            if ($is_complex == '0'){
                echo "
                <!-- Response Textarea -->
                <div class='assignment-response' id='response-area-div'>
                    <textarea name='response' placeholder='Enter your final answer here...'></textarea>
                </div>
                ";
            }
            // Else, there is a table to fill
            else {
            }
        echo"
        </div>
    </div>
    ";

    // Open button bar
    echo "<div class='button-bar'>";

    ininja_back_button($slide_metadata['slide_id'], $session_color);

    if ( $is_complex == 0)
        ininja_next_or_home_button($slide_metadata['slide_id'], $session_color);
    else {
        // A button that will submit the form and then proceed to the next slide

        // Get Slide info from DB
        $sql_slide_info = "SELECT * FROM eplatform_ALL_SLIDES WHERE slide_id = '$slide_metadata[slide_id]'";
        $result_slide_info = mysqli_query($con, $sql_slide_info);
        $slide_info = mysqli_fetch_assoc($result_slide_info);

        // Find next slide of this course (in submit assignment slides it always exists)
        $next_slide_num = intval($slide_info['slide_number_in_course']) + 1;
        $course_id = $slide_info['course_id'];

        // Get current user
        $user = wp_get_current_user();
        $user_id = $user->id;

        echo "
            <button class='session-color-button' style='background-color: $session_color; border: solid 1px $session_color;' type='button' onclick='submitTable(`$next_slide_num`, `$course_id`, `$user_id`);'>Next</button>
            <script>
                // submit table above 
                // need to pass user ID too
                function submitTable(next_slide_num, course_id, user_id){
                    let form = document.getElementById('answers_table_form');
                    // Append next_next_slide_num and user_id to form
                    let next_slide   = document.createElement('input');
                    let u_id         = document.createElement('input');
                    let c_id         = document.createElement('input');
                    u_id.type  = 'hidden';
                    u_id.name  = 'user_id';
                    u_id.value =  user_id;
                    next_slide.type  = 'hidden';
                    next_slide.name  = 'next_slide_num';
                    next_slide.value =  next_slide_num;                    
                    c_id.type  = 'hidden';
                    c_id.name  = 'course_id';
                    c_id.value =  course_id;
                    form.appendChild(next_slide);
                    form.appendChild(u_id);
                    form.appendChild(c_id);
                    form.submit();
                }
            </script>
        ";

    }


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

function ininja_research_button($research_link, $session_color){
    echo "
        <button 
            class='session-color-button' 
            style='background-color: $session_color; border: 2px solid $session_color;' 
            onclick='window.open(`$research_link`,`_blank`)'>
            Research
        </button>
        ";
}

function ininja_notes_button($slide_id, $session_color){
    echo "
        <button 
            class='session-color-button' 
            style='background-color: $session_color; border: 2px solid $session_color;' 
            onclick='showNotes();'>
            Notes
        </button>
        ";
}

function ininja_chat_button($session_color){
    echo "
        <button 
            class='session-color-button' 
            style='background-color: $session_color; border: 2px solid $session_color;' 
            onclick='chat();'>
            Chat to learners
        </button>
        ";
}

function ininja_notes_area($user_id, $slide_id, $notes, $session_color){
    echo "
    <script src='https://code.jquery.com/jquery-3.6.0.min.js' defer></script>
    <div class='notes-area' id='notes_div' hidden> 
        <label for='slide_notes' class='form-label'>Slide notes</label>
        <textarea class='form-control' id='slide_notes' rows='3' placeholder='Type your notes here...'>$notes</textarea>
        <button class='session-color-button' 
            type='button' 
            style='background-color: $session_color; border: solid 1px $session_color; width: 100%;' 
            onclick='saveNotes(`$user_id`, `$slide_id`);'>
                Save notes
        </button>
    </div>
    ";
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