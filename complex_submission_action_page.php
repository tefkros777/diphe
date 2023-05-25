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

$c00 = $_POST['0_0'];
$c01 = $_POST['0_1'];
$c02 = $_POST['0_2'];
$c03 = $_POST['0_3'];
$c04 = $_POST['0_4'];
$c05 = $_POST['0_5'];
$c06 = $_POST['0_6'];
$c07 = $_POST['0_7'];
$c08 = $_POST['0_8'];
$c10 = $_POST['1_0'];
$c11 = $_POST['1_1'];
$c12 = $_POST['1_2'];
$c13 = $_POST['1_3'];
$c14 = $_POST['1_4'];
$c15 = $_POST['1_5'];
$c16 = $_POST['1_6'];
$c17 = $_POST['1_7'];
$c18 = $_POST['1_8'];

// Check if record exists
$sql = "SELECT COUNT(1) FROM eplatform_iN_SPECIAL_INPUT WHERE user_id = '$user_id' AND slide_id = '$slide_id'";
$result = mysqli_query($con, $sql);
$num_rows = mysqli_num_rows($result);

// If exists, delete it
if ($num_rows > 0) {
    // Delete the record (we are going to replace it with the new one)
    $sql = "DELETE FROM eplatform_iN_SPECIAL_INPUT WHERE user_id = '$user_id' AND slide_id = '$slide_id'";
    $result = mysqli_query($con, $sql);
    if ( !$result ){
        die ('Could not delete previews answer record from DB');
    }
}

// Now create record and insert it to DB - Also escape special chars and prevent injections
$stmt   = $con->prepare("INSERT INTO eplatform_iN_SPECIAL_INPUT (answer_id, user_id, slide_id, `0_0`, `0_1`, `0_2`, `0_3`, `0_4`, `0_5`, `0_6`, `0_7`, `0_8`, `1_0`, `1_1`, `1_2`, `1_3`, `1_4`, `1_5`, `1_6`, `1_7`, `1_8`) VALUES (NULL, (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?)) ");
$result = $stmt->bind_param("isssssssssssssssssss", $user_id, $slide_id, $c00, $c01, $c02, $c03, $c04, $c05, $c06, $c07, $c08, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18);
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
//    echo $stmt->error;
    die();
}

?>