<?php 
/*Sub-Menu Template Part
@package FanXTheme2026
Classes used: block, container, hor-nav, sub-menu, sub-menu-section, self-centered-row
*/
?> 

<!--------------- Sub-Menu ------------->
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
                $post_type = get_post_type();
                $is_category = is_category();

                // Custom Post Types
                if ( $post_type === 'guests' ) {
                    wp_nav_menu( array(
                        'theme_location' => 'guests',
                        'menu_class' => 'horizontal-menu',
                        'container' => false,
                        'walker' => new Child_Only_Walker(),
                        'depth' => 2
                    ) );
                } elseif ( $post_type === 'features' ) {
                    wp_nav_menu( array(
                        'theme_location' => 'feature',
                        'menu_class' => 'horizontal-menu',
                        'container' => false,
                        'walker' => new Child_Only_Walker(),
                        'depth' => 2
                    ) );
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
                        wp_nav_menu( array(
                            'theme_location' => $category_menus[$parent_slug],
                            'menu_class' => 'horizontal-menu',
                            'container' => false,
                            'walker' => new Child_Only_Walker(),
                            'depth' => 2
                        ) );
                    }
                } else {
                    // Fallback only if nothing else matched
                    wp_nav_menu( array(
                        'theme_location' => 'experiences',
                        'menu_class' => 'horizontal-menu',
                        'container' => false,
                        'walker' => new Child_Only_Walker(),
                        'depth' => 2
                    ) );
                }
                ?>
        </div><!-- END hor-Nav Block -->
    </div><!-- END Sub-Menu block -->
</div><!--- END Sub-Menu-Section -->

