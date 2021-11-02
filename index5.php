<?php


require('function.php');
debug('画面表示処理開始-----------------------');
debug('$_SESSIONの中身1：'.print_r($_SESSION,true));



// 日数をカウントする関数
function countDay(){
    if(empty($_SESSION['day'])){
        $_SESSION['day'] = 0;
    }else{
        $_SESSION['day'] ++;
    } 
}



interface HistoryInterface{
    public static function set($str);
    public static function clear();
}


// 履歴管理クラス
class History implements HistoryInterface{
    public static function set($str){
        // セッションhistoryが作られていなければ作る
        if(empty($_SESSION['history'])) $_SESSION['history'] = '';
        // 文字列をセッションhistoryへ格納
        $_SESSION['history'] .=$str.'<br>';
    }
    public static function clear(){
        unset($_SESSION['history']);
    }

}

// ゲーム初期化関数
function init(){
    debug('ゲーム初期化関数です!!!!!!!!!!!!!!');
    unset($_SESSION);

    // $_SESSION = array();
    debug('$_SESSIONの中身2：'.print_r($_SESSION,true));

    History::set('ゲームを開始します');
    
    countDay();

}





if(!isset($_SESSION['page_flg'])) $_SESSION['page_flg'] = 'start_page';
// $doctor_action_flg=false;


// ページ遷移を制御
if(!empty($_POST)){
    debug('post送信があります');
    debug('$_POSTの中身：'.print_r($_POST,true));
    if(isset($_POST['start'])){
        debug('スタートが押されました');
        init();
        $_SESSION['page_flg'] = 'main_page';
    }elseif(isset($_POST['doctor_action'])){
        debug('医者の行動ボタンが押されました');
        $_SESSION['page_flg'] = 'main_page';
        $doctor_action_flg=true;
    }else{
        debug('不明です');
    }


}



debug('$_SESSIONの中身4：'.print_r($_SESSION,true));

debug('画面表示処理終了^^^^^^^^^^^^^^^^^^^^^^^^^');



?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>病院経営ゲーム</title>
</head>
<?php debug('ページ分岐です');
    switch($_SESSION['page_flg']){
        case ('start_page'):
            require('start_page.php');
            break;
        case ('main_page'):
            require('main_page.php');
            break;
        case ('equipment_page'):
            require('equipment.php');
            break;
        case ('game_over'):
            require('game_over.php');
            break;
        case ('game_clear'):
            require('game_clear.php');
            break;
        default:
            break;

}?>
    


</html>
