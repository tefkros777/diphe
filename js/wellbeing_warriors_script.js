function setResponseBodySize(){
    // Get the height of all elements above response-body div
    var sessionBarDiv   = document.getElementById('session-bar');
    var headerRowDiv    = document.getElementById('header-row');
    var questionBodyDiv = document.getElementById('question-body');
    var responseBodyDiv = document.getElementById('response-body');

    // The top of the response-body div is the sum of the height of all elements above it
    if (responseBodyDiv != null && questionBodyDiv != null && headerRowDiv !=null){
        var top = sessionBarDiv.offsetHeight + headerRowDiv.offsetHeight + questionBodyDiv.offsetHeight;
        responseBodyDiv.style.top = top + 'px';
    }
}

function setCurrentSession(){
    var sessionNumInput = document.getElementById('current_session_num');

    var divSession1 = document.getElementById('div_session_1');
    var divSession2 = document.getElementById('div_session_2');
    var divSession3 = document.getElementById('div_session_3');
    var divSession4 = document.getElementById('div_session_4');
    var divSession5 = document.getElementById('div_session_5');

    // If slide has no session, then it's session 0
    if ( sessionNumInput == null ) {
        sessionNum = '0';
    } else {
        sessionNum = sessionNumInput.value;
    }

    switch (sessionNum){
        case '1':
            // Set the button styles
            changeSessionButtonClass('1');
            break;
        case '2':
            changeSessionButtonClass('2');
            break;
        case '3':
            changeSessionButtonClass('3');
            break;
        case '4':
            changeSessionButtonClass('4');
            break;
        case '5':
            changeSessionButtonClass('5');
            break;
        default:
            changeSessionButtonClass('0');
            break;
    }
}

function setVideoSize(){
    var videoIFrame = document.getElementById("video_iframe");
    var videoTitleDiv = document.getElementById("video_title_div");
    var videoTitleText = document.getElementById("video_title_text");

    // If slide has a video
    if ( videoIFrame != null) {
        // If there is no video title
        if (videoTitleText.innerText == ""){
            // Enlarge video iFrame
            videoTitleDiv.hidden == true;
            videoIFrame.style.top = "10%";
            videoIFrame.style.height = "90%";
        }
    }
}

function showAnswer(){
    var responseBody = document.getElementById('response-body');
    var showAnswerButton = document.getElementById('btn_show');

    if (responseBody.hidden == true){
        responseBody.hidden = false;
        showAnswerButton.innerText = "Hide Answer"
    } else {
        responseBody.hidden = true;
        showAnswerButton.innerText = "Reveal Answer"
    }
}

function showNotes(){
    // Toggle notes area visibility
    var notesArea =  document.getElementById("notes_div");
    notesArea.hidden = !notesArea.hidden;
}

function saveNotes(userId, courseId, slideNum){
    var notesDiv      = document.getElementById("notes_div");
    var notesTextArea = document.getElementById("slide_notes");
    var notesData     = notesTextArea.value;

    //prepare json
    var jsonData = {
        "user_id": userId,
        "course_id": courseId,
        "slide_num": slideNum,
        "note_data": notesData
    }

    var URL = "https://diphe.cs.ucy.ac.cy/wp-content/plugins/diphe-platform/ww-ajax-calls.php"

    //POST the data
    $.ajax({
        url: URL,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(jsonData),
        success: function (response) {
            // alert('AJAX SUCCESSFUL');

            // Convert response from string to JSON
            var json = JSON.parse(response);

            if (response == '1'){
                alert('Notes saved');
            }

        },
        error: function(response) {
            // alert('AJAX FAILED');
            // alert(response);
            alert("There was a problem saving your notes");
        },
        cache: false,
        processData: false
    });
}

function changeSessionButtonClass(session_num){
    var buttons = document.querySelectorAll('.session-color-button');
    var newStyleClass = 'session-' + session_num + '-button';
    buttons.forEach(button => {
        button.classList.remove('session-color-button');
        button.classList.add(newStyleClass);
    });
}

function sessionClick(elem){
    var formID = "F" + elem.id;
    var form = document.getElementById(formID);
    form.submit();
}

// Called automatically when page loads
window.addEventListener("load", function() {
    // Once loading is finished, call function
    setCurrentSession();

    setResponseBodySize();
    // setVideoSize();
});