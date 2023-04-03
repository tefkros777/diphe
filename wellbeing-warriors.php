<?php
/*
 * Plugin Name: Wellbeing Warriors Plugin
 * Description: Logic for the Wellbeing Warriors courses
 * Version: 1.0
 * Author: Constantinos Tefkros Loizou, SEIT Lab
 * Author URI: github.com/tefkros777
 */

function wellness_warriors_start(){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Always Start from the beginning. There is no progress monitoring in this course

    // Get course type (student or teacher)
    if ( isset( $_POST['course_id'] ) ){
        $course_id = $_POST['course_id'];
        $course_name = $course_id == '1' ? "Student Version" : "Teacher Version";
    } else {
        echo "Could not obtain course_id";
//        die();
//        $course_id = '1';
//        $course_name = "fakery";
    }

    // Read slide number from POST
    if ( isset($_POST['next_slide']) ){
        $slide_num = $_POST['next_slide'];
    } else {
        // If POST does not contain slide number, go to the first slide
        $slide_num = '1';
    }

    // Work out slide type
    $sql_slide_type = "SELECT slide_type FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num' ";
    $query_slide_type = mysqli_query($con, $sql_slide_type);
    $slide_type = mysqli_fetch_assoc($query_slide_type);

    // Include custom CSS file
    $src_css = plugin_dir_url(__FILE__) . 'css/diphe-style.css';
    echo "<link rel='stylesheet' type='text/css' href='$src_css'>";

    // Top margin and header
    echo "
    <div class='top-header'>
        <h1>Wellbeing Warriors - $course_name</h1>
    </div>
    ";

    switch ($slide_type['slide_type']){
        case '1':
            // Cover
            cover_slide($course_id, $slide_num);
            break;
        case '2':
            // Video
            video_slide($course_id, $slide_num);
            break;
        case '3':
            // Quiz
            quiz_slide($course_id, $slide_num);
            break;
        case '4':
            // Survey
            survey_slide($course_id, $slide_num);
            break;
        case '5':
            // Guidance
            guidance_slide($course_id, $slide_num);
            break;
        case '6':
            // Disclaimer
            disclaimer_slide($course_id, $slide_num);
            break;
        default:
            // Error
            echo "Unknown slide type ($slide_type[slide_type])";
            echo "<br>";
            echo "Query: $sql_slide_type";
            break;
    }
}
add_shortcode('wellness_warriors_start', 'wellness_warriors_start');

function cover_slide($course_id, $slide_num){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB: Header, Cover image
    $sql_slide_data = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    $header = $slide_data['header'];
    $cover_img = $slide_data['cover_image'];

    // Logos are hardcoded
    $logo_ikkaido  = "https://diphe.cs.ucy.ac.cy/wp-content/uploads/2021/12/ikkaido.jpg";
    $logo_insideeu = "https://diphe.cs.ucy.ac.cy/wp-content/uploads/2023/02/Inside-logo.jpg";
    $logo_ucy      = "https://diphe.cs.ucy.ac.cy/wp-content/uploads/2021/07/ucy.jpg";
    $logo_eupea    = "https://diphe.cs.ucy.ac.cy/wp-content/uploads/2021/07/eupea.jpg";
    $logo_erasmus  = "https://diphe.cs.ucy.ac.cy/wp-content/uploads/2023/03/erasmus_logo_disclaimer.png";

    // Create Cover Slide Rendering
    echo"
    <div class='outer-container'>
        <div class='course-screen'>
            <!-- Image -->
            <div class='img-row'>
                <img src='$cover_img' alt='Course front page image' >
            </div>
            <!-- Header/Title -->
            <div class='header-row'>
                <h1>$header</h1>
            </div>
            <!-- Logos -->
            <div class='logo-row'>
                <img src='$logo_ikkaido' alt='IKKAIDO Logo'>
                <img src='$logo_insideeu' alt='InsideEU Logo'>
                <img src='$logo_ucy' alt='University of Cyprus Logo'>
                <img src='$logo_eupea' alt='EUPEA Logo'>
                <img src='$logo_erasmus' alt='Erasmus+ Logo'>
            </div>
        </div>
    </div>
    ";

    // Open button bar
    echo "
    <div class='button-bar'>
        <button class='session-color-button' onclick='history.back()'>Back</input>
    ";

        // Find next slide of this course if it exists
        $next_slide_num = intval($slide_num) + 1; // The number of the next slide (might not exist)

        $sql_next_slide = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$next_slide_num' ";
        $result_next_slide = mysqli_query($con, $sql_next_slide);
        $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

        if ( $next_slide_data != NULL ){
            echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                <button class='session-color-button' type='submit'>Next</input>
                <input class='session-color-button' type='hidden' name='next_slide' value='$next_slide_num'/>
                <input class='session-color-button' type='hidden' name='course_id' value='$slide_data[course_id]'/>
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

    // If in Teacher Version, show booklet
    if ($slide_data['course_id'] == '2') {
        echo "
        <a class='center-a-tag' href='https://drive.google.com/file/d/1fvR6OsBeL8MMnk-LOnM-MaFtS4v4tY0t/view?usp=share_link'>
            Also check out the Teacher Booklet
        </a>
        ";
    }
}

function video_slide($course_id, $slide_num){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB: Session Num, Header (if exists) Video link
    $sql_slide_data = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    $session_num = $slide_data['session_num'];
    $header      = $slide_data['header'];
    $video_link  = $slide_data['video_survey_link'];

    $session_color = "808080";
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

    // Create Video Slide Rendering
    echo"
    <div class='outer-container'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_num'/>
                <div style='background-color: $session_1_color'>Session 1</div>
                <div style='background-color: $session_2_color'>Session 2</div>
                <div style='background-color: $session_3_color'>Session 3</div>
                <div style='background-color: $session_4_color'>Session 4</div>
                <div style='background-color: $session_5_color'>Session 5</div>
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
    $src = plugin_dir_url(__FILE__) . 'js/wellbeing_warriors_script.js';
    echo "<script type='text/javascript' src='$src'></script>";

    // Open button bar
    echo "
    <div class='button-bar'>
        <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' onclick='history.back()'>Back</input>
    ";

    // Find next slide of this course if it exists
    $next_slide_num = intval($slide_num) + 1; // The number of the next slide (might not exist)

    $sql_next_slide = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$next_slide_num' ";
    $result_next_slide = mysqli_query($con, $sql_next_slide);
    $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

    if ( $next_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' type='submit'>Next</input>
                <input type='hidden' name='next_slide' value='$next_slide_num'/>
                <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
            </form>";
    } else {
        // There is no next slide - End of course
        // HOME button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' type='submit'>Home</input>
            </form>
            ";
    }

    // Close button bar
    echo "
    </div>
    ";
}

function quiz_slide($course_id, $slide_num){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB: Video link
    $sql_slide_data = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    // Get from DB: Session, Header, Question, Reply
    $session_num   = $slide_data['session_num'];
    $header        = $slide_data['header'];
    $question_text = $slide_data['question_text'];
    $response_text = $slide_data['response_text'];

    $session_color = "808080";
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

    // Create Quiz Slide Rendering
    echo"
    <div class='outer-container' onload='setResponseBodySize()'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar' id='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_num'/>
                <div style='background-color: $session_1_color'>Session 1</div>
                <div style='background-color: $session_2_color'>Session 2</div>
                <div style='background-color: $session_3_color'>Session 3</div>
                <div style='background-color: $session_4_color'>Session 4</div>
                <div style='background-color: $session_5_color'>Session 5</div>
            </div>
            <!-- Header -->
            <div class='header-row' id='header-row'>
                <h1>$header</h1>
            </div>
            <!-- Question -->
            <div class='question-body' id='question-body'>
                <h2>$question_text</h2>
            </div>            
            <!-- Response -->
            <div class='response-body' id='response-body' hidden>
                <h4>$response_text</h4>
            </div>
        </div>
    </div>
    ";

    // Include external JS
    $src = plugin_dir_url(__FILE__) . 'js/wellbeing_warriors_script.js';
    echo "<script type='text/javascript' src='$src'></script>";

    // Open button bar
    echo "
    <div class='button-bar'>
        <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' onclick='history.back()'>Back</button>
        <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' onclick='notesButton()'>Notes</button>
        <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' id='btn_show' onclick='showAnswer()'>Reveal Answer</button>
    ";

    // Find next slide of this course if it exists
    $next_slide_num = intval($slide_num) + 1; // The number of the next slide (might not exist)

    $sql_next_slide = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$next_slide_num' ";
    $result_next_slide = mysqli_query($con, $sql_next_slide);
    $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

    if ( $next_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' type='submit'>Next</input>
                <input type='hidden' name='next_slide' value='$next_slide_num'/>
                <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
            </form>";
    } else {
        // There is no next slide - End of course
        // HOME button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' type='submit'>Home</input>
            </form>
            ";
    }

    // Close button bar
    echo "
    </div>
    ";

}

function survey_slide($course_id, $slide_num){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB: Video link
    $sql_slide_data = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    // Get from DB: Session, Header, Text, Link
    $session_num   = $slide_data['session_num'];
    $header        = $slide_data['header'];
    $text          = $slide_data['question_text'];
    $survey_link   = $slide_data['video_survey_link'];

    $session_color = "808080";
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

    // Create survey Slide Rendering
    echo"
    <div class='outer-container' onload='setResponseBodySize()'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar' id='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_num'/>
                <div style='background-color: $session_1_color'>Session 1</div>
                <div style='background-color: $session_2_color'>Session 2</div>
                <div style='background-color: $session_3_color'>Session 3</div>
                <div style='background-color: $session_4_color'>Session 4</div>
                <div style='background-color: $session_5_color'>Session 5</div>
            </div>
            <!-- Header -->
            <div class='header-row' id='header-row'>
                <h1>$header</h1>
            </div>
            <!-- Text -->
            <div class='question-body' id='question-body'>
                <h2>$text</h2>
            </div>            
            <!-- Link -->
            <div class='response-body' id='response-body'>
                <!-- Open in new tab -->
                <h4><a href='$survey_link' target='_blank'>$survey_link</a></h4>
            </div>
        </div>
    </div>
    ";

    // Include external JS
    $src = plugin_dir_url(__FILE__) . 'js/wellbeing_warriors_script.js';
    echo "<script type='text/javascript' src='$src'></script>";

    // Open button bar
    echo "
    <div class='button-bar'>
        <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' onclick='history.back()'>Back</button>
    ";

    // Find next slide of this course if it exists
    $next_slide_num = intval($slide_num) + 1; // The number of the next slide (might not exist)

    $sql_next_slide = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$next_slide_num' ";
    $result_next_slide = mysqli_query($con, $sql_next_slide);
    $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

    if ( $next_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' type='submit'>Next</input>
                <input type='hidden' name='next_slide' value='$next_slide_num'/>
                <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
            </form>";
    } else {
        // There is no next slide - End of course
        // HOME button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' type='submit'>Home</input>
            </form>
            ";
    }

    // Close button bar
    echo "
    </div>
    ";

}

function guidance_slide($course_id, $slide_num){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB: Video link
    $sql_slide_data = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    // Get from DB: Session, Header, Question
    $session_num   = $slide_data['session_num'];
    $header        = $slide_data['header'];
    $guidance_text = $slide_data['question_text'];

    $session_color = "808080";
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

    // Create guidance Slide Rendering
    echo"
    <div class='outer-container' onload='setResponseBodySize()'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar' id='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_num'/>
                <div style='background-color: $session_1_color'>Session 1</div>
                <div style='background-color: $session_2_color'>Session 2</div>
                <div style='background-color: $session_3_color'>Session 3</div>
                <div style='background-color: $session_4_color'>Session 4</div>
                <div style='background-color: $session_5_color'>Session 5</div>
            </div>
            <!-- Header -->
            <div class='guidance-header'>
                <h3>$header</h3>
            </div>       
            <!-- Guidance Text -->
            <div class='guidance-body' >
                <p>$guidance_text</p>
            </div>
        </div>
    </div>
    ";

    // Include external JS
    $src = plugin_dir_url(__FILE__) . 'js/wellbeing_warriors_script.js';
    echo "<script type='text/javascript' src='$src'></script>";

    // Open button bar
    echo "
    <div class='button-bar'>
        <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' onclick='history.back()'>Back</button>
    ";

    // Find next slide of this course if it exists
    $next_slide_num = intval($slide_num) + 1; // The number of the next slide (might not exist)

    $sql_next_slide = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$next_slide_num' ";
    $result_next_slide = mysqli_query($con, $sql_next_slide);
    $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

    if ( $next_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' type='submit'>Next</input>
                <input type='hidden' name='next_slide' value='$next_slide_num'/>
                <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
            </form>";
    } else {
        // There is no next slide - End of course
        // HOME button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' type='submit'>Home</input>
            </form>
            ";
    }

    // Close button bar
    echo "
    </div>
    ";
}

function disclaimer_slide($course_id, $slide_num){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB: Video link
    $sql_slide_data = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    // Get from DB: Header, Text
    $header = $slide_data['header'];
    $text   = $slide_data['question_text'];

    // Create disclaimer Slide Rendering
    echo"
    <div class='outer-container'>
        <div class='course-screen'>
            <!-- Header -->
            <div class='disclaimer-header'>
                <h2>$header</h2>
            </div>       
            <hr>
            <!-- Disclaimer Text -->
            <div class='disclaimer-body'>
                <p>$text</p>
            </div>
        </div>
    </div>
    ";

    // Include external JS
    $src = plugin_dir_url(__FILE__) . 'js/wellbeing_warriors_script.js';
    echo "<script type='text/javascript' src='$src'></script>";

    // Open button bar
    echo "
    <div class='button-bar'>
        <button class='session-color-button' onclick='history.back()'>Back</button>
    ";

    // Find next slide of this course if it exists
    $next_slide_num = intval($slide_num) + 1; // The number of the next slide (might not exist)

    $sql_next_slide = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$next_slide_num' ";
    $result_next_slide = mysqli_query($con, $sql_next_slide);
    $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

    if ( $next_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                <button class='session-color-button' type='submit'>Next</input>
                <input type='hidden' name='next_slide' value='$next_slide_num'/>
                <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
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

function show_map(){

}