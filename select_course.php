<?php
/*
 * Plugin Name: DIPHE Platform Plugin
 * Description: Plugin logic for the DIPHE e-Learning platform
 * Version: 1.0
 * Author: Constantinos Tefkros Loizou, SEIT Lab
 * Author URI: github.com/tefkros777
 */

function select_course(){
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

    // Will need to query slides for this user and user progress (last slide for every course)

    // Show available courses for the user
    echo "<div class='button-bar'>";

    // Wellness Warrior Student
    echo"
        <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
            <button class='session-5-button' type='submit'>Wellbeing Warriors (Student)</button>
            <input type='hidden' name='course_id' value='1'/>
        </form>
    ";

    // Wellness Warrior Teacher
    echo"
        <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
            <button class='session-5-button' type='submit'>Wellbeing Warriors (Teacher)</button>
            <input type='hidden' name='course_id' value='2'/>
        </form>
    ";

    // i-Ninja Tier 1
    echo"
        <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
            <button class='session-1-button' type='submit'>i-Ninja Tier 1 (Under construction)</button>
            <input type='hidden' name='course_id' value='3'/>
            <input type='hidden' name='last_slide_for_user' value='1'/> <!--THIS IS HARDCODED FOR TESTING-->
        </form>
    ";

    echo "</div>";
}
add_shortcode('select_course', 'select_course');