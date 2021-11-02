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
        $counter = array('sick'=>0,'death'=>0,'recover'=>0);
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

    // function treatment($targetObj,$point){
    //     $targetObj->setHp($targetObj->getHp() + $point); 
    // }

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


// アイテムのインスタンス作成
$drug = new Item(itemName::DRUG,itemInfo::DRUG,'img/medical_vaccine_covid19.png',0);
$ecmo = new Item(itemName::ECMO,itemInfo::ECMO,'img/medical_ecmo_machine.png',0);
$bed = new Item(itemName::BED,itemInfo::BED,'img/kaigo_bed.png',5);
// 患者のインスタンス作成
$mild1 = new Mild(mt_rand(30,80),'img/sick_hatunetsu_man.png');
$critically1 = new Critically(mt_rand(30,80),'img');

$patients[] = $mild1;
$patients[] = $critically1;

// 医者のインスタンス作成

// 患者を生成する関数

// 簡単なテスト
// 0.医者を生成する
// 1.患者を生成する
// 2.医者のアクションを行う
// 3.患者のHPを回復する
// 4.患者のHPを減らす（病気の進行）
// 5.患者の生死を判断する
// 6.患者の症状レベルを更新する(後回し)


// $doctor1 = new Doctor(1);
// $doctor2 = new Doctor(2);
// $doctors = array();
// $doctors[] = $doctor1;
// $doctors[] = $doctor2;

// debug('$patientsの中身1：'.print_r($patients,true));


// $doctors[0]->treatment($patients[0],mt_rand(20,40));
// $doctors[1]->treatment($patients[0],mt_rand(20,40));
// $patients[0]->getSick(mt_rand(10,20));
// $patients[1]->getSick(mt_rand(30,50));
// $patients[0]->judgeCondition();
// $patients[1]->judgeCondition();

// debug('$patientsの中身2：'.print_r($patients,true));

// $sample = Mild::countLevel($patients);
// debug('$sampleの中身2：'.print_r($sample,true));


// $doctors[0]->treatment($patients[0],mt_rand(20,40));
// $doctors[1]->treatment($patients[0],mt_rand(20,40));
// $patients[0]->getSick(mt_rand(10,20));
// $patients[1]->getSick(mt_rand(30,50));
// $patients[0]->judgeCondition();
// $patients[1]->judgeCondition();


// debug('$patientsの中身3：'.print_r($patients,true));










// 患者をランダムに生成する関数
function makePatient($num){
    global $milds;
    global $moderates;
    global $criticallys;
    for($i=1; $i<=$num; $i++){
        if(mt_rand(0,1)){
            $milds[] = new Mild(mt_rand(30,80),'img/sick_hatunetsu_man.png');
        }elseif(mt_rand(0,2)){
            $moderates[] = new Moderate(mt_rand(30,80),'img/sleep_seki_man.png');
        }else{
            $criticallys[] = new Critically(mt_rand(30,80),'img/medical_ecmo_man.png');
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
for($i=1;$i<3;$i++){

}

makePatient(3);

debug('$mildsの中身：'.print_r($milds,true));
debug('$moderatesの中身：'.print_r($moderates,true));
debug('$criticallysの中身：'.print_r($criticallys,true));


$totalPatients = getTotalPatients();
debug('$totalPatientsの中身1：'.print_r($totalPatients,true));
$doctors = array();
$doctors[] = new Doctor(10);
// 3.患者のHPを回復する（医者の治療）
$doctors[0]->treatment($milds,mt_rand(20,40));
$totalPatients = getTotalPatients();
debug('$totalPatientsの中身2：'.print_r($totalPatients,true));
// 4.患者のHPを減らす（病気の進行）
// 軽症・中等症・重症で減少するHPが異なる
allGetSick($milds,mt_rand(10,20));
allGetSick($moderates,mt_rand(20,30));
allGetSick($criticallys,mt_rand(30,40));
$totalPatients = getTotalPatients();
debug('$totalPatientsの中身3：'.print_r($totalPatients,true));
// 5.患者の生死を判断する
updatePatients($milds);
updatePatients($moderates);
updatePatients($criticallys);
debug('$totalPatientsの中身4：'.print_r($totalPatients,true));


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
                        <li>治療した患者：7人</li>
                        <li>死者：1人</li>
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
                                <span>体力</span>
                                <span>医療能力</span>
                                <select name="" id="">
                                    <option value="0">選択してください</option>
                                    <option value="1">軽症者を治療する</option>
                                    <option value="2">中等者を治療する</option>
                                    <option value="3">重症者を治療する</option>
                                    <option value="4">休む</option>
                                    <option value="5">研修する</option>
                                </select>
                            </div>
                            
                            <div class="doctor-status">
                                <span>医者2</span>
                                <img src="img/doctor.png" alt="">
                                <span>体力</span>
                                <span>医療能力</span>
                                <select name="" id="">
                                    <option value="">選択してください</option>
                                    <option value="">治療する</option>
                                    <option value="">休む</option>
                                    <option value="">研修する</option>
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
                        </div>
                    </form>
                    </div>
                </div>

            </div>
            
        </div>
        
        <div class="msg-area">
            
        </div>
    </div>

</body>
</html>