<?php 
/** Functions File: ACF Icon Field Google Icons Add-On 
 * 
 * //NOTE: The icon definitions are hardcoded directly in the function, making it difficult to add, remove, or modify icons without editing this file. 
 * //TODO: Move the icon definitions to a separate configuration array or JSON file that can be easily maintained to allow non-developers to update icons and improve code maintainability.
*/


//Google Icon Picker Tab 
function add_google_icon_picker_tab( array $tabs ): array {
    $tabs['google'] = 'Google';
    return $tabs;
}
add_filter( 'acf/fields/icon_picker/tabs', 'add_google_icon_picker_tab' );


//Google Icons List
function add_google_icons( array $icons ): array {
    $base_url = get_template_directory_uri() . '/acf/fields/icon_picker/google/icons/'; // Base URL for the icons

    return array_merge(
        $icons,
        array(
        
        //Partners/Participants 
            array(
                'url'   => $base_url . 'heart_smile.svg', // Heart Smile 
                'key'   => 'heart-smile', 
                'label' => 'Heart Smile', 
            ),
        //eXperiences 
            array(
                'url'   => $base_url . 'shopping_bag.svg', //Shopping Bag 
                'key'   => 'shopping-bag',
                'label' => 'Shopping Bag',
            ),
        //Products 
            array(
                'url'   => $base_url . 'local_activity.svg', //Local Acitvity 
                'key'   => 'local-activity',
                'label' => 'Local Activity',
            ),
            array(
                'url'   => $base_url . 'photo_camera.svg', // Photo Camera 
                'key'   => 'photo-camera',
                'label' => 'Photo Camera',
            ),
        )
    );
}
add_filter( 'acf/fields/icon_picker/google/icons', 'add_google_icons' );


//Social Icon Picker Tab 
function add_social_icon_picker_tab( array $tabs ): array {
    $tabs['social'] = 'Social';
    return $tabs;
}
add_filter( 'acf/fields/icon_picker/tabs', 'add_social_icon_picker_tab' );

//Social Icons List
function add_social_icons( array $icons ): array {
    $base_url = get_template_directory_uri() . '/acf/fields/icon_picker/social/icons/'; // Base URL for the icons

    return array_merge(
        $icons,
        array(
        
            array(
                'url'   => $base_url . 'icon_one.svg',
                'key'   => 'icon-one', 
                'label' => 'Icon One', 
            ),
        )
    );
}

add_filter( 'acf/fields/icon_picker/social/icons', 'add_social_icons' );




?>