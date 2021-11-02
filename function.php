<?php
ini_set('log_errrors','on');
ini_set('error_log','php.log');

// デバッグ関数
$debug_flg = true;
function debug($str){
    global $debug_flg;
    if($debug_flg){
        error_log('デバッグ:'.$str);
    }
}

// セッションの準備
// セッションファイルの保存場所の設定
session_save_path('./var/tmp');
// セッションを使う
session_start();

session_regenerate_id();



?>