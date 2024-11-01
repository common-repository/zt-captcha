<?php
function ztcpt_captcha_settings(){
   echo '<br>';
   echo '<h2>'.esc_html__("Example of Captcha", ZTCPT_TEXT_DOMAIN).'</h2>';
   //Check for GD extention
   if(!function_exists('imagecreatetruecolor')){
        $link = 'https://bobcares.com/blog/php-install-gd-extension/';
        echo '<p class="warning_toaster">PHP GD extention must be enable to use some captcha types <a target="_blank" href="'.esc_url($link).'">Click here for help</a></p>';
    }else{
        do_shortcode('[ztcpt_captcha place="test"]');
    }

    //#check proper session path
    $disable_captcha_places='';
    if(!is_dir(session_save_path())){
        echo '<p class="warning_toaster">Failed to read session data: '.session_save_path().' No such file or directory. Captcha will not work if session is not readable</a></p>';
        update_option(sanitize_key('zt_captcha_wp_login_enable'),'');
        update_option(sanitize_key('zt_captcha_wp_register_enable'),'');
        update_option(sanitize_key('zt_captcha_wp_lost_password_enable'),'');
        update_option(sanitize_key('zt_captcha_wp_comments_enable'),'');
        $disable_captcha_places='disabled';
    }

   //get roles of website
   global $wp_roles;
   $roles = $wp_roles->get_names();
   ?>
<h2><?php esc_html_e( 'Basic Settings', ZTCPT_TEXT_DOMAIN );?></h2>
<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
    <table class="form-table">
        <thead></thead>
        <tfoot></tfoot>
        <tbody>
            <?php
                if(isset($_REQUEST['success']) && $_REQUEST['success']==1){
                    if(filter_var($_REQUEST['success'], FILTER_VALIDATE_INT)){
                        echo '<tr><p class="success_toaster">'.esc_html__("Settings Saved", ZTCPT_TEXT_DOMAIN).'</p></tr>';
                    }
                }
               ?>
            <tr>
                <th class="help-icon"><?php esc_html_e( 'Enable Captcha For', ZTCPT_TEXT_DOMAIN );?>
                </th>
                
                <td>
                    <fieldset id="wp_default">
                        <label class="cptch_related">
                            <input type="checkbox" <?php echo esc_attr($disable_captcha_places); ?> id="zt_captcha_wp_login_enable"
                                name="zt_captcha_wp_login_enable" value="<?php echo intval(1);?>"
                                <?php echo esc_html(get_option('zt_captcha_wp_login_enable') ? 'checked':'') ?>>
                                <?php esc_html_e( 'Login form', ZTCPT_TEXT_DOMAIN );?>
                        </label>
                        <br>
                        <label class="cptch_related">
                            <input type="checkbox" <?php echo esc_attr($disable_captcha_places); ?>
                                id="zt_captcha_wp_register_enable" name="zt_captcha_wp_register_enable" value="<?php echo intval(1);?>"
                                <?php echo esc_html(get_option('zt_captcha_wp_register_enable') ? 'checked':'') ?>>
                                <?php esc_html_e( 'Registration form', ZTCPT_TEXT_DOMAIN );?>
                        </label>
                        <br>
                        <label class="cptch_related">
                            <input type="checkbox" <?php echo esc_attr($disable_captcha_places); ?>
                                id="zt_captcha_wp_lost_password_enable" name="zt_captcha_wp_lost_password_enable"
                                value="<?php echo intval(1);?>"
                                <?php echo esc_html(get_option('zt_captcha_wp_lost_password_enable') ? 'checked':'') ?>>
                                <?php esc_html_e( 'Reset password form', ZTCPT_TEXT_DOMAIN );?>
                        </label>
                        <br>
                        <label class="cptch_related">
                            <input type="checkbox" <?php echo esc_attr($disable_captcha_places); ?>
                                id="zt_captcha_wp_comments_enable" name="zt_captcha_wp_comments_enable" value="<?php echo intval(1);?>"
                                <?php echo esc_html(get_option('zt_captcha_wp_comments_enable') ? 'checked':'') ?>>
                                <?php esc_html_e( 'Comment form', ZTCPT_TEXT_DOMAIN );?>
                        </label>
                        <br>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th class="help-icon"><?php esc_html_e( 'Not Verified Error Message', ZTCPT_TEXT_DOMAIN );?>
                </th>
                <td>
                    <input class="large-text" type="text" name="zt_captcha_error_message" id="zt_captcha_error_message" value="<?php echo esc_html(get_option('zt_captcha_error_message')); ?>" style="max-width: 25rem;">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Select Captcha Type', ZTCPT_TEXT_DOMAIN );?></th>
                <td>
                    <?php
                         $disable='';
                            if(!function_exists('imagecreatetruecolor')){
                                $link = 'https://bobcares.com/blog/php-install-gd-extension/';
                                echo '<p class="warning_toaster">PHP GD extention must be enable to use some captcha types <a target="_blank" href="'.esc_url($link).'">Click here for help</a></p>';
                                $disable='disabled';
                            }
                        ?>
                    <select class="large-text" style="width:100%" class="required" id="zt_captcha_selected_captcha"
                        name="zt_captcha_selected_captcha">
                        <option <?php echo esc_attr($disable); ?>
                            <?php echo esc_html(get_option('zt_captcha_selected_captcha','mathematical_captcha')=='mathematical_captcha' ? 'selected':'') ?>
                            value="mathematical_captcha"><?php esc_html_e( 'Mathematical Captcha', ZTCPT_TEXT_DOMAIN );?></option>
                        <option <?php echo esc_attr($disable); ?>
                            <?php echo esc_html(get_option('zt_captcha_selected_captcha')=='alphzt_captcha' ? 'selected':'') ?>
                            value="alphzt_captcha"><?php esc_html_e( 'Alphabet Image Captcha', ZTCPT_TEXT_DOMAIN );?></option>
                        <option <?php echo esc_attr($disable); ?>
                            <?php echo esc_html(get_option('zt_captcha_selected_captcha')=='number_captcha' ? 'selected':'') ?>
                            value="number_captcha"><?php esc_html_e( 'Numbers Image Captcha', ZTCPT_TEXT_DOMAIN );?></option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>

    <h2 class="captcha_type_settings captcha_type_show_settings_mathematical_captcha"
        style="<?php echo esc_html(get_option('zt_captcha_selected_captcha','mathematical_captcha')=='mathematical_captcha' ? '':'display:none') ?>">
        <?php esc_html_e( 'Mathematics Captcha Settings', ZTCPT_TEXT_DOMAIN );?></h2>
    <table
        style="<?php echo esc_html(get_option('zt_captcha_selected_captcha','mathematical_captcha')=='mathematical_captcha' ? '':'display:none') ?>"
        class="form-table captcha_type_settings captcha_type_show_settings_mathematical_captcha">
        <thead></thead>
        <tfoot></tfoot>
        <tbody>
            <tr>
                <th><?php esc_html_e( 'Algebraic Operation', ZTCPT_TEXT_DOMAIN );?></th>
                <td>
                    <select class="large-text" style="width:100%" class="required" id="zt_captcha_algebraic_operation"
                        name="zt_captcha_algebraic_operation">
                        <option <?php echo esc_html(get_option('zt_captcha_algebraic_operation')=='random' ? 'selected':'') ?>
                            value="random"><?php esc_html_e( 'Random', ZTCPT_TEXT_DOMAIN );?></option>
                        <option <?php echo esc_html(get_option('zt_captcha_algebraic_operation')=='+' ? 'selected':'') ?>
                            value="+"><?php esc_html_e( 'Addition', ZTCPT_TEXT_DOMAIN );?></option>
                        <option <?php echo esc_html(get_option('zt_captcha_algebraic_operation')=='-' ? 'selected':'') ?>
                            value="-"><?php esc_html_e( 'Subtraction', ZTCPT_TEXT_DOMAIN );?></option>
                        <option <?php echo esc_html(get_option('zt_captcha_algebraic_operation')=='*' ? 'selected':'') ?>
                            value="*"><?php esc_html_e( 'Multiplication', ZTCPT_TEXT_DOMAIN );?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'RHS Digits Length', ZTCPT_TEXT_DOMAIN );?></th>
                <td>
                    <select class="large-text" style="width:100%" class="required" required
                        id="zt_captcha_algebraic_rhs_length" name="zt_captcha_algebraic_rhs_length">
                        <option <?php echo esc_html(get_option('zt_captcha_algebraic_rhs_length','0-9')=='0-9' ? 'selected':'') ?> value="0-9"><?php esc_html_e( '1 (0-9)', ZTCPT_TEXT_DOMAIN );?></option>
                        <option <?php echo esc_html(get_option('zt_captcha_algebraic_rhs_length')=='10-99' ? 'selected':'') ?> value="10-99"><?php esc_html_e( '2 (0-99)', ZTCPT_TEXT_DOMAIN );?></option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>

    <h2 class="captcha_type_settings captcha_type_show_settings_alphzt_captcha"
        style="<?php echo esc_html(get_option('zt_captcha_selected_captcha')=='alphzt_captcha' ? '':'display:none') ?>">
        <?php esc_html_e( 'Alphabet Captcha Settings', ZTCPT_TEXT_DOMAIN );?></h2>
    <table style="<?php echo esc_html(get_option('zt_captcha_selected_captcha')=='alphzt_captcha' ? '':'display:none') ?>"
        class="form-table captcha_type_settings captcha_type_show_settings_alphzt_captcha">
        <thead></thead>
        <tfoot></tfoot>
        <tbody>
            <tr>
                <th><?php esc_html_e( 'Include Capital letter (ABCD-XYZ)', ZTCPT_TEXT_DOMAIN );?> </th>
                <td>
                    <input type="checkbox" <?php echo esc_html(get_option('zt_captcha_alpha_capital') ? 'checked':'') ?>
                        name="zt_captcha_alpha_capital">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Include Symbols (!@#$%^&=)', ZTCPT_TEXT_DOMAIN );?></th>
                <td>
                    <input type="checkbox" <?php echo esc_html(get_option('zt_captcha_alpha_symbol') ? 'checked':'') ?>
                        name="zt_captcha_alpha_symbol">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Captcha Length', ZTCPT_TEXT_DOMAIN );?></th>
                <td>
                    <select class="large-text" style="width:100%" class="required" required id="zt_captcha_alpha_length"
                        name="zt_captcha_alpha_length">
                        <option <?php echo esc_html(get_option('zt_captcha_alpha_length','4')=='4' ? 'selected':'') ?> value="<?php echo intval(4);?>">
                        <?php echo esc_html('4');?></option>
                        <option <?php echo esc_html(get_option('zt_captcha_alpha_length')=='5' ? 'selected':'') ?> value="<?php echo intval(5);?>"><?php echo esc_html('5');?>
                        </option>
                        <option <?php echo esc_html(get_option('zt_captcha_alpha_length')=='6' ? 'selected':'') ?> value="<?php echo intval(6);?>"><?php echo esc_html('6');?>
                        </option>
                        <option <?php echo esc_html(get_option('zt_captcha_alpha_length')=='7' ? 'selected':'') ?> value="<?php echo intval(7);?>"><?php echo esc_html('7');?>
                        </option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    <h2 class="captcha_type_settings captcha_type_show_settings_number_captcha"
        style="<?php echo esc_html(get_option('zt_captcha_selected_captcha')=='number_captcha' ? '':'display:none') ?>"><?php esc_html_e( 'Numeric Captcha Settings', ZTCPT_TEXT_DOMAIN );?>
        </h2>
    <table style="<?php echo esc_html(get_option('zt_captcha_selected_captcha')=='number_captcha' ? '':'display:none') ?>"
        class="form-table captcha_type_settings captcha_type_show_settings_number_captcha">
        <thead></thead>
        <tfoot></tfoot>
        <tbody>
            <tr>
                <th><?php esc_html_e( 'Include Symbols (!@#$%^&=)', ZTCPT_TEXT_DOMAIN );?> </th>
                <td>
                    <input type="checkbox" <?php echo esc_html(get_option('zt_captcha_numeric_symbol') ? 'checked':'') ?>
                        name="zt_captcha_numeric_symbol">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Captcha Length', ZTCPT_TEXT_DOMAIN );?> </th>
                <td>
                    <select class="large-text" style="width:100%" class="required" required
                        id="zt_captcha_numeric_length" name="zt_captcha_numeric_length">
                        <option <?php echo esc_html(get_option('zt_captcha_numeric_length','4')=='4' ? 'selected':'') ?>
                            value="<?php echo intval(4);?>"><?php echo esc_html('4');?></option>
                        <option <?php echo esc_html(get_option('zt_captcha_numeric_length')=='5' ? 'selected':'') ?> value="<?php echo intval(5);?>"><?php echo esc_html('5');?>
                        </option>
                        <option <?php echo esc_html(get_option('zt_captcha_numeric_length')=='6' ? 'selected':'') ?> value="<?php echo intval(6);?>"><?php echo esc_html('6');?>
                        </option>
                        <option <?php echo esc_html(get_option('zt_captcha_numeric_length')=='7' ? 'selected':'') ?> value="<?php echo intval(7);?>"><?php echo esc_html('7');?>
                        </option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    <hr>
    </hr>
    <table class="form-table">
        <thead></thead>
        <tfoot></tfoot>
        <tbody>
            <tr>
                <td class="save_captcha"></td>
                <td>
                    <?php wp_nonce_field( 'save_ztcpt_captcha_settings', 'token' ); ?>
                    <button name="action" value="save_ztcpt_captcha_settings" type="submit"
                        class="button button-primary"><?php esc_html_e( 'Save Changes', ZTCPT_TEXT_DOMAIN );?>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</form>
<script>
jQuery(document).ready(function() {
    var show_selected = jQuery('#zt_captcha_selected_captcha').val();
    if (show_selected == 'slide_captcha') {
        jQuery(".image_settings").hide();
    } else {
        jQuery(".image_settings").show();
    }

    jQuery("#append_status").on('click', function(event) {
        clone = jQuery('#cloner_status').html();
        jQuery(".zt_captcha_status_append_before").before(clone);
    });
    jQuery(document).on('change', '#zt_captcha_selected_captcha', function() {
        var show = jQuery(this).val();
        jQuery(".captcha_type_settings").hide();
        jQuery(".captcha_type_show_settings_" + show).show();
        if (show == 'slide_captcha') {
            jQuery(".image_settings").hide();
        } else {
            jQuery(".image_settings").show();
        }
    });

    if(jQuery('.success_toaster').length){
        setTimeout(function(){
            jQuery('.success_toaster').fadeOut(500);
        }, 3000);
    }
});
</script>
<style type="text/css">
    .success_toaster {
        background: #0881084a;
        padding: 5px;
        border-radius: 5px;
        width: 50%;
        color: #086208;
        text-align: center;
    }

    .warning_toaster {
        background: #ff000063;
        padding: 5px;
        border-radius: 5px;
        width: 50%;
        color: #c30000;
        text-align: center;
        margin-bottom: 5px !important;
    }

    .zt_captcha_field {
        display: none;
    }

    th {
        padding-left: 0;
    }

    span.help-icon:after {
        content: '?';
        margin-left: 5px;
        font-size: 10px;
        margin-top: 3px;
        cursor: default;
        color: white;
        position: absolute;
        width: 13px;
        background: #2271b1;
        border-radius: 50%;
        height: 13px;
        text-align: center;
        padding: 1px;
    }

    input.zt_captcha_user_skip_after_login {
        position: relative;
        /* left: 34px; */
        top: 2px
    }

    input.zt_captcha_user_skip_captcha_flag {
        position: relative;
        /* left: 41px; */
    }
    .save_captcha{
        padding: 20px 10px 20px 0 !important;
        width: 200px;

    }
</style>
<?php
}
