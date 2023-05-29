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


if( file_exists($_FILES['photo_video_answer']['tmp_name']) || is_uploaded_file($_FILES['photo_video_answer']['tmp_name'])) {
    // Handle file upload
    $file = $_FILES['photo_video_answer']; // This is how we access the file. From $_FILES not $_POST

    echo 'File received ';

    // Image File Properties
    $file_name  =  $file['name'];     // Filename incl extension
    $file_tmp   =  $file['tmp_name']; // Temp location on server
    $file_size  =  $file['size'];     // File size
    $file_error =  $file['error'];    // Error message (0 = no error)

    // Work out character file extension
    $file_ext = explode('.', $file_name);   // Separate the filename on every dot.
    $file_ext = strtolower(end($file_ext)); // The extension is the characters after the last dot.

    // Check if extension is allowed
    $allowed = array('jpg', 'jpeg', 'png', 'mp4'); // This is an array of all the extensions we want to allow

    // Check if file is allowed and begin processing
    if (in_array($file_ext, $allowed)){
//        echo 'File extension allowed ';

        // If there is no upload error
        if ($file_error === 0) {
//            echo 'No upload error ';

            // Create unique filename = uniqueID+extension
            $file_name_new = uniqid('', true) . '.' . $file_ext;
            $file_destination = '/sys-data/WebData/projects/diphe/wp-content/plugins/diphe-platform/user_file_submissions/' . $file_name_new;

            // Move file to known, accessible location
            if (move_uploaded_file($file_tmp, $file_destination)) {
//                echo 'File moved successfully ';

                // Change file permissions
                chmod($file_destination,0775);

                // Now build the URL for the file
                $base_path = "https://diphe.cs.ucy.ac.cy/wp-content/plugins/diphe-platform/user_file_submissions/";
                $file_url = $base_path . $file_name_new;

                // Need to store file_url in DB and replace existing record if exists

                // Check if record exists
                $sql = "SELECT COUNT(1) FROM eplatform_USER_SUBMISSIONS WHERE user_id = '$user_id' AND slide_id = '$slide_id'";
                $result = mysqli_query($con, $sql);
                $num_rows = mysqli_num_rows($result);

                // If exists, delete it
                if ($num_rows > 0) {
                    // Delete the record (we are going to replace it with the new one)
                    $sql = "DELETE FROM eplatform_USER_SUBMISSIONS WHERE user_id = '$user_id' AND slide_id = '$slide_id'";
                    $result = mysqli_query($con, $sql);
                    if ( !$result ){
                        die ('Could not delete previews answer record from DB');
                    }
                }

                // Add file url in DB
                $sql = "INSERT INTO eplatform_USER_SUBMISSIONS (user_id, slide_id, file_link) 
                        VALUES ('$user_id', '$slide_id', '$file_url')";
                $result = mysqli_query($con, $sql);

                if (!$result){
                    echo 'File upload succeded but could not add record to the DB';
                    die();
                }

            } else {
                echo 'Could not move file - If error is 0 it\'s probably permissions erros';
                echo "Error: " . $_FILES['photo_video_answer']['error'];
                die();
            }
        }
    } else {
        // Need to redirect with error message
        echo "Exception - ";
        die();
    }

} else {
    echo "No file received";
    die();
}

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