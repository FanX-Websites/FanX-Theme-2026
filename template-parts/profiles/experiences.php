<?php 
/* Template Part: Guest Profile Experiences 
@author FanXTheme2026
//INFO: All Guest/Feature Experiences featured here. 
//NOTE: Uses the template-parts.css Stylesheet 
//TODO: Create a CSS Grid for easy restructuring. Each eXperience gets it own box. 
//TODO: Update to cater to Postponed related tags ie: Photo Op Swaps/Refunds Available (template-part currently completely hidden) 
*/
?>

<!-- Guest eXperiences Details - MAIN DIV -------->
<div class="guest-xp-details"> 
    <?php 
        // Fetch all needed data once at the top
        $xp = get_field('xp');
        $xp = is_array( $xp ) ? $xp : array();
        $xp_terms = get_the_terms( get_the_ID(), 'xp' ); //eXperience Category
        $xp_status_terms = get_the_terms( get_the_ID(), 'xp-status' ); //XP Status Triggers
        
        // Fetch shared product category link (used for photo ops, autographs, and celeb extras)
        $ded_prod_cat_link = $xp['ded_prod_cat'] ?? '';
        $shared_link_url = '';
        if ( $ded_prod_cat_link ) {
            $shared_link_url = is_array($ded_prod_cat_link) ? ($ded_prod_cat_link['url'] ?? '') : $ded_prod_cat_link;
        }
        
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
        
        // Count experience blocks to determine layout
        $xp_block_count = 0;
        
        if ( $has_photo_ops ) {
            $xp_block_count++;
        }
        if ( $has_autographs ) {
            $xp_block_count++;
        }
        
        // Check for celeb extras
        $has_celeb_xtras = false;
        if ( $xp_status_terms && ! is_wp_error( $xp_status_terms ) ) {
            foreach ( $xp_status_terms as $term ) {
                if ( $term->slug === 'celeb-extras-purchase-now' || $term->slug === 'celeb-extras-onsite' ) {
                    $has_celeb_xtras = true;
                    break;
                }
            }
        }
        if ( $has_celeb_xtras ) {
            $xp_block_count++;
        }
        
        // Check for group photo ops
        $group_op_posts = $xp['group_op'] ?? array();
        if ( $group_op_posts ) {
            $xp_block_count++;
        }
        
        // Check for vendor booths
        $vend_booth_posts = $xp['vend_booth'] ?? array();
        if ( $vend_booth_posts ) {
            $xp_block_count++;
        }
        
        // // Determine layout class based on block count
        // $xp_layout_class = 'layout-4col'; // Default to 4 columns
        // if ( $xp_block_count === 1 ) {
        //     $xp_layout_class = 'layout-1col';
        // } elseif ( $xp_block_count === 2 ) {
        //     $xp_layout_class = 'layout-2col';
        // } elseif ( $xp_block_count === 3 ) {
        //     $xp_layout_class = 'layout-3col';
        // }
    ?>
    
    <!-- Guest eXperiences - Grid Container -->
    <div class="guest-xp-card grid-container <?php echo $xp_layout_class; ?>">
        
        <?php if ( $xp_block_count === 0 ) : ?>
            <div class="xp-soon">
                <p>More Info Coming Soon.</p>
            </div>
        <?php endif; ?>
            
            <?php if ( $has_photo_ops ) : ?>

            <div class="grid-block xp-block">  
            <!-- Photo Ops ------------------------------------------------->
                <div class="guest-op-info">
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
                    ?>
                    
                    <!-- Photo Op Status Messages -->
                    <h5 class="xp-title">Photo Ops</h5>
                        
                        <div class="guest-ops-price">
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
                        
                        <!-- Photo Op Days/Links ------------------------>
                        <div class="days guest-xp">
                            <?php 
                            $days_cats = get_the_terms( get_the_ID(), 'days' );
                            
                            if ( ! empty( $days_cats ) && ! is_wp_error( $days_cats ) ) {
                                //Sort by Day Name for correct Appearance Order
                                $order = ['thursday' => 1, 'friday' => 2, 'saturday' => 3, 'sunday' => 4];
                                usort($days_cats, fn($a, $b) => ($order[$a->slug] ?? 99) - ($order[$b->slug] ?? 99));
                                
                                // If shared product link exists, use it for all days
                                if ( $shared_link_url ) {
                                    $day_names = array();
                                    foreach ( $days_cats as $cat ) {
                                        $day_names[] = esc_html($cat->name);
                                    }
                                    echo '<a href="' . esc_url($shared_link_url) . '">' . implode( ' | ', $day_names ) . '</a>';
                                } else {
                                    // Original behavior: individual day URLs
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
                            }
                            ?>
                        </div>
                        <!-- END Photo Op Days/Links ------------------------>
                </div>
                <!-- END Photo Ops <------------------------------------------------------->

            </div><!-- END Grid Block 1 -->
            <?php endif; ?>
            
            <?php
            // COMMENTED OUT - $has_autographs already calculated at top of template for block counting
            // Calculate $has_autographs for the conditional wrap
            // $has_autographs = false;
            // if ( $xp_terms && ! is_wp_error( $xp_terms ) ) {
            //     foreach ( $xp_terms as $term ) {
            //         if ( $term->slug === 'autographs' ) {
            //             $has_autographs = true;
            //             break;
            //         }
            //     }
            // }
            ?>
            
            <?php if ( $has_autographs ) : ?>

            <div class="grid-block xp-block">
            <!-- Autographs -------------------------------------------------->
                <div class="guest-auto-info">
                    <?php 
                    $auto_price = $xp['auto_price'] ?? '';
                    $has_pre_purchase_autographs = false;
                    $is_autographs_coming_soon = false;
                    
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
                    ?>
                        <h4 class="xp-title">Autographs</h4> <!------ TITLE ------> 
                        
                        <div class="auto-price guest-xp">
                            <?php echo esc_html($auto_price); ?> <!---- PRICE  ---->
                        </div>
                        
                        <div class="auto-status guest-xp">
                            <?php 
                                    if ( $is_autographs_coming_soon ) {
                                        echo '<span class="xp-soon">Available for Pre-Purchase Soon</span>'; //COMING SOON
                                    } elseif ( $has_pre_purchase_autographs ) {
                                        // Use shared product link if available, otherwise use individual autograph link
                                        if ( $shared_link_url ) {
                                            echo '<span class="xp-now"><a href="' . esc_url($shared_link_url) . '">Pre-Purchase NOW***</a></span>'; //Pre-Purchase Link (shared)
                                        } else {
                                            $celeb_auto_link = get_field('celeb_auto_url', 'option'); 
                                            $link_url = is_array($celeb_auto_link) ? ($celeb_auto_link['url'] ?? '') : $celeb_auto_link;
                                            echo '<span class="xp-now"><a href="' . esc_url($link_url) . '">Pre-Purchase NOW***</a></span>'; //Pre-Purchase Link
                                        }
                                    } else {
                                        echo '<span class="xp-now">Available at Event***</span>'; //Available at Event - Default
                                    } 
                                ?>
                        </div>
                        <!-- END Autographs ---------->
                </div>
            </div><!-- END Grid Block 2 -->
            <?php endif; ?>
            
            <?php
            // Calculate celeb extras conditions
            $has_celeb_xtras_purchase_now = false;
            $has_celeb_xtras_onsite = false;
            
            if ( $xp_status_terms && ! is_wp_error( $xp_status_terms ) ) {
                foreach ( $xp_status_terms as $term ) {
                    if ( $term->slug === 'celeb-extras-purchase-now' ) {
                        $has_celeb_xtras_purchase_now = true;
                        break;
                    } elseif ( $term->slug === 'celeb-extras-onsite' ) {
                        $has_celeb_xtras_onsite = true;
                        break;
                    }
                }
            }
            ?>
            
            <?php if ( $has_celeb_xtras_purchase_now || $has_celeb_xtras_onsite ) : ?>

            <div class="grid-block xp-block">
            <!-- Celebrity Row eXtras --->
                <div class="celeb-xtra-info">
                        <h4 class="xp-title">Celebrity Row Extras</h4>
                        
                        <div class="celeb-status guest-xp">
                            <?php if ( $has_celeb_xtras_purchase_now ) : ?>
                                <?php if ( $shared_link_url ) : ?>
                                    <span class="xp-now"><a href="<?php echo esc_url($shared_link_url); ?>">Available NOW</a></span>
                                <?php else : ?>
                                    <span class="xp-now">Available NOW</span>
                                <?php endif; ?>
                            <?php elseif ( $has_celeb_xtras_onsite ) : ?>
                                <span class="xp-now">Available onsite</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php 
                            $celeb_xtras_content = $xp['celeb_extras'] ?? '';
                            if ( $celeb_xtras_content ) {
                                echo '<div class="celeb-extras-content guest-xp">';
                                echo $celeb_xtras_content;
                                echo '</div>';
                            }
                        ?>
                </div>   
            </div><!-- END Grid Block 3 -->
            <?php endif; ?>

            <?php
            $group_op_posts = $xp['group_op'] ?? array();
            ?>
            
            <?php if ( $group_op_posts ) : ?>

            <div class="grid-block xp-block">
            <!--- Group Photo Ops -->
                <div class="group-op-info">
                            <h4 class="xp-title">Group Photo Ops</h4>
                            
                            <div class="grp-ops guest-xp">
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
                                            <span class="xp-now"><a href="<?php echo esc_url($button_url); ?>">Buy Group Ops NOW</a></span>
                                            
                                        <?php } ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                </div>
            <!-- END Group Photo Ops --->
            </div><!-- END Grid Block 4 -->
            <?php endif; ?>
            
            <!-- Vendor Booths --->
            <?php if ( $vend_booth_posts ) : ?>

            <div class="grid-block xp-block">  
                <div class="vend-booth-info">
                            <h4 class="xp-title">Featured at: </h4>
                            
                            <div class="vend-booths guest-xp">
                                <?php
                                foreach ( (array)$vend_booth_posts as $post_id ) {
                                    $title = get_the_title($post_id); //Title
                                    $post_url = get_permalink($post_id); //Post URL
                                    $sched = get_field('sched', $post_id);
                                    $room_booth = is_array($sched) ? ($sched['room_booth'] ?? '') : ''; //Room/Booth
                                ?>
                                    <div class="vend-booth guest-xp">
                                        <h3><a href="<?php echo esc_url($post_url); ?>"><?php echo esc_html($title); ?></a></h3>
                                        <?php 
                                            if ( $room_booth ) {
                                                echo esc_html($room_booth);
                                            } 
                                        ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                </div>
            <!-- END Vendor Booths --->
            </div><!-- END Grid Block 5 -->
            <?php endif; ?>
                        
            <!-- Panel Programming(Panels)--->
                <!-- <h4 class="xp-title">Panels</h4>
                <div class="panels guest-xp">
                </div> -->
            <!--- END Panel Programming -->

            <!-- Vendor Floor/Programming Location -->
                <!-- <h4 class="xp-title">Floor Location</h4>
                <div class="floor-loca guest-xp"></div> -->
            <!-- END Vendor Floor/Programming Location -->            

        </div><!-- END Grid Container -->
</div><!-- END Guest eXperiences Block -->

</div><!-- END Guest XP Details - MAIN DIV -->