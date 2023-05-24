function sessionClick(elem){
    var formID = "F" + elem.id;
    var form = document.getElementById(formID);
    form.submit();
}

function showNotes(){
    // Toggle notes area visibility
    var notesArea =  document.getElementById("notes_div");
    notesArea.hidden = !notesArea.hidden;
}

function saveNotes(userId, slideId){
    // alert('notes btn pressed');
    var notesDiv      = document.getElementById("notes_div");
    var notesTextArea = document.getElementById("slide_notes");
    var notesData     = notesTextArea.value;

    //prepare json
    var jsonData = {
        "user_id": userId,
        "slide_id": slideId,
        "note_data": notesData
    }

    var URL = "https://diphe.cs.ucy.ac.cy/wp-content/plugins/diphe-platform/in-ajax-calls.php"

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

function chat(){
    alert('This functionality has not been implemented yet');
}

function setResponseBodySize(){
    var responseAreaDiv = document.getElementById('response-area-div');
    // If we are in a submit assignment slide, which has a response area div
    if (responseAreaDiv != null){
        // Calculate the top of the responseAreaDiv
        var sessionBarDiv = document.getElementById('session-bar');
        var headerRowDiv = document.getElementById('header-row');
        var bodyRowDiv = document.getElementById('body-row');

        var top = sessionBarDiv.offsetHeight + headerRowDiv.offsetHeight + bodyRowDiv.offsetHeight;

        responseAreaDiv.style.top = top + 'px';
    }
}

function setCurrentSession(){
    var sessionNum = document.getElementById('current_session_num').value;

    var divSession1 = document.getElementById('div_session_1');
    var divSession2 = document.getElementById('div_session_2');
    var divSession3 = document.getElementById('div_session_3');
    var divSession4 = document.getElementById('div_session_4');
    var divSession5 = document.getElementById('div_session_5');

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

function changeSessionButtonClass(session_num){
    var buttons = document.querySelectorAll('.session-color-button');
    var newStyleClass = 'session-' + session_num + '-button';
    buttons.forEach(button => {
        button.classList.remove('session-color-button');
        button.classList.add(newStyleClass);
    });
}

// Called automatically when page loads
window.addEventListener("load", function() {
    // Once loading is finished, call function
    setCurrentSession();
    // setResponseBodySize();
    // setVideoSize();
});