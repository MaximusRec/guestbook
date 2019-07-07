<?//<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js'></script>

require_once ("guestBook.php");

$obj = new guestBook();
$countRec= $obj->countRec();
$colPage= $obj->recPerPage($countRec, recPerPage);
$pag= $obj->pagination($colPage, 1);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>GuestBook</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="/js/jquery.min.js"></script>
    <script src="/js/ajaxupload.js"></script>
    <script src="/js/upload.js"></script>

    <script type="text/javascript">
        var page= 0;
        var order= "";

        function readGestBook (page, order) {
            $.ajax ( {
                url:"read.php",
                type:"GET",
                data: "page=" + page + "&order=" + order,
                dataType: "html",
                beforeSend: function () {
                    $("#comments").text ("Ожидание данных...");
                },
                success: function (data) {
                    $("#comments").html(data);
                }
            });
        }

        function selOrder() {
            order =  $("#order option:selected").val();
            readGestBook (page, order);
            $('.box').hide();
        }

        $(document).ready (function () {
            var request;
            var counterPage="0";
            var focus = false;
            $('.box').hide();
            $(document).on("click", "a", function(){
                var linkTargetUrl=$(this).attr("href");
                $("#CommentBox").load(linkTargetUrl);
                $('.box').show();
            });


        $(".box").bind("click", function(){
                $('.box').hide();
        });
        $("#close").bind("click", function(){
                $('.box').hide();
        });

        $("#send").bind("click", function() {
                $('.box').hide();
                $.ajax ( {
                    url:"load.php",
                    type:"POST",
                    data:({login: $("#login").val(),email: $("#email").val(),url: $("#url").val(),texts: $("#texts").val(),img: $("#img2").val()}),
                    dataType: "html",
                    dataType:"json",
                    beforeSend: function () {
                        $("#information").text ("Ожидание данных...");
                    },
                    success: function (data) {
                        $("#information").text ("Данные получены.");       $("#information").css("background", "#dfffbc");
                        request = JSON.parse(JSON.stringify(data));

                        if ( request['login']['error'] == 'yes' ) {
                            $("#infologin").text(request['login']['message']);
                            $("#infologin").css("background", "#ffb698");
                            $("#login").focus();
                            focus = true;
                        } else {
                            $("#infologin").text('');
                        }

                        if ( request['email']['error'] == 'yes' ) {
                            $("#infoemail").text(request['email']['message']);
                            $("#infoemail").css("background", "#ffb698");
                            if ( focus === false )
                            {
                                $("#email").focus();
                                focus = true;
                            }
                        } else {
                            $("#infoemail").text('');
                        }

                        if ( request['url']['error'] == 'yes' ) {
                            $("#infourl").text(request['url']['message']);
                            $("#infourl").css("background", "#ffb698");
                        } else {
                            $("#infourl").text('');
                        }

                        if ( request['texts']['error'] == 'yes' ) {
                            $("#infotexts").text(request['texts']['message']);
                            $("#infotexts").css("background", "#ffb698");
                            if ( focus === false )
                            {
                                $("#texts").focus();
                            }
                        } else {
                            $("#infotexts").text('');
                        }

                        if ( request['result']['error'] == 'success' ) {
                            $("#information").text(request['result']['message']);
                            $("#information").css("background", "#dfffbc");
                        } else if ( request['result']['error'] == 'fail' ) {
                            $("#information").text(request['result']['message']);
                            $("#information").css("background", "#ffb698");
                        }

                        readGestBook (page, order);

                    },
                });

            });

            readGestBook (page, order);

            $(".pagination").bind("click", function() {
                page= $(this).text();
                readGestBook (page, order);
                $('.box').hide();
            });

        });
    </script>

</head>
<img style=' float: right;' src="/uploads/img/gif.gif" alt="">
<form enctype="multipart/form-data" method="post">
    <h1 style="text-align: center;">Гостевая книга</h1>

    <div> Имя пользователя*:<br>
        <input type='text' id='login' value='' >
    <div class="info" style='display: inline-block;'  id="infologin"></div></div><br>

    <div> E-mail*:<br>
        <input type='text' id='email' value='' >
    <div class="info" id="infoemail"></div></div><br>

    <div> Homepage:<br>
        <input type='text' id='url' value='' placeholder="введите адрес сайта" >
    <div class="info" id="infourl"></div></div><br>

     Изображение:  <input type='hidden' id='img2' value='' >
    <div style="text-align: center; width:340px;">
        <div id="files"><img style='border-radius: 3px; border: 1px solid #000; cursor: pointer;' src="/uploads/img/image.jpg" alt="" id="img_upload" ></div>

    <div id="upload" >Загрузить</div>
    </div><br><br>

    <div> Текст*:<br>
        <textarea type='text' rows='6' cols='125' name='texts' id='texts' ></textarea>
    <div id="infotexts"></div></div>

    <div> * - поля, обязательные к заполнению</div>

    <div style="text-align: center;" ><input type='submit' id='send' onclick="return false" value='Отправить' >

    <div class="info" id="information"></div></div>


</form> <hr>


<h2 style="text-align: center;">Оставленные коментарии:</h2>
Сортировка: <select style="cursor: pointer;" id="order" size="1" onchange='selOrder()' >
    <option >Выберите поле для сортировки</option>
    <option value="login">Логин</option>
    <option value="logindesk">Логин (обратная)</option>
    <option value="email">E-mail</option>
    <option value="emaildesk">E-mail (обратная)</option>
    <option value="acreated">Дата создания</option>
    <option value="acreateddesk">Дата создания (обратная)</option>
</select>

<?=$pag;?>

<div class="box">
    <div id="CommentBox"></div>
    <hr>
    <div style="text-align: center; padding: 10px;">
        <a id="close" onclick="return false;">Закрыть</a>
    </div>
</div>

<div id="comments"></div>

</body>
</html>






