<?php
function generateImg($source, $text1, $text2, $text3, $font = './msyhbd.ttf') {
    $date = '' . date ( 'Ymd' ) . '/';
    $img = $date . md5 ( $source . $text1 . $text2 . $text3 ) . '.jpg';
    if (file_exists ( './' . $img )) {
        return $img;
    }
 
    $main = imagecreatefromjpeg ( $source );
 
    $width = imagesx ( $main );
    $height = imagesy ( $main );
 
    $target = imagecreatetruecolor ( $width, $height );
 
    $white = imagecolorallocate ( $target, 255, 255, 255 );
    imagefill ( $target, 0, 0, $white );
 
    imagecopyresampled ( $target, $main, 0, 0, 0, 0, $width, $height, $width, $height );
 
    $fontSize = 18;//像素字体
    $fontColor = imagecolorallocate ( $target, 255, 0, 0 );//字的RGB颜色
    $fontBox = imagettfbbox($fontSize, 0, $font, $text1);//文字水平居中实质
    imagettftext ( $target, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), 190, $fontColor, $font, $text1 );
 
    $fontBox = imagettfbbox($fontSize, 0, $font, $text2);
    imagettftext ( $target, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), 370, $fontColor, $font, $text2 );
 
    $fontBox = imagettfbbox($fontSize, 0, $font, $text3);
    imagettftext ( $target, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), 560, $fontColor, $font, $text3 );
 
    //imageantialias($target, true);//抗锯齿，有些PHP版本有问题，谨慎使用
 
    imagefilledpolygon ( $target, array (10 + 0, 0 + 142, 0, 12 + 142, 20 + 0, 12 + 142), 3, $fontColor );//画三角形
    imageline($target, 100, 200, 20, 142, $fontColor);//画线
    imagefilledrectangle ( $target, 50, 100, 250, 150, $fontColor );//画矩形
 
    //bof of 合成图片
    $child1 = imagecreatefromjpeg ( 'http://gtms01.alicdn.com/tps/i1/T1N0pxFEhaXXXxK1nM-357-88.jpg' );
    imagecopymerge ( $target, $child1, 0, 400, 0, 0, imagesx ( $child1 ), imagesy ( $child1 ), 100 );
    //eof of 合成图片
 
    @mkdir ( './' . $date );
    imagejpeg ( $target, './' . $img, 95 );
 
    imagedestroy ( $main );
    imagedestroy ( $target );
    imagedestroy ( $child1 );
    return $img;
}
//http://my.oschina.net/cart/
generateImg ( 'http://1.popular.sinaapp.com/munv/pic.jpg', 'my.oschina.net/cart', 'PHP文字水平居中', '3个字' );
exit;




/** 
 * 作者：friker 
 * 开发时间：20160516 
 * 功能：图片处理 
 * 
 */  
//class ImageController extends CI_Controller{  
$index = new ImageController;
$index->index();
class ImageController{
  
    //public function __construct()  
    //{  
        //parent::__construct();  
        //date_default_timezone_set('Asia/Shanghai');  
        //error_reporting( E_ALL&~E_NOTICE&~E_WARNING);  
        //$this->load->library('curl');  
    //}  
  
    /** 
     * @todo : 本函数用于 将方形的图片压缩后 
     *         再裁减成圆形 做成logo 
     *         与背景图合并 
     * @return 返回url 
     */  
    public function index(){  
        //头像  
        $headimgurl = './avatar.jpg';  
        //背景图  
        $bgurl = './demo.jpeg';  
        $imgs['dst'] = $bgurl;  
        //第一步 压缩图片  
        $imggzip = $this->resize_img($headimgurl);  
        //第二步 裁减成圆角图片  
        $imgs['src'] = $this->test($imggzip);  
        //第三步 合并图片  
        $dest = $this->mergerImg($imgs);  
    }  
  
    public function resize_img($url,$path='./'){  
        $imgname = $path.uniqid().'.jpg';  
        $file = $url;  
        list($width, $height) = getimagesize($file); //获取原图尺寸  
        $percent = (110/$width);  
        //缩放尺寸  
        $newwidth = $width * $percent;  
        $newheight = $height * $percent;  
        $src_im = imagecreatefromjpeg($file);  
        $dst_im = imagecreatetruecolor($newwidth, $newheight);  
        imagecopyresized($dst_im, $src_im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);  
        imagejpeg($dst_im, $imgname); //输出压缩后的图片  
        imagedestroy($dst_im);  
        imagedestroy($src_im);  
        return $imgname;  
    }  
  
    //第一步生成圆角图片  
    public function test($url,$path='./'){  
        $w = 110;  $h=110; // original size  
        $original_path= $url;  
        $dest_path = $path.uniqid().'.png';  
        $src = imagecreatefromstring(file_get_contents($original_path));  
        $newpic = imagecreatetruecolor($w,$h);  
        imagealphablending($newpic,false);  
        $transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);  
        $r=$w/2;  
        for($x=0;$x<$w;$x++)  
            for($y=0;$y<$h;$y++){  
                $c = imagecolorat($src,$x,$y);  
                $_x = $x - $w/2;  
                $_y = $y - $h/2;  
                if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){  
                    imagesetpixel($newpic,$x,$y,$c);  
                }else{  
                    imagesetpixel($newpic,$x,$y,$transparent);  
                }  
            }  
        imagesavealpha($newpic, true);  
        // header('Content-Type: image/png');  
        imagepng($newpic, $dest_path);  
        imagedestroy($newpic);  
        imagedestroy($src);  
        unlink($url);  
        return $dest_path;  
    }  
  
    //php 合并图片  
    public function mergerImg($imgs,$path='./') {  
  
        $imgname = $path.rand(1000,9999).uniqid().'.jpg';  
        list($max_width, $max_height) = getimagesize($imgs['dst']);  
        $dests = imagecreatetruecolor($max_width, $max_height);  
        $dst_im = imagecreatefromjpeg($imgs['dst']);  
        imagecopy($dests,$dst_im,0,0,0,0,$max_width,$max_height);  
        imagedestroy($dst_im);  
  
        $src_im = imagecreatefrompng($imgs['src']);  
        $src_info = getimagesize($imgs['src']);  
        imagecopy($dests,$src_im,270,202,0,0,$src_info[0],$src_info[1]);  
        imagedestroy($src_im);  
  
        // var_dump($imgs);exit;  
        // header("Content-type: image/jpeg");  
        imagejpeg($dests,$imgname);  
        // unlink($imgs['dst']);  
        unlink($imgs['src']);  
        return $imgname;  
    }  
}  
//$bigImgPath = './demo.jpeg';
//$qCodePath = './avatar.jpg';
 
//$bigImg = imagecreatefromstring(file_get_contents($bigImgPath));
//$qCodeImg = imagecreatefromstring(file_get_contents($qCodePath));
 
//list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($qCodePath);
//// imagecopymerge使用注解
//imagecopymerge($bigImg, $qCodeImg, 200, 300, 0, 0, $qCodeWidth, $qCodeHight, 100);
 
//list($bigWidth, $bigHight, $bigType) = getimagesize($bigImgPath);
 
 
//switch ($bigType) {
    //case 1: //gif
        //header('Content-Type:image/gif');
        //imagegif($bigImg);
        //break;
    //case 2: //jpg
        //header('Content-Type:image/jpg');
        //imagejpeg($bigImg);
        //break;
    //case 3: //jpg
        //header('Content-Type:image/png');
        //imagepng($bigImg);
        //break;
    //default:
        //# code...
        //break;
//}
 
//imagedestroy($bigImg);
//imagedestroy($qCodeImg);
//try {
    //throw new Exception(json_encode(array('foo' => 1, 'bar' => 2)));
    ////throw new Exception('hello');
//} catch (Exception $e) {
    //echo $e->getMessage();
//}
//exit;
//$resJson = '没有返回数据';
//$resArr = json_decode($resJson, true);
//var_dump($resArr);
//var_dump($resArr['foo']['bar']);
//exit;
////$result = include_once(__DIR__ . '/include.php', __DIR__ . '/include2.php');
//var_dump($result);
