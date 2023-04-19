<?php
// DB Connection
$DB_NAME = "diphedb";
$MYSQL_USERNAME = "diphedb";
$MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
$HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

// CONNECT WITH THE DATABASE
$con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

if (isset($_POST)){
    $data = json_decode(file_get_contents("php://input"));
    $user_id = $data->user_id;
    $course_id = $data->course_id;
    $slide_num = $data->slide_num;
    $note_body = $data->note_data;

    // Update user notes for this slide
    $sql_update_user_notes = "UPDATE eplatform_WW_USER_NOTES SET note_body = '$note_body' WHERE user_id = '$user_id' AND course_id = '$course_id' AND slide_num = '$slide_num' ";
    $result = mysqli_query($con, $sql_update_user_notes);

    echo $result;

}
