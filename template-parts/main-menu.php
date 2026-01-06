<?php 
/** Template Part: Main Menu & Logo 
 * Displays the Main Navigation Menu and Site Logo
 * @package FanXTheme2026
 *
 * 
 * Used in: header.php
 * 
 * Classes used: menu-logo, menu-logo-mobile, menu-toggle-container, main-menu-nav, main-nav, dropdown-menu, menu-description
 */
?>


<!-- Site Logo - Mobile -->
 <div class="menu-logo-mobile-sec">
            <a href="/" class="menu-logo-mobile">
                <img src="<?php 
                    $logo = get_field('event_logo','option');
                    echo esc_url($logo['url'] ?? get_site_icon_url());
                ?>" alt="<?php bloginfo('name'); ?> Logo">
            </a>
    </div><!-- END menu-logo-mobile-sec --> 
<!--- END Site Logo - Mobile -->  

<!--- Menu Toggle -->  
<div class="menu-toggle-container"> 
        <button class="menu-toggle">
                MENU ▼
        </button>
    
    <div class="main-menu-nav">
        <nav class="main-nav" role="navigation"> 
            
            <!-- Site Logo - Desktop -->
                <a href="/" class="menu-logo">
                    <img src="<?php 
                        $logo = get_field('event_logo','option');
                        echo esc_url($logo['url'] ?? get_site_icon_url());
                    ?>" alt="<?php bloginfo('name'); ?> Logo">
                </a>
            <!--- END Site Logo - Desktop-->
                
            <?php 
            //Header Navigation - Main Menu
            $menus = array(
                'products' => 'Purchase', //Tickets & Photo Ops
                'event-info' => 'Event Info', //Event Info, Schedule, etc
                'guests' => 'Guests', //Guest LIsts
                'experiences' => 'eXperiences',//experience pages 
                'hoteltravel' => 'Hotel & Travel', //Hotels & Travel Info
                'participate' => 'Participate', //Exhibitors & Panelists, etc 
                'updates' => 'Updates', //Updates & Announcements 
                'about' => 'About' //Company & Team info 
            );

            foreach( $menus as $location => $label ) {
                echo '<div class="menu-container">';
                wp_nav_menu( array(
                    'theme_location' => $location,
                    'menu_class' => 'dropdown-menu', 
                    'container' => false,
                    'walker' => new Description_Walker(), 
                    'fallback_cb' => false, 
                ));
                echo '</div>';
            }
            
            //Menu item Description 
            class Description_Walker extends Walker_Nav_Menu {
                function start_el( &$output, 
                    $item, $depth = 0, 
                    $args = null, 
                    $id = 0 ) {
                        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
                        $li_open = '<li>';
                        $li_close = '</li>';
                        $a_open = '<a href="' . esc_url( $item->url ) . '">';
                        $a_close = '</a>';
                        
                        $title = apply_filters( 'nav_menu_item_title', 
                        $item->title, $item, $args, $depth );
                        $description = $item->description ? 
                        ' <span class="menu-description">' . 
                        $item->description . '</span>' : '';

                        $output .= $indent 
                        . $li_open 
                        . $a_open 
                        . $title 
                        . $description 
                        . $a_close;
                    }
                
                function end_el( &$output, 
                    $item, 
                    $depth = 0, 
                    $args = null ) 
                    {
                    $output .= "</li>\n";
                    }
            }
            ?>

        </nav><!-- END Main Nav -->
    </div><!-- END Main Menu Nav -->
</div><!-- Menu Toggle (Mobile) -->