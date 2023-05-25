<?php
// DB Connection
$DB_NAME = "diphedb";
$MYSQL_USERNAME = "diphedb";
$MYSQL_PASSWORD = "JNJaBF0oIAUG0SUd";
$HOST_SERVER = "dbserver.in.cs.ucy.ac.cy";

// CONNECT WITH THE DATABASE
$con = mysqli_connect($HOST_SERVER, $MYSQL_USERNAME, $MYSQL_PASSWORD, $DB_NAME) or die (' Could not connect to the DB ');

// Get data from POST
$next_slide_num = $_POST[next_slide_num];

$user_id   = $_POST[user_id];
$slide_id  = $_POST[slide_id];
$course_id = $_POST[course_id];

$answer_body = $_POST[response];

// Check if record exists
$sql = "SELECT COUNT(1) FROM eplatform_iN_SIMPLE_INPUT WHERE user_id = '$user_id' AND slide_id = '$slide_id'";
$result = mysqli_query($con, $sql);
$num_rows = mysqli_num_rows($result);

// If exists, delete it
if ($num_rows > 0) {
    // Delete the record (we are going to replace it with the new one)
    $sql = "DELETE FROM eplatform_iN_SIMPLE_INPUT WHERE user_id = '$user_id' AND slide_id = '$slide_id'";
    $result = mysqli_query($con, $sql);
    if ( !$result ){
        die ('Could not delete previews answer record from DB');
    }
}

// Now create record and insert it to DB - Also escape special chars and prevent injections
$stmt   = $con->prepare("INSERT INTO eplatform_iN_SIMPLE_INPUT (answer_id, user_id, slide_id, answer_body) VALUES (NULL, (?), (?), (?)) ");
$result = $stmt->bind_param("iss", $user_id, $slide_id, $answer_body);
$result = $stmt->execute();

if ( $result ){
    // Success - Redirect to next slide
    echo "
    <form id='redirection_form' action='https://diphe.cs.ucy.ac.cy/e-learning-platform/i-ninja' method='post'>
        <input class='session-color-button' type='hidden' name='next_slide' value='$next_slide_num'/>
        <input class='session-color-button' type='hidden' name='course_id' value='$course_id'/>
    </form>
    ";

    // JS to submit form and redirect
    echo "
    <script>
        // Called automatically when page loads
        window.addEventListener('load', function() {
            // Once loading is finished, submit form to redirect
            let redirectionForm = document.getElementById('redirection_form');
            redirectionForm.submit();
        });
    </script>
    ";
}
// Insertion failed
else {
    echo "Could not insert answers into DB";
    echo "<br>";
    echo $stmt->error;
    die();
}

?>