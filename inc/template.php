<?php
function ztcpt_captcha_image_template($place){
    wp_enqueue_script("ztcpt_captcha_app_js",array('jquery') , '1.0', true);
    wp_enqueue_style("ztcpt_captcha_default");
    wp_enqueue_style( 'dashicons' );
    ob_start();
    if(is_array($place)){
        if($place){
            $place=esc_html__($place['place'], ZTCPT_TEXT_DOMAIN);
        }
    }
 ?>
    <div class="zt_captcha_container">
        <div class="zt_captcha_image_div">
            <!-- place variable insert with below id bcoz if multiple shortcode call in same page not reflect in js.  -->
            <img id="a_c_image_<?php if(!empty($place)){echo esc_attr($place);}else{echo '';} ?>"
                src="<?php echo esc_url(admin_url('admin-post.php?action=ztcpt_create_captcha_image&place='.$place.'&rand='.random_int(0, 10000))); ?>">
            <span style="color:#162453" class="dashicons dashicons-image-rotate reload_a_c_image"
                onclick="a_c_load_another('a_c_image_<?php echo esc_js($place);?>')"></span>
        </div>
        <input class="zt_captcha_field" type="text" value="" name="<?php echo ZTCPT_VALIDATE_REQ; ?>" id="<?php if(!empty($place)){echo esc_attr($place);}else{echo '';} ?>">
        <input class="zt_captcha_field_place" type="hidden" value="<?php if(!empty($place)){echo esc_attr($place);}else{echo '';} ?>" name="a_c_validate_place">
    </div>
<?php
 return ob_get_clean();
}
