<?php

require_once "FaceDetector.php";

$file_name = $_FILES['face_image']['name'];//ファイル名
$upload_pic = "./upload_pic/".$file_name; //写真の保存先
//$upload_pic = "./".$file_name; //写真の保存先
$originalImage =''; //取得した元画像のパス

try {

    move_uploaded_file($_FILES['face_image']['tmp_name'],$upload_pic);//一時ファイルから写真フォルダに移動


} catch (Exception $e) {

    echo $e->getMessage();

}

//DBに接続して画像パスをDBに登録
$dsn='mysql:dbname=portfolio;host=docker-lamp-202004-master_db_1;charset=utf8';
$username='root';
$password='root';
$sql='INSERT INTO up_pic(pic_path,file_name)VALUE(?,?)';


try {

    $pdo = new PDO($dsn,$username,$password);
    $prepare = $pdo->prepare($sql);
    $prepare->bindValue(1,$upload_pic,PDO::PARAM_STR);
    $prepare->bindValue(2,$file_name,PDO::PARAM_STR);
    $prepare->execute();

} catch (PDOException $e) {

    echo $e->getMessage();

}
$pdo = null;

//DBに接続してアップした画像のパスを取得してくる



$sql='SELECT pic_path FROM up_pic WHERE file_name LIKE ?';
$re_ile_name = '%'.$file_name.'%';


try {

   $pdo = new PDO($dsn,$username,$password);
    $prepare = $pdo->prepare($sql);
    $prepare->bindValue(1,$re_ile_name,PDO::PARAM_STR);
    $prepare->execute();


    while ($row = $prepare->fetch()) {

        $originalImage = $row['pic_path'];

    }

} catch (PDOException $e) {

    echo $e->getMessage();

}
$pdo = null;

//ライブラリFaceDetector.phpで顔検知
$detector = new svay\FaceDetector('detection.dat');
$detector->faceDetect($originalImage);
$detector->toJpeg();

if($originalImage){ //PHPで顔認識を試したいだけなのでディレクトリとDBから全削除

    unlink($originalImage);
    $sql = 'DELETE FROM up_pic';
    try {

        $pdo = new PDO($dsn,$username,$password);
        $pdo->query($sql);

    } catch (PDOException $e) {

        echo $e->getMessage();

    }
    $pdo = null;

}







