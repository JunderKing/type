<?php
header("Content-type:text/html;charset=utf-8");

header ( 'Content-type: image/png' ); 

$font_size = 18; //字体大小 14px

$text = '有朋自远方来。不亦乐呼'; 

$font = 'fonts/simsun.ttc'; 

$font  =   iconv("UTF-8","gb2312",$font);

$fontarea = imagettfbbox($font_size,0,$font,$text); //确定会变化的字符串的位置

$text_width = $fontarea[2]-$fontarea[0]+($font_size/3); //字符串文本框长度

$text_height = $fontarea[1]-$fontarea[7]+($font_size/3); ////字符串文本框高度

$im = imagecreate( $text_width , $text_height ); 

$white = imagecolorallocate($im, 255,255,255); //定义透明色

$red = imagecolorallocate ( $im , 255 , 0 , 0);  //文本色彩

imagettftext ( $im , $font_size , 0 , 0, $text_height-($font_size/2.5) , $red , $font , $text ); 

imagecolortransparent($im,$white);

imagepng ( $im ); 

imagedestroy ($im); 
