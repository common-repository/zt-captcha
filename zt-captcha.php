<?php
/*
Plugin Name: ZT Captcha
Description: The simple captcha plugin was developed to keep the WordPress website safe. Captcha helps protect you from spam and password decryption by asking you to complete a simple test that proves you are human and not a computer trying to break into a password-protected account.
Version: 1.0.4
Author: Webcresty
Author URI: https://www.webcresty.com/
Text Domain: zt-captcha
*/

defined('ABSPATH') || die("you do not have access to this page!");
define('ZTCPT_CAPTCHA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define('ZTCPT_CAPTCHA_URL_DIR',plugin_dir_url( __FILE__ ) );
define('ZTCPT_CAPTCHA_VERSION','1.0.2');
define('ZTCPT_VALIDATE_REQ','a_c_validate');
define('ZTCPT_SESSION_STORAGE','a_security_code');
define('ZTCPT_TEXT_DOMAIN','zt-captcha');
/*Hook Act when user activate the plugin*/

register_activation_hook( __FILE__, 'ztcpt_captcha_activate' );

if(!function_exists('ztcpt_captcha_activate')){
    function ztcpt_captcha_activate(){
        //set default values
        update_option( sanitize_key('zt_captcha_error_message'), __('Captcha not verified.', ZTCPT_TEXT_DOMAIN));
    }
}

/*Hook Act when user delete the plugin*/

register_uninstall_hook(__FILE__, 'ztcpt_captcha_uninstall');

if(!function_exists('ztcpt_captcha_uninstall')){
    function ztcpt_captcha_uninstall(){
        update_option(sanitize_key('zt_captcha_font_families_name'),'monofont.ttf');
        update_option(sanitize_key('zt_captcha_font_families'),ZTCPT_CAPTCHA_PLUGIN_DIR.'fonts/monofont.ttf');
    }
}

if(!function_exists('ztcpt_captcha_resource')){
    function ztcpt_captcha_resource() {
        require_once(ZTCPT_CAPTCHA_PLUGIN_DIR . 'captcha_settings.php');
        require_once(ZTCPT_CAPTCHA_PLUGIN_DIR.'/request/CaptchaRequest.php');
        require_once(ZTCPT_CAPTCHA_PLUGIN_DIR.'/inc/Zt_Captcha.php');
        require_once(ZTCPT_CAPTCHA_PLUGIN_DIR.'/inc/template.php');

        /*script*/
        $jquery=array('jquery');
        wp_enqueue_script( 'jquery' );
        wp_register_script('ztcpt_captcha_app_js', ZTCPT_CAPTCHA_URL_DIR.'js/app.js',$jquery, ZTCPT_CAPTCHA_VERSION, false);
        wp_register_script('ztcpt_captcha_propper_js',ZTCPT_CAPTCHA_URL_DIR.'js/popper.min.js',$jquery, ZTCPT_CAPTCHA_VERSION, false);

        /* end script */
        /*Assign Url Variables */
        $url_array = array(
            'admin_post_url' =>admin_url('admin-post.php'),
        );

        /*Style*/
        wp_register_style( 'ztcpt_captcha_default', ZTCPT_CAPTCHA_URL_DIR.'css/default.css', false, ZTCPT_CAPTCHA_VERSION );

        /*
        * Add the CAPTCHA to the WP login form
        */
        if (get_option('zt_captcha_wp_login_enable',0)) {

            add_action( 'login_form', function(){ztcpt_captcha('wp_login'); });

                add_filter( 'wp_authenticate_user', 'ztcpt_a_cptch_login_check', 21, 1 );

            /*woocomerce*/
            add_action( 'woocommerce_login_form', function(){ztcpt_captcha('wp_login'); });
        }

        /**
         * Add Captcha during registration
         *
         */
        if (get_option('zt_captcha_wp_register_enable',0)) {
            add_action( 'register_form',function(){ztcpt_captcha('wp_register');});
            add_action( 'signup_extra_fields',function(){ztcpt_captcha('wp_register');});
            add_action( 'signup_blogform',function(){ztcpt_captcha('wp_register');});

            add_filter( 'registration_errors', 'ztcpt_a_cptch_register_check', 9, 3 );
                    if ( is_multisite() ) {
                        add_filter( 'wpmu_validate_user_signup', 'ztcpt_a_cptch_register_validate' );
                        add_filter( 'wpmu_validate_blog_signup', 'ztcpt_a_cptch_register_validate' );
                    }
            //woocomerce
            add_action( 'woocommerce_register_form', function(){ztcpt_captcha('wc_register'); });
            add_action( 'woocommerce_register_post', 'ztcpt_a_cptch_wooc_validate_fields', 10, 3 );
            
        }

        /*
        * Add the CAPTCHA into the WP lost password form
        */
        if (get_option('zt_captcha_wp_lost_password_enable',0)) {
            add_action( 'lostpassword_form',function(){ztcpt_captcha('wp_forget');});
            add_filter( 'lostpassword_errors', 'ztcpt_a_cptch_lostpassword_check', 10, 2);
            //woocomerce
            add_action( 'woocommerce_lostpassword_form',function(){ztcpt_captcha('wp_forget');});
            add_action( 'lostpassword_post', 'ztcpt_a_cptch_wooc_forget_validate'); // for lost password woocommerce
        }
        /*
        * Add the CAPTCHA to the WP comments form
        */
        if (get_option('zt_captcha_wp_comments_enable',0)) {
            /*
                * Common hooks to add necessary actions for the WP comment form,
                * but some themes don't contain these hooks in their comments form templates
                */
            /*
                * Try to display the CAPTCHA before the close tag </form>
                * in case if hooks 'comment_form_after_fields' or 'comment_form_logged_in_after'
                * are not included to the theme comments form template
                */
                add_filter('comment_form_submit_field','ztcpt_create_field_in_comment');
                add_filter( 'preprocess_comment', 'ztcpt_a_cptch_comment_post' );

        }

    }
}

if(!function_exists('ztcpt_create_field_in_comment')){
    function ztcpt_create_field_in_comment($submit_field){

        return gravity_ztcpt_captcha('wp_comment').$submit_field;

    }

}

add_action( 'init', 'ztcpt_captcha_resource' );

add_action('admin_menu','callback_ztcpt_captcha_resource');

if(!function_exists('callback_ztcpt_captcha_resource')){
    function callback_ztcpt_captcha_resource() {

    //this is the main item for the menu

    add_menu_page(__( 'ZT Captcha Settings', ZTCPT_TEXT_DOMAIN ), //page title
    __( 'ZT Captcha Settings', ZTCPT_TEXT_DOMAIN ), //menu title
    'manage_options', //capabilities
    'ztcpt_captcha_settings', //menu slug
    'ztcpt_captcha_settings', //function
    'dashicons-lock'
    );
    
    }
}

add_shortcode('ztcpt_captcha','ztcpt_captcha');
if(!function_exists('ztcpt_captcha')){
    function ztcpt_captcha($place=''){
        echo ztcpt_captcha_image_template($place);
    }
}

/*render Simple Captcha from gravity field */
if(!function_exists('gravity_ztcpt_captcha')){
    function gravity_ztcpt_captcha($place=''){
        return ztcpt_captcha_image_template($place);
    }
}

/*validate captcha on login hook */
if(!function_exists('ztcpt_a_cptch_login_check')){
    function ztcpt_a_cptch_login_check($user){
        ztcptCaptchaSessionStart();
        if(get_option('zt_captcha_selected_captcha')=='mathematical_captcha'){
            return ztcpt_is_numeric_case($user);
        }
        if(get_option('zt_captcha_selected_captcha')=='number_captcha'){
            if(get_option('zt_captcha_numeric_symbol')!='on'){
                return ztcpt_is_numeric_case($user);
            }
            else{
                return ztcpt_is_alphanumeric_case($user);
            }
        }
        if(get_option('zt_captcha_selected_captcha')=='alphzt_captcha'){
            if((get_option('zt_captcha_alpha_capital')=='on') && (get_option('zt_captcha_alpha_symbol')=='on')){
                return ztcpt_is_alphanumeric_case($user);
            }
            elseif((get_option('zt_captcha_alpha_capital')=='on') || (get_option('zt_captcha_alpha_symbol')=='on')){
                return ztcpt_is_alphanumeric_case($user);
            }
            else{
                return ztcpt_is_alphanumeric_case($user); 
            }
        }
    }
}

/*validate captcha on register */
if(!function_exists('ztcpt_a_cptch_register_check')){
    function ztcpt_a_cptch_register_check($errors) {
        ztcptCaptchaSessionStart();
        if(get_option('zt_captcha_selected_captcha')=='mathematical_captcha'){
            return ztcpt_is_numeric_register_case($errors);
        }
        if(get_option('zt_captcha_selected_captcha')=='number_captcha'){
            if(get_option('zt_captcha_numeric_symbol')!='on'){
                return ztcpt_is_numeric_register_case($errors);
            }
            else{
                return ztcpt_is_alphanumeric_register_case($errors);
            }
        }
        if(get_option('zt_captcha_selected_captcha')=='alphzt_captcha'){
            if((get_option('zt_captcha_alpha_capital')=='on') && (get_option('zt_captcha_alpha_symbol')=='on')){
                return ztcpt_is_alphanumeric_register_case($errors);
            }
            elseif((get_option('zt_captcha_alpha_capital')=='on') || (get_option('zt_captcha_alpha_symbol')=='on')){
                return ztcpt_is_alphanumeric_register_case($errors);
            }
            else{
                return ztcpt_is_alphanumeric_register_case($errors); 
            }
        }
    }
}

/*validate captcha on register */
if(!function_exists('ztcpt_a_cptch_register_validate')){
    function ztcpt_a_cptch_register_validate($results){
            ztcptCaptchaSessionStart();
            if(get_option('zt_captcha_selected_captcha')=='mathematical_captcha'){
                return ztcpt_is_numeric_validate_register_case($results,$place = 'wp_register');
            }
            if(get_option('zt_captcha_selected_captcha')=='number_captcha'){
                if(get_option('zt_captcha_numeric_symbol')!='on'){
                    return ztcpt_is_numeric_validate_register_case($results,$place = 'wp_register');
                }
                else{
                    return ztcpt_is_alphanumeric_validate_register_case($results,$place = 'wp_register');
                }
            }
            if(get_option('zt_captcha_selected_captcha')=='alphzt_captcha'){
                if((get_option('zt_captcha_alpha_capital')=='on') && (get_option('zt_captcha_alpha_symbol')=='on')){
                    return ztcpt_is_alphanumeric_validate_register_case($results,$place = 'wp_register');
                }
                elseif((get_option('zt_captcha_alpha_capital')=='on') || (get_option('zt_captcha_alpha_symbol')=='on')){
                    return ztcpt_is_alphanumeric_validate_register_case($results,$place = 'wp_register');
                }
                else{
                    return ztcpt_is_alphanumeric_validate_register_case($results,$place = 'wp_register'); 
                }
            }         
    }
}
/*validate captcha on lost pasw form */
if(!function_exists('ztcpt_a_cptch_lostpassword_check')){
    function ztcpt_a_cptch_lostpassword_check($results){
        ztcptCaptchaSessionStart();
        if(get_option('zt_captcha_selected_captcha')=='mathematical_captcha'){
            return ztcpt_is_numeric_validate_register_case($results,$place = 'wp_forget');
        }
        if(get_option('zt_captcha_selected_captcha')=='number_captcha'){
            if(get_option('zt_captcha_numeric_symbol')!='on'){
                return ztcpt_is_numeric_validate_register_case($results,$place = 'wp_forget');
            }
            else{
                return ztcpt_is_alphanumeric_validate_register_case($results,$place = 'wp_forget');
            }
        }
        if(get_option('zt_captcha_selected_captcha')=='alphzt_captcha'){
            if((get_option('zt_captcha_alpha_capital')=='on') && (get_option('zt_captcha_alpha_symbol')=='on')){
                return ztcpt_is_alphanumeric_validate_register_case($results,$place = 'wp_forget');
            }
            elseif((get_option('zt_captcha_alpha_capital')=='on') || (get_option('zt_captcha_alpha_symbol')=='on')){
                return ztcpt_is_alphanumeric_validate_register_case($results,$place = 'wp_forget');
            }
            else{
                return ztcpt_is_alphanumeric_validate_register_case($results,$place = 'wp_forget'); 
            }
        }
    }
}

/*validate captcha on comment */
if(!function_exists('ztcpt_a_cptch_comment_post')){
    function ztcpt_a_cptch_comment_post($comment){

        /* Added for compatibility with WP Wall plugin. This does NOT add CAPTCHA to WP Wall plugin, It just prevents the "Error: You did not enter a Captcha phrase." when submitting a WP Wall comment */
        if ( function_exists( 'WPWall_Widget' ) && isset($_REQUEST['wpwall_comment'])) {
            /* Skip capthca */
            $wall_widget=intval(1);
        }

        /* Skip the CAPTCHA for comment replies from the admin menu */
        if (isset($_REQUEST['action']) && 'replyto-comment' == $_REQUEST['action'] && (check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false ))){
            $comment_reply=intval(1);
        }

        ztcptCaptchaSessionStart();
            if(isset($_REQUEST[ZTCPT_VALIDATE_REQ]) && ('' == $comment['comment_type'] || 'comment' == $comment['comment_type'] || 'review' == $comment['comment_type']) && !isset($comment_reply) && !isset($wall_widget)){
                if(get_option('zt_captcha_selected_captcha')=='mathematical_captcha'){
                    return ztcpt_is_numeric_validate_comment_case($comment);
                }
                if(get_option('zt_captcha_selected_captcha')=='number_captcha'){
                    if(get_option('zt_captcha_numeric_symbol')!='on'){
                        return ztcpt_is_numeric_validate_comment_case($comment);
                    }
                    else{
                        return ztcpt_is_alphanumeric_validate_comment_case($comment);
                    }
                }
                if(get_option('zt_captcha_selected_captcha')=='alphzt_captcha'){
                    if((get_option('zt_captcha_alpha_capital')=='on') && (get_option('zt_captcha_alpha_symbol')=='on')){
                        return ztcpt_is_alphanumeric_validate_comment_case($comment);
                    }
                    elseif((get_option('zt_captcha_alpha_capital')=='on') || (get_option('zt_captcha_alpha_symbol')=='on')){
                        return ztcpt_is_alphanumeric_validate_comment_case($comment);
                    }
                    else{
                        return ztcpt_is_alphanumeric_validate_comment_case($comment); 
                    }
                }  
            }
        
    }
}

/** Validate woocomerce register fields. */
if(!function_exists('ztcpt_a_cptch_wooc_validate_fields')){
    function ztcpt_a_cptch_wooc_validate_fields( $username, $email, $validation_errors ) {
        ztcptCaptchaSessionStart();
            if(get_option('zt_captcha_selected_captcha')=='mathematical_captcha'){
                return ztcpt_is_numeric_wooc_validate_case($username, $email, $validation_errors);
            }
            if(get_option('zt_captcha_selected_captcha')=='number_captcha'){
                if(get_option('zt_captcha_numeric_symbol')!='on'){
                    return ztcpt_is_numeric_wooc_validate_case($username, $email, $validation_errors);
                }
                else{
                    return ztcpt_is_alphanumeric_wooc_validate_case($username, $email, $validation_errors);
                }
            }
            if(get_option('zt_captcha_selected_captcha')=='alphzt_captcha'){
                if((get_option('zt_captcha_alpha_capital')=='on') && (get_option('zt_captcha_alpha_symbol')=='on')){
                    return ztcpt_is_alphanumeric_wooc_validate_case($username, $email, $validation_errors);
                }
                elseif((get_option('zt_captcha_alpha_capital')=='on') || (get_option('zt_captcha_alpha_symbol')=='on')){
                    return ztcpt_is_alphanumeric_wooc_validate_case($username, $email, $validation_errors);
                }
                else{
                    return ztcpt_is_alphanumeric_wooc_validate_case($username, $email, $validation_errors); 
                }
            }  
            
    }
}

if(!function_exists('ztcptCaptchaSessionStart')){
    function ztcptCaptchaSessionStart(){
        if (!session_id()) {
            @session_start();
        }
    }
}

/* validate login case condition */
if(!function_exists('ztcpt_is_numeric_case')){
    function ztcpt_is_numeric_case($user){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wp_login'])){
            if(is_numeric($_REQUEST[ZTCPT_VALIDATE_REQ])==is_numeric($_SESSION[ZTCPT_SESSION_STORAGE]['wp_login'])){
                if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(isset($_SESSION[ZTCPT_SESSION_STORAGE]) && isset($_SESSION[ZTCPT_SESSION_STORAGE]['wp_login']) && $_SESSION[ZTCPT_SESSION_STORAGE]['wp_login']==$ZTCPT_VALIDATE_REQ){
                        return $user;
                    }else{
                        $message = esc_html__($zt_captcha_error_message , ZTCPT_TEXT_DOMAIN);
                        return new WP_Error( 'captcha_not_verified', $message );
                    }
                }else{
                    return $user;
                }
            }
            else{
                $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                return new WP_Error( 'captcha_not_verified', $message );
            }
        }
        else{
            $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
            return new WP_Error( 'captcha_not_verified', $message );
        }
    }
}

if(!function_exists('ztcpt_is_alphanumeric_case')){
    function ztcpt_is_alphanumeric_case($user){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wp_login'])){
            if(is_string($_REQUEST[ZTCPT_VALIDATE_REQ])==is_string($_SESSION[ZTCPT_SESSION_STORAGE]['wp_login'])){
                if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(isset($_SESSION[ZTCPT_SESSION_STORAGE]) && isset($_SESSION[ZTCPT_SESSION_STORAGE]['wp_login']) && $_SESSION[ZTCPT_SESSION_STORAGE]['wp_login']==$ZTCPT_VALIDATE_REQ){
                        return $user;
                    }else{
                        $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                        return new WP_Error( 'captcha_not_verified', $message );
                    }
                }else{
                    return $user;
                }
            }
            else{
                $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                return new WP_Error( 'captcha_not_verified', $message );
            }

        }
        else{
            $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
            return new WP_Error( 'captcha_not_verified', $message );
        }
    }
}    
/*end validate login case condition*/


/*Start script validate register case condition*/
if(!function_exists('ztcpt_is_numeric_register_case')){
    function ztcpt_is_numeric_register_case($errors){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wp_register'])){
            if(is_numeric($_REQUEST[ZTCPT_VALIDATE_REQ])==is_numeric($_SESSION[ZTCPT_SESSION_STORAGE]['wp_register'])){
                if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(!isset($_SESSION[ZTCPT_SESSION_STORAGE]) || !isset($_SESSION[ZTCPT_SESSION_STORAGE]['wp_register']) || $_SESSION[ZTCPT_SESSION_STORAGE]['wp_register']!=$ZTCPT_VALIDATE_REQ){
                        $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                        $errors->add( 'captcha_not_verified', $message);
                    }
                }
            }
            else{
                $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                $errors->add( 'captcha_not_verified', $message);
            }
        }
        else{
            $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
            $errors->add( 'captcha_not_verified', $message);
        }
        return $errors;
    }
}

if(!function_exists('ztcpt_is_alphanumeric_register_case')){
    function ztcpt_is_alphanumeric_register_case($errors){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wp_register'])){
            if(is_string($_REQUEST[ZTCPT_VALIDATE_REQ])==is_string($_SESSION[ZTCPT_SESSION_STORAGE]['wp_register'])){
                if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(!isset($_SESSION[ZTCPT_SESSION_STORAGE]) || !isset($_SESSION[ZTCPT_SESSION_STORAGE]['wp_register']) || $_SESSION[ZTCPT_SESSION_STORAGE]['wp_register']!=$ZTCPT_VALIDATE_REQ){
                        $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                        $errors->add( 'captcha_not_verified', $message);
                    }
                }
            }
            else{
                $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                $errors->add( 'captcha_not_verified', $message);
            }
        }
        else{
            $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
            $errors->add( 'captcha_not_verified', $message, ZTCPT_TEXT_DOMAIN);
        }    
        return $errors;
    }
}
/*end validate register case condition*/

/* validate capcha register case condition */
if(!function_exists('ztcpt_is_numeric_validate_register_case')){
    function ztcpt_is_numeric_validate_register_case($results,$place){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE][$place])){
            if(is_numeric($_REQUEST[ZTCPT_VALIDATE_REQ])==is_numeric($_SESSION[ZTCPT_SESSION_STORAGE][$place])){
                if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(isset($_SESSION[ZTCPT_SESSION_STORAGE]) && isset($_SESSION[ZTCPT_SESSION_STORAGE][$place]) && $_SESSION[ZTCPT_SESSION_STORAGE][$place]==$ZTCPT_VALIDATE_REQ){
                        return $results;
                    }else{
                        $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                        return new WP_Error( 'captcha_not_verified', $message );
                    }
                }
            }
            else{
                $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                return new WP_Error( 'captcha_not_verified', $message );
            }
        }
        else{
            $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
            return new WP_Error( 'captcha_not_verified', $message );
        }
        
    }
}


if(!function_exists('ztcpt_is_alphanumeric_validate_register_case')){
    function ztcpt_is_alphanumeric_validate_register_case($results,$place){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE][$place])){
            if(is_string($_REQUEST[ZTCPT_VALIDATE_REQ])==is_string($_SESSION[ZTCPT_SESSION_STORAGE][$place])){
                if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(isset($_SESSION[ZTCPT_SESSION_STORAGE]) && isset($_SESSION[ZTCPT_SESSION_STORAGE][$place]) && $_SESSION[ZTCPT_SESSION_STORAGE][$place]==$ZTCPT_VALIDATE_REQ){
                        return $results;
                    }else{
                        $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                        return new WP_Error( 'captcha_not_verified', $message );
                    }
                }
            }
            else{
                $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                return new WP_Error( 'captcha_not_verified', $message );
            }
        }
        else{
            $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
            return new WP_Error( 'captcha_not_verified', $message );
        }
    }
}

/*end validate capcha register case condition*/

/* start validate comment form case */
if(!function_exists('ztcpt_is_numeric_validate_comment_case')){
    function ztcpt_is_numeric_validate_comment_case($comment){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
            if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wp_comment'])){
            
                if(is_numeric($_REQUEST[ZTCPT_VALIDATE_REQ])==is_numeric($_SESSION[ZTCPT_SESSION_STORAGE]['wp_comment'])){
                    
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(!isset($_SESSION[ZTCPT_SESSION_STORAGE])  || !isset($_SESSION[ZTCPT_SESSION_STORAGE]['wp_comment']) || $_SESSION[ZTCPT_SESSION_STORAGE]['wp_comment']!=$ZTCPT_VALIDATE_REQ){

                        $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                        set_transient('captcha_error_message', $message, 60);
                            wp_redirect(wp_get_referer()); 
                            exit();
                     
                    }
                }
                else{  
                    $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                    set_transient('captcha_error_message', $message, 60);
                        wp_redirect(wp_get_referer()); 
                        exit();
                    
                }
            }
            else{ 
                 $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                set_transient('captcha_error_message', $message, 60);
                    wp_redirect(wp_get_referer()); 
                    exit();

            }
        }
        return $comment;
    }
}

if(!function_exists('ztcpt_is_alphanumeric_validate_comment_case')){
    function ztcpt_is_alphanumeric_validate_comment_case($comment){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
            if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wp_comment'])){
                if(is_string($_REQUEST[ZTCPT_VALIDATE_REQ])==is_string($_SESSION[ZTCPT_SESSION_STORAGE]['wp_comment'])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(!isset($_SESSION[ZTCPT_SESSION_STORAGE])  || !isset($_SESSION[ZTCPT_SESSION_STORAGE]['wp_comment']) || $_SESSION[ZTCPT_SESSION_STORAGE]['wp_comment']!=$ZTCPT_VALIDATE_REQ){

                        $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                       set_transient('captcha_error_message', $message, 60);
                        wp_redirect(wp_get_referer()); 
                        exit();
                    }
                }
                else{  

                    $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                    set_transient('captcha_error_message', $message, 60);
                        wp_redirect(wp_get_referer()); 
                        exit();
                }
            }
            else{ 
                
                $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                set_transient('captcha_error_message', $message, 60);    
                  wp_redirect(wp_get_referer()); 
                    exit();
            }
        }    
        return $comment;
        
    }
}
/* end validate comment form case */


/* Start Woocommerce Register validate form case */
if(!function_exists('ztcpt_is_numeric_wooc_validate_case')){
    function ztcpt_is_numeric_wooc_validate_case($username, $email, $validation_errors){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
            if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wc_register'])){
                if(is_numeric($_REQUEST[ZTCPT_VALIDATE_REQ])==is_numeric($_SESSION[ZTCPT_SESSION_STORAGE]['wc_register'])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(!isset($_SESSION[ZTCPT_SESSION_STORAGE]) || !isset($username) || !isset($email) || !isset($_SESSION[ZTCPT_SESSION_STORAGE]['wc_register']) || $_SESSION[ZTCPT_SESSION_STORAGE]['wc_register']!=$ZTCPT_VALIDATE_REQ){
                        $validation_errors->add( ZTCPT_VALIDATE_REQ, __( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN ) );
                    }
                }
                else{
                    $validation_errors->add( ZTCPT_VALIDATE_REQ, __( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN ) );
                }
            }
            else{
                $validation_errors->add( ZTCPT_VALIDATE_REQ, __( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN ) );
            }
            
        }
    }
}

if(!function_exists('ztcpt_is_alphanumeric_wooc_validate_case')){
    function ztcpt_is_alphanumeric_wooc_validate_case($username, $email, $validation_errors){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
            if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wc_register'])){
                if(is_string($_REQUEST[ZTCPT_VALIDATE_REQ])==is_string($_SESSION[ZTCPT_SESSION_STORAGE]['wc_register'])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(!isset($_SESSION[ZTCPT_SESSION_STORAGE]) || !isset($username) || !isset($email) || !isset($_SESSION[ZTCPT_SESSION_STORAGE]['wc_register']) || $_SESSION[ZTCPT_SESSION_STORAGE]['wc_register']!=$ZTCPT_VALIDATE_REQ){
                        $validation_errors->add( ZTCPT_VALIDATE_REQ, __( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN ) );
                    }
                }
                else{
                    $validation_errors->add( ZTCPT_VALIDATE_REQ, __( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN ) );
                }
            }
            else{
                $validation_errors->add( ZTCPT_VALIDATE_REQ, __( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN ) );
            }
        }
    }
}
/* end script Woocommerce Register validate form case */

/*lost password for woocommerce account*/

if(!function_exists('ztcpt_a_cptch_wooc_forget_validate')){
    function ztcpt_a_cptch_wooc_forget_validate($errors) {
        ztcptCaptchaSessionStart();
        if(get_option('zt_captcha_selected_captcha')=='mathematical_captcha'){
            return ztcpt_is_numeric_wooc_forget_case($errors);
        }
        if(get_option('zt_captcha_selected_captcha')=='number_captcha'){
            if(get_option('zt_captcha_numeric_symbol')!='on'){
                return ztcpt_is_numeric_wooc_forget_case($errors);
            }
            else{
                return ztcpt_is_alphanumeric_wooc_forget_case($errors);
            }
        }
        if(get_option('zt_captcha_selected_captcha')=='alphzt_captcha'){
            if((get_option('zt_captcha_alpha_capital')=='on') && (get_option('zt_captcha_alpha_symbol')=='on')){
                return ztcpt_is_alphanumeric_wooc_forget_case($errors);
            }
            elseif((get_option('zt_captcha_alpha_capital')=='on') || (get_option('zt_captcha_alpha_symbol')=='on')){
                return ztcpt_is_alphanumeric_wooc_forget_case($errors);
            }
            else{
                return ztcpt_is_alphanumeric_wooc_forget_case($errors); 
            }
        }
    }
}


if(!function_exists('ztcpt_is_numeric_wooc_forget_case')){
    function ztcpt_is_numeric_wooc_forget_case($errors){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wp_forget'])){
            if(is_numeric($_REQUEST[ZTCPT_VALIDATE_REQ])==is_numeric($_SESSION[ZTCPT_SESSION_STORAGE]['wp_forget'])){
                if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(!isset($_SESSION[ZTCPT_SESSION_STORAGE]) || !isset($_SESSION[ZTCPT_SESSION_STORAGE]['wp_forget']) || $_SESSION[ZTCPT_SESSION_STORAGE]['wp_forget']!=$ZTCPT_VALIDATE_REQ){
                        $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                        $errors->add( 'captcha_not_verified', $message);
                    }
                }
            }
            else{
                $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                $errors->add( 'captcha_not_verified', $message);
            }
        }
        else{
            $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
            $errors->add( 'captcha_not_verified', $message);
        }
        return $errors;
    }
}

if(!function_exists('ztcpt_is_alphanumeric_wooc_forget_case')){
    function ztcpt_is_alphanumeric_wooc_forget_case($errors){
        $zt_captcha_error_message = get_option('zt_captcha_error_message');
        if(strlen($_REQUEST[ZTCPT_VALIDATE_REQ])==strlen($_SESSION[ZTCPT_SESSION_STORAGE]['wp_forget'])){
            if(is_string($_REQUEST[ZTCPT_VALIDATE_REQ])==is_string($_SESSION[ZTCPT_SESSION_STORAGE]['wp_forget'])){
                if(isset($_REQUEST[ZTCPT_VALIDATE_REQ])){
                    $ZTCPT_VALIDATE_REQ = sanitize_text_field($_REQUEST[ZTCPT_VALIDATE_REQ]);
                    if(!isset($_SESSION[ZTCPT_SESSION_STORAGE]) || !isset($_SESSION[ZTCPT_SESSION_STORAGE]['wp_forget']) || $_SESSION[ZTCPT_SESSION_STORAGE]['wp_forget']!=$ZTCPT_VALIDATE_REQ){
                        $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                        $errors->add( 'captcha_not_verified', $message);
                    }
                }
            }
            else{
                $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
                $errors->add( 'captcha_not_verified', $message);
            }
        }
        else{
            $message = esc_html__( $zt_captcha_error_message, ZTCPT_TEXT_DOMAIN);
            $errors->add( 'captcha_not_verified', $message, ZTCPT_TEXT_DOMAIN);
        }    
        return $errors;
    }
}



         /**
         * Display the Comment form Error Message
         *
         */

function display_captcha_error_message() {
    // Check if the transient exists
    if (false !== ($captcha_error_message = get_transient('captcha_error_message'))) {

        echo '<script>
            jQuery(document).ready(function() {
                var errorMessage = \'' . esc_js($captcha_error_message) . '\';
                var postCommentButton = jQuery(".zt_captcha_field");

                // Insert the error message above the "Post Comment" button
                postCommentButton.before(\'<div class="captcha-error">\' + errorMessage + \'</div>\');

             
            });
        </script>';


  delete_transient('captcha_error_message');


    }
}
add_action('comment_form', 'display_captcha_error_message');

