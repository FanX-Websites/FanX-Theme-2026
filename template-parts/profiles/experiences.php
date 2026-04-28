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
                                //FIXME: Temporary fix until shortcut link issue is resolved
                                // Determine Photo Ops URL based on current site
                                $site_url = home_url();
                                $photo_ops_url = '';
                                
                                if ( strpos( $site_url, 'fanxsaltlake' ) !== false ) {
                                    $photo_ops_url = 'https://checkout.conventions.leapevent.tech/eh/FanX_Salt_Lake_Comic_Convention_2026/56010?cc=ops';
                                } elseif ( strpos( $site_url, 'indianacomicconvention' ) !== false ) {
                                    $photo_ops_url = 'https://checkout.conventions.leapevent.tech/eh/Indiana_Comic_Convention_2026/55749?cc=ops';
                                } elseif ( strpos( $site_url, 'tampabaycomicconvention' ) !== false ) {
                                    $photo_ops_url = 'https://checkout.conventions.leapevent.tech/eh/Tampa_Bay_Comic_Convention_2026/55768?cc=ops';
                                } else {
                                    $photo_ops_url = $op_url; // Fallback to custom field
                                }
                                
                                echo ' - <span class="xp-now"><a href="' . esc_url($photo_ops_url) . '">Buy Photo Ops NOW**</a></span>'; //BUY NOW
                            }
                        } else {
                            echo ' - <span class="xp-soon">More Info Coming Soon*</span>'; //NO PRICE - Coming Soon
                        }
                    ?> 
                </div>
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

                <div class="grp-ops guest-xp"> 
                    
                </div>
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

        