// JavaScript Document

/*
 * Snowflakes 1.0 - CMS & Web Publishing
 * http://cyrilinc.co.uk/snowflakes/
 * Copyright (c) 2013 Cyril inc
 * Licensed under MIT and GPL
 * Date: Tues, Jan 15 2013 11:33:31 -1100
 */

$(document).ready(function() {
    var touch = $('#touch-menu');
    var menu = $('.primary_nav');

    $(touch).on('click', function(/*e*/) {
        /*e.preventDefault();*/
        menu.slideToggle(500);
    });

    $(window).resize(function() {
        var w = $(window).width();
        if (w > 767 && menu.is(':hidden')) {
            menu.removeAttr('style');
        }
    });
});

$(document).ready(function() {
// Colorbox for images
    $("a.colorbox").colorbox({
        maxHeight: '95%',
        maxWidth: '95%',
        transition: "fade"
    });
    // Called directly, without assignment to an element:
    var a_href = $('.colorboxLink').attr('href');
    $(".colorboxLink").colorbox({href: "" + a_href});
    $(".SummaryDescription").hide();
    $(".SummaryDescBtnDown").click(function()
    {
        $(this).next(".SummaryDescription").slideToggle(500);
        $(this).toggleClass("SummaryDescBtnUp", 1000, "easeOutSine");
    });
    $(".SummaryDescBtnUp2").click(function()
    {
        $(this).next(".SummaryDescription2").slideToggle(500);
        $(this).toggleClass("SummaryDescBtnDown2", 1000, "easeInSine");
    });


});

$(document).ready(function() {
    $(".HalfSlider").css("overflow", "hidden");
    $(".HalfSlider").css("display", "block");
    $(".HalfSliderMain").before('<ul class="HalfSliderNav">').cycle({
        fx: 'fade',
        pause: 1,
        delay: -6000,
        pager: '.HalfSliderNav',
        pagerEvent: 'mouseover',
        fastOnEvent: true,
        pagerAnchorBuilder: function(index, el) {
            return '<a href="#"> </a>';
        }
    });
});
function deleteConfirmation(deletelink, nameto, notOwner) {
    notOwner = typeof notOwner !== 'undefined' && notOwner === true ? "\n Note: Because you do not own \"" + nameto + "\" , a request will be sent to the creator to either accept or reject \"" + nameto + "\" being deleted, " : '';
    var answer = confirm("Are you sure you wan't to delete \"" + nameto + "\"?" + notOwner)

    if (answer) {
        window.location = deletelink;
    }
}


function GetQuery() {
// This function is anonymous, is executed immediately and 
// the return value is assigned to QueryString!
    var query_string = {};
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        // If first entry with this name
        if (typeof query_string[pair[0]] === "undefined") {
            query_string[pair[0]] = pair[1];
            // If second entry with this name
        } else if (typeof query_string[pair[0]] === "string") {
            var arr = [query_string[pair[0]], pair[1]];
            query_string[pair[0]] = arr;
            // If third or later entry with this name
        } else {
            query_string[pair[0]].push(pair[1]);
        }
    }
    return query_string;
}
			