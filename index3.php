<?php

use Patient as GlobalPatient;

require('function.php');
debug('メインページ-----------------------');

// 患者格納用クラス
$patients = array();

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

// HP関係なく、一定の確率で症状が進行する。HPは引き継ぐ。
// 症状を進行させる関数
// function getInprove($obj){
//     // 軽症者は中等症へ
//     if($obj instanceof Mild && !mt_rand(0,5)){
//         debug('中等症にします');
//         return new Moderate($obj->getHp(),'');
//     }elseif($obj instanceof Moderate && !mt_rand(0,5)){
//         debug('重症にします');
//         return new Critically($obj->getHp(),'');
//     }else{
//         debug('何も起こりません');
//         return $obj;
//     }
// }




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
        $this->setSkill($num);    
    }
    function rest($num){
        $this->setHp($this->getHp()+$num);
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
        if(empty($_SESSION['history'])) $_SESSION['hisotry'] = '';
        // 文字列をセッションhistoryへ格納
        $_SESSION['history'] .=$str.'<br>';
    }
    public static function clear(){
        unset($_SESSION['history']);
    }

}

// ゲーム初期化関数
function init(){
    unset($_SESSION);

}


// アイテムのインスタンス作成
$drug = new Item(itemName::DRUG,itemInfo::DRUG,'img/medical_vaccine_covid19.png',0);
$ecmo = new Item(itemName::ECMO,itemInfo::ECMO,'img/medical_ecmo_machine.png',0);
$bed = new Item(itemName::BED,itemInfo::BED,'img/kaigo_bed.png',5);



// 患者をランダムに生成する関数
function makePatient($num){
    global $milds;
    global $moderates;
    global $criticallys;
    for($i=1; $i<=$num; $i++){
        if(mt_rand(0,1)){
            $milds[] = new Mild(mt_rand(30,80),'img/sick_hatunetsu_man.png');
            History::set('軽症患者が入院しました');
        }elseif(mt_rand(0,2)){
            $moderates[] = new Moderate(mt_rand(30,80),'img/sleep_seki_man.png');
            History::set('中等症患者が入院しました');
        }else{
            $criticallys[] = new Critically(mt_rand(30,80),'img/medical_ecmo_man.png');
            History::set('重症患者が入院しました');
        }
    }
}

$counterPatients = array('light'=>'','middle'=>'','heavy'=>'');

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
    global $milds;
    global $moderates;
    global $criticallys;
    return array_merge($milds,$moderates,$criticallys);
}

// 患者の管理は、状態を問わず、症状度に分けて配列に入れて行う

// 模擬テスト
debug('模擬テスト開始--------------------------------');
for($i=1;$i<4;$i++){
    debug($i.'回目のループです');
    
    makePatient(2);
    
    $totalPatients = getTotalPatients();
    debug('$totalPatientsの中身1：'.print_r($totalPatients,true));
    $doctors = array();
    $doctors[] = new Doctor(10);
    // 3.患者のHPを回復する（医者の治療）
    $doctors[0]->treatment($criticallys,mt_rand(10,20));
    $totalPatients = getTotalPatients();
    debug('治療終了：$totalPatientsの中身2：'.print_r($totalPatients,true));
    // 4.患者のHPを減らす（病気の進行）
    // 軽症・中等症・重症で減少するHPが異なる
    allGetSick($milds,mt_rand(10,20));
    allGetSick($moderates,mt_rand(20,30));
    allGetSick($criticallys,mt_rand(50,60));
    $totalPatients = getTotalPatients();
    // debug('病気の進行後：$totalPatientsの中身3：'.print_r($totalPatients,true));
    // 5.患者の生死を判断する
    updatePatients($milds);
    updatePatients($moderates);
    updatePatients($criticallys);
    $totalPatients = getTotalPatients();
    debug('生死の判定後：$totalPatientsの中身4：'.print_r($totalPatients,true));

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
<body>
    <h1>病院経営ゲーム</h1>
    <h3>ゲームクリア条件：患者を20人治療する    　ゲームオーバー条件：死者が3人出る
    </h3>
    <div class="contents-wrap">
        <div class="main-area">
            <div class="head">
                <div class="people-status">
                    <ul>
                        <li>感染状況：中</li>
                        <li>治療した患者：<?php echo Patient::countLevel($totalPatients)['recover'];?>人</li>
                        <li>死者：<?php echo Patient::countLevel($totalPatients)['dead'];?>人</li>
                        <li>病院の資金：300万円 </li>
                    </ul>
                </div>
                <div class="equipment-status">
                    <span>所有設備</span>
                    <img src="img/medical_vaccine_covid19.png" alt="">
                    <span>×<?php echo $drug->getAmount();?></span>
                    <img src="img/medical_ecmo_machine.png" alt="">
                    <span>×<?php echo $ecmo->getAmount();?></span>
                    <img src="img/kaigo_bed.png" alt="">
                    <span>×<?php echo $bed->getAmount();?></span>
                </div>
            </div>
            <div class="game-main">
                <div class="charactor-area">
                    <div class="patients-area">
                        <span>患者の数と状態</span>
                        <div class="patients-status light-patients">
                            <img src="img/sick_hatsunetsu_man.png" alt="">
                            <span>×</span>
                            <span><?php echo Patient::countLevel($milds)['sick'];?>人</span>
                        </div>
                        <div class="patients-status light-patients">
                            <img src="img/sleep_seki_man.png" alt="">
                            <span>×</span>
                            <span><?php echo Patient::countLevel($moderates)['sick'];?>人</span>
                        </div>
                        <div class="patients-status light-patients">
                            <img src="img/medical_ecmo_man.png" alt="">
                            <span>×</span>
                            <span><?php echo Patient::countLevel($criticallys)['sick'];?>人</span>
                        </div>
                    </div>


                    <div class="doctor-select-wrap">
                    <form action="">
                        <div class="doctor-wrap">

                            <div class="doctor-status">
                                
                                <span>医者１</span>
                                <img src="img/doctor.png" alt="">
                                <span>医療能力：<?php echo $doctors[0]->getSkill();?></span>
                                <select name="doctor_action" id="">
                                    <option value="0">選択してください</option>
                                    <option value="1">軽症者を治療する</option>
                                    <option value="2">中等者を治療する</option>
                                    <option value="3">重症者を治療する</option>
                                    <option value="4">研修する</option>
                                </select>
                            </div>
                            
                            <div class="doctor-status">
                                <span>医者2</span>
                                <img src="img/doctor.png" alt="">
                                <span>医療能力：<?php echo $doctors[0]->getSkill();?></span>
                                <select name="doctor_action" id="">
                                    <option value="0">選択してください</option>
                                    <option value="1">軽症者を治療する</option>
                                    <option value="2">中等者を治療する</option>
                                    <option value="3">重症者を治療する</option>
                                    <option value="4">研修する</option>
                                </select>
                            </div>

                            
                        </div>
                        <div class="medicine-status">
                            <span>特攻薬</span>
                            <select name="" id="">
                                <option value="">使わない</option>
                                <option value="">使う</option>
                            </select>
                        </div>
                        <div class="select-wrap">
                            <input type="submit" value="決定する">
                            <input type="submit" value="設備拡張">
                            <input type="submit" value="再スタート">
                        </div>
                    </form>
                    </div>
                </div>

            </div>
            
        </div>
        
        <div class="msg-area">
            <?php echo $_SESSION['history'];?>
        </div>
    </div>

</body>
</html>