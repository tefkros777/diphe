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

    // Show available courses for the user
    echo "<div class='button-bar'>";

    echo"
        <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
            <button type='submit'>Wellbeing Warriors (Student)</button>
            <input type='hidden' name='course_id' value='1'/>
        </form>
    ";

    echo"
        <form action='https://diphe.cs.ucy.ac.cy/e-learning-platform/wellbeing-warriors' method='post'>
            <button type='submit'>Wellbeing Warriors (Teacher)</button>
            <input type='hidden' name='course_id' value='2'/>
        </form>
    ";

    echo "</div>";
}
add_shortcode('select_course', 'select_course');