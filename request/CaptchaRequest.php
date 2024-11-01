<?php

if(!class_exists('ZTCPT_CaptchaRequest') ):

    class ZTCPT_CaptchaRequest
    {

        public $img_opt = ['zt_captcha_wp_login_enable','zt_captcha_wp_register_enable','zt_captcha_wp_lost_password_enable','zt_captcha_wp_comments_enable','zt_captcha_alpha_capital','zt_captcha_alpha_symbol','zt_captcha_numeric_symbol'];

        function __construct( )
        {
            /*Captcha Image*/
            add_action( 'admin_post_ztcpt_create_captcha_image', [$this,'ztcpt_create_captcha_image'] );
            add_action( 'admin_post_nopriv_ztcpt_create_captcha_image',  [$this,'ztcpt_create_captcha_image'] );

            /*Captcha Settings*/
            add_action( 'admin_post_save_ztcpt_captcha_settings', [$this,'save_ztcpt_captcha_settings'] );
            
        }
        
        function ztcpt_create_captcha_image(){
            $zt_captcha = new ZTCPT_Captcha();
            $text= $zt_captcha->ztcpt_generate_captcha_string();
            $zt_captcha_text_color='#162453';
            $zt_captcha_background_color='#ffffff';
            $zt_captcha_image_width=120;
            $zt_captcha_image_height=40;
            $zt_captcha_noise_line=3;
            $zt_captcha_noise_dot=10;
            $zt_captcha_noise_color='#162453';
            $zt_captcha_font_size=75;
            $zt_captcha->ztcpt_phpcaptcha($text,$zt_captcha_text_color,$zt_captcha_background_color,$zt_captcha_image_width,$zt_captcha_image_height,$zt_captcha_noise_line,$zt_captcha_noise_dot,$zt_captcha_noise_color,$zt_captcha_font_size);        
        }

        function save_ztcpt_captcha_settings(){
        /*Validate the the request*/
            if($_POST['token']){
                $token = sanitize_text_field($_POST['token']);
                if ( ! isset($token) || ! wp_verify_nonce($token, 'save_ztcpt_captcha_settings' ) ){
                    echo  esc_html_e( __( 'Sorry, your nonce did not verify.', ZTCPT_TEXT_DOMAIN ) );
                    die;
                }
            }
            
            if($_POST['zt_captcha_selected_captcha']){
                $zt_captcha_selected_captcha = sanitize_text_field($_POST['zt_captcha_selected_captcha']);
                update_option(sanitize_key('zt_captcha_selected_captcha'),sanitize_text_field($zt_captcha_selected_captcha));
            }

            if(isset($_POST['zt_captcha_error_message'])){
                $zt_captcha_error_message = sanitize_text_field($_POST['zt_captcha_error_message']);
                update_option(sanitize_key('zt_captcha_error_message'),$zt_captcha_error_message);
            }

            $this->ztcpt_repeated_fieldclone();
            $this->ztcpt_save_mathematics_captcha_setting();
            $this->ztcpt_save_image_captcha_setting();
            wp_redirect(admin_url('/admin.php?page=ztcpt_captcha_settings&success=1'));
        }
        

        function ztcpt_repeated_fieldclone(){
            $data = $this->img_opt;
            foreach($data as $key){
                if(isset($_POST[$key]) && $_POST[$key]!=''){
                    update_option(sanitize_key($key),sanitize_text_field($_POST[$key]));
                }
                else{
                    if($key!='zt_captcha_font_families'){
                        update_option(sanitize_key($key),0);
                    }
                }
            }
           
        }

        function ztcpt_save_mathematics_captcha_setting(){

            /*Mathematics Captcha Settings*/
            if($_POST['zt_captcha_algebraic_operation']){
                $zt_captcha_algebraic_operation = sanitize_text_field($_POST['zt_captcha_algebraic_operation']);
                    update_option(sanitize_key('zt_captcha_algebraic_operation'),$zt_captcha_algebraic_operation);
            }
            
            if($_POST['zt_captcha_algebraic_rhs_length']){
                $zt_captcha_algebraic_rhs_length = sanitize_text_field($_POST['zt_captcha_algebraic_rhs_length']);
                update_option(sanitize_key('zt_captcha_algebraic_rhs_length'),$zt_captcha_algebraic_rhs_length);
            }

            if($_POST['zt_captcha_alpha_length']){
                $zt_captcha_alpha_length = sanitize_text_field($_POST['zt_captcha_alpha_length']);
                update_option(sanitize_key('zt_captcha_alpha_length'),$zt_captcha_alpha_length);
            }

            if($_POST['zt_captcha_numeric_length']){
                $zt_captcha_numeric_length = sanitize_text_field($_POST['zt_captcha_numeric_length']);
                update_option(sanitize_key('zt_captcha_numeric_length'),$zt_captcha_numeric_length);
            }

            /*Mathematics Captcha Settings*/
        }

            /*Captcha Image Settings*/
        function ztcpt_save_image_captcha_setting(){
            
        } 

    }

$captcha_req=new ZTCPT_CaptchaRequest;

endif;