<?php
/**
 * 验证码类
 * User: wangql
 * Date: 2015/4/14
 * Time: 9:55
 * Author:wangwf Modify
 * Date: 2015/6/9
 * eg：
 * 
    $obj = new Verify();
    //添加验证码标示
    $obj->randrsi();//生成验证码
    $obj->draw();//画图，旋转,扭曲,干扰线
 */
class VerifyCode {
    
    var $Image        = "";
    var $tmpimg       = "";
    var $iscale       = 5;
    var $Width        = 70;           //图片宽
    var $Height       = 25;           //图片高
    var $Length       = 4;            //验证码位数
    var $BgColor      = "#FFEFDB";    //背景色
    var $line_color   = '#707070';
    var $gdlinecolor  = "";
    var $ttf_file     = '';           //字体文件位置
    var $font_ratio   = 0.65;          //验证码是否完全显示 1默认完全显示 
    var $code         = array();
    var $captcha_code = '';
    var $perturbation = 0.6;         //字体扭曲程度
    var $TFonts       = array("font.ttf");
    var $TFontSize    = array(15,16); //字体大小范围
    var $TFontAngle   = array(-3,3); //旋转角度
    var $TLine        = true;//是否画干扰线,干扰线的样式有2种
    var $num_lines    = 2;//干扰线条数
    var $TLine_length = 70;//干扰线长度
    var $TPadden      = 1.0;//字符间距
    var $Txbase       = 5;//x轴两边距离
    var $Tybase       = 5 ;//y轴两边距离
    var $FontColors   = array('#f36161','#6bc146','#5368bd');  //字体颜色,红绿蓝
    var $Chars        = "2345678abcdefhkmnpqrstuvwxyzABCDEFGHKLMNPQRUVWXY";//验证码范围（字母数字）
        
    /**
     * 输出验证码并把验证码的值保存的session中
     * 验证码保存到session的格式为： array('verifyCode' => '验证码值');
     * @access public
     * @param string $id 要生成验证码的标识
     * @return void
     */
    public  function randrsi() //生成验证码
    {
	     $this->TFontAngle=range($this->TFontAngle[0],$this->TFontAngle[1]);
	     $this->TFontSize=range($this->TFontSize[0],$this->TFontSize[1]);
	
	     $arr=array();
	     $this->ttf_file= dirname(__FILE__)."/".$this->TFonts[0];
	
	     $charlen=strlen($this->Chars)-1;
	     $anglelen=count($this->TFontAngle)-1;   //角度范围
	     $fontsizelen=count($this->TFontSize)-1; //字体范围
	     $fontcolorlen=count($this->FontColors)-1;//颜色范围
	
	     for($i=0;$i<$this->Length;$i++) //得到字符与颜色
	     {
		     //$char=$this->Chars[mt_rand(0,$charlen)]; //得到字符
		     //$this->captcha_code.=$char;
		                    
		     $char=$this->Chars[rand(0,$charlen)]; ///得到字符
		     $angle=$this->TFontAngle[rand(0,$anglelen)]; ///旋转角度
		     $fontsize=$this->TFontSize[rand(0,$fontsizelen)]; ///字体大小
		     $fontcolor=$this->FontColors[rand(0,$fontcolorlen)]; ///字体大小
		     $bound=$this->_calculateTextBox($fontsize,$angle,$this->ttf_file,$char); ///得到范围
		     $arr[]=array($fontsize,$angle,$fontcolor,$char,$this->ttf_file,$bound);  ///得到矩形框
		     $this->captcha_code.=$char;
	     }

	     $this->code=$arr; //验证码
	     return $this->captcha_code; 
      }

        public function draw() //画图
        {
	        if(empty($this->captcha_code)) $this->randrsi();
	        $this->Image = imagecreatetruecolor( $this->Width, $this->Height );
	        $this->tmpimg= imagecreatetruecolor($this->Width * $this->iscale, $this->Height * $this->iscale);
	        $line_color_arr    = $this->_getColor($this->line_color);
	        $this->gdlinecolor = imagecolorallocate($this->Image,$line_color_arr[0],$line_color_arr[1],$line_color_arr[2]);
	
	        $back = $this->_getColor2($this->_getColor( $this->BgColor)); //背景颜色
	        imageFilledrectangle($this->Image, 0, 0, $this->Width, $this->Height, $back); //填充背景
	        imagefilledrectangle($this->tmpimg, 0, 0,$this->Width * $this->iscale, $this->Height * $this->iscale,$back);//填充背景
	
	        $color = '';
	        $fontcolorlen = count($this->FontColors)-1;
	        $fontcolor = $this->FontColors[mt_rand(0,$fontcolorlen)]; 
	        $color = $this->_getColor2($this->_getColor($fontcolor));//每个字统一颜色
	        $this->gdlinecolor = $color;//干扰线的颜色可与字体一致。
	        imagepalettecopy($this->tmpimg, $this->Image);
	
	        $width2  = $this->Width * $this->iscale;
	        $height2 = $this->Height * $this->iscale;
	        $height2 = $this->Height * $this->iscale;
	
	        $ratio   = ($this->font_ratio) ? $this->font_ratio : 0.4;
	        if ((float)$ratio < 0.1 || (float)$ratio >= 1) {
	            $ratio = 0.4;
	        }
	
	        $font_size = $height2 * $ratio;
	        foreach ($this->code as $v) //逐个画字符，随机调整字符间距，实现黏连效果 方案2
	        {
	        	$bound=$v[5];
	            imagettftext($this->tmpimg, $font_size, $v[1], $this->Txbase*$this->iscale+10, $bound['height']*$this->iscale+20,$color , $v[4], $v[3]);
	            $this->Txbase=$this->Txbase+$bound['width']*$this->TPadden-$bound['left'];//计算下一个左边距
	        }
	
	        $this->distortedCopy();
	        //$this->TLine?$this->_wirteSinLine($color,$this->TLine_length):""; //画干扰线方案1
	        if($this->TLine) $this->drawLines(); //画干扰线方案2
	
	        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
	        header('Cache-Control: post-check=0, pre-check=0', false);
	        header('Pragma: no-cache');
	        header("Content-Type: image/png");
	        imagepng( $this->Image);
	        imagedestroy($this->Image);
        }
        
        /**
         * Draws distorted lines on the image
         */
        protected function drawLines()
        {
            for ($line = 0; $line < $this->num_lines; ++ $line) {
                $x = $this->Width * (1 + $line) / ($this->num_lines + 1);
                $x += (0.5 - $this->frand()) * $this->Width / $this->num_lines;
                $y = mt_rand($this->Height * 0.1, $this->Height * 0.9);

                $theta = ($this->frand() - 0.5) * M_PI * 0.7;
                $w = $this->Width;
                $len = mt_rand($w * 0.4, $w * 0.7);
                $lwid = mt_rand(0, 2);

                $k = $this->frand() * 0.6 + 0.2;
                $k = $k * $k * 0.5;
                $phi = $this->frand() * 6.28;
                $step = 0.5;
                $dx = $step * cos($theta);
                $dy = $step * sin($theta);
                $n = $len / $step;
                $amp = 1.5 * $this->frand() / ($k + 5.0 / $len);
                $x0 = $x - 0.5 * $len * cos($theta);
                $y0 = $y - 0.5 * $len * sin($theta);

                $ldx = round(- $dy * $lwid);
                $ldy = round($dx * $lwid);

                for ($i = 0; $i < $n; ++ $i) {
                    $x = $x0 + $i * $dx + $amp * $dy * sin($k * $i * $step + $phi);
                    $y = $y0 + $i * $dy - $amp * $dx * sin($k * $i * $step + $phi);
                    imagefilledrectangle($this->Image, $x, $y, $x + $lwid, $y + $lwid, $this->gdlinecolor);
                }
            }
        }
        
            protected function distortedCopy()
        {
                $numpoles = 3; // distortion factor
                // make array of poles AKA attractor points
                for ($i = 0; $i < $numpoles; ++ $i) {
                    $px[$i]  = mt_rand($this->Width  * 0.2, $this->Width  * 0.8);
                    $py[$i]  = mt_rand($this->Height * 0.2, $this->Height * 0.8);
                    $rad[$i] = mt_rand($this->Height * 0.2, $this->Height * 0.8);
                    $tmp     = ((- $this->frand()) * 0.15) - .15;
                    $amp[$i] = $this->perturbation * $tmp;
                }

                $bgCol = imagecolorat($this->tmpimg, 0, 0);
                $width2 = $this->iscale * $this->Width;
                $height2 = $this->iscale * $this->Height;
                imagepalettecopy($this->Image, $this->tmpimg); // copy palette to final image so text colors come across
                // loop over $img pixels, take pixels from $tmpimg with distortion field
        

                for ($ix = 0; $ix < $this->Width; ++ $ix) {
                    for ($iy = 0; $iy < $this->Height; ++ $iy) {
                        $x = $ix;
                        $y = $iy;
                        for ($i = 0; $i < $numpoles; ++ $i) {
                            $dx = $ix - $px[$i];
                            $dy = $iy - $py[$i];
                            if ($dx == 0 && $dy == 0) {
                                continue;
                            }
                            $r = sqrt($dx * $dx + $dy * $dy);
                            if ($r > $rad[$i]) {
                                continue;
                            }
                            $rscale = $amp[$i] * sin(3.14 * $r / $rad[$i]);
                            $x += $dx * $rscale;
                            $y += $dy * $rscale;
                        }
                        $c = $bgCol;
                        $x *= $this->iscale;
                        $y *= $this->iscale;
                        if ($x >= 0 && $x < $width2 && $y >= 0 && $y < $height2) {
                            $c = imagecolorat($this->tmpimg, $x, $y);
                        }
                        if ($c != $bgCol) { // only copy pixels of letters to preserve any background image
                            imagesetpixel($this->Image, $ix, $iy, $c);
                        }
                    }
                }
        }
        
        private function frand()
        {
            return 0.0001 * mt_rand(0,9999);
        }

        /**
         *通过字体角度得到字体矩形宽度*
         *
         * @param int $font_size 字体尺寸
         * @param float $font_angle 旋转角度
         * @param string $font_file 字体文件路径
         * @param string $text 写入字符
         * @return array 返回长宽高
         */
        private function _calculateTextBox($font_size, $font_angle, $font_file, $text) {
                $box = imagettfbbox($font_size, $font_angle, $font_file, $text);

                $min_x = min(array($box[0], $box[2], $box[4], $box[6]));
                $max_x = max(array($box[0], $box[2], $box[4], $box[6]));
                $min_y = min(array($box[1], $box[3], $box[5], $box[7]));
                $max_y = max(array($box[1], $box[3], $box[5], $box[7]));

                return array(
                    'left' => ($min_x >= -1) ? -abs($min_x + 1) : abs($min_x + 2),
                    'top' => abs($min_y),
                    'width' => $max_x - $min_x,
                    'height' => $max_y - $min_y,
                    'box' => $box
                );
        }

        private function  _getColor( $color ) //#ffffff
        {
                return array(hexdec($color[1].$color[2]),hexdec($color[3].$color[4]),hexdec($color[5].$color[6]));
        }

        private function  _getColor2( $color ) //#ffffff
        {
                return imagecolorallocate ($this->Image, $color[0], $color[1], $color[2]);
        }

        //画正弦干扰线
        private function _wirteSinLine($color,$w)
        {
                $img=$this->Image;
                $h=$this->Height;
                $h1=mt_rand(-5,5);
                $h2=mt_rand(-1,1);
                $w2=mt_rand(10,15);
                $h3=mt_rand(4,6);

                for($i=-$w/2;$i<$w/2;$i=$i+0.1)
                {
                        $y=$h/$h3*sin($i/$w2)+$h/2+$h1;
                        imagesetpixel($img,$i+$w/2,$y,$color);
                        $h2!=0?imagesetpixel($img,$i+$w/2,$y+$h2,$color):"";
                }
        }
        
    /**
    * 验证验证码是否正确
    * @access public
    * @param string $code 用户验证码
    * @param string $key 验证码标识
    * @return bool 用户验证码是否正确
    */
    public static function check( $verify, $key = "user_pass" ) {
        if(session_id() == ''){ session_start(); }
        
        $key = empty( $key ) ? "user_pass" : $key;
        
        if( empty( $_SESSION[$key]['verifyCode'] ) ){
            return false;
        }
        
        $session_verify = $_SESSION[$key]['verifyCode'];
        
        if( empty( $session_verify ) || empty( $verify ) ){
            return false;
        }

        if( strtolower( $verify ) == $session_verify ) {
            return true;
        }

        return false;
    }
}
