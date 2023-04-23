
function setTheme( id ) {

    let params = "?action=setTheme";
    params += "&i=" + id;

    const xmlhttp = new XMLHttpRequest();
    const callbackHandler = function () { setThemeCallback(xmlhttp); };
    xmlhttp.onreadystatechange = callbackHandler;
    xmlhttp.open("GET", RS_URL_API + params, true);
    xmlhttp.send();

}

function setThemeCallback( xmlhttp ) {

    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        const result = JSON.parse(xmlhttp.responseText);

        console.info("Result");
        console.info("result.Success=" + result.Success);
        if ( result.Success !== "false" ) {
            location.reload();
        }
        else {
            renderAlert("Unable the set the theme.", ALERT_LEVEL.warning, null, 300000);
        }
    }

}
