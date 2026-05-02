/**
 * Template Part: Double Buttons - View Profile | Button Repeater (e.g. Buy Photo Ops)
//TODO: Update from Photo Op specific metrics to Profile/Button Repeater metrics to use across multiple templates 
 */

<!-- Footer Buttons Button Group -->
                <footer class="entry-footer">
                    <div class="button-group">
                        <!-- View Profile Button -->
                        <a href="<?php the_permalink(); ?>" class="button button-left">
                            View Profile
                        </a>
                        
                        <!-- Buy Photo Ops Button //FIXME: Update to use Button Repeater metrics -->
                        <?php if ( $has_photo_ops && $op_price ) : ?>
                            <?php 
                                // Get Photo Ops URL from options page
                                $celeb_op_link = get_field('celeb_op_fri_url', 'option'); 
                                $photo_ops_url = is_array($celeb_op_link) ? ($celeb_op_link['url'] ?? '') : $celeb_op_link;
                                
                                if ( $photo_ops_url ) {
                                    $button_text = $is_coming_soon ? 'Coming Soon' : 'BUY NOW';
                                    ?>
                                    <a href="<?php echo esc_url($photo_ops_url); ?>" class="button button-right" target="_blank">
                                        <?php echo $button_text; ?>
                                    </a>
                                    <?php
                                }
                            ?>
                        <?php endif; ?>
                    </div><!-- END Button Group -->
                </footer>
                <!-- END Footer Buttons -->