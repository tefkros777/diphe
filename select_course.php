<?php
/*
 * Plugin Name: DIPHE Platform Plugin
 * Description: Plugin logic for the DIPHE e-Learning platform
 * Version: 1.0
 * Author: Constantinos Tefkros Loizou, SEIT Lab
 * Author URI: github.com/tefkros777
 */

function select_course(){
    // Redirect if user is not logged in
    if (!is_user_logged_in()){
        header("Location: https://diphe.cs.ucy.ac.cy/login");
        die();
    }

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

    // Get role of current user
    $user    = wp_get_current_user();
    $roles   = (array) $user->roles;
    $teacher_access = in_array("um_teacher", $roles) || in_array("administrator", $roles);


    // Show available courses for the user
    echo "<div class='button-bar'>";

    // Wellness Warrior Student
    echo "
        <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
            <button type='submit'>Well-Being Warriors (Student)</button>
            <input type='hidden' name='course_id' value='1'/>
        </form>
    ";

    // Wellness Warrior Teacher - Only visible to teachers
    if ($teacher_access) {
        echo "
            <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
                <button type='submit'>Well-Being Warriors (Teacher)</button>
                <input type='hidden' name='course_id' value='2'/>
            </form>
        ";
    }

    //TODO: Check if is unlocked for this user
    // i-Ninja Tier 1
    echo "
        <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
            <button class='session-5-button' type='submit'>i-Ninja Tier 1 (Under construction)</button>
            <input type='hidden' name='course_id' value='3'/>
            <input type='hidden' name='last_slide_for_user' value='1'/> <!--THIS IS HARDCODED FOR TESTING-->
        </form>
    ";

    echo "</div>";

}
add_shortcode('select_course', 'select_course');