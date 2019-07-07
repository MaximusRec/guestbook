<?php
require_once ("guestBook.php");

$obj = new guestBook();

        $login = $obj->validateLogin($_POST['login']);          //  Получаем логин

        $email = $obj->validateEmail($_POST['email']);          //  Получаем email

        $url = $obj->validateUrl($_POST['url']);          //  Получаем URL

        $texts = $obj->validateTexts($_POST['texts']);          //  Получаем текст

        if (!empty($_POST['img']))
            $obj->img = $_POST['img'];          //  Получаем имя картинки

        $result = $obj->addComment();     //  Записываем в БД

//  Возвращаем результат работы
        $data = [
            'login' => $login,
            'email' => $email,
            'url' => $url,
            'texts' => $texts,
            'img' => $obj->img,
            'result' => $result,
        ];
        echo json_encode($data);
?>