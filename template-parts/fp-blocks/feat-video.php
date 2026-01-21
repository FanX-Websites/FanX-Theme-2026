<?php 
// Featured Video Block for the Front Page 

?> 

<!-- Featured Video --> 

    <div class="feat-vid-fp-block">
        <div class="resp-embed">
        <?php 
            $iframe = get_field('vid_featured'); //Featured Video - time sensitive
            
            // Use backup video if featured is empty
            if (empty($iframe)) {
                $iframe = get_field('vid_backup'); //Backup Video - Main Highlight reel
            }

            if (!empty($iframe)) {
                //iframe src - extract src from iframe HTML
                preg_match('/src=["\']([^"\']+)["\']/', $iframe, $matches);
                $raw_src = isset($matches[1]) ? $matches[1] : '';
                

                $validated_src = wp_http_validate_url( $raw_src );
                
                $allowed_hosts = array(
                    'cloudflarestream.com',
                );
                $src = '';
                if ( $validated_src ) {
                    $host = parse_url( $validated_src, PHP_URL_HOST );
                    
                    $is_allowed = false;
                    foreach ( $allowed_hosts as $allowed_host ) {
                        if ( $host === $allowed_host || strpos( $host, '.' . $allowed_host ) !== false ) {
                            $is_allowed = true;
                            break;
                        }
                    }
                    
                    if ( $is_allowed ) {
                        $src = $validated_src;
                    }
                }
                if ( $src ) {
                    //Parameters
                    $params = array(
                        'controls'  => 1,
                        'hd'        => 1,
                        'autohide'  => 1,
                        'autoplay'  => 1,
                        'muted'     => 1,
                        'loop'      => 1,
                    );
                    $new_src = add_query_arg($params, $src);
                    
                    // Build iframe from scratch with validated URL
                    $safe_iframe = '<iframe src="' . esc_url($new_src) . '" width="100%" height="100%" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                    echo $safe_iframe;
                } else {
                    // If the src is not a valid, allowed URL, do not render the iframe.
                    echo '<p class="no-video-alert">The featured video is shy. <br/> It will be out to play soon.</p>';
                }
            } else {
                // Optional: fallback message when no featured video is set.
                echo '<p class="no-video-alert">The featured video is shy. <br/> It will be out to play soon.</p>';
            }
        ?>
        </div><!-- END Responsive Embed-->
    </div><!-- End Feat Vid -->


<style>

    .feat-vid-fp-block{/** The Block size & positioning in Parent Div*/
        background-color: var(--color_drk);
        margin: 0; 
        padding: 0;
        overflow: hidden;
        width: 100%;
        position: relative;
        aspect-ratio: 16 / 9;
    }
    .resp-embed{
        width: 100%;
        height: 100%;
        margin: 0; 
        padding: 0;
    }
    
    .resp-embed iframe {
        width: 100%;
        height: 100%;
    }

    .no-video-alert{
        color: var(--color_lght);
        font-size: 1.2rem;
        text-align: center;
        padding: 2rem;
    }

</style>