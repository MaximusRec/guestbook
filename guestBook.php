<?php
define("recPerPage", 10, true); //  Количество записей на странице


class guestBook
{
    var $pdo;
    var $login;
    var $email;
    var $url;
    var $texts;
    var $img;

    public function __construct()
    {
        include $_SERVER['DOCUMENT_ROOT'] . "/pdo.php";
        $this->pdo = $pdo;
    }

    /**
     * Функция проверяет пустое ли поле.
     * @param $value
     * @return bool
     */
    public function validateEmpty($value)
    {
        if (($value == "") OR !isset($value)) return $result = true;
        return $result = false;
    }

    /**
     * Функция валидации поля login. Обязательное, только латинские буквы и цифры.
     * @param $login
     * @return array
     */
    public function validateLogin($login)
    {
        if ($this->validateEmpty($login)) return ["error" => "yes", "message" => "Поле с именем не может быть пустым"];
        if (preg_match("/[^a-zA-Z\d]+/", $login)) {
            return $result = ["error" => "yes", "message" => "Допустимы к использованию только цифры и латинские символы."];
        } else {
            $this->login = $login;
            return $result = ["error" => "no", "message" => ""];
        }
    }

    /**
     * Функци валидации для поля email. Обязательное, проверка соответствия email + проверка на уникальность в БД
     * @param $email
     * @return array
     */
    public function validateEmail($email)
    {
        if ($this->validateEmpty($email)) return ["error" => "yes", "message" => "Пожалуйста заполните это поля."];

        if (!preg_match("/^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$/", $email)) {
            $result = ["error" => "yes", "message" => "Допустимы к использованию только цифры и латинские символы."];
        } else {
            try {
                $sql = "SELECT COUNT(`email`) as `counts` FROM `t_message` WHERE `email` = :email ;";
                $queryes = $this->pdo->prepare($sql);
                $queryes->execute([':email' => $email]);
                $results = $queryes->fetch();

                if ($results['counts'] != 0) {
                    $result = ["error" => "yes", "message" => "Такой email уже зарегестрирован в системе"];
                } else {
                    $this->email = $email;
                    $result = ["error" => "no", "message" => ""];
                }
            } catch (PDOException $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
                return false;
            }
        }

        return $result;
    }

    /**
     * Функция валидации для поля URL. Необязательное, проверка на соответствие URL
     * @param $url
     * @return array
     */
    public function validateUrl($url)
    {
        if (!empty ($url)) {
            if (!preg_match("/^((https?|ftp)\:\/\/)?([a-z0-9]{1})((\.[a-z0-9-])|([a-z0-9-]))*\.([a-z]{2,6})(\/?)$/", $url)) {
                $result = ["error" => "yes", "message" => "Неправильный формат имени сайта, введите пожалуйста по примеру «http://www.my-site.com»"];
            } else {
                $this->url = $url;
                $result = ["error" => "no", "message" => ""];
            }
        } else $result = ["error" => "no", "message" => ""];

        return $result;
    }

    /**
     * Функция валидации для текста. Обязательное, проверка на отсутствие тегов
     * @param $texts
     * @return array
     */
    public function validateTexts($texts)
    {   $result= [];
        if ($this->validateEmpty($texts)) return $result= ["error" => "yes", "message" => "Ваше сообщение не может быть пустым, заполните пожалуйста"];

        if ($texts != strip_tags($texts)) {
            $result= ["error" => "yes", "message" => "В вашем тексте содержатся теги, а они запрещены."];
        } else {
            $this->texts = $texts;
            $result= ["error" => "no", "message" => ""];
        }

        return $result;
    }

    /**
     * Проверяем все ли обязательные поля заполнены
     * @return bool
     */
    public function check()
    {
        if (!empty($this->login) AND
            !empty($this->email) AND
            !empty($this->texts))
            $result = true;
        else $result = false;

        return $result;
    }

    /**
     * Вычисляем общее количество заметок в БД
     * @return bool|int
     */
    public function countRec()
    {
        try {
            $sql = "SELECT COUNT(*) as `counts` FROM `t_message` ";
            $queryes = $this->pdo->prepare($sql);
            $queryes->execute();
            $results = $queryes->fetch();

            if ($results) {
                return $results['counts'];
            } else {
                return false;
            }

        } catch (PDOException $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Вычисляем кол-во страниц пагинации
     * @param $countPage
     * @param $recPerPage
     * @return int
     */
    public function recPerPage($countPage, $recPerPage)
    {
        if ($recPerPage == 0) $recPerPage = recPerPage;
        $paginationPage = ((int)$countPage / $recPerPage);
        return ceil($paginationPage);
    }

    /**
     * Функция вывода на печать объекты для выбора номера страницы пагинации
     * @param $countPage - количество страниц в пагинации
     * @param $page - выбранная страница
     * @return string
     */
    public function pagination($countPage, $page)
    {
        if ( $countPage > 1)
        {
            $result = "";
            for ($n = 1; $n <= $countPage; $n++) {
                    $result .= '&nbsp;&nbsp;<div class="pagination">' . $n . '</div>';
            }
            $result = "Пагинация:" . $result;
        }

        return $result;
    }


    /**
     * Читаем нужныю часть записей из БД. В результате выполнения возвращает массив с выборкой.
     * @param $page
     * @param $sort
     * @return bool|array
     */
    public function readRec($page, $sort, $recPerPage)
    {
        $limit = $recPerPage;
        if ($page == "1" ) $page = 0;
        if ($page > 0 ) $page = (int)$page - 1;
        if (!empty($page)) $offset = (int)$page * $recPerPage; else $offset = 0;

        $data = [':limit' => $limit,
            ':offset' => $offset];

        try {
            $sql = "SELECT `login`,`email`,`url`,`texts`, `a_created`, `a_id` FROM `t_message` ";

            if ($sort == 'login') {
                $sql .= " ORDER BY `login` ASC ";
            }
            if ($sort == 'logindesk') {
                $sql .= " ORDER BY `login` DESC ";
            }

            if ($sort == 'email') {
                $sql .= " ORDER BY `email` ASC ";
            }
            if ($sort == 'emaildesk') {
                $sql .= " ORDER BY `email` DESC ";
            }

            if ($sort == 'a_created') {
                $sql .= " ORDER BY `a_created` ASC ";
            }
            if ($sort == 'acreateddesk') {
                $sql .= " ORDER BY `a_created` DESC ";
            }

            $sql .= "LIMIT :offset, :limit ;";
            $queryes = $this->pdo->prepare($sql);
            $result = $queryes->execute($data);
            while ($results = $queryes->fetch()) {
                $res[] = $results;
            }

            if ($result) {
                return $res;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
            return false;
        }
    }


    /**
     * Выборка одного комментария
     * @param $id
     * @return bool
     */
    public function showRec($id)
    {
        try {
            $sql = "SELECT * FROM `t_message` WHERE `a_id` = :id ";
            $queryes = $this->pdo->prepare($sql);
            $result = $queryes->execute( [':id' => $id] );
            $results = $queryes->fetch();

            if ($results) {
                return $results;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Записываем комментарий/отзыв в БД
     * @param $login
     * @param $email
     * @param $url
     * @param $texts
     * @return bool
     */
    public function addRec($login, $email, $url, $texts, $img)
    {
        $data = [':login' => $login,
            ':email' => $email,
            ':texts' => $texts];
        $urlSql = $urlSql2 = $urlImg = $urlImg2 = '';
        if (!empty ($url)) {
            $data[':url'] = $url;            $urlSql = ', `url`';            $urlSql2 = ', :url ';
        }
        if (!empty ($img)) {
            $data[':img'] = $img;            $urlImg = ', `img`';            $urlImg2 = ', :img ';
        }

        try {
            $sql_queryes = "INSERT INTO `t_message` ( `login`, `email`, `texts` {$urlSql} {$urlImg}) VALUES ( :login, :email, :texts {$urlSql2} {$urlImg2});";
            $queryes = $this->pdo->prepare($sql_queryes);
            $result = $queryes->execute($data);

            if ($result) {
                $result = true;
            } else {
                $result = false;
            }

        } catch (PDOException $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
            $result = false;
        }

        return $result;
    }


    /**
     * Добавляем запись в БД и по результатам отвечаем о успехе записи.
     * Внутри работаем с переменными класса
     * @return array
     */
    public function addComment()
    {
        if ($this->check() == true) {
            $rec = $this->addRec($this->login, $this->email, $this->url, $this->texts, $this->img);
            if ($rec == true) $result = ["error" => "success", "message" => "Данные добавлены успешно"];
            else $result = ["error" => "fail", "message" => "К сожалению ваш комментарий не сохранён."];

        } else
            $result = ["error" => "fail", "message" => "К сожалению ваш комментарий не сохранён."];

        return $result;
    }


    /**
     * Функция изменения размера изображения
     * @param $src - картинка источник
     * @param $dst - обработанное пережатое изображение
     * @param $width - требуемая ширина изображения
     * @param $height - требуемая высота изображения
     * @return bool|string
     *
     * use $this->image_resize($file, $newFile, 320, 240);
     */
    function image_resize($src, $dst, $width, $height){

        if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";

        $type = strtolower(substr(strrchr($src,"."),1));
        if($type == 'jpeg') $type = 'jpg';
        switch($type){
            case 'gif': $img = imagecreatefromgif($src); break;
            case 'jpg': $img = imagecreatefromjpeg($src); break;
            case 'png': $img = imagecreatefrompng($src); break;
            default : return "Unsupported picture type!";
        }

        $dst_x = 0;
        $dst_y = 0;
        $width_ok = $width;
        $height_ok = $height;

        // resize
        $ratio = min($width/$w, $height/$h);

        $height = $h * $ratio;
        $width = $w * $ratio;

        $dst_x = ceil(( $width_ok - $width ) / 2) ;
        $dst_y = ceil(( $height_ok - $height ) / 2) ;

        //  округляем для красоты
        $x = 0;
        $width= ceil($width);
        $height= ceil($height);
        $w= ceil($w);
        $h= ceil($h);
        $width_ok= ceil($width_ok);
        $height_ok= ceil($height_ok);


        $new = imagecreatetruecolor($width_ok, $height_ok);

        // preserve transparency
        if($type == "gif" or $type == "png"){
            imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127)); // создадим белый фон +
            $white = imagecolorallocate($new, 255, 255, 255);
            imagefill($new, 0, 0, $white);
            imagealphablending($new, true);
            imagesavealpha($new, true);
        }

        if($type == "jpg"){
            //  белый фон для jpg
            $white = imagecolorallocate($new, 255, 255, 255);
            imagefill($new, 0, 0, $white);
        }

        imagecopyresampled($new, $img, $dst_x, $dst_y, $x, 0, $width, $height, $w, $h);

        //  текст для отладки
        imagestring($new, 5, 45, 165, "offset [ {$dst_x}px * {$dst_y}px ]", 0x4f2eff);
        imagestring($new, 6, 45, 185, "original [ {$w}px * {$h}px ]", 0x4f2eff);
        imagestring($new, 6, 45, 205, "resize [ {$width}px * {$height}px ]", 0x4f2eee);
        imagestring($new, 6, 45, 225, "end size [ {$width_ok}px * {$height_ok}px ]", 0x4f2eee);

        switch($type){
            case 'bmp': imagewbmp($new, $dst); break;
            case 'gif': imagegif($new, $dst); break;
            case 'jpg': imagejpeg($new, $dst); break;
            case 'png': imagepng($new, $dst); break;
        }

        imagedestroy($new, $img);   //  очистка памяти

        return true;
    }




/**
 * Функция изменения размеров изображения
 * @param  $file - file name to resize
 * @param  $string - The image data, as a string
 * @param  $width - new image width
 * @param  $height - new image height
 * @param  $proportional - keep image proportional, default is no
 * @param  $output - name of the new file (include path if needed)
 * @param  $delete_original - if true the original image will be deleted
 * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
 * @param  $quality - enter 1-100 (100 is best quality) default is 100
 * @param  $grayscale - if true, image will be grayscale (default is false)
 * @return boolean|resource
 */
public function smart_resize_image($file,
                                   $string             = null,
                                   $width              = 0,
                                   $height             = 0,
                                   $proportional       = false,
                                   $output             = 'file',
                                   $delete_original    = true,
                                   $use_linux_commands = false,
                                   $quality            = 100,
                                   $grayscale          = false,
                                   $debug = false
) {

if (file_exists ( $file )) {
    if ($height <= 0 && $width <= 0) return false;
    if ($file === null && $string === null) return false;
    # Setting defaults and meta
    $info = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
    $image = '';
    $final_width = 0;
    $final_height = 0;
    list($width_old, $height_old) = $info;
    $cropHeight = $cropWidth = 0;
    # Calculating proportionality
    if ($proportional) {
        $dst_x = $dst_y = 0;
        $width_ok = $width;
        $height_ok = $height;

    // resize
        if      ($width  == 0)  {$ratio = $height / $height_old;}
        elseif  ($height == 0)  {$ratio = $width / $width_old;}
        else  {$ratio = min($width / $width_old, $height / $height_old);}

        $final_height = $height_old * $ratio;
        $final_width = $width_old * $ratio;
    } else {
        $final_width = ($width <= 0) ? $width_old : $width;
        $final_height = ($height <= 0) ? $height_old : $height;
        $widthX = $width_old / $width;
        $heightX = $height_old / $height;

        $x = min($widthX, $heightX);
        $cropWidth = ($width_old - $width * $x) / 2;
        $cropHeight = ($height_old - $height * $x) / 2;
    }

    $dst_x = ceil(($width_ok - $final_width) / 2);
    $dst_y = ceil(($height_ok - $final_height) / 2);

    # Loading image to memory according to type
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);
            break;
        case IMAGETYPE_GIF:
            $file !== null ? $image = imagecreatefromgif($file) : $image = imagecreatefromstring($string);
            break;
        case IMAGETYPE_PNG:
            $file !== null ? $image = imagecreatefrompng($file) : $image = imagecreatefromstring($string);
            break;
        default:
            return false;
    }

    # Making the image grayscale, if needed
    if ($grayscale) {
        imagefilter($image, IMG_FILTER_GRAYSCALE);
    }

    # This is the resizing/resampling/transparency-preserving magic
    $image_resized = imagecreatetruecolor($width_ok, $height_ok);

    if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) {
        imagecolortransparent($image_resized, imagecolorallocatealpha($image_resized, 0, 0, 0, 127)); // создадим белый фон +
        $white = imagecolorallocate($image_resized, 255, 255, 255);
        imagefill($image_resized, 0, 0, $white);
        imagealphablending($image_resized, true);
        imagesavealpha($image_resized, true);
    }
    if ($info[2] == IMAGETYPE_JPEG) {
        //  белый фон для jpg
        $white = imagecolorallocate($image_resized, 255, 255, 255);
        imagefill($image_resized, 0, 0, $white);
    }

    imagecopyresampled($image_resized, $image, $dst_x, $dst_y, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);

    if ($debug) {
        //  текст для отладки
        imagestring($image_resized, 5, 45, 165, "offset [ {$dst_x}px * {$dst_y}px ]", 0x4f2eff);
        imagestring($image_resized, 6, 45, 185, "original [ {$width_old}px * {$height_old}px ]", 0x4f2eff);
        imagestring($image_resized, 6, 45, 205, "resize [ {$final_width}px * {$final_height}px ]", 0x4f2eee);
        imagestring($image_resized, 6, 45, 225, "end size [ {$width_ok}px * {$height_ok}px ]", 0x4f2eee);
    }

    # Taking care of original, if needed
    if ($delete_original) {
        if ($use_linux_commands) exec('rm ' . $file);
        else @unlink($file);
    }
    # Preparing a method of providing result
    switch (strtolower($output)) {
        case 'browser':
            $mime = image_type_to_mime_type($info[2]);
            header("Content-type: $mime");
            $output = NULL;
            break;
        case 'file':
            $output = $file;
            break;
        case 'return':
            return $image_resized;
            break;
        default:
            break;
    }

    # Writing image according to type to the output destination and image quality
    switch ($info[2]) {
        case IMAGETYPE_GIF:
            imagegif($image_resized, $output);
            break;
        case IMAGETYPE_JPEG:
            imagejpeg($image_resized, $output, $quality);
            break;
        case IMAGETYPE_PNG:
            $quality = 9 - (int)((0.9 * $quality) / 10.0);
            imagepng($image_resized, $output, $quality);
            break;
        default:
            return false;
    }

    imagedestroy($image_resized, $output, $file);   //  очистка памяти

    return true;
} else return false;
}


    /**
     * Функция уменьшающая изображения до заданного размера в 320 * 240
     * @param $file - файл источник (большое изображение)
     * @param $newFile - новый файл, в который сохраняется уже обработанное изоображение
     */
  public function resize_image($file, $newFile)
  {
      $this->smart_resize_image($file,
                              "",
                              320,
                              240,
                              true,
                              $newFile,
                              true,
                              false,
                              90,
                              false,
                              true);
  }


}