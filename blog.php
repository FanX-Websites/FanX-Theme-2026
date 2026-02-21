<?php
// Blog Template - Forwards Blog Page to Updates & Announcements Category

if (is_home()) {
    wp_redirect(home_url('/blog/event-updates-announcements/'));
    exit;
}
?>