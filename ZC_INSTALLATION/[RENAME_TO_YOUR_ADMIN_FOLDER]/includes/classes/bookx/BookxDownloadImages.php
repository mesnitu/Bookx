<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.5.6a
 *
 * @package admin
 * @author  mesnitu
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 * 
 * @copyright Portions Gumlet
 * @see https://github.com/gumlet/php-image-resize/
 * @license https://github.com/gumlet/php-image-resize/blob/master/Licence.md name
 *
 * @version BookX V 1.0.0
 * @version $Id: [admin]includes\classes\bookx\BookxDownloadImages.php 2019-01-25 mesnitu $
 */

namespace Bookx;

/**
 * Description of BookxDownloadImages
 *
 * @author mesnitu
 */
class DownloadImage
{

    const temp_folder = BOOKX_TEMP_FOLDER;
    const image_folder = DIR_FS_CATALOG_IMAGES;

    var $transliterate = true;
    var $download = true;
    var $image_name = [];
    var $url = [];
    var $dest_folder_name = [];
    var $temp_filename = [];
    var $dest_filename = [];
    var $replace_patterns = array(' ', '-', '.');
    var $file_ext = [];

    public function __construct($resize = false, $width = false)
    {
        if (!file_exists(self::temp_folder)) {
            throw new BookxException('Temp Folder doesn\'t exist');
        }

        if ($this->transliterate == true && !class_exists('CeonURIMappingAdmin') && !extension_loaded('intl')) {
            throw new BookxException('No way to clean Img / Folder names');
        }
        if ($resize) {
            $this->width = $width;
        }
    }

    /**
     * @param boolean $transliterate establish whether CEON or intl should be used to clean
     * folder and image names, Default true. If false, it will process with the given names
     * 
     * @return $this
     */
    public function setTransliterate($transliterate)
    {
        $this->transliterate = $transliterate;
        return $this;
    }

    public function getImage_name()
    {
        return $this->image_name;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getDest_folder()
    {
        return $this->dest_folder;
    }

    /**
     * 
     * @param array $image_name builds an array of names to process
     * @param type $option (lowercase, titlecase)
     * @return $this
     */
    public function setImage_name($image_name, $option = null)
    {
        if ($this->transliterate == true) {
            $this->image_name[] = $this->cleanImageName($image_name, $option);
        } else {
            $this->image_name[] = $image_name;
        }
        return $this;
    }

    /**
     * 
     * @param array $url builds an array of urls
     */
    public function setUrl($url)
    {
        $this->url[] = $url;
        $this->setFile_ext($url);
        return $this;
    }

    public function getTemp_folder()
    {
        return $this->temp_folder;
    }

    /**
     * @param array $dest_folder_name builds an array on destination folders
     * @param string $option (lowercase, titlecase)
     * @return $this
     */
    public function setDest_folder($dest_folder_name, $option = null)
    {
        $temp = str_replace(DIR_FS_CATALOG_IMAGES, '', $dest_folder_name);
        $this->dest_folder[] = $this->cleanImageName($temp, $option);
        return $this;
    }

    public function getReplace_patterns()
    {
        return $this->replace_patterns;
    }

    /**
     * 
     * @param array $replace_patterns array to add more replacement patterns if 
     * @return $this
     */
    public function setReplace_patterns($replace_patterns)
    {
        foreach (explode(',', $replace_patterns) as $value) {
            $this->replace_patterns[] = $value;
        }
        return $this;
    }

    public function getFile_ext()
    {
        return $this->file_ext;
    }

    public function setTemp_folder($temp_folder)
    {
        $this->temp_folder[] = $temp_folder;
        return $this;
    }

    /**
     * 
     * @param array $pathinfo sets an array PATHINFO_EXTENSION
     * @return $this
     */
    public function setFile_ext($pathinfo)
    {
        $ext = pathinfo($pathinfo, PATHINFO_EXTENSION);
        $this->file_ext[] = $ext;
        return $this;
    }

    private function preProcess()
    {
        if (empty($this->url)) {
            $this->download = false;
        }

        $i = 0;
        foreach ($this->image_name as $key => $name) {
            if (empty($name)) {

                throw new BookxException('Image name cannot be empty');
            }

            if (($this->file_ext[$i] !== 'jpg') &&
                ($this->file_ext[$i] !== 'png') &&
                ($this->file_ext[$i] !== 'gif')) {
                throw new BookxException('Invalid file extension');
            }
            if (empty($this->dest_folder[$i])) {
                throw new BookxException('Destination Folder cannot be empty');
            }

            $this->temp_filename[] = self::temp_folder . $name . '.' . $this->file_ext[$i];
            $folder = DIR_FS_CATALOG_IMAGES . $this->dest_folder[$i];
            $this->dest_filename[] = $folder . '/' . $name . '.' . $this->file_ext[$i];

            if (!file_exists($folder)) {
                umask(000);
                mkdir($folder, '0755', true);
            }

            $i++;
        }
        return true;
    }

    public function process()
    {

        if ($this->preProcess() == true) {
            $i = 0;
            if ($this->download == true) {
                foreach ($this->url as $key => $url) {
                    $this->downloadImage($url, $this->temp_filename[$i]);
                    if (filesize($this->temp_filename[$i]) == 0) {
                        throw new BookxException('Something went wrong with download. Filesize is 0. Maybe a wrong SSL certificate or redirection');
                    }
                    $i++;
                }
            }

            if (BOOKX_RESIZE_IMAGES == true || !empty($this->width)) {
                $this->resizeImage();
            } else {
                $this->moveImage();
            }
        }
    }

    private function moveImage()
    {
        try {
            $i = 0;
            foreach ($this->temp_filename as $key => $file) {

                copy($file, $this->dest_filename[$i]);
                $i++;
            }
        } catch (\Bookx\BookxException $e) {
            $this->error = 'Could not copy image ' . $e->getMessage();
        }
    }

    private function downloadImage($url, $filename)
    {

        if (!file_exists($filename)) {

            $ch = curl_init($url);
            $fp = fopen($filename, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            //curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
    }

    private function resizeImage()
    {
        include_once BOOKX_EXTRA_DATAFILES_FOLDER . 'libs/ImageResize/ImageResize.php';
        include_once BOOKX_EXTRA_DATAFILES_FOLDER . 'libs/ImageResize/ImageResizeException.php';

        try {
            $i = 0;
            foreach ($this->temp_filename as $key => $filename) {

                $image = new \Gumlet\ImageResize($filename);
                $image->resizeToWidth($this->width, $allow_enlarge = true);
                $image->save($this->dest_filename[$i]);

                $i++;
            }
        } catch (\Gumlet\ImageResizeException $e) {
            $this->error = 'Could not resize image ' . $e->getMessage();
        }
    }

    private function cleanImageName($post_name, $option = 'lower')
    {

        if (class_exists('CeonURIMappingAdmin')) {

            require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdmin.php');
            $handleUri = new CeonURIMappingAdmin();

            $lang_code = $_SESSION['languages_code'];

            $name = $handleUri->_convertStringForURI(trim($post_name), $lang_code);
            
        } elseif (extension_loaded('intl') && !class_exists('CeonURIMappingAdmin')) {

            $name = transliterator_transliterate('Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC;', $post_name);
            
        } else {
            $error = true;
        }
        //some extra string checks
        if ($option == 'titlecase') {
            return str_replace($this->replace_patterns, '', ucwords($name));
        } else {
            return str_replace($this->replace_patterns, '_', strtolower($name));
        }
    }

}
