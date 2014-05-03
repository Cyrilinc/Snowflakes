/*
 * Snowflakes Editor jQuery plugin
 * version 0.1
 *
 * Copyright (c) 2014 Cyril Adelekan (Cyrilinc.co.uk)
 *
 * Dual licensed under the MIT and GPL (LICENSE.txt)
 *
 * http://Cyrilinc.co.uk/Snowflakes/
 *
 */
$(document).ready(function() {
    richTextField.document.designMode = 'On';
    var bodyText = $('textarea#Pmessage2').val().length ? $('textarea#Pmessage2').val() : '<span style="color: rgb(128, 128, 128); font-family: Strait; font-size: small;">Body Text</span>';

    richTextField.document.open('text/html', 'replace');
    richTextField.document.write(bodyText);
    richTextField.document.close();

});

function promptValue(name)
{
    name = prompt(name);
    if (name === null)
        return false;
    return name;
}
function sfEditorAction(command, editoption)
{
    richTextField.document.execCommand(command, false, editoption);
}

function transferEditorData(formname) {
    var theForm = document.getElementById(formname);
    var theText=window.frames['richTextField'].document.body.innerHTML;
    if(theText==='<span style="color: rgb(128, 128, 128); font-family: Strait; font-size: small;">Body Text</span>')
        $('textarea#Pmessage2').val("");
    else 
        $('textarea#Pmessage2').val(theText);
    theForm.submit();
}
