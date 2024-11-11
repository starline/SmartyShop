<?php

/**
 * GoodGin CMS - The Best of gins
 * 
 * @author Andi Huga
 * @version 2.0
 * 
 */

namespace GoodGin;

class Images extends GoodGin
{
    private $allowed_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico', 'webp');

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Get images
     * @param Integer|Array $entity_id
     * @param $entity_name - product
     * @return array
     */
    public function getImages($entity_id, $entity_name)
    {
        if (empty($entity_id) || empty($entity_name)) {
            return array();
        }

        // images
        $query = $this->Database->placehold(
            "SELECT 
				i.*
			FROM 
				__content_images AS i 
			WHERE 
				1 
				AND i.entity_name=? 
				AND i.entity_id in(?@) 
			ORDER BY 
				i.entity_id,
				i.position",
            $entity_name,
            (array)$entity_id
        );

        $this->Database->query($query);
        return $this->Database->results();
    }


    /**
     * Upload and add image to Database
     * @param $filename
     * @param $name
     * @param $entity_id
     * @param $entity_name
     * @param $width
     * @param $height
     * @return $id - image ID
     */
    public function uploadAddImage($filename, $name, $entity_id, $entity_name, $width = false, $height = false)
    {
        if ($image_name = $this->uploadImage($filename, $name, $width, $height)) {
            if ($image_id = $this->addImage($entity_id, $entity_name, $image_name)) {
                return $image_id;
            }
        }
        return false;
    }


    /**
     * Add image
     * @param $entity_id
     * @param $entity_name
     * @param $filename
     * @return $id - image ID
     *
     */
    public function addImage($entity_id, $entity_name, $filename)
    {
        if (!empty($entity_id) and !empty($entity_name) and !empty($filename)) {

            $query = $this->Database->placehold(
                "SELECT 
                    id 
                FROM 
                    __content_images 
                WHERE 
                    entity_name='$entity_name' 
                    AND entity_id=? 
                    AND filename=?",
                $entity_id,
                $filename
            );

            $this->Database->query($query);
            $id = $this->Database->result('id');

            // If there isn't image in the database
            if (empty($id)) {
                $query = $this->Database->placehold("INSERT INTO __content_images SET entity_name='$entity_name', entity_id=?, filename=?", $entity_id, $filename);
                $this->Database->query($query);
                $id = $this->Database->getInsertId();

                $query = $this->Database->placehold("UPDATE __content_images SET position=id WHERE entity_name='$entity_name' AND id=?", $id);
                $this->Database->query($query);
            }

            return ($id);
        }
        return false;
    }


    /**
     * Update image
     * @param $id
     * @param $image
     *
     */
    public function updateImage($id, $image)
    {
        $query = $this->Database->placehold("UPDATE __content_images SET ?% WHERE id=?", $image, $id);
        $this->Database->query($query);

        return ($id);
    }


    public function deleteImage($id)
    {
        // Select file name
        $query = $this->Database->placehold("SELECT filename FROM __content_images WHERE id=?", $id);
        $this->Database->query($query);
        $filename = $this->Database->result('filename');

        // Delete image by ID
        $query = $this->Database->placehold("DELETE FROM __content_images WHERE id=? LIMIT 1", $id);
        $this->Database->query($query);

        // Select images count by name
        $query = $this->Database->placehold("
			SELECT 
				count(*) as count 
			FROM 
				__content_images 
			WHERE 
				filename=?
			LIMIT 
				1
		", $filename);

        $this->Database->query($query);
        $count = $this->Database->result('count');

        // If there isn't image, delete file by name
        if ($count == 0) {
            $file = pathinfo($filename, PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            // Удалить все ресайзы
            $rezised_images = glob($this->Config->root_dir . $this->Config->images_resized_dir . $file . ".*x*." . $ext);
            if (is_array($rezised_images)) {
                foreach (glob($this->Config->root_dir . $this->Config->images_resized_dir . $file . ".*x*." . $ext) as $f) {
                    @unlink($f);
                }
            }

            @unlink($this->Config->root_dir . $this->Config->images_resized_dir . $filename);
        }
    }


    /**
     * Создание превью изображения
     * @param $filename файл с изображением (без пути к файлу)
     * @param $max_w максимальная ширина
     * @param $max_h максимальная высота
     * @return $string имя файла превью
     */
    public function resize($filename)
    {

        list($source_file, $width, $height, $set_watermark) = $this->getResizeParams($filename);

        // Если файл удаленный (http://), зальем его себе
        if (substr($source_file, 0, 7) == 'http://') {

            // Имя оригинального файла
            if (!$original_file = $this->downloadImage($source_file)) {
                return false;
            }
        } else {
            $original_file = $source_file;
        }

        $resized_file = $this->addResizeParams($original_file, $width, $height, $set_watermark);

        // Пути к папкам с картинками
        $originals_dir = $this->Config->root_dir . $this->Config->images_originals_dir;
        $resized_dir = $this->Config->root_dir . $this->Config->images_resized_dir;

        // Проверяем что файл существует
        if (!is_file($originals_dir . $original_file)) {
            header("http/1.0 404 not found");
            return false;
        }

        $watermark_offet_x = $this->Settings->watermark_offset_x;
        $watermark_offet_y = $this->Settings->watermark_offset_y;

        if ($set_watermark && is_file($this->Config->root_dir . $this->Config->images_watermark_file)) {
            $watermark = $this->Config->root_dir . $this->Config->images_watermark_file;
        } else {
            $watermark = null;
        }

        return $this->resizeUploadImage($originals_dir . $original_file, $resized_dir . $resized_file, $width, $height, $watermark, $watermark_offet_x, $watermark_offet_y);
    }


    /**
     * Ресайз загруженых изображений
     * @param $sharpen - четкость изображжения 0-100 (0 - без изменений)
     * @param $watermark_transparency - прозначность водяного знака 0-100 (больше - прозрачнее)
     * @param $quality - качество изображения
     */
    public function resizeUploadImage($original_file_path, $new_file_path, $width = false, $height = false, $watermark = null, $watermark_offet_x = 0, $watermark_offet_y = 0, $watermark_transparency = false, $sharpen = false, $quality = false)
    {

        // Ужимать загружаемые изображения, по-умолчанию
        // Настройки взять в config
        if (!$width) {
            $width  = $this->Config->images_max_size;
        }

        if (!$height) {
            $height  = $this->Config->images_max_size;
        }

        // Четкость изображения. Настройки сайта. 0-100 (больше - четче)
        if (!$sharpen) {
            $sharpen = min(100, (int)$this->Settings->images_sharpen) / 100;
        }

        // Прозрачность водяного знака. Настройки сайта
        if (!$watermark_transparency) {
            $watermark_transparency = min(100, (int)$this->Settings->watermark_transparency);
        }

        if (class_exists('Imagick') && $this->Config->images_use_imagick) {
            $this->imageResizeImagick($original_file_path, $new_file_path, $width, $height, $watermark, $watermark_offet_x, $watermark_offet_y, $watermark_transparency, $sharpen, $quality);
        } else {
            $this->imageResizeGD($original_file_path, $new_file_path, $width, $height, $watermark, $watermark_offet_x, $watermark_offet_y, $watermark_transparency, $quality);
        }

        return $new_file_path;
    }


    /**
     * Добавляем параметры размера, водяного знака
     * @param $filename
     * @param $width
     * @param $height
     * @param $set_watermark
     */
    public function addResizeParams($filename, $width = 0, $height = 0, $set_watermark = false)
    {
        if ('.' != ($dirname = pathinfo($filename, PATHINFO_DIRNAME))) {
            $file = $dirname . '/' . pathinfo($filename, PATHINFO_FILENAME);
        } else {
            $file = pathinfo($filename, PATHINFO_FILENAME);
        }
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($width > 0 || $height > 0) {
            $resized_filename = $file . '.' . ($width > 0 ? $width : '') . 'x' . ($height > 0 ? $height : '') . ($set_watermark ? 'w' : '') . '.' . $ext;
        } else {
            $resized_filename = $file . '.' . ($set_watermark ? 'w.' : '') . $ext;
        }

        return $resized_filename;
    }


    public function getResizeParams($filename)
    {

        // Определаяем параметры ресайза
        if (!preg_match('/(.+)\.([0-9]*)x([0-9]*)(w)?\.([^\.]+)$/', $filename, $matches)) {
            return false;
        }

        $file = $matches[1];								// имя запрашиваемого файла
        $width = $matches[2];								// ширина будущего изображения
        $height = $matches[3];								// высота будущего изображения
        $set_watermark = $matches[4] == 'w';				// ставить ли водяной знак
        $ext = $matches[5];									// расширение файла

        return array($file . '.' . $ext, $width, $height, $set_watermark);
    }


    /**
     * Заливаем файл на сервер по http://
     */
    public function downloadImage($filename)
    {

        // Заливаем только если такой файл есть в базе
        $this->Database->query('SELECT 1 FROM __content_images WHERE filename=? LIMIT 1', $filename);
        if (!$this->Database->result()) {
            return false;
        }

        // Имя оригинального файла
        $basename = explode('&', pathinfo($filename, PATHINFO_BASENAME));
        $uploaded_file = array_shift($basename);
        $base = urldecode(pathinfo($uploaded_file, PATHINFO_FILENAME));
        $ext = pathinfo($uploaded_file, PATHINFO_EXTENSION);

        // Если такой файл существует, нужно придумать другое название
        $new_name = urldecode($uploaded_file);

        while (file_exists($this->Config->root_dir . $this->Config->images_originals_dir . $new_name)) {
            $new_base = pathinfo($new_name, PATHINFO_FILENAME);

            // Google likes '-'
            if (preg_match('/-([0-9]+)$/', $new_base, $parts)) {
                $new_name = $base . '-' . ($parts[1] + 1) . '.' . $ext;
            } else {
                $new_name = $base . '-1' . '.' . $ext;
            }
        }

        // Перед долгим копированием займем это имя
        $this->Database->query('UPDATE __content_images SET filename=? WHERE filename=?', $new_name, $filename);

        fclose(fopen($this->Config->root_dir . $this->Config->images_originals_dir . $new_name, 'w'));
        copy($filename, $this->Config->root_dir . $this->Config->images_originals_dir . $new_name);

        return $new_name;
    }


    /**
     * Заливаем файл на сервер
     * Через эту функцию заливаем оригинальные изображения
     * @param $filename
     * @param $name
     * @param $width
     * @param $height
     * @return $image_name
     */
    public function uploadImage($filename, $name, $width = false, $height = false)
    {

        // Имя оригинального файла
        $name = $this->Misc->transliteration_ru_en($name);
        $new_name = pathinfo($name, PATHINFO_BASENAME); // Выбираем имя с размершением. Отбрасываем путь (/)
        $base = pathinfo($new_name, PATHINFO_FILENAME);
        $ext = pathinfo($new_name, PATHINFO_EXTENSION);

        // Пропускаем толькор разрешенные расширения
        if (in_array(strtolower($ext), $this->allowed_extentions)) {

            // Если файл с таким именем уже существует, добавим индес в названии
            while (file_exists($this->Config->root_dir . $this->Config->images_originals_dir . $new_name)) {
                $new_base = pathinfo($new_name, PATHINFO_FILENAME);

                // Google likes '-'
                if (preg_match('/-([0-9]+)$/', $new_base, $parts)) {
                    $new_name = $base . '-' . ($parts[1] + 1) . '.' . $ext;
                } else {
                    $new_name = $base . '-1' . '.' . $ext;
                }
            }

            $image_path = $this->Config->root_dir . $this->Config->images_originals_dir . $new_name;

            // Загружаем файл в папку оригинальных файлов
            if (move_uploaded_file($filename, $image_path)) {

                // если задали максимальный размер,
                if ($width || $height) {
                    $this->resizeUploadImage($image_path, $image_path, $width, $height);
                }

                return $new_name;
            }
        }

        return false;
    }


    /**
     * Создание превью средствами GD
     * Проверено для php7.2
     * Добавлена поддержка .webp
     * Добавлен ресайз watermark под размер изображения
     * Добавлен Bugfix прозрачности imagecopymergeAlpha
     *
     * @param $src_file - исходный файл
     * @param $dst_file - файл с результатом
     * @param $max_w - максимальная ширина
     * @param $max_h - максимальная высота
     * @param $quality - качество изображения
     * @return bool
     */
    private function imageResizeGD($src_file, $dst_file, $max_w, $max_h, $watermark = null, $watermark_offet_x = 0, $watermark_offet_y = 0, $watermark_opacity = 100, $quality = false)
    {

        // Default 80%
        if (!$quality) {
            $quality = 80;
        }

        // Качество изображения
        if (!empty($this->Config->images_quality)) {
            $quality = $this->Config->images_quality;
        }

        // Параметры исходного изображения
        @list($src_w, $src_h, $src_type) = array_values(getimagesize($src_file));
        $src_type = image_type_to_mime_type($src_type);

        if (empty($src_w) || empty($src_h) || empty($src_type)) {
            return false;
        }

        // Если размер изображения больше ($max_w, $max_h) - уменьшим его
        if (!$watermark && ($src_w <= $max_w) && ($src_h <= $max_h)) {

            // Нет - просто скопируем файл
            if (!copy($src_file, $dst_file)) {
                return false;
            }
            return true;
        }

        // Размеры превью при пропорциональном уменьшении
        @list($dst_w, $dst_h) = $this->calcContrainSize($src_w, $src_h, $max_w, $max_h);
        $dst_img = imagecreatetruecolor($dst_w, $dst_h);

        // Читаем изображение
        switch ($src_type) {
            case 'image/jpeg':
                $src_img = imageCreateFromJpeg($src_file);
                break;

            case 'image/gif':
                $src_img = imageCreateFromGif($src_file);

                // Сохраняем прозрачность
                $trnprt_indx = imagecolortransparent($src_img);
                if ($trnprt_indx >= 0) {
                    $trnprt_color = imagecolorsforindex($src_img, $trnprt_indx);
                    $trnprt_indx = imagecolorallocate($dst_img, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                    imagefill($dst_img, 0, 0, $trnprt_indx);
                    imagecolortransparent($dst_img, $trnprt_indx);
                }
                break;

            case 'image/png':
                $src_img = imageCreateFromPng($src_file);

                // Включаем альфа канала
                imageAlphaBlending($dst_img, false);
                imageSaveAlpha($dst_img, true);

                // Recalculate quality value for png image. From 0 (no compression) to 9
                $quality = round(($quality / 100) * 10);
                $quality = max(0, (float)$quality); // не допускаем меньше 0
                $quality = min(10, (float)$quality); // не допускаем больше 10
                $quality = 10 - $quality;

                break;

            case 'image/webp':
                $src_img = imagecreateFromWebp($src_file);
                break;

            default:
                return false;
        }

        if (empty($src_img)) {
            return false;
        }

        // resample the image with new sizes
        if (!imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h)) {
            return false;
        }

        // Watermark
        if (!empty($watermark) && is_readable($watermark)) {
            $overlay = ImageCreateFromPNG($watermark);

            imageAlphaBlending($dst_img, true); // Включить режим сопряжения цветов для изображения
            imageSaveAlpha($dst_img, true); // Включить сохранение альфа канала

            // Get the size of overlay
            $overlay_width = imagesX($overlay);
            $overlay_height = imagesY($overlay);

            // Делаем Watermark всегда делаем меньше на 10% от изображения
            if ($overlay_width > ($dst_w - 0.1 * $dst_w)) {
                $new_overlay_width =  $dst_w - $dst_w * 0.1;
                $new_overlay_height = $overlay_height / ($overlay_width / $new_overlay_width);

                $dst_overlay = imagecreatetruecolor($new_overlay_width, $new_overlay_height);
                imageAlphaBlending($dst_overlay, false); // Отключить режим сопряжения цветов для изображения
                imageSaveAlpha($dst_overlay, true); // Включить сохранение альфа канала
                imagecopyresampled($dst_overlay, $overlay, 0, 0, 0, 0, $new_overlay_width, $new_overlay_height, $overlay_width, $overlay_height);

                $overlay = $dst_overlay;
                $overlay_width = $new_overlay_width;
                $overlay_height = $new_overlay_height;
            }

            $watermark_x = min(($dst_w - $overlay_width) * $watermark_offet_x / 100, $dst_w);
            $watermark_y = min(($dst_h - $overlay_height) * $watermark_offet_y / 100, $dst_h);

            if ($src_type == 'image/png') {
                imagecopy($dst_img, $overlay, $watermark_x, $watermark_y, 0, 0, $overlay_width, $overlay_height);
            } else {
                $this->imagecopymergeAlpha($dst_img, $overlay, $watermark_x, $watermark_y, 0, 0, $overlay_width, $overlay_height, $watermark_opacity);
            }
        }

        // Сохраняем изображение
        switch ($src_type) {
            case 'image/jpeg':
                return imageJpeg($dst_img, $dst_file, intval($quality));
            case 'image/gif':
                return imageGif($dst_img, $dst_file, intval($quality));
            case 'image/png':
                return imagePng($dst_img, $dst_file, intval($quality));
            case 'image/webp':
                return imagewebp($dst_img, $dst_file, intval($quality));
            default:
                return false;
        }
    }


    /**
     * PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
     * by Sina Salek
     *
     * Bugfix by Ralph Voigt (bug which causes it
     * to work only for $src_x = $src_y = 0.
     * Also, inverting opacity is not necessary.)
     * 08-JAN-2011
     *
     * imagecopymerge - Без этого Bugfix Затемняет фон водяного знака.
     * Значение opacity: 0-100 (100 непрозрачный)
     *
     **/
    public function imagecopymergeAlpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity)
    {

        $src_x ?? 0;
        $src_y ?? 0;

        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);

        // copying relevant section from background to the cut resource
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);

        // copying relevant section from watermark to the cut resource
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);

        // insert cut resource to destination image
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $opacity);
    }


    /**
     * Создание превью средствами imagick
     *
     * @param $src_file исходный файл
     * @param $dst_file файл с результатом
     * @param $max_w максимальная ширина
     * @param $max_h максимальная высота
     * @param $quality - качество изображения
     * @return bool
     */
    private function imageResizeImagick($src_file, $dst_file, $max_w, $max_h, $watermark = null, $watermark_offet_x = 0, $watermark_offet_y = 0, $watermark_opacity = false, $sharpen = 0.2, $quality = 100)
    {

        // Качество изображения. Default // 100%
        if (!empty($this->Config->images_quality)) {
            $quality = $this->Config->images_quality;
        }

        $thumb = new Imagick();

        // Читаем изображение
        if (!$thumb->readImage($src_file)) {
            return false;
        }

        // Размеры исходного изображения
        $src_w = $thumb->getImageWidth();
        $src_h = $thumb->getImageHeight();

        // Нужно ли обрезать?
        if (!$watermark && ($src_w <= $max_w) && ($src_h <= $max_h)) {
            // Нет - просто скопируем файл
            if (!copy($src_file, $dst_file)) {
                return false;
            }
            return true;
        }

        // Размеры превью при пропорциональном уменьшении
        list($dst_w, $dst_h) = $this->calcContrainSize($src_w, $src_h, $max_w, $max_h);

        // Уменьшаем
        $thumb->thumbnailImage($dst_w, $dst_h);

        // Преобразуем transparency в opacity
        // Значение: 0-1 (1 непрозрачный)
        if ($watermark_opacity) {
            $watermark_opacity = 1 - min(100, $watermark_opacity) / 100;
        } else {
            $watermark_opacity = 1;
        }

        // Устанавливаем водяной знак
        if ($watermark && is_readable($watermark)) {
            $overlay = new Imagick($watermark);
            //$overlay->setImageOpacity($watermark_opacity);
            //$overlay_compose = $overlay->getImageCompose();
            $overlay->evaluateImage(Imagick::EVALUATE_MULTIPLY, $watermark_opacity, Imagick::CHANNEL_ALPHA);

            // Get the size of overlay
            $overlay_width = $overlay->getImageWidth();
            $overlay_height = $overlay->getImageHeight();

            $watermark_x = min(($dst_w - $overlay_width) * $watermark_offet_x / 100, $dst_w);
            $watermark_y = min(($dst_h - $overlay_height) * $watermark_offet_y / 100, $dst_h);
        }


        // Анимированные gif требуют прохода по фреймам
        foreach ($thumb as $frame) {
            // Уменьшаем
            $frame->thumbnailImage($dst_w, $dst_h);

            /* Set the virtual canvas to correct size */
            $frame->setImagePage($dst_w, $dst_h, 0, 0);

            // Наводим резкость
            if ($sharpen > 0) {
                $thumb->adaptiveSharpenImage($sharpen, $sharpen);
            }

            if (isset($overlay) && is_object($overlay)) {
                // $frame->compositeImage($overlay, $overlay_compose, $watermark_x, $watermark_y, imagick::COLOR_ALPHA);
                $frame->compositeImage($overlay, imagick::COMPOSITE_OVER, $watermark_x, $watermark_y, imagick::COLOR_ALPHA);
            }
        }

        // Убираем комменты и т.п. из картинки
        $thumb->stripImage();

        // устанавливаем тип компресии JPEG2000
        $thumb->setImageCompression(Imagick::COMPRESSION_JPEG2000);
        $thumb->setImageCompressionQuality($quality);

        // Записываем картинку
        if (!$thumb->writeImages($dst_file, true)) {
            return false;
        }

        // Уборка
        $thumb->destroy();
        if (isset($overlay) && is_object($overlay)) {
            $overlay->destroy();
        }

        return true;
    }


    /**
     * Вычисляет размеры изображения, до которых нужно его пропорционально уменьшить, чтобы вписать в квадрат $max_w x $max_h
     * @param $src_w ширина исходного изображения
     * @param $src_h высота исходного изображения
     * @param $max_w максимальная ширина
     * @param $max_h максимальная высота
     * @return array(w, h)
     */
    public function calcContrainSize($src_w, $src_h, $max_w = 0, $max_h = 0)
    {

        if ($src_w == 0 || $src_h == 0) {
            return false;
        }

        $dst_w = $src_w;
        $dst_h = $src_h;

        if ($src_w > $max_w && $max_w > 0) {
            $dst_h = $src_h * ($max_w / $src_w);
            $dst_w = $max_w;
        }

        if ($dst_h > $max_h && $max_h > 0) {
            $dst_w = $dst_w * ($max_h / $dst_h);
            $dst_h = $max_h;
        }
        return array($dst_w, $dst_h);
    }


    private function filesIdentical($fn1, $fn2)
    {
        $buffer_len = 1024;

        if (!$fp1 = fopen(dirname(__DIR__) . '/' . $fn1, 'rb')) {
            return false;
        }

        if (!$fp2 = fopen($fn2, 'rb')) {
            fclose($fp1);
            return false;
        }

        $same = true;
        while (!feof($fp1) and !feof($fp2)) {
            if (fread($fp1, $buffer_len) !== fread($fp2, $buffer_len)) {
                $same = false;
                break;
            }
        }

        if (feof($fp1) !== feof($fp2)) {
            $same = false;
        }

        fclose($fp1);
        fclose($fp2);

        return $same;
    }
}
