<?php
/*
Plugin Name: Social Feed Lite
Description: Lightweight plugin to show Instagram posts. Use shortcode [social_feed_lite]
Version: 1.0.0
Author: WPHelpline
Author URI: https://wphelpline.com
License: GPLv2 or later
Text Domain: social-feed-lite
*/

define('social_feed_lite_domain', 'social-feed-lite');
define('social_feed_lite_url', plugin_dir_url( __FILE__ ));  

include_once('includes/functions.php'); 

// Settings page for Social Feed Lite
add_action( 'admin_menu', 'social_feed_lite_admin_menu' );
function social_feed_lite_admin_menu() {
    add_menu_page(
        __( 'Social Feed Lite', social_feed_lite_domain ),
        __( 'Social Feed Lite', social_feed_lite_domain ),
        'manage_options',
        'social_feed_lite-settings',
        'social_feed_lite_admin_page_contents',
        'dashicons-instagram',
        50
    );

    // Main Connection page
    add_submenu_page(
        'social_feed_lite-settings',
        __( 'Connect Instagram', social_feed_lite_domain ),
        __( 'Connect Instagram', social_feed_lite_domain ),
        'manage_options',
        'social_feed_lite-settings',
        'social_feed_lite_admin_page_contents'
    );

    // Credentials and Other input page
    add_submenu_page(
        'social_feed_lite-settings',
        __( 'Customize Feed', social_feed_lite_domain ),
        __( 'Customize Feed', social_feed_lite_domain ),
        'manage_options',
        'social_feed_lite-info',
        'social_feed_lite_info_page_contents'
    );
}

// Callback function for admin settings page
function social_feed_lite_admin_page_contents() {

    if( ! current_user_can( 'manage_options' ) ){

        return false;
    }

    // Disconnect functionality
    if( isset($_GET['disconnect']) && $_GET['disconnect'] == 'true' ){

        delete_option('social_feed_lite_token');
        delete_option('social_feed_lite_token_long');
    }

    // Get token and User id
    $social_feed_lite_token_long = get_option('social_feed_lite_token_long');
    $social_feed_lite_token = get_option('social_feed_lite_token'); ?>

    

    <?php // If token and user id not exist show connect button
    if( ! $social_feed_lite_token && ! $social_feed_lite_token_long ){

        // URL from instagram API for generating code which will be used to generate token and user id
        $url = 'https://plugins.wphelpline.com/instagram-auth.php?state='.site_url(); ?>            

        <div class="social_feed_lite_container">              
           <h3><?php echo esc_html('Social Feed Lite'); ?></h3>
           <a class="connect-btn" href="<?php echo esc_url($url); ?>"><?php esc_html_e('Connect', social_feed_lite_domain); ?></a>
        </div>

    <?php

    // Else show Connected and disconnect button
    }else{ ?>

        <div class="social_feed_lite_container">
           <h3><?php echo esc_html('Social Feed Lite'); ?></h3>
           <span class="connected"><?php esc_html_e('Connected!', social_feed_lite_domain); ?></span>
           <a class="disconnect-btn" href="<?php echo esc_url(admin_url('admin.php?page=social_feed_lite-settings&disconnect=true')); ?>"><?php esc_html_e('Disconnect', social_feed_lite_domain); ?></a>
        </div>  

    <?php }
}

// Callback function for admin info page
function social_feed_lite_info_page_contents() {

    if ( isset( $_POST['il_submit'] ) ) {
        
        // Update Option 
        $il_info_input = array();

        if( ! empty($_POST['il_info']) ){

            foreach ( $_POST['il_info'] as $key => $value ) { 

                $il_info_input[$key] = sanitize_text_field( $value ); 

            }
        }

        update_option( 'il_info', $il_info_input );       
    }

    $il_info = get_option('il_info'); ?>

    <div class="social_feed_lite_container">
        <h3><?php echo esc_html('Social Feed Lite'); ?></h3>

        <form name="social_feed_lite_form" class="social_feed_lite_form" method="POST">

            <div class="social_feed_lite_field-cont">
                <label for="numberposts"><?php esc_html_e('Number of Posts', social_feed_lite_domain); ?></label>
                <input type="text" min="1" class="social_feed_lite_field" name="il_info[numberposts]" value="<?php echo isset($il_info['numberposts']) && ! empty($il_info['numberposts']) ? esc_attr($il_info['numberposts']) : 10; ?>" >
            </div>

            <input type="submit" class="social_feed_lite_submit" name="il_submit" value="Save">

        </form>
    </div>

<?php }