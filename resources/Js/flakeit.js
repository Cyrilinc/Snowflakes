// JavaScript Document

/*
 * Snowflakes 2.0 - CMS & Web Publishing
 * http://cyrilinc.co.uk/snowflakes/
 * Copyright (c) 2014 Cyril inc
 * Licensed under MIT and GPL
 * Date: Mon, Mar 15 2014 17:00:31
 */

$(document).ready(function() {
    $('.flakeit').click(function()
    {
        var ID = $(this).attr("id");
        var sid = ID.split("flakeit");
        var New_ID = sid[1];
        var Snowflake_type = $(this).attr("data-type");
        var URL = flakeitUrl.length > 0 ? flakeitUrl : 'flakeit.php';
        //alert(URL);
        var text=$("#flakeit" + New_ID + " span").text();
        var flakeOrNot = text==="Flake it"?"true":"false";

        var dataString = {flakeit: flakeOrNot, id: New_ID, type: Snowflake_type, submit: true};
        $.ajax({
            type: "POST",
            url: URL,
            data: dataString,
            cache: false,
            success: function(data, textStatus, jqXHR) {
                if (Snowflake_type === 'snowflake' || Snowflake_type === 'event') {
                    
                    $("#flakeit" + New_ID + " span").text(flakeOrNot==="true"?"Unflake it":"Flake it");
                    
                    $("#flakecount" + New_ID).text(data);
                    var src = $("#flakeit" + New_ID + " img").attr("src");
                    src = src.replace(flakeOrNot==="true"?"Snowflakes.png":"FlakeIt.png", flakeOrNot==="true"?"FlakeIt.png":"Snowflakes.png");
                    $("#flakeit" + New_ID + " img").attr("src", src);
                }
                else {
                    var src = $("#flakeit" + New_ID + " img").attr("src");
                    src = src.replace(flakeOrNot==="true"?"Snowflakes.png":"FlakeIt.png", flakeOrNot==="true"?"FlakeIt.png":"Snowflakes.png");
                    $("#flakeit" + New_ID + " img").attr("src", src);
                }
                
            },
            error: function(error) {
                console.log(error);
            }

        });
    });
});