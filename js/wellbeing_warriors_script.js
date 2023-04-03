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
            // do not color anything
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

function notesButton(){
    alert('This function has not been implemented yet');
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
    setResponseBodySize();
    setVideoSize();
});