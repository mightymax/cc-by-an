<?php

class WebshopAppImages 
{
    protected $app;
    protected $size, $width, $height, $upload;

    public $product_image_size_large = 800;
    public $product_image_size_small = 300;

    protected $target_dirs = [
        'large' => __DIR__ . '/images/products/large/',
        'small' => __DIR__ . '/images/products/small/'
    ];

    public function __construct(WebshopApp $app, $formFieldName = 'img') {
        if (!ini_get('file_uploads')) {
            $app->setMessage("System error: file uploads not allowd by 'file_uploads' ini directive.");
            $app->redirect();
        }
        $this->app = $app;
        $this->formFieldName= $formFieldName;
        //we need php GD extension to create square image with specific sizes:
        if (!extension_loaded('gd')) {
            if (!dl('gd.so')) {
                $app->setMessage("System error: the Php GD extension is not loaded.");
                $app->redirect();
            }
        }
    }

    //extra strict checking of $_FILES super global:
    public function isUploadFile() {
        if (
            $_FILES 
            && isset($_FILES[$this->formFieldName]) 
            && is_array($_FILES[$this->formFieldName]) 
            && isset($_FILES[$this->formFieldName]['error'])
            && $_FILES[$this->formFieldName]['error'] != UPLOAD_ERR_NO_FILE
        ) {
            $this->upload = $_FILES[$this->formFieldName];
            return true;
        }
        return false;
    }

    public function checkUploadedFile() {
        //is this upload an error?
        if ((int)$this->upload['error'] !== 0) {
            //since it is important for us to know what is going on, we check the type of error:
            switch($this->upload['error']) {
                case UPLOAD_ERR_NO_FILE:
                    $debug = 'geen bestand verzonden';
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $debug = 'bestand is te groot';
                    break;
                default:
                    $debug = 'onbekende fout';
                    break;
            }

            $this->app->setMessage("Er is een fout opgetreden bij het uploaden van de afbeelding (<code>{$debug}</code>)", 'error');
            $this->app->redirect('addproduct', "&product=" . @$_REQUEST['product']);
        }
        return true;
    }

    public function checkIfUploadedFileIsAnImage()
    {
        // We use the Php function getimagesize(), since it returns false if the argument is not an image.
        // Extra benefit: we now have the pixelsize of our image as well!
        $size = @getimagesize($this->upload["tmp_name"]);

        // check the mime type, we can not trust the mime value from getimagesize()
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($this->upload['tmp_name']);

        if (false == $size || $mime != 'image/jpeg') {
            $app->setMessage("Je kunt alleen afbeeldingen uploaden van het type image/jpeg", 'error');
            $app->redirect('addproduct');
        }
        
        $width = $size[0];
        $height = $size[1];
        if ($width < $this->product_image_size_large || $height < $this->product_image_size_large) {
            $app->setMessage("De productafbeelding moet ten minste {$this->product_image_size_large}x{$this->product_image_size_large}px groot zijn.", 'warning');
            $app->redirect('addproduct');
        }
        $this->size = $size;
        $this->width = $width;
        $this->height = $height;
    }

    public function checkUploadDirectories()
    {
        //at the end of the image manipulation, we need to store the file on disk
        // so before we continue, let's check if our target directories exists and are writable for the webserver:
        $target_dir_large = $this->target_dirs['large'];
        $target_dir_small = $this->target_dirs['small'];
        if (!realpath($target_dir_large) || !is_dir($target_dir_large)) {
            $this->app->setMessage("System error: missing target dir <code>{$target_dir_large}</code> for large images.", 'error');
            $this->app->redirect('addproduct');
        }
        if (!realpath($target_dir_small) || !is_dir($target_dir_small)) {
            $this->app->setMessage("System error: missing target dir <code>{$target_dir_small}</code> for large images.", 'error');
            $this->app->redirect('addproduct');
        }

        if (!is_writable($target_dir_large)) {
            $this->app->setMessage("System error: target dir <code>{$target_dir_large}</code> is not writable.", 'error');
            $this->app->redirect('addproduct');
        }
        if (!is_writable($target_dir_small)) {
            $this->app->setMessage("System error: target dir <code>{$target_dir_small}</code> is not writable.", 'error');
            $this->app->redirect('addproduct');
        }
    }

    public function createCroppedAndResizedImage() {
        //load the uploaded file to a Php GD image:
        $image = imagecreatefromjpeg($this->upload['tmp_name']);
        if (!$image) {
            $this->app->setMessage("System error: <code>imagecreatefromjpeg()</code> failed in WebshopAppImages on line ".__LINE__, 'error');
            $this->app->redirect('addproduct');
        }
        $image_width =imagesx($image);
        $image_height = imagesy($image);
        // if this image is not square, crop it:
        if ($image_width != $image_height) {
            $cropsize = min($image_width, $image_height);
            $x = 0; $y = 0;
            //crop from center of image algorithm:
            if ($image_height > $image_width) { //portrait image
                $y = intval(($image_height - $cropsize) / 2);
            } else { //landscape image
                $x = intval(($image_width - $cropsize) / 2);
            }
            $image_square = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $cropsize, 'height' => $cropsize]);
            if (!$image_square) {
                $this->app->setMessage("System error: <code>imagecrop()</code> failed in WebshopAppImages on line ".__LINE__, 'error');
                $this->app->redirect('addproduct');
            }
            // "destroy" old uploaded image to replace it with the cropped image and save memory:
            imagedestroy($image);
            $image = $image_square;
            imagedestroy($image_square);
            $image_width =imagesx($image);
            $image_height = imagesy($image);
        }

        // at this stage we have a square image. But maybe it is not $product_image_size_large pixels?
        if ($image_width > $this->product_image_size_large) {
            $resized_image = imagecreatetruecolor($this->product_image_size_large, $this->product_image_size_large);
            if (!$resized_image) {
                $this->app->setMessage("System error: <code>imagecreatetruecolor()</code> failed in WebshopAppImages on line ".__LINE__, 'error');
                $this->app->redirect('addproduct');
            }
            
            if (!imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $this->product_image_size_large, $this->product_image_size_large, $image_width, $image_height)) {
                $this->app->setMessage("System error: <code>imagecopyresampled()</code> failed in WebshopAppImages on line ".__LINE__, 'error');
                $this->app->redirect('addproduct');
            }
            // "destroy" old uploaded image to replace it with the resized image and save memory:
            imagedestroy($image);
            $image = $resized_image;
            imagedestroy($resized_image);
        }

        if (!$image) {
            $this->app->setMessage("System error: <code>WebshopAppImages::createCroppedAndResizedImage()</code> failed in WebshopAppImages on line ".__LINE__, 'error');
            $this->app->redirect('addproduct');
        }

        return $image;
    }

    public function createThumbnailFromLargeImage(GdImage $large_image)
    {
        $thumbnail_image = imagecreatetruecolor($this->product_image_size_small, $this->product_image_size_small);
        if (!$thumbnail_image) {
            $this->app->setMessage("System error: <code>imagecreatetruecolor()</code> failed in WebshopAppImages on line ".__LINE__, 'error');
            $this->app->redirect('addproduct');
        }
        if (!imagecopyresampled($thumbnail_image, $large_image, 0, 0, 0, 0, $this->product_image_size_small, $this->product_image_size_small, $this->product_image_size_large, $this->product_image_size_large)) {
            $this->app->setMessage("System error: <code>imagecopyresampled()</code> failed in WebshopAppImages on line ".__LINE__, 'error');
            $this->app->redirect('addproduct');
        }
        if (!$thumbnail_image) {
            $this->app->setMessage("System error: <code>WebshopAppImages::createThumbnailFromLargeImage()</code> failed in WebshopAppImages on line ".__LINE__, 'error');
            $this->app->redirect('addproduct');
        }
        return $thumbnail_image;
    }

    public function saveImage(GdImage $image, Array $product, $large_or_small)
    {
        $target_dir = ($large_or_small == 'small') ? $this->target_dirs['small'] : $this->target_dirs['large'];
        $filename = (int)$product['id'] . '.jpg';
        $target_file = $target_dir . '/' . $filename;
        if (file_exists($target_file) && !is_writable($target_file)) {
            $this->app->setMessage("Systeemfout: afbeelding {$large_or_small}/{$filename} bestaat al maar kan niet worden vervangen.", 'error');
            return false;
        }
        if (!imagejpeg($image, $target_file, 95)) {
            $this->app->setMessage("Systeemfout: afbeelding {$large_or_small}/{$filename} kon niet worden opgeslagen.", 'error');
            return false;
        }
        return true;
    }

    public function deleteImage(Array $product, $large_or_small)
    {
        $target_dir = ($large_or_small == 'small') ? $this->target_dirs['small'] : $this->target_dirs['large'];
        $filename = (int)$product['id'] . '.jpg';
        $target_file = $target_dir . '/' . $filename;
        if (file_exists($target_file) && !is_writable($target_file)) {
            $this->app->setMessage("Systeemfout: afbeelding {$large_or_small}/{$filename} kan niet worden verwijderd.", 'error');
            return false;
        }
        return unlink($target_file);

    }

    
}