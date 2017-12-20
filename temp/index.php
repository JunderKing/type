<?php
$http = new HttpController;
$http->index();

class HttpController {
    private $appId;
    private $appSecret;

    private $openId;
    private $myName;
    private $input;

    private $accessToken;
    private $bgUrl;
    private $avatarUrl;
    private $nickname;

    private $resultUrl;
    private $mediaId;

    public function index() {
        //获取参数
        $this->getParam();
        //获取用户信息
        $this->getUserInfo();
        //处理图片
        $handle = new ImageController;
        $this->resultUrl = $handle->index($this->bgUrl, $avatarUrl, $nickname);
        //上传素材
        $this->uploadImage();
        //返回结果
        $this->response();
    }

    public function getParam() {
        $postStr = file_get_contents("php://input");
        if (!$postStr) {
            return false;
        }
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->openId = $postObj->FromUserName;
        $this->myName = $postObj->ToUserName;
        $this->input = trim($postObj->Content);
    }

    public function getUserInfo() {
        if (!$this->openId) {
            return false;
        }
        $appId = $this->appId;
        $appSecret = $this->appSecret;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
        $resJson = file_get_contents($url);
        $resArr = json_decode($resJson, true);
        $accessToken =$this->accessToken = $resArr['access_token'];
        $openId = $this->openId;
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openId";
        $resJson = file_get_contents($url);
        $resArr = json_decode($resJson, true);
        $this->avatarUrl = $resArr['headimgurl'];
        $this->nickname = $resArr['nickname'];
    }

    public function uploadImage () {
        if (!$this->resultUrl) {
            return false;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->accessToken."&type=image";
        $file = __DIR__ . substr($this->resultUrl, 1);
        $data = array('media'=>new CURLFile($file));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $resJson = curl_exec($curl);
        $resArr = json_decode($resJson, true);
        $this->mediaId = $resArr['media_id'];
        curl_close($curl);
    }

    public function response() {
        $time = time();
        if ($this->mediaId) {
            $reply = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[image]]></MsgType><Image><MediaId>< ![CDATA[%s] ]></MediaId></Image></xml>";
            $returnStr = sprintf($reply, $this->openId, $this->myName, $time, $this->mediaId);
        } else {
            $reply = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content><FuncFlag>0</FuncFlag></xml>";
            $text = "请重试";
            $resultStr = sprintf($reply, $this->openId, $this->myName, $time, $text);
        }
    }
}

class ImageController{
    private $avatarDiam = 110;
    private $avatarPosX = 220;
    private $avatarPosY = 220;

    public function index($bgUrl = './demo.jpeg', $avatarUrl = './avatar.jpg', $nickname = '🌞🔥Jun.K') {
        $startTime = microtime(true);
        //第一步 压缩图片
        $avatarZip = $this->resizeImage($avatarUrl);
        //第二步 裁减成圆角图片
        $avatarUrl = $this->cropImage($avatarZip);
        unlink($avatarZip);
        //第三步 合并图片
        $tempUrl = $this->mergeImage($bgUrl, $avatarUrl);
        unlink($avatarUrl);
        $deltaTime = microtime(true) - $startTime;
        echo "时间：$deltaTime 秒";
        //第四步 添加文字
        $nickname = $this->filterEmoji($nickname);
        $resultUrl = $this->addText($tempUrl, $nickname);
        return $resultUrl;
    }
  
    public function resizeImage($imageUrl, $path='./'){
        $destImage = $path.uniqid().'.jpg';
        list($width, $height) = getimagesize($imageUrl);
        $widthScale = ($this->avatarDiam / $width);
        $heightScale = ($this->avatarDiam / $height);
        $newWidth = $width * $widthScale;
        $newHeight = $height * $heightScale;
        $srcImage = imagecreatefromjpeg($imageUrl);
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresized($newImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($newImage, $destImage); //输出压缩后的图片
        imagedestroy($newImage);
        imagedestroy($srcImage);
        return $destImage;
    }
  
    //第一步生成圆角的png图片  
    public function cropImage($imageUrl, $path='./'){  
        $original_path= $imageUrl;  
        $destImage = $path.uniqid().'.png';  
        $srcImage = imagecreatefromstring(file_get_contents($imageUrl));
        //创建一张空白图片
        $newImage = imagecreatetruecolor($this->avatarDiam, $this->avatarDiam);  
        imagealphablending($newImage, false);  
        //将空白图片设置为完全透明
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);  
        $diam = $this->avatarDiam;
        $radius = $diam / 2;
        for($x = 0; $x < $diam; $x++) {
            for($y = 0; $y < $diam; $y++){  
                $color = imagecolorat($srcImage, $x, $y);  
                $_x = $x - $diam / 2;  
                $_y = $y - $diam / 2;  
                if((($_x * $_x) + ($_y * $_y)) < ($radius * $radius)){  
                    imagesetpixel($newImage, $x, $y, $color);  
                }else{  
                    imagesetpixel($newImage, $x, $y, $transparent);  
                }  
            }  
        }
        imagesavealpha($newImage, true);  
        imagepng($newImage, $destImage);
        imagedestroy($newImage);
        imagedestroy($srcImage);
        // 删除处理前文件
        return $destImage;  
    }  
  
    //php 合并图片  
    public function mergeImage($bgUrl, $avatarUrl, $path='./') {  
        $destImage = $path.rand(1000,9999).uniqid().'.jpg';
        $destImage = './out.jpeg';
        list($max_width, $max_height) = getimagesize($bgUrl);
        $dests = imagecreatetruecolor($max_width, $max_height);
        $dst_im = imagecreatefromjpeg($bgUrl);
        imagecopy($dests, $dst_im, 0, 0, 0, 0, $max_width, $max_height);
        imagedestroy($dst_im);
  
        $src_im = imagecreatefrompng($avatarUrl);
        list($avatarWidth, $avatarHeight) = getimagesize($avatarUrl);
        imagecopy($dests, $src_im, $this->avatarPosX, $this->avatarPosY, 0, 0, $avatarWidth, $avatarHeight);
        imagedestroy($src_im);
  
        imagejpeg($dests, $destImage);  
        return $destImage;  
    }

    // 添加文字
    public function addText($bgUrl, $text) {
        $font_size = 18; //字体大小 14px
        //$text = '有朋自远方来。不亦乐呼'; 
        //$text = '🌞🔥依一哥';
        $text = mb_convert_encoding($text, 'utf8mb4');
        $font = './msyhbd.ttf'; 
        //$font = iconv("UTF-8", "gb2312", $font);
        $fontarea = imagettfbbox($font_size, 0, $font, $text); //确定会变化的字符串的位置
        //$text_width = $fontarea[2] - $fontarea[0] + ($font_size/3); //字符串文本框长度
        $textWidth = $fontarea[2] - $fontarea[0];
        //$text_height = $fontarea[1] - $fontarea[7] + ($font_size/3); ////字符串文本框高度
        $textHeight = $fontarea[1] - $fontarea[7];
        //$im = imagecreate($textWidth, $textHeight); 
        $im = imagecreatefromjpeg($bgUrl);
        $whiteColor = imagecolorallocate($im, 255, 255, 255); //定义透明色
        $textColor = imagecolorallocate($im, 0, 0, 0);  //文本色彩
        ////imagettftext($im, $font_size, 0, 0, $text_height - ($font_size/2.5) ,$textColor ,$font ,$text); 
        imagettftext($im, $font_size, 0, 0, $text_height, $textColor, $font, $text); 
        //imagecolortransparent($im, $whiteColor);
        $filename = './outpub.png';
        imagepng($im, $filename); 
        imagedestroy($im); 
        return $filename;
    }

    public function filterEmoji($str) {
        $str = preg_replace_callback( '/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);
        return $str;
    }
}  
