<?php
session_start();
function ghazale_inquiry_update_message()
{
    if (isset($_SESSION['message'])) {
        $output = $_SESSION['message'];

        //clear message after use
        $_SESSION['message'] = null;
        return $output;
    }

    if (isset($_SESSION['message_frontend'])) {
        $output = $_SESSION['message_frontend'];

        //clear message after use
        $_SESSION['message_frontend'] = null;
        return $output;
    }
}