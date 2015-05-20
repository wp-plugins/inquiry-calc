/**
 * sliding inquiry questions one by one
 * conditions of the conditional questions would only appear if the user gives positive answer to the parent question
 */
jQuery(document).ready(function($){

    var slider_height = $(".ghazale_question_box").height();

    $(".ghazale_question_box").diyslider({
        width: "500px", // width of the slider
        height: slider_height, // height of the slider
        display: 1, // number of slides you want it to display at once
        loop:  false // disable looping on slides
    });

    // buttons to change slide
    $("#ghazale_inquiry_go_right").bind("click", function(){
        // Go to the next slide
        $(".ghazale_question_box").diyslider("move", "forth");
    });


    $("#ghazale_inquiry_go_left").bind("click", function(){
        // Go to the previous slide
        $(".ghazale_question_box").diyslider("move", "back");
    });



    var sum = Number($(".ghazale_inquiry_min_flat_cost").html());
    $(".ghazale-q-onoffswitch-checkbox").each(function (index) {

        $(".ghazale-q-onoffswitch-checkbox").eq(index).change(function(){
            //alert("clicked");
            if($(".ghazale-q-onoffswitch-checkbox").eq(index).prop('checked')) {
                sum += Number($(".ghazale_frontend_q_cost").eq(index).text());
                $(".ghazale_inquiry_final_cost").html(sum);
            }

            if($(".ghazale-q-onoffswitch-checkbox").eq(index).prop('checked')==false){
                sum -= Number($(".ghazale_frontend_q_cost").eq(index).text());
                $(".ghazale_inquiry_final_cost").html(sum);
            }
        });

        $(".ghazale-q-onoffswitch-checkbox").eq(index).click(function(){
           $(".ghazale_inquiry_conditions").eq(index).slideToggle("slow");
        });
    });


    $(".ghazale-c-onoffswitch-checkbox").each(function (index) {
        $(".ghazale-c-onoffswitch-checkbox").eq(index).change(function(){
            if($(".ghazale-c-onoffswitch-checkbox").eq(index).prop('checked')) {
                sum += Number($(".ghazale_frontend_c_cost").eq(index).text());
                $(".ghazale_inquiry_final_cost").html(sum);
            }
            if($(".ghazale-c-onoffswitch-checkbox").eq(index).prop('checked')==false){
                sum -= Number($(".ghazale_frontend_c_cost").eq(index).text());
                $(".ghazale_inquiry_final_cost").html(sum);
            }
        });
    });

    $(".ghazale_inquiry_user_email_field").hide();
    $(".ghazale_inquiry_email_cost_breakdown").click(function(){
        $(".ghazale_inquiry_user_email_field").slideToggle();
    });
});