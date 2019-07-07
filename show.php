<?php
/**
 * Вывод одного комментария, который возвращается по Ajax на главную страницу.
 *
 */
require_once ("guestBook.php");

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    $obj = new guestBook();
    $data = $obj->showRec($id);     //$_SERVER['DOCUMENT_ROOT']
    echo "Комментарий № <strong>{$data['a_id']}</strong> от {$data['login']}<hr>";
    if (!empty($data['img'])) echo "<img class='imgbox' src='/uploads/" . $data['img'] . "' alt='image' >";
    echo $data['texts'];
}