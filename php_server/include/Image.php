<?php

    class Image {
        private $file;
        private $uploadTemplate;
        public  $validName;
        private $originalExtension;

        private $name;
        private $width = 0;
        private $height = 0;
        private $maxWidth = 1000;
        private $maxHeight = 0;
        public $src_x = 0;
        public $src_y = 0;
        public $src_width = 0;
        public $src_height = 0;
        private $quality = 85;

        private $target;

        private $check = true;
        private $error;
		public  $put_contents = false;

        public function __construct($fileArray, $name = null, $uploadTemplate='profile')
        {
            $this->file= $fileArray;
            if($name != null)$this->name = $name;

            $this->uploadTemplate = $uploadTemplate;
        }
		
		private function profile(){
            $this->target = root_public . 'upload/profile/' . $this->name . '.jpg';
            $this->maxWidth=200;
			$this->maxHeight=200;
            $this->quality=80;
            $this->resize();
        }
		
        private function sanitize(){
            $this->convert();
            if(!$this->check)
                return false;
            switch ($this->uploadTemplate) {
                case 'profile':
                    $this->profile();
                    break;
                default:
                    $this->profile();
                    break;
            }
        }

        private function validate(){
            switch (mime_content_type($this->file['tmp_name'])) {
                case 'image/jpeg':
                    $this->originalExtension = 'jpg';
                    break;
                case 'image/png':
                    $this->originalExtension = 'png';
                    break;
                case 'image/gif':
                    $this->originalExtension = 'gif';
                    break;
                case 'image/bmp':
                    $this->originalExtension = 'bmp';
                    break;
                case 'image/webp':
                    $this->originalExtension = 'webp';
                    break;
                default:
                    $this->check=false;
                    return false;
                    break;
            }
        }

        private function convert(){
            $originalImage = $this->file['tmp_name'];
            switch ($this->originalExtension) { // set originalExtension in validate
                case 'jpg':
                    $imageTmp=imagecreatefromjpeg($originalImage);
                    break;
                case 'png':
                    $imageTmp=imagecreatefrompng($originalImage);
                    break;
                case 'gif':
                    $imageTmp=imagecreatefromgif($originalImage);
                    break;
                case 'bmp':
                    $imageTmp=imagecreatefrombmp($originalImage);
                    break;
                case 'webp':
                    $imageTmp=imagecreatefromwebp($originalImage);
                    break;
            }
            imagejpeg($imageTmp, $originalImage);
            imagedestroy($imageTmp);
        }

        private function resize() {

            $sourceImage = $targetImage = $this->file['tmp_name'];

            if (!$image = @imagecreatefromjpeg($sourceImage)){
                $this->check=false;
                return false;
            }

            list($origWidth, $origHeight) = getimagesize($sourceImage);

            if($this->width == 0){
                $newWidth = $this->maxWidth;
            }
            if($this->height == 0){
                $newHeight = $this->maxHeight;
            }
            else{
                $newWidth = $this->width;
                $newHeight = $this->height;
            }
            if ($newWidth == 0)
                $newWidth  = $origWidth;

            if ($newHeight == 0)
                $newHeight = $origHeight;

            if($this->width == 0 || $this->height == 0){
                $widthRatio = $newWidth / $origWidth;

                $heightRatio = $newHeight / $origHeight;

                $ratio = min($widthRatio, $heightRatio);

                $newWidth  = (int)$origWidth * $ratio;

                $newHeight = (int)$origHeight * $ratio;
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            if($this->src_width == 0 || $this->src_height == 0)
            {
                $this->src_width = $origWidth;
                $this->src_height = $origHeight;
            }

            imagecopyresampled($newImage, $image, 0, 0, $this->src_x, $this->src_y, $newWidth, $newHeight, $this->src_width, $this->src_height);

            imagejpeg($newImage, $targetImage, $this->quality);

            imagedestroy($image);

            imagedestroy($newImage);

        }

        public function upload(){
            $this->validate();
            if($this->check)
            {
                $this->sanitize();
                if($this->check){
					if($this->put_contents == true)
						$upload = file_put_contents($this->target,file_get_contents($this->file['tmp_name']));
					else 
						$upload = move_uploaded_file($this->file['tmp_name'], $this->target);
					return $upload ? true : "مشکل در آپلود تصویر";
                }
                else
                    return $this->error;
            }
            else
                return $this->error;
        }

        public function rollback(){
            unlink($this->target);
        }
    }
    

?>