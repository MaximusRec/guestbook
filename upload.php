<?php
/**
 * Файл загружки и обработки изображения под стандартные размеры при превышении допустимых размеров.
 */
$maxWidth = 320;    $maxHeight = 240;
$endDir = $_SERVER["DOCUMENT_ROOT"] . '/uploads/';
$uploaddir = $_SERVER["DOCUMENT_ROOT"] . '/uploads/temp/';
$resizedir = $_SERVER["DOCUMENT_ROOT"] . '/uploads/resize/';

$namefile = $_FILES['uploadfile']['name'];
$tmpnamefile = $_FILES['uploadfile']['tmp_name'];
$file = $uploaddir . basename($namefile);
$fileResize = $resizedir . basename($namefile);
$fileEnd = $endDir . basename($namefile);

$pathinfo = pathinfo($namefile);
$ext = $pathinfo['extension'];
$filetypes = array('jpg', 'gif', 'png', 'JPG', 'PNG', 'GIF');

if (!in_array($ext, $filetypes)) {
    echo "<p>Неправильный формат файла</p>";
} else {
    if (move_uploaded_file($tmpnamefile, $file)) {
            list($width, $height, $type, $attr) = getimagesize($file);

            if (((int)$width > $maxWidth) OR ((int)$height > $maxHeight)) {
                if (file_exists($file)) {
                    if (rename($file, $fileResize)) {
                        //  Меняем размер
                        require_once("guestBook.php");
                        $obj = new guestBook();
                        $resizeImage = $obj->resize_image($fileResize, $fileEnd);   //  изменяем размер карнтинки и записываем в конечный файл
                        unlink($file);          //  Удаляем временный файл
                        unlink($fileResize);     //  Удаляем временный файл
                        echo $namefile;
                    }
                }

            } else {
                    if (file_exists($file)) {
                        rename($file, $fileEnd);       //  если изображение проходит по разсеру то просто копируем в конечную папку.
                    }
                }

    }
}