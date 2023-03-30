function setResponseBodySize(){
    // Get the height of all elements above response-body div
    var sessionBarDiv   = document.getElementById('session-bar');
    var headerRowDiv    = document.getElementById('header-row');
    var questionBodyDiv = document.getElementById('question-body');
    var responseBodyDiv = document.getElementById('response-body');

    // The top of the response-body div is the sum of the height of all elements above it
    if (responseBodyDiv != null && questionBodyDiv != null && headerRowDiv !=null){
        var top = sessionBar.offsetHeight + headerRowDiv.offsetHeight + questionBodyDiv.offsetHeight;
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
            // Set session header colors
            divSession1.style.backgroundColor = "#A30100";
            // Set the button styles
            changeSessionButtonClass('1');
            break;
        case '2':
            // Set session header colors
            divSession1.style.backgroundColor = "#A30100";
            divSession2.style.backgroundColor = "#1983E7";
            changeSessionButtonClass('2');
            break;
        case '3':
            // Set session header colors
            divSession1.style.backgroundColor = "#A30100";
            divSession2.style.backgroundColor = "#1983E7";
            divSession3.style.backgroundColor = "#702EA0";
            changeSessionButtonClass('3');
            break;
        case '4':
            // Set session header colors
            divSession1.style.backgroundColor = "#A30100";
            divSession2.style.backgroundColor = "#1983E7";
            divSession3.style.backgroundColor = "#702EA0";
            divSession4.style.backgroundColor = "#1A7B00";
            changeSessionButtonClass('4');
            break;
        case '5':
            // Set session header colors
            divSession1.style.backgroundColor = "#A30100";
            divSession2.style.backgroundColor = "#1983E7";
            divSession3.style.backgroundColor = "#702EA0";
            divSession4.style.backgroundColor = "#1A7B00";
            divSession5.style.backgroundColor = "#F18601";
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

    // If there is no video title
    if (videoTitleText.innerText == ""){
        // Enlarge video iFrame
        videoTitleDiv.hidden == true;
        videoIFrame.style.top = "10%";
        videoIFrame.style.height = "90%";
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