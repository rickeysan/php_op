<?php


require('function.php');
debug('画面表示処理開始-----------------------');
debug('$_SESSIONの中身1：'.print_r($_SESSION,true));


// 医者格納用クラス
// $doctors = array();


// アイテム名と説明と画像
class itemName{
    const DRUG = '特効薬';
    const ECMO = 'ECMO';
    const BED = '病床';
}
class itemInfo{
    const DRUG ='重傷の人間をすべて回復させることができます';
    const ECMO = '中等度・重傷者の治療効率が1.2倍になります(複数効果あり）';
    const BED = '入院できる人数です';
}

// アイテムクラス
class Item{
    protected $name;
    protected $info;
    protected $img;
    protected $amount;
    public function __construct($name,$info,$img,$amount){
        $this->name = $name;
        $this->info = $info;
        $this->img = $img;
        $this->amount = $amount;
    }
    public function getName(){
        return $this->name;
    }
    public function getInfo(){
        return $this->info;
    }
    public function getImg(){
        return $this->img;
    }
    public function getAmount(){
        return $this->amount;
    }
    public function setAmount(){

    }
}



// 抽象クラス（人クラス）
abstract class Human{
    protected $hp;
    protected $img;
    public function setHp($num){
        $this->hp = $num;
    }
    public function getHp(){
        return $this->hp;
    }
}

// 日数をカウントする関数
function countDay(){
    if(empty($_SESSION['day'])){
        $_SESSION['day'] = 0;
    }else{
        $_SESSION['day'] ++;
    } 
}



// 患者クラス
class Patient extends Human{
    // 死亡の判定は$hpで、回復の判定は$need_treatmentの値で行う
    protected $condition_flg;
    // $condition_flgは、0は病気状態、1は死亡、2は回復

    public function __construct($hp,$img){
        $this->hp = $hp;
        $this->condition_flg = 0;
        $this->img = $img;
    }

    public function setConditionFlg($num){
        $this->condition_flg = $num;
    }

    public function getConditionFlg(){
        return $this->condition_flg;
    }
    public static function countLevel($array){
        $counter = array('sick'=>0,'dead'=>0,'recover'=>0);
        foreach($array as $key=>$val){
            if($val->getConditionFlg() == 0){
                $counter['sick']++;
            }elseif($val->getConditionFlg() == 1){
                $counter['dead']++;
            }else{
                $counter['recover']++;
            }
        }
        return $counter;
    }
    function judgeCondition(){
        if($this->hp <= 0){
            $this->setConditionFlg(1);
        }elseif($this->hp >= 100){
            $this->setConditionFlg(2);
        }
    }
    public function getSick($num){
        if(!$this->getConditionFlg()){
            $this->setHp($this->getHp()-$num);
        }
    }
    public function getHeal($num){
        if(!$this->getConditionFlg()){
            $this->setHp($this->getHp()+$num);
        }
    }
}


// 軽症者クラス
class Mild extends Patient{
    public function __construct($hp,$img){
        parent::__construct($hp,$img);
    }
    
}

// 中等症クラス
class Moderate extends Patient{
    public function __construct($hp,$img){
        parent::__construct($hp,$img);
    }
}

// 重傷者クラス
class Critically extends Patient{
    public function __construct($hp,$img){
        parent::__construct($hp,$img);
    }
}



// 医者クラス
class Doctor extends Human{
    protected $skill;
    public function __construct($skill){
        $this->skill = $skill;
    }
    function getSkill(){
        return $this->skill;
    }
    function setSkill($num){
        $this->skill = $num;
    }
    function treatment($targetObjArray,$point){
        foreach($targetObjArray as $key=>$val){
            $val->getHeal($point); 
        }
    }
    function study($num){
        $this->setSkill($this->getSkill()+$num);    
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
    global $totalPatients;
    global $doctors;
    
    debug('ゲーム初期化関数です');
    $_SESSION = array();
    // 患者格納用クラス
    $totalPatients = array();
    debug('$totalPatientsの中身0：'.print_r($totalPatients,true));
    $_SESSION['milds'] = array();
    $_SESSION['moderates'] = array();
    $_SESSION['criticallys'] = array();

    History::set('ゲームを開始します');
    createPatients(mt_rand(1,2));
    $totalPatients = getTotalPatients();
    debug('$totalPatientsの中身2：'.print_r($totalPatients,true));
    createDoctor();
    createDoctor();
    countDay();

}


// アイテムのインスタンス作成
$drug = new Item(itemName::DRUG,itemInfo::DRUG,'img/medical_vaccine_covid19.png',0);
$ecmo = new Item(itemName::ECMO,itemInfo::ECMO,'img/medical_ecmo_machine.png',0);
$bed = new Item(itemName::BED,itemInfo::BED,'img/kaigo_bed.png',5);



// 患者をランダムに生成する関数
function createPatients($num){
    for($i=1; $i<=$num; $i++){
        debug($i.'回目のループです');
        if(mt_rand(0,1)){
            $_SESSION['milds'][] = new Mild(mt_rand(30,80),'img/sick_hatunetsu_man.png');
            History::set('軽症患者が入院しました');
            debug('ループ1');
        }elseif(mt_rand(0,2)){
            $_SESSION['moderates'][] = new Moderate(mt_rand(30,80),'img/sleep_seki_man.png');
            History::set('中等症患者が入院しました');
            debug('ループ2');

        }else{
            $_SESSION['criticallys'][] = new Critically(mt_rand(30,80),'img/medical_ecmo_man.png');
            History::set('重症患者が入院しました');
            debug('ループ3');

        }
    }
}

// 医者を生成する関数
function createDoctor(){
    global $doctors;
    $_SESSION['doctors'][] = new Doctor(1);
}

// 患者の生死を更新する関数
function updatePatients($objArray){
    foreach($objArray As $key=>$val){
        $val->judgeCondition();
    }
}

// 患者の病気が進行する関数
function allGetSick($objArray,$num){
    foreach($objArray as $key=>$val){
        $val->getSick($num);
    }
}



$milds = array();
$moderates = array();
$criticallys = array();
// 3つの配列を一つにまとめる関数
function getTotalPatients(){
    return array_merge($_SESSION['milds'],$_SESSION['moderates'],$_SESSION['criticallys']);
}

// 患者の管理は、状態を問わず、症状度に分けて配列に入れて行う

// // 模擬テスト
// debug('模擬テスト開始--------------------------------');
// for($i=1;$i<4;$i++){
//     debug($i.'回目のループです');
    
//     createPatients(2);
    
//     $totalPatients = getTotalPatients();
//     debug('$totalPatientsの中身1：'.print_r($totalPatients,true));
//     $doctors = array();
//     $doctors[] = new Doctor(10);
//     // 3.患者のHPを回復する（医者の治療）
//     $doctors[0]->treatment($criticallys,mt_rand(10,20));
//     $totalPatients = getTotalPatients();
//     debug('治療終了：$totalPatientsの中身2：'.print_r($totalPatients,true));
//     // 4.患者のHPを減らす（病気の進行）
//     // 軽症・中等症・重症で減少するHPが異なる
//     allGetSick($milds,mt_rand(10,20));
//     allGetSick($moderates,mt_rand(20,30));
//     allGetSick($criticallys,mt_rand(50,60));
//     $totalPatients = getTotalPatients();
//     // debug('病気の進行後：$totalPatientsの中身3：'.print_r($totalPatients,true));
//     // 5.患者の生死を判断する
//     updatePatients($milds);
//     updatePatients($moderates);
//     updatePatients($criticallys);
//     $totalPatients = getTotalPatients();
//     debug('生死の判定後：$totalPatientsの中身4：'.print_r($totalPatients,true));

// }

// PHPメソッドのロジック
// 1.医者を生成する
// 2.患者を生成する
// 3.医者のアクションを行う
// 4.患者の病気を進行させる
// 5.患者の生死を判断する
// 5-2.患者の状態を集計する
// 初めの画面で、1と2のみを行う
// 行動選択後の遷移では、2~5を行う

// スタート画面からの遷移
// init();

if(!isset($_SESSION['page_flg'])) $_SESSION['page_flg'] = 'start_page';
$doctor_action_flg=false;


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

    debug('$_SESSIONの中身2：'.print_r($_SESSION,true));

}




// 医者がどの患者を治療するか決める関数
function decideTreatment($str){
    switch($str){
        case 1:
            return $_SESSION['midls'];
            break;
        case 2:
            return $_SESSION['moderates'];
            break;
        case 3:
            return $_SESSION['criticallys'];
            break;
    }
    
}

// 医者の行動ボタンが押された時の処理
if($doctor_action_flg){
    debug('医者が行動します');
    debug('$_SESSIONの中身3：'.print_r($_SESSION,true));
    if(isset($_POST['doctor_action1'])){
        $_SESSION['doctors'][0]->treatment(decideTreatment($_POST['doctor_action1']));
    }
    if(isset($_POST['doctor_action2'])){
        $_SESSION['doctors'][1]->treatment(decideTreatment($_POST['doctor_action2']));
    }
    $doctor_action_flg=false;
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

}?>
    


</html>
<?php 
debug('$_SESSIONの中身5：'.print_r($_SESSION,true));
?>