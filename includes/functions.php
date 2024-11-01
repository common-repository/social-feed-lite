<?php 
add_action('init', 'social_feed_lite_code');
function social_feed_lite_code(){

	if( is_user_logged_in() ){
			
		$current_user = wp_get_current_user();

		if( user_can( $current_user, 'administrator' ) ) {

			// Handle data after authentication and save access token
			$data = isset($_GET['data']) && !empty($_GET['data']) ? array_map( 'sanitize_text_field', $_GET['data'] ) : '';

			if( !empty($data) ){  

				if( !isset($data['error']) && !empty($data['social_feed_lite_token']) && !empty($data['social_feed_lite_token_long']) ){

					update_option('social_feed_lite_token_long', $data['social_feed_lite_token']);
		    		update_option('social_feed_lite_token', $data['social_feed_lite_token_long']);
				}else{
					esc_html_e('Invalid API Keys', social_feed_lite_domain);
				}

				// Redirect to admin page
		        header("Location: " . admin_url('admin.php?page=social_feed_lite-settings'));
		        exit();
			}else{	

				// Check whether token is expired, if yes regenerate
				$user_media = social_feed_lite_get_media();

				if( isset($user_media->error) && $user_media->error->type == 'OAuthException' ){

				        // URL from instagram API for generating code which will be used to generate token and user id
				        $url = 'https://plugins.wphelpline.com/instagram-auth.php?state='.site_url();

				        // Redirect to authentication
				        header('Location: ' . $url);
				        exit();

				}
			}
		}
	}

}

add_action( 'admin_enqueue_scripts', 'social_feed_lite_enqueue_admin_script' );
function social_feed_lite_enqueue_admin_script( $hook ) {

	if( !in_array($hook, array('social-feed-lite_page_social_feed_lite-info', 'toplevel_page_social_feed_lite-settings')) ){
    	return;
	}


    wp_enqueue_style( 'social-feed-lite-admin-css', social_feed_lite_url . '/admin/css/admin.css', array(), '1.0' );
}

function social_feed_lite_get_media(){

	$social_feed_lite_token = get_option('social_feed_lite_token_long');

	$url = "https://graph.instagram.com/me/media?fields=id,media_url,media_type,permalink,thumbnail_url&access_token=" . $social_feed_lite_token;

	$resp 			= wp_remote_get($url);
	$responseBody 	= wp_remote_retrieve_body( $resp );
	$result 		= json_decode( $responseBody );

	return $result;
}

add_shortcode('social_feed_lite', 'social_feed_lite_cb');
function social_feed_lite_cb(){

	$response = '';
	$il_info = get_option('il_info');
	$numberposts = isset($il_info['numberposts']) && ! empty($il_info['numberposts']) ? esc_attr($il_info['numberposts']) : 10;

	ob_start();

		// Get user media from access token
        $user_media = social_feed_lite_get_media();

        // If error
        if( ( isset($user_media->error) && $user_media->error->type == 'OAuthException' ) || empty($user_media) ){ ?>

        	<style>
        		.social_feed_lite_conn-cont { text-align: center; }
				.social_feed_lite_check-connection { color: #000; padding: 25px; border: 2px solid #000; font-weight: 500; }
        	</style>

        	<div class="social_feed_lite_conn-cont">
	        	<span class="social_feed_lite_check-connection"><?php esc_html_e('Please check connection!', social_feed_lite_domain); ?></span>
	        </div>

        <?php }else{

        	// Loop over images
        	if( isset($user_media->data) && ! empty($user_media->data) ){ ?>

				<style>
					.social_feed_lite_shortcode_container .social_feed_lite_media-container { width: 24%; display: inline-block; opacity: 1; transition: all .5s; padding: 0px 1px; }
					.social_feed_lite_shortcode_container .social_feed_lite_media-container:hover { opacity: 0.85; }
					.social_feed_lite_shortcode_container .social_feed_lite_carousel_container { position: relative; }
					.social_feed_lite_shortcode_container .social_feed_lite_carousel_container a { text-decoration: none; }
					.social_feed_lite_carousel_icon_main { position: relative; display: contents; }
					.social_feed_lite_carousel_icon { position: absolute;	height: 20px; width: 20px; z-index: 9999; top: 10px; right: 10px; cursor: pointer; }
					.social_feed_lite_shortcode_container .social_feed_lite_media-container img { height: auto; width: 100%; }
					.social_feed_lite_shortcode_container { max-width: 670px; }
					.social_feed_lite_shortcode_container .social_feed_lite_media-container { position: relative; }
					.social_feed_lite_play_icon { position: absolute;	left: 0; right: 0; text-align: center; top: 45%; cursor: pointer; }

					@media (max-width: 767px) {
						.social_feed_lite_shortcode_container .social_feed_lite_media-container { width: 32%; }
						.social_feed_lite_shortcode_container .social_feed_lite_carousel_container { display: inline-block; }
						.social_feed_lite_carousel_icon { top: 10px; right: 10px; }							
						.social_feed_lite_shortcode_container { max-width: 750px; }							
					}

					@media (max-width: 575px) {
						.social_feed_lite_shortcode_container .social_feed_lite_media-container { Width: 48%; }							
						.social_feed_lite_carousel_icon { top: 10px; right: 10px; }
					}
				</style>

				<div class="social_feed_lite_shortcode_container">
				
				<?php 
				$counter = 1;
        		
        		foreach( $user_media->data as $media ){

        			if( $counter > $numberposts ){

        				break;
        			}

        			if( $media->media_type == 'IMAGE' ){ ?>

	        			<div class="social_feed_lite_image_container social_feed_lite_media-container">
	        				<a href="<?php echo esc_url($media->permalink); ?>" target="_blank">
	        					<img src="<?php echo esc_url($media->media_url); ?>" >
	        				</a>
	        			</div>

	        		<?php }elseif( $media->media_type == 'CAROUSEL_ALBUM' ){ ?>

	        			<div class="social_feed_lite_carousel_container social_feed_lite_media-container ">
							<a href="<?php echo esc_url($media->permalink); ?>" target="_blank">

								<!-- Carousel Icon -->
								<div class="social_feed_lite_carousel_icon_main">
		        				<div class="social_feed_lite_carousel_icon"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"> <defs> <style> .cls-1 { fill: #fff; fill-rule: evenodd; } </style> </defs> <path class="cls-1" d="M14,15v3H0V3H4V0H18V15H14ZM1,4V17H13V15H4V4H1ZM17,1H5V14H17V1Z"/> </svg> </div>
								</div>

								<img src="<?php echo esc_url($media->media_url); ?>">
							</a>
	        			</div>

	        		<?php }elseif( $media->media_type == 'VIDEO' ){ ?>

	        			<div class="social_feed_lite_video_container social_feed_lite_media-container">
	        				<a href="<?php echo esc_url($media->permalink); ?>" target="_blank">

		        				<!-- Play Icon -->
		        				<div class="social_feed_lite_play_icon_main"> <div class="social_feed_lite_play_icon"> <svg xmlns="http://www.w3.org/2000/svg" width="14" height="18" viewBox="0 0 14 18"> <defs> <style> .cls-1 { fill: #fff; fill-rule: evenodd; } </style> </defs> <path class="cls-1" d="M13.163,10.615L2.953,17.669a1.853,1.853,0,0,1-1.949.1,1.954,1.954,0,0,1-1-1.718V1.946A1.954,1.954,0,0,1,1,.228a1.853,1.853,0,0,1,1.949.1l10.21,7.054A1.975,1.975,0,0,1,13.163,10.615Z"/> </svg> </div> </div>
        						<img src="<?php echo esc_url($media->thumbnail_url); ?>">
        					</a>
	        			</div>

        			<?php }
        			$counter++;
        		} ?>
    			</div>
        	<?php 
        	}
        }

    $response = ob_get_contents();
    ob_end_clean();

return $response;
}