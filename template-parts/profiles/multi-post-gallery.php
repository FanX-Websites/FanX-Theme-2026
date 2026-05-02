<?php 
/** Template Part: Multi-post Gallery 
 * 
 * Templates used in: Default Profiles (single.php)
 */
?> 

<?php
$post_ids = get_field('gal_multi_post_ids');

if( $post_ids ) :  //IF Gallery links to any Posts - THEN display gallery - ELSE skip entire section
?> 
<!-- Multi-Post Gallery TITLE --->
    <div class= "multi-post-gallery title">
        <?php echo get_field('gal_multi_post_title'); ?>
    </div>

<!-- Multi-Post Gallery Subtitle --->
    <div class= "multi-post-gallery subtitle">
        <?php echo  get_field('gal_multi_post_sub'); ?>
    </div>

<!-- Multi-Post Gallery Content --->
    <div class= "multi-post-gallery posts">
        <?php 
        foreach( (array)$post_ids as $post_id ) {
            ?>
            <div class="gallery-post"><!-- Featured Post -->
                <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
                    <?php if( has_post_thumbnail( $post_id ) ) : ?>
                        <div class="gallery-post feat-img"><!-- Featured Image -->
                            <?php echo get_the_post_thumbnail( $post_id, 'medium' ); ?>
                        </div>
                    <?php endif; ?>
                    <div><!-- Featured Post Title -->
                        <?php echo esc_html( get_the_title( $post_id ) ); ?>
                    </div>
                </a>
            </div>
            <?php
        }
        ?>
    </div><!-- END Multi-Post Gallery Content -->
<?php 
endif;