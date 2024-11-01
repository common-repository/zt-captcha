<?php
if(!class_exists('ZTCPT_Captcha') ):
class ZTCPT_Captcha
{
    public function ztcpt_phpcaptcha($text,$textColor='#162453',$backgroundColor='#ffffff',$imgWidth='120',$imgHeight='40',$noiceLines='3',$noiceDots='10',$noiceColor='#162453',$fontSize=75)
    {
        ob_clean();
        /* Settings */
        $font = ZTCPT_CAPTCHA_PLUGIN_DIR.'fonts/monofont.ttf';

        /* font */
        $textColor=$this->ztcpt_hexToRGB($textColor);

        $fontSize = $imgHeight * ($fontSize/100);

        $im = imagecreatetruecolor($imgWidth, $imgHeight);
        $textColor = imagecolorallocate($im, $textColor['r'],$textColor['g'],$textColor['b']);

        $backgroundColor = $this->ztcpt_hexToRGB($backgroundColor);
        $backgroundColor = imagecolorallocate($im, $backgroundColor['r'],$backgroundColor['g'],$backgroundColor['b']);

        imagefill($im,0,0,$backgroundColor);
        list($x, $y) = $this->ztcpt_ImageTTFCenter($im, $text, $font, $fontSize);
        imagettftext($im, $fontSize, 0, $x, $y, $textColor, $font, $text);
        /* generating lines randomly in background of image */
        $noiceColor=$this->ztcpt_hexToRGB($noiceColor);
        $noiceColor = imagecolorallocate($im, $noiceColor['r'],$noiceColor['g'],$noiceColor['b']);
        if($noiceLines>0){
            for( $i=0; $i<$noiceLines; $i++ ) {
                imageline($im, mt_rand(0,$imgWidth), mt_rand(0,$imgHeight),mt_rand(0,$imgWidth), mt_rand(0,$imgHeight), $noiceColor);
            }
        }

        if($noiceDots>0){
            
            /* generating the dots randomly in background */
            for( $i=0; $i<$noiceDots; $i++ ) {
                imagefilledellipse($im, mt_rand(0,$imgWidth),mt_rand(0,$imgHeight), 3, 3, $noiceColor);
            }
        }
        $zt_captcha_image_quality=50;
        imagejpeg($im,NULL,$zt_captcha_image_quality);
        /* Showing image */
        header('Content-Type: image/jpeg');
        /* defining the image type to be shown in browser widow */
        imagedestroy($im);
        /* Destroying image instance */
    }

    /*function to convert hex value to rgb array*/
    protected function ztcpt_hexToRGB($colour)
    {
            if ( $colour[0] == '#' ) {
                $colour = substr( $colour, 1 );
            }
            if ( strlen( $colour ) == 6 ) {
                list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
            } elseif ( strlen( $colour ) == 3 ) {
                list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
            } else {
                return false;
            }
            $r = hexdec( $r );
            $g = hexdec( $g );
            $b = hexdec( $b );
            return array( 'r' => $r, 'g' => $g, 'b' => $b );
    }


    /*function to get center position on image*/
    protected function ztcpt_ImageTTFCenter($image, $text, $font, $size, $angle = 8) 
    {
        $xi = imagesx($image);
        $yi = imagesy($image);
        $box = imagettfbbox($size, $angle, $font, $text);
        $xr = abs(max($box[2], $box[4]))+5;
        $yr = abs(max($box[5], $box[7]));
        $x = intval(($xi - $xr) / 2);
        $y = intval(($yi + $yr) / 2);
        return array($x, $y);
    }

    public function ztcpt_generate_captcha_string()
    {
       $selected_captcha=get_option('zt_captcha_selected_captcha','mathematical_captcha');
       $generate_captcha_string='';
        switch ($selected_captcha) {
            case 'mathematical_captcha':
                $generate_captcha_string=$this->mathematical_captcha();
                break;
            case 'alphzt_captcha':
                $generate_captcha_string=$this->alphzt_captcha();
                break;
            case 'number_captcha':
                $generate_captcha_string=$this->number_captcha();
                break;
            
            default:
                $generate_captcha_string=$this->mathematical_captcha();
                break;
        }
        return $generate_captcha_string;
    }

    public function mathematical_captcha()
    {
        $operation=get_option('zt_captcha_algebraic_operation','random');
        if($operation=='random'){
            $temp_random_operations=['+','-','*'];
            $randm_int=random_int(0,2);
            $operation=$temp_random_operations[$randm_int];
            $operation_str=$temp_random_operations[$randm_int];
        }

        $length=get_option('zt_captcha_algebraic_rhs_length','0-9');
        $length_arr=explode('-',$length);
        $a=random_int($length_arr[0],$length_arr[1]);
        $b=random_int($length_arr[0],$length_arr[1]);

        switch ($operation) {
            case '+':
                $captcha_string=$a.'+'.$b.'=?';
                $captcha_aswer=($a+$b);
                break;
            case '-':
                $captcha_string= $a > $b ? $a.'-'.$b.'=?' : $b.'-'.$a.'=?';
                $captcha_aswer=abs($a-$b);
                break;
            case '*':
                $captcha_string=$a.'X'.$b.'=?';
                $captcha_aswer=($a*$b);
                break;
                    
            default:
                
                break;
        }
        ztcptCaptchaSessionStart();
            $place = sanitize_text_field($_REQUEST['place']);
            if($place){
                $_SESSION[ZTCPT_SESSION_STORAGE][$place]=$captcha_aswer;
            }
        return $captcha_string;
    }

    public function alphzt_captcha()
    {
        /*Get Settings */
        $aptcha_alpha_capital=get_option('zt_captcha_alpha_capital',0);
        $captcha_alpha_symbol=get_option('zt_captcha_alpha_symbol',0);
        $captcha_alpha_length=get_option('zt_captcha_alpha_length',4);

        $characters='abcdefghijklmnopqrstuvwxyz';

        if($aptcha_alpha_capital){
            $characters .='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if($captcha_alpha_symbol){
            $characters .='!@#$%^&*=';
        }
        
        $answer =$this->getRandomString($captcha_alpha_length,$characters);

        if($captcha_alpha_symbol && !$aptcha_alpha_capital){
            while(!$this->string_have_symbol($answer)){
                $answer =$this->getRandomString($captcha_alpha_length,$characters);
            }
        }

        if(!$captcha_alpha_symbol && $aptcha_alpha_capital){
            while(!$this->string_have_capital($answer)){
                $answer =$this->getRandomString($captcha_alpha_length,$characters);
            }
        }

        if($captcha_alpha_symbol && $aptcha_alpha_capital){
            
            while(!($this->string_have_symbol($answer) && $this->string_have_capital($answer))){
                $answer =$this->getRandomString($captcha_alpha_length,$characters);
            }
        }

        ztcptCaptchaSessionStart();
        $place = sanitize_text_field($_REQUEST['place']);
        if($place){
            $_SESSION[ZTCPT_SESSION_STORAGE][$place]=$answer;
        }
        return $answer;
    }

    public function number_captcha()
    {
        /*Get Settings */
        $captcha_numeric_symbol=get_option('zt_captcha_numeric_symbol',0);
        $captcha_numeric_length=get_option('zt_captcha_numeric_length',4);
        $characters='1234567890';
        if($captcha_numeric_symbol){
            $characters .='!@#$%^&=';
        }

        $answer=$this->getRandomString($captcha_numeric_length,$characters);
        if($captcha_numeric_symbol){
            while(!$this->string_have_symbol($answer)){
                $answer=$this->getRandomString($captcha_numeric_length,$characters);
            }
        }

        ztcptCaptchaSessionStart();
        $place = sanitize_text_field($_REQUEST['place']);
        if($place){
            $_SESSION[ZTCPT_SESSION_STORAGE][$place]=$answer;
        }
        return $answer;
    }

    public function getRandomString($n,$characters) {
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = random_int(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    function string_have_symbol($my_string){
        if (preg_match('/[!@#$%^&=]/', $my_string))
        {
            return true;
        }else{
            return false;
        }
    }

    function string_have_capital($my_string){
        if (preg_match('/[ABCDEFGHIJKLMNOPQRSTUVWXYZ]/', $my_string))
        {
            return true;
        }else{
            return false;
        }
    }
}

$zt_captcha=new ZTCPT_Captcha;

endif;
