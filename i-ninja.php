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

    // Get course tier
    if ( isset( $_POST['course_id'] ) ){
        $course_id   = $_POST['course_id'];
        if ($course_id == 3) $course_tier = "Tier 1";
        if ($course_id == 4) $course_tier = "Tier 2";
    } else {
        echo "Could not obtain course_id";
    }

    // Top margin and header
    echo "
    <div class='top-header'>
        <h1>i-Ninja - $course_tier</h1>
    </div>";


    /*
     * If we arrive here from another slide, look at next_slide.
     * If we arrive here from select_course, look at user progress table
     */
    if ( isset( $_POST['next_slide'] ) && isset( $_POST['last_slide_for_user'] ) ){
        echo "We ended up in a state where we received both a next slide number and the last slide of this user. How is this possible?";
        die();
    } else if ( isset( $_POST['next_slide'] ) ){
        // Load next slide of this course
        $slide_to_load = $_POST['next_slide'];
    } else if ( isset( $_POST['last_slide_for_user'] ) ) {
        // Get last slide for this user
        $slide_to_load = $_POST['last_slide_for_user'];
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

    // Create Video Slide Rendering
    echo"

    <div class='outer-container'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_id'/>
                <div id='div_session_1'>[DEMO] Session 1</div>
                <div id='div_session_2'>[DEMO] Session 2</div>
                <div id='div_session_3'>[DEMO] Session 3</div>
                <div id='div_session_4'>[DEMO] Session 4</div>
                <div id='div_session_5'>[DEMO] Session 5</div>
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

    // Include external JS
    $src = plugin_dir_url(__FILE__) . 'js/i-ninja_script.js';
    echo "<script type='text/javascript' src='$src'></script>";

    // Open button bar
    echo "
    <div class='button-bar'>
        <button class='session-color-button' onclick='history.back()'>Back</input>
    ";

    // Find next slide of this course if it exists
    $next_slide_num = intval($slide_metadata['slide_number_in_course']) + 1; // The number of the next slide (might not exist)

    $sql_next_slide = "SELECT * FROM eplatform_ALL_SLIDES WHERE course_id = '$course_id' AND slide_number_in_course = '$next_slide_num' ";
    $result_next_slide = mysqli_query($con, $sql_next_slide);
    $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

    if ( $next_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                <button class='session-color-button' type='submit'>Next</input>
                <input type='hidden' name='next_slide' value='$next_slide_num'/>
                <input type='hidden' name='course_id' value='$course_id'/>
            </form>";
    } else {
        // There is no next slide - End of course
        // HOME button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/' method='post'>
                <button class='session-color-button' type='submit'>Home</input>
            </form>
            ";
    }

    // Close button bar
    echo "
    </div>
    ";
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

    // Create Video Slide Rendering
    echo"

    <div class='outer-container'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_id'/>
                <div id='div_session_1'>[DEMO] Session 1</div>
                <div id='div_session_2'>[DEMO] Session 2</div>
                <div id='div_session_3'>[DEMO] Session 3</div>
                <div id='div_session_4'>[DEMO] Session 4</div>
                <div id='div_session_5'>[DEMO] Session 5</div>
            </div>
            <!-- Header -->
            <div class='video-title' id='video_title_div'>
                <h3 id='video_title_text'>$header</h3>
            </div>
            <!-- Body -->
            <div class='assignment_body' id='assignment_body'>
                $body
            </div>
        </div>
    </div>
    ";

    // Include external JS
    $src = plugin_dir_url(__FILE__) . 'js/i-ninja_script.js';
    echo "<script type='text/javascript' src='$src'></script>";

    // Open button bar
    echo "
    <div class='button-bar'>
        <button class='session-color-button' onclick='history.back()'>Back</input>
    ";

    // Find next slide of this course if it exists
    $next_slide_num = intval($slide_metadata['slide_number_in_course']) + 1; // The number of the next slide (might not exist)

    $sql_next_slide = "SELECT * FROM eplatform_ALL_SLIDES WHERE course_id = '$course_id' AND slide_number_in_course = '$next_slide_num' ";
    $result_next_slide = mysqli_query($con, $sql_next_slide);
    $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

    if ( $next_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
                <button class='session-color-button' type='submit'>Next</input>
                <input type='hidden' name='next_slide' value='$next_slide_num'/>
                <input type='hidden' name='course_id' value='$course_id'/>
            </form>";
    } else {
        // There is no next slide - End of course
        // HOME button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/' method='post'>
                <button class='session-color-button' type='submit'>Home</input>
            </form>
            ";
    }

    // Close button bar
    echo "
    </div>
    ";
}

function submit_assignment_slide($course_id, $slide_to_load){

}


