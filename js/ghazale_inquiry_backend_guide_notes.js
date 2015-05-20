/**
 * Show/Hide backend guide notes
 */
jQuery(document).ready(function($) {
    $("#ghazale_inquiry_guide_notes").hide();
    $("#ghazale_inquiry_guide_show_hide").click(function(){
        $("#ghazale_inquiry_guide_notes").slideToggle();
    });
});