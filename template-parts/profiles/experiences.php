<?php 
/* Template Part: Guest Profile Experiences 
@author FanXTheme2026
//INFO: All Guest/Feature Experiences featured here. 
//NOTE: Uses the template-parts.css Stylesheet 
//TODO: Create a CSS Grid for easy restructuring. Each eXperience gets it own box. 
//TODO: Update to cater to Postponed related tags ie: Photo Op Swaps/Refunds Available (template-part currently completely hidden) 
*/
?>

<!-- Guest eXperiences --------> 
 <div class="guest-xp-block block">
    <h3 class="guest-xp">GUEST EXPERIENCES:</h3>
    
    <?php 
        // Fetch all needed data once at the top
        $xp = get_field('xp');
        $xp = is_array( $xp ) ? $xp : array();
        $xp_terms = get_the_terms( get_the_ID(), 'xp' ); //eXperience Category
        $xp_status_terms = get_the_terms( get_the_ID(), 'xp-status' ); //XP Status Triggers
        
        // Check for Xperience Categories 
        $has_photo_ops = false;
        $has_autographs = false;
        
        if ( $xp_terms && ! is_wp_error( $xp_terms ) ) {
            foreach ( $xp_terms as $term ) {
                if ( $term->slug === 'photo-ops' ) {
                    $has_photo_ops = true;
                } elseif ( $term->slug === 'autographs' ) {
                    $has_autographs = true;
                }
            }
        }
    ?>
    
    <?php if ( ! $has_photo_ops && ! $has_autographs ) : ?>
        <div class="xp-soon">
           More Info Comiing SOON
        </div>
    <?php else : ?>
    
    <!-- Photo Ops ------------------------------------------------->
        <div class="guest-op-info guest-xp">
            <?php 
                $op_price = $xp['op_price'] ?? ''; //Price
                $op_url = $xp['op_url'] ?? ''; //Outgoing Link - LEAP 
                $is_coming_soon = false;
            
            // Check for coming soon status
            if ( $xp_status_terms && ! is_wp_error( $xp_status_terms ) ) {
                foreach ( $xp_status_terms as $term ) {
                    if ( $term->slug === 'photo-ops-coming-soon' ) { //XP Status - Coming Soon Trigger
                        $is_coming_soon = true;
                        break;
                    }
                }
            }
            
            // Photo Op Status Messages
            if ( $has_photo_ops ) : ?>
                <div class="guest-ops-price">
                    <strong>Photo Ops:</strong> 
                    <?php 
                        if ( $op_price ) { //Price
                            echo esc_html($op_price);
                            if ( $is_coming_soon ) {
                                echo ' - <span class="xp-soon">Available for Pre-Purchase SOON</span>'; //COMING SOON
                            } else { 
                                echo ' - <span class="xp-now">Buy Photo Ops NOW**</span>'; //BUY NOW
                            }
                        } else {
                            echo ' - <span class="xp-soon">More Info Coming Soon*</span>'; //NO PRICE - Coming Soon
                        }
                    ?> 
                </div>
                
                <!-- Photo Op Days/Links -->
                <div class="days guest-xp">
                    <?php 
                    $days_cats = get_the_terms( get_the_ID(), 'days' );
                    
                    if ( ! empty( $days_cats ) && ! is_wp_error( $days_cats ) ) {
                        //Sort by Day Name for correct Appearance Order
                        $order = ['thursday' => 1, 'friday' => 2, 'saturday' => 3, 'sunday' => 4];
                        usort($days_cats, fn($a, $b) => ($order[$a->slug] ?? 99) - ($order[$b->slug] ?? 99));
                        
                        // Map day slugs to ACF field names for photo ops URLs
                        $day_url_map = [
                            'thursday' => 'celeb_op_sun_url',
                            'friday'   => 'celeb_op_fri_url',
                            'saturday' => 'celeb_op_sat_url',
                            'sunday'   => 'celeb_op_sun_url',
                        ];
                        
                        $links = array();
                        foreach ( $days_cats as $cat ) {
                            // Get the URL field for this day
                            $field_name = $day_url_map[$cat->slug] ?? null;
                            $day_url = '';
                            
                            if ( $field_name ) {
                                $day_link = get_field($field_name, 'option');
                                $day_url = is_array($day_link) ? ($day_link['url'] ?? '') : $day_link;
                            }
                            
                            // Build the link with URL if available, otherwise just the text
                            if ( $day_url ) {
                                $links[] = '<a href="' . esc_url($day_url) . '">' . esc_html($cat->name) . '</a>';
                            } else {
                                $links[] = esc_html($cat->name);
                            }
                        }
                        echo implode( ' | ', $links );
                    }
                    ?>
                </div>
                <!-- END Photo Op Days/Links -->
            <?php endif; ?>
        </div>
        <!-- END Photo Ops <--------------------------------------------->

    <!-- Autographs ------------------------------>
            <?php 
            $auto_price = $xp['auto_price'] ?? '';
            $has_autographs = false;
            $has_pre_purchase_autographs = false;
            $is_autographs_coming_soon = false;
            
            // Autographs XP Category Trigger
            if ( $xp_terms && ! is_wp_error( $xp_terms ) ) {
                foreach ( $xp_terms as $term ) {
                    if ( $term->slug === 'autographs' ) {
                        $has_autographs = true;
                        break;
                    }
                }
            }
            // PRE-PURCHASE Autographs XP Status Trigger
            if ( $xp_status_terms && ! is_wp_error( $xp_status_terms ) ) {
                foreach ( $xp_status_terms as $term ) {
                    if ( $term->slug === 'pre-purchase-autographs' ) {
                        $has_pre_purchase_autographs = true;
                        break;
                    } elseif ( $term->slug === 'pre-purchase-autographs-soon' ) { //Autographs Coming Soon Trigger
                        $is_autographs_coming_soon = true;
                        break;
                    }
                }
            } 
            if ( $has_autographs ) : ?>
                <div class="auto-price guest-xp">
                    <strong>Autographs:</strong> 
                        <?php 
                            echo esc_html($auto_price);
                            if ( $is_autographs_coming_soon ) {
                                echo ' - <span class="xp-soon">Available for Pre-Purchase Soon</span>'; //COMING SOON
                            } elseif ( $has_pre_purchase_autographs ) { 
                                $celeb_auto_link = get_field('celeb_auto_url', 'option'); 
                                $link_url = is_array($celeb_auto_link) ? ($celeb_auto_link['url'] ?? '') : $celeb_auto_link;
                                echo ' - <span class="xp-now"><a href="' . esc_url($link_url) . '">Pre-Purchase NOW***</a></span>'; //Pre-Purchase Link
                            } else {
                                echo ' - Available at Event***'; //Available at Event - Default
                            } 
                        ?>
                </div>
            <?php endif; ?>
            <!-- END Autographs -->
            
            <!-- Celebrity Row eXtras --->
            <?php 
            $has_celeb_xtras_purchase_now = false;
            
            // Celebrity Row eXtras XP Status Trigger
            if ( $xp_status_terms && ! is_wp_error( $xp_status_terms ) ) {
                foreach ( $xp_status_terms as $term ) {
                    if ( $term->slug === 'celeb-extras-purchase-now' ) {
                        $has_celeb_xtras_purchase_now = true;
                        break;
                    }
                }
            }
            
            if ( $has_celeb_xtras_purchase_now ) : ?>
                <div class="celeb-xtras guest-xp">
                    <strong>Celebrity Row Extras:</strong> 
                    <span class="xp-now">Available NOW</span>
                    <?php 
                        $celeb_xtras_content = $xp['celeb_extras'] ?? '';
                        if ( $celeb_xtras_content ) {
                            echo '<div class="celeb-extras-content">';
                            echo $celeb_xtras_content;
                            echo '</div>';
                        }
                    ?>
                </div>
            <?php endif; ?>
            <!-- END Celebrity Row eXtras --->
            <!--- Group Photo Ops -->
            <?php
            $group_op_posts = $xp['group_op'] ?? array();
            if ( $group_op_posts ) : ?>
                <div class="grp-ops guest-xp">
                    <strong>Group Photo Ops:</strong>
                    <?php
                    foreach ( (array)$group_op_posts as $post_id ) {
                        $ops_xp = get_field('xp', $post_id);  
                        $price = is_array($ops_xp) ? ($ops_xp['op_price'] ?? '') : ''; //Price
                        $button_url = '';
                        if ( have_rows('button', $post_id) ) {
                            while ( have_rows('button', $post_id) ) {
                                the_row();
                                $button_url = get_sub_field('url');
                                break; // Get first button only
                            }
                        }
                        $title = get_the_title($post_id); //Title
                        ?>
                        <div class="grp-op guest-xp">
                            <?php echo esc_html($title); ?> - 
                            <?php echo esc_html($price); ?>
                            <?php if ($button_url) { ?>
                                - <span class="xp-now"><a href="<?php echo esc_url($button_url); ?>">Buy Group Ops NOW</a></span>
                            <?php } ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            <?php endif; ?>
            <!-- END Group Photo Ops --->
            
            <!-- Panel Programming(Panels)--->
                <div class="panels guest-xp">
                </div>
            <!--- END Panel Programming -->

            <!-- Vendor Floor/Programming Location --> 
             <div class="floor-loca guest-xp"> 
                    
                </div>
            <!-- END Vendor Floor/Programming Location -->            


            <!-- END Group Photo Ops -->
    <?php endif; // End xp_terms check ?>
</div><!-- END Guest eXperiences Block -->

        