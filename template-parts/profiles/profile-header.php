<?php 
/** Template Part: Profile Header  
 * 
 * 
 * 
*/
?>

<div class="self-centered-inside block"><!-- Self Centered Container for Profile Header & Submenu -->
    <!--  Profile Header -->
    <div class="profile-header block"> 
        
        <!-- Submenu --- [Template Part] -->
        <div class="container">
            <?php get_template_part('template-parts/sub-menu'); ?>
        </div><!-- END Submenu -->

        <!-- Main Category --->
        <h1>
            <?php 
                $categories = get_the_category(); //Main Category 
                if ( ! empty( $categories ) ) { 
                    foreach ( $categories as $category ) {
                        echo '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a> ';
                    }
                }
            ?><!-- END Main Category -->

        </h1><!-- Profile Header Text --> 
        
        <!-- Blog Category Subtitle (Posts Only) --->
    <h2>    
        <?php 
            if ( get_post_type() === 'post' ) {
                $blog_cats = get_the_terms( get_the_ID(), 'blog' );
                if ( $blog_cats && ! is_wp_error( $blog_cats ) ) {
                    
                    foreach ( $blog_cats as $blog_cat ) {
                        echo '<a href="' . esc_url( get_term_link( $blog_cat->term_id, 'blog' ) ) . '">' . esc_html( $blog_cat->name ) . '</a> ';
                    }
                   
                }
            }
        ?>
    </h2>
        <!-- END Blog Category Subtitle -->
    </div><!--END Profile Header -->
</div><!-- END Self Centered -->