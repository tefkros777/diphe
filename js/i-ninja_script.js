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

// Called automatically when page loads
window.addEventListener("load", function() {
    // Once loading is finished, call function
    // setCurrentSession();
    // setResponseBodySize();
    // setVideoSize();
});