<?php 
/** Basic Layout Template Part: Content 1 Full 
 * Used in: Category ADA Page
*/

?>

<!---- Content 1 Full Section Layout --->

<div class="content-1-full self-centered-column">

    <!-- Content Block 1 ---------------------->
        <div class="self-centered-column block">
            <h2 style="text-align: center;">
            <?php 
                // Get term ID once for reuse
                $term_id = get_queried_object_id();
                //Content Block 1 - Title
                echo get_field( 'feat_con_title_1', 'term_' . $term_id );
                ?></h2>
            <h3 style="text-align: center;">   
            <?php //Content Block 1 - Subtext
                echo get_field('feat_con_subtext_1', 'term_' . $term_id);
                ?></h3>
            <p>   
            <?php //Content Block 1 - Content Text
                echo get_field( 'feat_con_content_1', 'term_' . $term_id);
                ?></p>
                
        </div><!-- END Content Block 1 -------------------------->

</div><!-- END Content 1 Full Section Layout -->