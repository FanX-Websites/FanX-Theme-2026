<?php 
// Featured Video Block for the Front Page 

?> 

<!-- Featured Video --> 

    <div class="feat-vid">
        <div class="resp-embed">
        <?php 
            $iframe = get_field('info_feat_vid'); 
            //iframe src
            preg_match('/src="(.+?)"/', $iframe, $matches);
            $src = $matches[1];
            //Parameters
            $params = array(
                'controls'  => 1,
                'hd'        => 1,
                'autohide'  => 1
            );
            $new_src = add_query_arg($params, $src);
            $iframe = str_replace($src, $new_src, $iframe);
            //Atributes 
            $attributes = 'frameborder="0"';
                $iframe = str_replace('>
                </iframe>', ' ' . $attributes . '></iframe>', $iframe);

            // Display customized HTML.
            echo $iframe;
        ?>
        </div><!-- END Responsive Embed-->
    </div><!-- End Feat Vid -->


<style>

    .feat-vid{
        width: 60%; 
        height: 65%;
        position: absolute;
        top: 0; 
        left: 0;
    }

    .resp-embed{
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .resp-embed iframe{
        width: 100%; 
        height: 100%; 
        display: block;
        border: none;
    }

</style>