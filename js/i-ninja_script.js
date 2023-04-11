function sessionClick(elem){
    var formID = "F" + elem.id;
    var form = document.getElementById(formID);
    form.submit();
}

function notes(){
    alert('This function has not been implemented yet');
}
function chat(){
    alert('This function has not been implemented yet');
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
        // do not color anything
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