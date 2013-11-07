<?php
require_once 'eveapi/factory.php';

class utils {
    function utils() {
        

    }

    public static function get_portrait($charID, $size = 256) {
        $base_url= "http://image.eveonline.com/Character";
        $cached_file = "cache/portrait/".$charID."_".$size.".jpg";
        if (!file_exists($cached_file)){
            //$size = "256";
            $param = $charID."_".$size.".jpg";
            $portrait = imagecreatefromjpeg($base_url."/".$param);
            imagejpeg($portrait,$cached_file);
            imagedestroy($portrait);
        }
        return "lib/".$cached_file;
    }

    public static function get_icon($iconFile,$size = 16) {
        if(preg_match('|^res:/UI/Texture/Icons/(.*)$|', $iconFile, $match)) {
            $iconFileName = $match[1];

        } else {
            $icon = explode('_', $iconFile);
            $icon[0] = preg_replace('/^0/', '', $icon[0]);
            $icon[1] = preg_replace('/^0/', '', $icon[1]);
            $iconFileName = $icon[0].'_'.$size.'_'.$icon[1].'.png';
            if(!file_exists("/home/market/www/".ICON."items/".$iconFileName))
                $iconFileName = $icon[0].'_32_'.$icon[1].'.png';
        }
        return ICON."items/".$iconFileName;

    }

    public static function get_logo($corpID, $size = 128) {
        $filename = 'cache/corps/'.$corpID.'_'.$size.'.jpg';

        if (!file_exists($filename)) {
            $eAPI = AleFactory::getEVEOnline();
            $eAPI->setCredentials(API_KEY, API_VCODE);
            $eAPI->setCharacterID(API_CHAR);
               
            $param = array (corporationID => $corpID);
            $corp = $eAPI->corp->CorporationSheet($param);
            $logo = $corp->result->logo;

            $data["shape1"] = $logo->shape1;
            $data["shape2"] = $logo->shape2;
            $data["shape3"] = $logo->shape3;
            $data["colour1"] = $logo->color1;
            $data["colour2"] = $logo->color2;
            $data["colour3"] = $logo->color3;

            CorporationLogo($data, $size, $filename);
        }
        return "lib/".$filename;
    }

    private static function CorporationLogo($data, $size = 64, $filename)
    {
        /* Generates corp logo defined by the parameters in data object. The data
    object may be an eveapi logo element from the CorporationSheet, a dict
    containing the shapes and colors, or a sequence containing a shapes- and colors
    sequence. Optionally, size other than the default 64px may be specified, and
    transparency can be turned off, in which case it will render the logo on
    a background with the color of your choice if specified, otherwise black.*/

        $resourcePath = "../img/corplogos";
        
        // eveapi corpsheet logo data
        $shape1 = $data["shape1"];
        $shape2 = $data["shape2"];
        $shape3 = $data["shape3"];
        
        $colour1 = $data["colour1"];
        $colour2 = $data["colour2"];
        $colour3 = $data["colour3"];
        
        //$logo = imagecreatefrompng($resourcePath . "/baselogo.png"); // open image
        $logo = imagecreatetruecolor(64,64);
        imagealphablending($logo, 1);
        imagesavealpha($logo, 1);

        if ($shape3)
        {
            $layer3 = imagecreatefrompng($resourcePath . "/" . $colour3 . "/" . $shape3 . ".png"); // open image
            imagealphablending($layer3, 1); // setting alpha blending on
            imagesavealpha($layer3, 1);
            imagecopy( $logo, $layer3, 0 , 0 , 0, 0,64 , 64);
        } 
        
        if ($shape2)
        {
            $layer2 = imagecreatefrompng($resourcePath . "/" . $colour2 . "/" . $shape2 . ".png"); // open image
            imagealphablending($layer2, 1); // setting alpha blending on
            imagesavealpha($layer2, 1);
            imagecopy( $logo , $layer2 , 0 , 0 , 0 , 0 , 64 , 64 );
        }
        if ($shape1)
        {
            $layer1 = imagecreatefrompng($resourcePath . "/" . $colour1 . "/" . $shape1 . ".png"); // open image
            imagealphablending($layer1, 1); // setting alpha blending on
            imagesavealpha($layer1, 1);
            imagecopy( $logo , $layer1 , 0 , 0 , 0 , 0 , 64 , 64 );
        } 
        
        for ($x=0 ; $x <= 64; $x++)
        {
            for ($y=0 ; $y <= 64; $y++)
            {
                $rgb = imagecolorat( $logo, $x, $y);
                list($r, $g, $b, $a) = imagecolorsforindex($logo, $rgb);
                
                if ($shape1)
                {
                    $rgb1 = imagecolorat( $layer1, $x, $y);
                    list($r1, $g1, $b1, $alayer1) = imagecolorsforindex($layer1, $rgb1);
                    $a1 = ((255 - $alayer1) / 255.0);
                } else {
                    $a1 = 1.0;
                } 
                if ($shape2)
                {
                    $rgb2 = imagecolorat( $layer2, $x, $y);
                    list($r2, $g2, $b2, $alayer2) = imagecolorsforindex($layer2, $rgb2);
                    $a2 = ((255 - $alayer2) / 255.0);
                } else {
                    $a2 = 1.0;
                } 
                if ($shape3)
                {
                    $rgb3 = imagecolorat( $layer3, $x, $y);
                    list($r3, $g3, $b3, $alayer3) = imagecolorsforindex($layer3, $rgb3);
                    $a3 = ((255 - $alayer3) / 255.0);
                } else {
                    $a3 = 1.0;
                } 
                $a = (1.0-($a1*$a2*$a3));
                if ($a)
                {
                    $newpix = imagecolorallocatealpha($logo, int($r/$a), int($g/$a), int($b/$a), int(255*$a));
                    imagesetpixel($logo, $x, $y, $newpix);
                }
            }
        }//*/
        
        if ($size != 64)
        {
            
            $newsize = imagecreatetruecolor($size, $size);
            imagealphablending ( $newsize , true );
            if(function_exists('imageantialias')) imageantialias ( $newsize , true );
            imagecopyresampled($newsize, $logo, 0, 0, 0, 0, $size, $size, 64, 64);
            //imagepng ( $newsize , "cache/corps/" . $filename . "_" . $size . ".jpg" );
            
        } else {    
            // write logo to disk
            //imagepng ( $logo , "img/corps/" . $filename . ".jpg" );
        }
        
        imagejpeg ( $logo , $filename );
        
        imagedestroy($logo);
        if ($shape1)
            imagedestroy($layer1);
        if ($shape2)
            imagedestroy($layer2);
        if ($shape3)
            imagedestroy($layer3);
    }


    public static function ceiling($number, $significance = 1)
    {
        return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
    }


    public static function flooring($number, $significance = 1)
    {
        return ( is_numeric($number) && is_numeric($significance) ) ? (floor($number/$significance)*$significance) : false;
    }


    public static function speround($number,$precision = 1)
    {
      return ( is_numeric($number) && is_numeric($precision) ) ? round((pow(10,log10($number)-(int)(log10($number)))),$precision) * pow(10,(int)(log10($number))) : false;
    }

    public static function b2n($bValue = false) {
        if(is_numeric($bValue))
            return ($bValue ? 1 : 0);
        else {
            return (strtolower($bValue) == 'false' ? 0 : 1);
        }
    }

}

?>