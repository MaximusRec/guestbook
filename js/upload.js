$(function(){
    new AjaxUpload($("#upload"), {
        action: "upload.php",
        name: "uploadfile",
        onSubmit: function(file, ext){
            if (!(ext && /^(jpg|png|gif)$/.test(ext))){                // extension is not allowed
					$('#status').text('Ошибка! Разрешены только картинки jpg, gif, bmp');
					return false;	                // cancel upload
            }
            $('#status').text('Загрузка... ');
        },
        onComplete: function(response){
            if(response != null){
                $('#files').html('<img style="border-radius: 3px; border: 1px solid #000;" src="/uploads/' + response + '" alt="">');
                $('#upload').text('Изменить');
                $('#img2').val(response);
            } else {
                $('#files').text('Файл не загружен');
            }
        }
    });

    /*Мультиселектор чето не сработал, продублировал*/

    new AjaxUpload($("#img_upload"), {
        action: "upload.php",
        name: "uploadfile",
        onSubmit: function(file, ext){
            if (!(ext && /^(jpg|png|gif)$/.test(ext))){                // extension is not allowed
					$('#status').text('Ошибка! Разрешены только картинки jpg, gif, bmp');
					return false;	                // cancel upload
            }
            $('#status').text('Загрузка... ');
        },
        onComplete: function(response){
            if(response != null){
                $('#files').html('<img style="border-radius: 3px; border: 1px solid #000;" src="/uploads/' + response + '" alt="">');
                $('#upload').text('Изменить');
                $('#img2').val(response);
            } else {
                $('#files').text('Файл не загружен');
            }
        }
    });
});