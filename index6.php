<?php

use Critically as GlobalCritically;

require('function.php');
debug('画面表示処理開始-----------------------');
debug('$_SESSIONの中身1：'.print_r($_SESSION,true));


// アイテム名と説明と画像
class itemName{
    const DRUG = '特効薬';
    const ECMO = 'ECMO';
    const BED = '病床';
}
class itemInfo{
    const DRUG ='重傷の人間をすべて完治させることができます';
    const ECMO = '中等度・重傷者の治療効率が1.2倍になります(複数効果あり）';
    const BED = '入院できる人数です';
}

// アイテムクラス
class Item{
    protected $name;
    protected $info;
    protected $img;
    protected $amount;
    protected $price;
    public function __construct($name,$info,$img,$amount,$price){
        $this->name = $name;
        $this->info = $info;
        $this->img = $img;
        $this->amount = $amount;
        $this->price = $price;
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
    public function setAmount($num){
        $this->amount=$num;
    }
    public function getPrice(){
        return $this->price;
    }
}

// 治療薬クラス
class Drug extends Item{
    public function __construct($name,$info,$img,$amount,$price){
        parent::__construct($name,$info,$img,$amount,$price);
    }
    public function useDrug($targetObjArray){
        foreach($targetObjArray as $key=>$val){
            if($val->getConditionFlg()==0){
                $val->setConditionFlg(2);
                History::set('治療薬を使ったおかげで'.$val->getName().'が回復しました');
            }
        }
    }
}

// ECMOクラス
class Ecmo extends Item{
    public function __construct($name,$info,$img,$amount,$price){
        parent::__construct($name,$info,$img,$amount,$price);
    }
}

// BEDクラス
class Bed extends Item{
    public function __construct($name,$info,$img,$amount,$price){
        parent::__construct($name,$info,$img,$amount,$price);
    }
}


// 抽象クラス（人クラス）
abstract class Human{
    protected $name;
    protected $hp;
    protected $img;
    public function __construct($name){
        $this->name = $name;
    }
    public function getName(){
        return $this->name;
    }
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
        $_SESSION['day'] = 1;
    }else{
        $_SESSION['day'] ++;
    } 
    History::set($_SESSION['day'].'日目です');
}



// 患者クラス
class Patient extends Human{
    // 死亡の判定は$hpで、回復の判定は$need_treatmentの値で行う
    protected $condition_flg;
    // $condition_flgは、0は病気状態、1は死亡、2は回復

    public function __construct($name,$hp,$img){
        parent::__construct($name);
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
        debug('judgeCondition関数です');
        debug('対象オブジェクト：'.print_r($this,true));
        $msg = $this->name;
        if($this instanceof Mild){
            $msg.='(軽症患者)';
        }elseif($this instanceof Moderate){
            $msg.='(中等症患者)';
        }else{
            $msg.='(重症患者)';
        }
        if($this->getConditionFlg() == 0 && $this->hp <= 0){
            $msg.='が死亡しました';
            History::set($msg);
            $this->setConditionFlg(1);
        }elseif($this->getConditionFlg() == 0 && $this->hp >= 100){
            $msg.='が回復しました';
            History::set($msg);
            $this->setConditionFlg(2);
        }
        $msg = '';
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
    public function __construct($name,$hp,$img){
        parent::__construct($name,$hp,$img);
    }
    
}

// 中等症クラス
class Moderate extends Patient{
    public function __construct($name,$hp,$img){
        parent::__construct($name,$hp,$img);
    }
}

// 重傷者クラス
class Critically extends Patient{
    public function __construct($name,$hp,$img){
        parent::__construct($name,$hp,$img);
    }
    // 容体が急変して、大ダメージを負うことがある
    public function getSick($num){
        if(!mt_rand(0,2)){
            debug('大ダメージを受ける');
            $this->setHp($this->getHp()-(int)$num*1.2);
        }else{
            parent::getSick($num);
        }
    }

}

// 名前を生成する関数
function makeName($num){
    $name = '';
    $chars = 'ABCDEFGHIJKLNMOPQRSTUVWXYZ';
    for($i=1;$i<=$num;$i++){
        $name .=$chars[mt_rand(0,mb_strlen($chars)-1)];
    }
    $name .='さん';
    return $name;
}



// 医者クラス
class Doctor extends Human{
    protected $skill;
    public function __construct($name,$skill){
        parent::__construct($name);
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
            if($val instanceof Moderate || $val instanceof Critically && !empty($_SESSION['ecmo']->getAmount())){
                debug('ecmoを使います');
                debug('増加前の$point'.$point);
                $point = $point*(1+0.1*$_SESSION['ecmo']->getAmount());
                $point = (int)$point;
                debug('増加後の$point'.$point);
            }else{
                $val->getHeal($point); 
            }
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
// アイテムのインスタンス作成
$drug = new Drug(itemName::DRUG,itemInfo::DRUG,'img/medical_vaccine_covid19.png',0,100);
$ecmo = new Ecmo(itemName::ECMO,itemInfo::ECMO,'img/medical_ecmo_machine.png',0,200);
$bed = new Bed(itemName::BED,itemInfo::BED,'img/kaigo_bed.png',3,70);

// ゲーム初期化関数
function init(){
    global $totalPatients;
    global $drug;
    global $ecmo;
    global $bed;

    debug('ゲーム初期化関数です');
    $_SESSION = array();
    debug('$_SESSIONの中身6：'.print_r($_SESSION,true));
    // 患者格納用クラス
    $totalPatients = array();
    $_SESSION['milds'] = array();
    $_SESSION['moderates'] = array();
    $_SESSION['criticallys'] = array();
    
    $_SESSION['drug'] = $drug;
    $_SESSION['ecmo'] = $ecmo;
    $_SESSION['bed'] = $bed;


    History::set('ゲームを開始します');
    createPatients(mt_rand(1,2));
    $totalPatients = getTotalPatients();
    createDoctor();
    $_SESSION['money'] = 1000;


}



// 患者をランダムに生成する関数
function createPatients($num){
    for($i=1; $i<=$num; $i++){
        debug('すべての患者情報：'.print_r(GetTotalPatients(),true));
        if(getTotalPatientsNum(GetTotalPatients())<$_SESSION['bed']->getAmount() ){
            if(mt_rand(0,1)){
                $sample =  new Mild(makeName(3),mt_rand(30,80),'img/sick_hatunetsu_man.png');
                $_SESSION['milds'][] = $sample;
                History::set($sample->getName().'(軽症患者)が入院しました');
            }elseif(mt_rand(0,1)){
                $sample =  new Moderate(makeName(3),mt_rand(30,80),'img/sleep_seki_man.png');
                $_SESSION['moderates'][] = $sample;
                History::set($sample->getName().'(中等症患者)が入院しました');
            }else{
                $sample =  new Critically(makeName(3),mt_rand(30,80),'img/medical_ecmo_man.png');
                $_SESSION['criticallys'][] = $sample;
                History::set($sample->getName().'(重症患者)が入院しました');
            }
        }
    }
}

// 医者を生成する関数
function createDoctor(){
    global $doctors;
    $_SESSION['doctors'][] = new Doctor('A医師',1);
    $_SESSION['doctors'][] = new Doctor('B医師',2);
}

// 患者の生死を更新する関数
function updatePatients($objArray){
    debug('updatePatients関数です');
    foreach($objArray as $key=>$val){
        debug('$valの中身：'.print_r($val,true));
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

// 入院状態の患者の数を数える
function getTotalPatientsNum($targetObjArray){
    $counter = 0;
    foreach($targetObjArray as $key => $val){
        if($val->getConditionFlg() == 0){
            $counter++;
        }
    }
    return $counter;
}

// PHPメソッドのロジック
// 1.医者を生成する
// 2.患者を生成する
// 3.医者のアクションを行う
// 4.患者の病気を進行させる
// 5.患者の生死を判断する
// 5-2.患者の状態を集計する
// 初めの画面で、1と2のみを行う
// 行動選択後の遷移では、2~5を行う



if(!isset($_SESSION['page_flg'])) $_SESSION['page_flg'] = 'start_page';
$doctor_action_flg=false;
$buy_item_flg = false;
$return_main_flg = false;


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
        if(!empty($_POST['use_drug'])){
            debug('治療薬を使います');
            History::set('治療薬を使いました');
            $_SESSION['drug']->useDrug($_SESSION['criticallys']);
            $_SESSION['drug']->setAmount($_SESSION['drug']->getAmount()-1);
        }
    }elseif(isset($_POST['buy'])){
        debug('設備画面が押されました');
        $_SESSION['page_flg'] = 'equipment_page';

    }elseif(isset($_POST['buy_item'])){
        debug('購入決定が押されました');
        $_SESSION['page_flg'] = 'main_page';
        $buy_item_flg = true;
        
    }elseif(isset($_POST['return_main'])){
        debug('戻るが押されました');
        $_SESSION['page_flg'] = 'main_page';
        $return_main_flg = true;
    }else{
        debug('不明です');
    }

}

countDay();



// 医者がどの患者を治療するか決める関数
function decideTreatment($str){
    switch($str){
        case 1:
            return $_SESSION['milds'];
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
    if(isset($_POST['doctor_action1'])){
        $_SESSION['doctors'][0]->treatment(decideTreatment($_POST['doctor_action1']),mt_rand(30,40));
    }
    if(isset($_POST['doctor_action2'])){
        $_SESSION['doctors'][1]->treatment(decideTreatment($_POST['doctor_action2']),mt_rand(30,40));
    }

    allgetSick($_SESSION['milds'],mt_rand(10,20));
    allgetSick($_SESSION['moderates'],mt_rand(20,30));
    allgetSick($_SESSION['criticallys'],mt_rand(30,40));
    updatePatients($_SESSION['milds']);
    updatePatients($_SESSION['moderates']);
    updatePatients($_SESSION['criticallys']);

    $totalPatients = getTotalPatients();
    if(Patient::countLevel($totalPatients)['dead']>=10){
        debug('ゲームオーバーです');
        $_SESSION['page_flg'] = 'game_over';
    }elseif(Patient::countLevel($totalPatients)['recover']>=10){
        debug('ゲームクリアです');
        $_SESSION['page_flg'] = 'game_clear';
    }
    // debug('治療・病気進行後の$_SESSIONの中身2：'.print_r($_SESSION,true));

    createPatients(mt_rand(1,2));
    $get_money = mt_rand(1,5)*100;
    $_SESSION['money'] += $get_money;
    History::set('国から'.$get_money.'万円の補助金をもらった');

    $doctor_action_flg=false;

}



// アイテム購入処理
if($buy_item_flg){
    debug('アイテムの購入処理をします');
    if(!empty($_POST['buy_drug'])){
        $buy_amount = $_POST['buy_drug'];
        $_SESSION['drug']->setAmount($_SESSION['drug']->getAmount()+$buy_amount);
        $_SESSION['money'] -=$buy_amount*$_SESSION['drug']->getPrice();
    };
    if(!empty($_POST['buy_ecmo'])){
        $buy_amount = $_POST['buy_ecmo'];
        $_SESSION['ecmo']->setAmount($_SESSION['ecmo']->getAmount()+$buy_amount);
        $_SESSION['money'] -=$buy_amount*$_SESSION['ecmo']->getPrice();
    };
    if(!empty($_POST['buy_bed'])){
        $buy_amount = $_POST['buy_bed'];
        $_SESSION['bed']->setAmount($_SESSION['bed']->getAmount()+$buy_amount);
        $_SESSION['money'] -=$buy_amount*$_SESSION['bed']->getPrice();
    }
    $totalPatients = getTotalPatients();
    
    debug('アイテム変更後の$_SESSIONの中身1：'.print_r($_SESSION,true));

    $buy_item_flg = false;
}

// アイテム購入画面からメイン画面に戻ってきた場合
if($return_main_flg){
    debug('何も購入せずにメイン画面に戻ります');
    // ゲームは進行させない
    $totalPatients = getTotalPatients();

}



// jQueryでの処理用に、アイテムの値段と所持金額を渡すためのJSONファイルを作成
$php_data = array($_SESSION['drug']->getPrice(),$_SESSION['ecmo']->getPrice(),$_SESSION['bed']->getPrice(),$_SESSION['money']);
$json_data = json_encode($php_data);






debug('の$_SESSIONの中身2：'.print_r($_SESSION,true));

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
debug('の$_SESSIONの中身3：'.print_r($_SESSION,true));

    switch($_SESSION['page_flg']){
        case ('start_page'):
            require('start_page.php');
            break;
        case ('main_page'):
            require('main_page.php');
            break;
        case ('equipment_page'):
            require('buy_equipment.php');
            break;
        case ('game_over'):
            require('game_over.php');
            break;
        case ('game_clear'):
            require('game_clear.php');
            break;

}?>
    
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script>
        // 医者のアクションが選ばれていないと、「決定する」ボタンを押せないようにする
        // selectのDOMを取得する
        // 2つのselectが選択されているか判定
        // 選択されていないとき、ボタンにカーソルを当てると警告が出る
        $(function(){
            console.log('こんにちは');
            // selectのDOMを取得する
            var $select1 = $('#doctor_action1'),
                $select2 = $('#doctor_action2');
            var $submit_button = $('#submit_button');
            $submit_button.prop('disabled',true);
            $select1.change(function(){
                var val1 = $(this).val(),
                    val2 = $select2.val();
                console.log(val1);
                console.log(val2);
                if(val1 ==0 || val2 ==0){
                    console.log('決定ボタンは押せません');
                    $submit_button.prop('disabled',true);
                }else{
                    $submit_button.prop('disabled',false);
                }
            })

            $select2.change(function(){
                var val1 = $select1.val(),
                    val2 = $(this).val();
                console.log(val1);
                console.log(val2);
                if(val1 ==0 || val2 ==0){
                    console.log('決定ボタンは押せません');
                    $submit_button.prop('disabled',true);
                }else{
                    $submit_button.prop('disabled',false);
                }
            })
            
            // 設備購入画面での金額チェック
            // 各々の設備のselectの値が変わるのを判定して発火
            // 各々のselectの値を取得
            // 合計金額を取得する
            // 所持金<合計金額なら、ボタンを押せないようにして、赤文字で喚起する

            const js_array = JSON.parse('<?php echo $json_data; ?>')
            console.log(js_array);

            // selectのDOMを取得
            var $drug_select = $('#drug_select'),
                $ecmo_select = $('#ecmo_select'),
                $bed_select = $('#bed_select');
            var $total_amount = $('#total_amount');
            var $buy_item_button = $('#buy_item_button');
            var $total_amount_err = $('#total_amount_err');
            $total_amount.text('0');
            $buy_item_button.prop('disabled',true);

            $drug_select.change(function(){
                var drug_amount = $drug_select.val(),
                    ecmo_amount = $ecmo_select.val(),
                    bed_amount = $bed_select.val();
                $total_price = drug_amount*js_array[0]+ecmo_amount*js_array[1]+bed_amount*js_array[2];
                console.log($total_price);
                $total_amount.text($total_price);
                if($total_price>js_array[3] || $total_price==0){
                    console.log('所持金額オーバーです');
                    $total_amount_err.text('金額オーバーです');
                    $buy_item_button.prop('disabled',true);
                }else{
                    $buy_item_button.prop('disabled',false);
                    $total_amount_err.text('');
                }
            })

            $ecmo_select.change(function(){
                var drug_amount = $drug_select.val(),
                    ecmo_amount = $ecmo_select.val(),
                    bed_amount = $bed_select.val();
                $total_price = drug_amount*js_array[0]+ecmo_amount*js_array[1]+bed_amount*js_array[2];
                console.log($total_price);
                $total_amount.text($total_price);
                if($total_price>js_array[3] || $total_price==0){
                    console.log('所持金額オーバーです');
                    $total_amount_err.text('金額オーバーです');
                    $buy_item_button.prop('disabled',true);
                }else{
                    $buy_item_button.prop('disabled',false);
                    $total_amount_err.text('');
                }
            })

            $bed_select.change(function(){
                var drug_amount = $drug_select.val(),
                    ecmo_amount = $ecmo_select.val(),
                    bed_amount = $bed_select.val();
                $total_price = drug_amount*js_array[0]+ecmo_amount*js_array[1]+bed_amount*js_array[2];
                console.log($total_price);
                $total_amount.text($total_price);
                if($total_price>js_array[3] || $total_price==0){
                    console.log('所持金額オーバーです');
                    $total_amount_err.text('金額オーバーです');
                    $buy_item_button.prop('disabled',true);
                }else{
                    $buy_item_button.prop('disabled',false);
                    $total_amount_err.text('');
                }
            })
        })


    </script>

</html>
