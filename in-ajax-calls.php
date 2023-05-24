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
    $slide_id = $data->slide_id;
    $note_body = $data->note_data;

    // Update user notes for this slide
    $sql_update_user_notes = "UPDATE eplatform_iN_USER_NOTES SET note_body = '$note_body' WHERE user_id = '$user_id' AND slide_id = '$slide_id' ";
    $result = mysqli_query($con, $sql_update_user_notes);

    // Prevent SQL Injection
    $stmt   = $con->prepare("UPDATE eplatform_WW_USER_NOTES SET note_body = (?) WHERE user_id = (?) AND course_id = (?) AND slide_num = (?)");
    $result = $stmt->bind_param("siii", $note_body, $user_id, $course_id, $slide_num);
    $result = $stmt->execute();

    echo $result;

}
