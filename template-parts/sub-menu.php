<?php 
/* Template Part: Sub-Menu
 * Description: Displays the sub-menu based on the current post type or category.
 * Used in: header.php (or wherever the sub-menu is needed)
 * 
 * Logic:
 * - Checks the current post type and category to determine which menu to display.
 * - Uses a custom walker (Child_Only_Walker) to display only child menu items.
 * - Fallbacks to the 'experiences' menu if no specific menu matches.
 * 
 * Note: Ensure that the corresponding menus are created in the WordPress admin and assigned to the correct theme locations.
 * Classes used: block, container, hor-nav, sub-menu, sub-menu-section, self-centered-row
*/
?> 

<!--------------- Sub-Menu --------------->
    <!-- Menu Children ONLY --->
        <?php
        class Child_Only_Walker extends Walker_Nav_Menu {
             function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
                // Skip parent items (depth 0)
                if ( $depth === 0 ) {
                    return;
                }
                // Otherwise display normally
                parent::start_el( $output, $item, $depth, $args, $id );
                }
            }   
            ?>
    <!-- END Menu Children ONLY -->

<!------------ Sub-Menu Section----------->
 <div class="sub-menu-section self-centered-row ">
    <div class="sub-menu block">
        <div class="hor-nav block"><!-- Horizontal Navigation---------->
            <?php 
                // Helper function to check if a menu has submenu items
                function menu_has_submenus( $menu_location ) {
                    $menu_items = wp_get_nav_menu_items( $menu_location );
                    if ( empty( $menu_items ) ) {
                        return false;
                    }
                    foreach ( $menu_items as $item ) {
                        if ( $item->menu_item_parent != 0 ) {
                            return true;
                        }
                    }
                    return false;
                }

                $post_type = get_post_type();
                $is_category = is_category();

                // Custom Post Types
                if ( $post_type === 'guests' ) {
                    $menu_args = array(
                        'theme_location' => 'guests',
                        'menu_class' => 'horizontal-menu',
                        'container' => false,
                        'depth' => 2
                    );
                    if ( menu_has_submenus( 'guests' ) ) {
                        $menu_args['walker'] = new Child_Only_Walker();
                    }
                    wp_nav_menu( $menu_args );
                } elseif ( $post_type === 'features' ) {
                    $menu_args = array(
                        'theme_location' => 'feature',
                        'menu_class' => 'horizontal-menu',
                        'container' => false,
                        'depth' => 2
                    );
                    if ( menu_has_submenus( 'feature' ) ) {
                        $menu_args['walker'] = new Child_Only_Walker();
                    }
                    wp_nav_menu( $menu_args );
                } elseif ( $is_category ) {
                    // Category menus (check before fallback)
                    $cat = get_queried_object();
                    
                    if ( $cat->parent !== 0 ) {
                        $parent_cat = get_term( $cat->parent, 'category' );
                        $parent_slug = $parent_cat->slug;
                    } else {
                        $parent_slug = $cat->slug;
                    }
                    
                    $category_menus = array(
                        'guests' => 'guests',
                        'features' => 'features'
                    );
                    
                    if ( isset( $category_menus[$parent_slug] ) ) {
                        $menu_location = $category_menus[$parent_slug];
                        $menu_args = array(
                            'theme_location' => $menu_location,
                            'menu_class' => 'horizontal-menu',
                            'container' => false,
                            'depth' => 2
                        );
                        if ( menu_has_submenus( $menu_location ) ) {
                            $menu_args['walker'] = new Child_Only_Walker();
                        }
                        wp_nav_menu( $menu_args );
                    }
                } else {
                    // Fallback only if nothing else matched
                    $menu_args = array(
                        'theme_location' => 'experiences',
                        'menu_class' => 'horizontal-menu',
                        'container' => false,
                        'depth' => 2
                    );
                    if ( menu_has_submenus( 'experiences' ) ) {
                        $menu_args['walker'] = new Child_Only_Walker();
                    }
                    wp_nav_menu( $menu_args );
                }
                ?>
        </div><!-- END hor-Nav Block -->
    </div><!-- END Sub-Menu block -->
</div><!--- END Sub-Menu-Section -->

