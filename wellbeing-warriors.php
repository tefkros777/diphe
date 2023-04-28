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


    // Get course type (student or teacher)
    if ( isset( $_POST['course_id'] ) ){
        $course_id = $_POST['course_id'];
        $course_name = $course_id == '1' ? "Student Version" : "Teacher Version";
    } else {
        echo "Could not obtain course_id";
    }

    // Get last slide for this user
    $user    = wp_get_current_user();
    $user_id = $user->id;
    $sql_last_slide   = "SELECT slide_num FROM eplatform_WW_USER_PROGRESS WHERE user_id = '$user_id' AND course_id = '$course_id' ";
    $query_last_slide = mysqli_query($con, $sql_last_slide);
    $last_slide       =  mysqli_fetch_assoc($query_last_slide)['slide_num'];

    // If there is no last slide, start from the beginning
    if ($last_slide == null) $slide_num = '1';
    else $slide_num = $last_slide;

    // Read slide number from POST
    if ( isset($_POST['next_slide']) ) $slide_num = $_POST['next_slide'];

    // Work out slide type
    $sql_slide_type = "SELECT slide_type FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num' ";
    $query_slide_type = mysqli_query($con, $sql_slide_type);
    $slide_type = mysqli_fetch_assoc($query_slide_type);

    // Include custom CSS file
    $src_css = plugin_dir_url(__FILE__) . 'css/diphe-style.css';
    echo "<link rel='stylesheet' type='text/css' href='$src_css'>";

    // Include external JS
    $src = plugin_dir_url(__FILE__) . 'js/wellbeing_warriors_script.js';
    echo "<script type='text/javascript' src='$src'></script>";

    // Top margin and header
    echo "
    <div class='top-header'>
        <h1>Well-Being Warriors - $course_name</h1>
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
        case '9':
            // Image help
            img_help_slide($course_id, $slide_num);
            break;
        default:
            // Error
//            echo "Unknown slide type ($slide_type[slide_type])";
//            echo "<br>";
//            echo "Query: $sql_slide_type";
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

    // No session = default color
    $session_color = "#61A93F";

    // Open button bar
    echo "<div class='button-bar'>";

    // Back button
    back_button($course_id, $slide_num, $session_color);

    // Next button
    next_or_home_button($course_id, $slide_num, $session_color);

    // Close button bar
    echo "</div>";

    // If in Teacher Version, show booklet
    if ($slide_data['course_id'] == '2') {
        echo "
        <a class='center-a-tag' href='https://drive.google.com/file/d/1fvR6OsBeL8MMnk-LOnM-MaFtS4v4tY0t/view?usp=share_link'>
            Also check out the Teacher Booklet
        </a>
        ";
    }

    // Save progress before finishing
    save_user_progress($course_id, $slide_num);
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

    // Find session colors
    $session_colors  = get_session_colors($session_num);
    $session_1_color = $session_colors['session_1_color'];
    $session_2_color = $session_colors['session_2_color'];
    $session_3_color = $session_colors['session_3_color'];
    $session_4_color = $session_colors['session_4_color'];
    $session_5_color = $session_colors['session_5_color'];
    $session_color   = $session_colors['session_color'];

    // Find first slide for every session
    $s1 = find_first_slide_of_session($course_id, '1');
    $s2 = find_first_slide_of_session($course_id, '2');
    $s3 = find_first_slide_of_session($course_id, '3');
    $s4 = find_first_slide_of_session($course_id, '4');
    $s5 = find_first_slide_of_session($course_id, '5');

    // Create Video Slide Rendering
    echo "
    <div class='outer-container' xmlns=\"http://www.w3.org/1999/html\">
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_num'/>
                <div id='1' style='background-color: $session_1_color' onclick='sessionClick(this)'>
                    <form id='F1' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s1'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 1
                </div>
                <div id='2' style='background-color: $session_2_color' onclick='sessionClick(this)'>
                    <form id='F2' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s2'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 2
                    </div>
                <div id='3' style='background-color: $session_3_color' onclick='sessionClick(this)'>
                    <form id='F3' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s3'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 3
                </div>
                <div id='4' style='background-color: $session_4_color' onclick='sessionClick(this)'>
                    <form id='F4' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s4'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 4
                </div>
                <div id='5' style='background-color: $session_5_color' onclick='sessionClick(this)' '>
                    <form id='F5' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s5'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 5
                </div>
            </div>";
            // Some slides have no video title
            if ($header != null){
                // There is a title
                echo"
                 <!-- Header -->
                <div class='video-title' id='video_title_div'>
                    <h3 id='video_title_text'>$header</h3>
                </div>
                ";
                $video_style = 'top: 20% !important; height: 80% !important;';

            } else {
                // There is no title
                $video_style = 'top: 10% !important; height: 90% !important;';
            }

           echo "
            <!-- Video -->
            <iframe 
                class='video-iframe'
                id='video_iframe'
                src='$video_link' 
                style='$video_style';
                title='YouTube video player'
                allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' 
                allowfullscreen>
            </iframe>
        </div>
    </div>
    ";

    // Open button bar
    echo "<div class='button-bar'>";

    // Back button
    back_button($course_id, $slide_num, $session_color);

    // Next button
    next_or_home_button($course_id, $slide_num, $session_color);

    // Close button bar
    echo "</div>";

    save_user_progress($course_id, $slide_num);
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

    // Find session colors
    $session_colors  = get_session_colors($session_num);
    $session_1_color = $session_colors['session_1_color'];
    $session_2_color = $session_colors['session_2_color'];
    $session_3_color = $session_colors['session_3_color'];
    $session_4_color = $session_colors['session_4_color'];
    $session_5_color = $session_colors['session_5_color'];
    $session_color   = $session_colors['session_color'];

    // Find first slide for every session
    $s1 = find_first_slide_of_session($course_id, '1');
    $s2 = find_first_slide_of_session($course_id, '2');
    $s3 = find_first_slide_of_session($course_id, '3');
    $s4 = find_first_slide_of_session($course_id, '4');
    $s5 = find_first_slide_of_session($course_id, '5');

    // Create Quiz Slide Rendering
    echo"
    <div class='outer-container' onload='setResponseBodySize()'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar' id='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_num'/>
                <div id='1' style='background-color: $session_1_color' onclick='sessionClick(this)'>
                    <form id='F1' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s1'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 1
                </div>
                <div id='2' style='background-color: $session_2_color' onclick='sessionClick(this)'>
                    <form id='F2' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s2'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 2
                </div>
                <div id='3' style='background-color: $session_3_color' onclick='sessionClick(this)'>
                    <form id='F3' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s3'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 3
                </div>
                <div id='4' style='background-color: $session_4_color' onclick='sessionClick(this)'>
                    <form id='F4' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s4'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 4
                </div>
                <div id='5' style='background-color: $session_5_color' onclick='sessionClick(this)'>
                    <form id='F5' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s5'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 5
                </div>
            </div>
            <!-- Header -->
            <div class='header-row' id='header-row'>
                <h1>$header</h1>
            </div>
            <!-- Question -->
            <div class='question-body' id='question-body'>
                <h3>$question_text</h3>
            </div>            
            <!-- Response -->
            <div class='response-body' id='response-body' hidden>
                <p>$response_text</p>
            </div>
        </div>
    </div>
    ";

    // Open button bar
    echo "<div class='button-bar'>";

    // Back button
    back_button($course_id, $slide_num, $session_color);

    // Notes button
    echo "<button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' onclick='showNotes();'>Notes</button>";

    // Reveal answer button
    echo "<button class='session-color-button' style='background-color: $session_color; border: 2px solid $session_color;' id='btn_show' onclick='showAnswer()'>Reveal Answer</button>";

    // Next button
    next_or_home_button($course_id, $slide_num, $session_color);

    // Close button bar
    echo "</div>";

    // Get current user
    $user    = wp_get_current_user();
    $user_id = $user->id;

    // Get user notes
    $sql_user_notes    = "SELECT * FROM eplatform_WW_USER_NOTES WHERE user_id = '$user_id' AND course_id = '$course_id' AND slide_num = '$slide_num' ";
    $query_user_notes  = mysqli_query($con, $sql_user_notes);
    $result_user_notes = mysqli_fetch_object($query_user_notes);

    // If user has no notes on this slide, create entry
    if ($result_user_notes == null){
        $sql_create_notes_entry = "INSERT INTO eplatform_WW_USER_NOTES (user_id, course_id, slide_num, note_body) VALUES ('$user_id', '$course_id', '$slide_num', NULL)";
        mysqli_query($con, $sql_create_notes_entry);
        $notes = "Type your notes here...";
    } else {
        $notes = $result_user_notes->note_body;
    }

    // Notes area (hidden by default)
    echo "
    <script src='https://code.jquery.com/jquery-3.6.0.min.js' defer></script>
    <div class='notes-area' id='notes_div' hidden> 
        <label for='slide_notes' class='form-label'>Slide notes</label>
        <textarea class='form-control' id='slide_notes' rows='3' placeholder='Type your notes here...'>$notes</textarea>
        <button class='session-color-button' type='button' style='background-color: $session_color; border: solid 1px $session_color; width: 100%;' onclick='saveNotes($user_id, $course_id, $slide_num);'>Save notes</button>
    </div>
    ";


    save_user_progress($course_id, $slide_num);
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

    // Find session colors
    $session_colors  = get_session_colors($session_num);
    $session_1_color = $session_colors['session_1_color'];
    $session_2_color = $session_colors['session_2_color'];
    $session_3_color = $session_colors['session_3_color'];
    $session_4_color = $session_colors['session_4_color'];
    $session_5_color = $session_colors['session_5_color'];
    $session_color   = $session_colors['session_color'];

    // Find first slide for every session
    $s1 = find_first_slide_of_session($course_id, '1');
    $s2 = find_first_slide_of_session($course_id, '2');
    $s3 = find_first_slide_of_session($course_id, '3');
    $s4 = find_first_slide_of_session($course_id, '4');
    $s5 = find_first_slide_of_session($course_id, '5');

    // Create survey Slide Rendering
    echo"
    <div class='outer-container' onload='setResponseBodySize()'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar' id='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_num'/>
                <div id='1' style='background-color: $session_1_color' onclick='sessionClick(this)'>
                    <form id='F1' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s1'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 1
                </div>
                <div id='2' style='background-color: $session_2_color' onclick='sessionClick(this)'>
                    <form id='F2' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s2'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 2
                </div>
                <div id='3' style='background-color: $session_3_color' onclick='sessionClick(this)'>
                    <form id='F3' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s3'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 3
                </div>
                <div id='4' style='background-color: $session_4_color' onclick='sessionClick(this)'>
                    <form id='F4' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s4'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 4
                </div>
                <div id='5' style='background-color: $session_5_color' onclick='sessionClick(this)'>
                    <form id='F5' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s5'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 5
                </div>
            </div>
            <!-- Header -->
            <div class='header-row' id='header-row'>
                <h1>$header</h1>
            </div>
            <!-- Text -->
            <div class='question-body' id='question-body'>
                <h3>$text</h3>
            </div>            
            <!-- Link -->
            <div class='response-body' id='response-body'>
                <!-- Open in new tab -->
                <p><a href='$survey_link' target='_blank'>$survey_link</a></p>
            </div>
        </div>
    </div>
    ";

    // Open button bar
    echo "<div class='button-bar'>";

    // Back button
    back_button($course_id, $slide_num, $session_color);

    // Next button
    next_or_home_button($course_id, $slide_num, $session_color);

    // Close button bar
    echo "</div>";

    save_user_progress($course_id, $slide_num);
}

function guidance_slide($course_id, $slide_num){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB
    $sql_slide_data = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    // Get from DB: Session, Header, Question
    $session_num   = $slide_data['session_num'];
    $header        = $slide_data['header'];
    $guidance_text = $slide_data['question_text'];

    // Find session colors
    $session_colors  = get_session_colors($session_num);
    $session_1_color = $session_colors['session_1_color'];
    $session_2_color = $session_colors['session_2_color'];
    $session_3_color = $session_colors['session_3_color'];
    $session_4_color = $session_colors['session_4_color'];
    $session_5_color = $session_colors['session_5_color'];
    $session_color   = $session_colors['session_color'];

    // Find first slide for every session
    $s1 = find_first_slide_of_session($course_id, '1');
    $s2 = find_first_slide_of_session($course_id, '2');
    $s3 = find_first_slide_of_session($course_id, '3');
    $s4 = find_first_slide_of_session($course_id, '4');
    $s5 = find_first_slide_of_session($course_id, '5');

    // Create guidance Slide Rendering
    echo"
    <div class='outer-container' onload='setResponseBodySize()'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar' id='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_num'/>
                <div id='1' style='background-color: $session_1_color' onclick='sessionClick(this)'>
                    <form id='F1' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s1'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 1
                </div>
                <div id='2' style='background-color: $session_2_color' onclick='sessionClick(this)'>
                    <form id='F2' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s2'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 2
                </div>
                <div id='3' style='background-color: $session_3_color' onclick='sessionClick(this)'>
                    <form id='F3' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s3'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 3
                </div>
                <div id='4' style='background-color: $session_4_color' onclick='sessionClick(this)'>
                    <form id='F4' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s4'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 4
                </div>
                <div id='5' style='background-color: $session_5_color' onclick='sessionClick(this)'>
                    <form id='F5' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s5'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 5
                </div>
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

    echo "<script type='text/javascript' src='$src'></script>";

    // Open button bar
    echo "<div class='button-bar'>";

    // Back button
    back_button($course_id, $slide_num, $session_color);

    // Next button
    next_or_home_button($course_id, $slide_num, $session_color);

    // Close button bar
    echo "</div>";

    save_user_progress($course_id, $slide_num);
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

    // No session = default colour
    $session_color = "#61A93F";

    // Open button bar
    echo "<div class='button-bar'>";

    // Back button
    back_button($course_id, $slide_num, $session_color);

    // Next button
    next_or_home_button($course_id, $slide_num, $session_color);

    // Close button bar
    echo "</div>";

    save_user_progress($course_id, $slide_num);
}

function img_help_slide($course_id, $slide_num){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB
    $sql_slide_data = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$slide_num'";
    $result_slide_data = mysqli_query($con,$sql_slide_data);
    $slide_data = mysqli_fetch_assoc($result_slide_data);

    // Get from DB: Session, Image, Text
    $session_num   = $slide_data['session_num'];
    $header        = $slide_data['header'];
    $img_uri       = $slide_data['cover_image'];
    $text          = $slide_data['question_text'];

    // Find session colors
    $session_colors  = get_session_colors($session_num);
    $session_1_color = $session_colors['session_1_color'];
    $session_2_color = $session_colors['session_2_color'];
    $session_3_color = $session_colors['session_3_color'];
    $session_4_color = $session_colors['session_4_color'];
    $session_5_color = $session_colors['session_5_color'];
    $session_color   = $session_colors['session_color'];

    // Find first slide for every session
    $s1 = find_first_slide_of_session($course_id, '1');
    $s2 = find_first_slide_of_session($course_id, '2');
    $s3 = find_first_slide_of_session($course_id, '3');
    $s4 = find_first_slide_of_session($course_id, '4');
    $s5 = find_first_slide_of_session($course_id, '5');

    // Create guidance Slide Rendering
    echo"
    <div class='outer-container' onload='setResponseBodySize()'>
        <div class='course-screen'>
            <!-- Session Bar -->
            <div class='session-bar' id='session-bar'>
                <input type='hidden' id='current_session_num' value='$session_num'/>
                <div id='1' style='background-color: $session_1_color' onclick='sessionClick(this)'>
                    <form id='F1' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s1'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 1
                </div>
                <div id='2' style='background-color: $session_2_color' onclick='sessionClick(this)'>
                    <form id='F2' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s2'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 2
                </div>
                <div id='3' style='background-color: $session_3_color' onclick='sessionClick(this)'>
                    <form id='F3' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s3'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 3
                </div>
                <div id='4' style='background-color: $session_4_color' onclick='sessionClick(this)'>
                    <form id='F4' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s4'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 4
                </div>
                <div id='5' style='background-color: $session_5_color' onclick='sessionClick(this)'>
                    <form id='F5' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                        <input type='hidden' name='next_slide' value='$s5'/>
                        <input type='hidden' name='course_id' value='$slide_data[course_id]'/>
                    </form>
                    Session 5
                </div>
            </div>
            <!-- Header -->
            <div class='guidance-header'>
                <h3>$header</h3>
            </div>
            <!-- Image -->
            <div class='img-container'>
                <img src='$img_uri' alt='How to enable subtitles image' >
            </div>            
            <!-- Text -->
            <div class='img-help'>
                <p>$text</p>
            </div>
        </div>
    </div>
    ";

    // Open button bar
    echo "<div class='button-bar'>";

    // Back button
    back_button($course_id, $slide_num, $session_color);

    // Next button
    next_or_home_button($course_id, $slide_num, $session_color);

    // Close button bar
    echo "</div>";

    save_user_progress($course_id, $slide_num);
}

function show_map(){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');


}

function find_first_slide_of_session($course_id, $session_num){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Get from DB: Session Num, Header (if exists) Video link
    $sql_first_slide_in_session = "SELECT slide_num FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND session_num = '$session_num' ORDER BY slide_num LIMIT 1";
    $result_first_slide_in_session = mysqli_query($con,$sql_first_slide_in_session);
    $slide_first_slide_in_session = mysqli_fetch_assoc($result_first_slide_in_session);
    return $slide_first_slide_in_session['slide_num'];

}

function get_session_colors($session_num): array {
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

function save_user_progress($course_id, $slide_num){
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

    // Check if user has any existing progress for this course
    $sql_user_has_progress = "SELECT * FROM eplatform_WW_USER_PROGRESS WHERE user_id = '$id' AND course_id = '$course_id'";
    $query_user_has_progress = mysqli_query($con, $sql_user_has_progress);
    $result_user_has_progress = mysqli_fetch_object($query_user_has_progress);

    // If user has no progress record on this course, create one
    if ($result_user_has_progress == null){
        $sql_create_progress_record = "INSERT INTO eplatform_WW_USER_PROGRESS (user_id, course_id, slide_num) VALUES ('$id', '$course_id', '$slide_num')";
        return mysqli_query($con, $sql_create_progress_record);
    }

    // Update slide left at for this course
    $sql_update_user_progress = "UPDATE eplatform_WW_USER_PROGRESS SET slide_num = '$slide_num' WHERE user_id = '$id' AND course_id = '$course_id' ";
    return mysqli_query($con, $sql_update_user_progress);
}

function back_button($course_id, $current_slide_num, $session_color){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Find previous slide of this course if it exists
    $prev_slide_num = intval($current_slide_num) - 1;

    $sql_prev_slide = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$prev_slide_num' ";
    $result_prev_slide = mysqli_query($con, $sql_prev_slide);
    $prev_slide_data   = mysqli_fetch_assoc($result_prev_slide);

    if ( $prev_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: solid 1px $session_color;' type='submit'>Back</input>
                <input class='session-color-button' type='hidden' name='next_slide' value='$prev_slide_num'/>
                <input class='session-color-button' type='hidden' name='course_id' value='$course_id'/>
            </form>";
    } else {
        // There is no previous slide - Beginning of course
        // Button is disabled
        echo "
            <button class='disabled-button' disabled>Back</button>
        ";
    }
}

function next_or_home_button($course_id, $current_slide_num, $session_color){
    // DB Connection
    $DB_NAME = "diphedb";
    $MYSQL_USERNAME = "diphedb";
    $MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
    $HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

    // CONNECT WITH THE DATABASE
    $con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

    // Find next slide of this course if it exists
    $next_slide_num = intval($current_slide_num) + 1; // The number of the next slide (might not exist)

    $sql_next_slide = "SELECT * FROM eplatform_WELLNESS_WARRIOR WHERE course_id = '$course_id' AND slide_num = '$next_slide_num' ";
    $result_next_slide = mysqli_query($con, $sql_next_slide);
    $next_slide_data   = mysqli_fetch_assoc($result_next_slide);

    if ( $next_slide_data != NULL ){
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: solid 1px $session_color;' type='submit'>Next</input>
                <input class='session-color-button' type='hidden' name='next_slide' value='$next_slide_num'/>
                <input class='session-color-button' type='hidden' name='course_id' value='$course_id'/>
            </form>";
    } else {
        // There is no next slide - End of course
        // HOME button
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/' method='post'>
                <button class='session-color-button' style='background-color: $session_color; border: solid 1px $session_color;' type='submit'>Home</button>
            </form>
            ";
    }
}