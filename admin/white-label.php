<?php


//Footer Text
function et_change_admin_footer_text () {
 return __("Powered by Dan Farr Productions. 
 The FanX [year] Theme was designed & coded with ♥ by <a href='https://dev.dancingfraxinus.com/'>Elizabeth Moore</a>.");
}
add_filter( 'admin_footer_text', 'et_change_admin_footer_text' );


//Use When Needed:
    remove_action('shutdown', 'wp_ob_end_flush_all', 1);  //Flush error
    flush_rewrite_rules(); //Flush Rules