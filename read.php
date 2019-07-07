<?php
/**
 * Страница вывода таблицы с оставленными комментариями.
 * Реагирует на пагинацию и сортировку
 */

require_once ("guestBook.php");

$obj = new guestBook();
$countsRec= $obj->countRec();
$colPage= $obj->recPerPage($countsRec, recPerPage);
if (!empty($_GET['page'])) $page= $_GET['page']; else $page= 1;
if (!empty($_GET['order'])) $order= $_GET['order']; else $order= "";
$datas= $obj->readRec($page, $order, recPerPage );
$i=($page-1)*recPerPage+1;
?>


<table style="width: 90%; text-align: center; border: 1px solid black; ">

    <table style="width: 90%; text-align: center; border: 1px solid black; ">
        <tbody>
        <tr>
            <td>№</td>
            <td>User name</td>
            <td>e-mail</td>
            <td>Адрес сайта</td>
            <td>Дата создания</td>
            <td>Посмотреть</td>
        </tr>
        </tbody>

        <tr>

        <?php if ( !empty($datas) ): ?>
            <? foreach($datas as $data): ?>
                <tr>
                    <td><?=$i++?></td>
                    <td><?=$data['login']?></td>
                    <td><?=$data['email']?></td>
                    <td><?=$data['url']?></td>
                    <td><?=date_format( date_create($data['a_created']) , 'd.m.Y H:i:s');?></td>
                    <td>
                        <a class="links" href="show.php?id=<?=$data['a_id']?>" onclick="return false;">Просмотреть текст</a>
                     </td>
                </tr>
            <? endforeach; ?>
        <?php else: ?>

        <tr>
            <td colspan="5">
                <strong>Записей не найдено</strong>
            </td>
        </tr>

        <? endif; ?>
</table>
