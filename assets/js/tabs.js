function openCity(evt, formsName) {
    var i, tabcontent, tablinks;

    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    document.getElementById(formsName).style.display = "block";
    evt.currentTarget.className += " active";
}

// Wait for DOM to be ready before trying to click the default tab
document.addEventListener("DOMContentLoaded", function () {
    var defaultTab = document.getElementById("defaultOpen");
    if (defaultTab) {
        defaultTab.click();
    } else {
        // Fallback: if no "defaultOpen" id exists, just open the first tab found
        var firstTab = document.getElementsByClassName("tablinks")[0];
        if (firstTab) {
            firstTab.click();
        }
    }
});