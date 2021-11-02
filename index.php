<?php

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
// 重症度クラス
class Severity{
    const light = 1;
    const middle = 2;
    const heavy = 3;
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
}


// 軽症者クラス
class Mild extends Patient{
    public function __construct($hp,$img){
        parent::__construct($hp,$img);
    }
    function judgeCondition(){
        if($this->hp <= 0){
            $condition_flg = 1;
        }elseif($this->hp >= 100){
            $condition_flg = 2;
        }
    }
    public function getSick($num){
        $this->setHp($this->getHp()-$num);
    }
    public function getDamage($num){
        $this->setHp($this->getHp()+$num);
    }

    public static function countMilds($array){
        $counter = array('sick'=>0,'death'=>0,'recover'=>0);
        foreach($array as $key=>$val){
            if($val->getConditionFlg() == 0){
                $counter['sick']++;
            }elseif($val->getConditionFlg() == 1){
                $counter['death']++;
            }else{
                $counter['recover']++;
            }
        }
        return $counter;
    }
}

// 中等症クラス
class Moderate extends Mild{
    public function __construct($hp,$img){
        parent::__construct($hp,$img);
    }
}

// 重傷者クラス
class Critically extends Mild{
    public function __construct($hp,$img){
        parent::__construct($hp,$img);
    }
    
}

// HP関係なく、一定の確率で症状が進行する。HPは引き継ぐ。
// 症状を進行させる関数
function getInprove($obj){
    // 軽症者は中等症へ
    if($obj instanceof Mild && !mt_rand(0,5)){
        debug('中等症にします');
        return new Moderate($obj->getHp(),'');
    }elseif($obj instanceof Moderate && !mt_rand(0,5)){
        debug('重症にします');
        return new Critically($obj->getHp(),'');
    }else{
        debug('何も起こりません');
        return $obj;
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

    function treatment($targetObj,$point){
        $targetObj->setNeedTreatment($targetObj->getNeedTreatment()-$point); 
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
$mild2 = new Mild(mt_rand(30,80),'img/sick_hatunetsu_man.png');

$patients[] = $mild1;
$patients[] = $mild2;
debug('$patientsの中身：'.print_r($patients,true));

$sample = Mild::countMilds($patients);
debug('$sampleの中身：'.print_r($sample,true));


// 患者を生成する関数
function makePatient($severity){
    global $patients;
    if($severity == 1){
        $patients[] = new Patient(mt_rand(10,20),1,mt_rand(5,10),'img/sick_hatunetsu_man.png');
    }elseif($severity == 2){
        $patients[] = new Patient(mt_rand(10,20),2,mt_rand(10,15),'img/sleep_seki_man.png');
    }else{
        $patients[] = new Patient(mt_rand(10,20),3,mt_rand(15,20),'img/medical_ecmo_man.png');
    }
}

$counterPatients = array('light'=>'','middle'=>'','heavy'=>'');

// 患者の生死を更新する関数
function updatePatiens($patients){
    foreach($patients As $key=>$val){
        $val->judgeDeath();
        $val->judgeRecovery();
    }
}





// 患者の状態を集計する関数
function countPatient($patients){
    $counterPatients = array('light'=>0,'middle'=>0,'heavy'=>0);
    foreach ($patients as $key=>$val){
        switch($val->getSeverity()){
            case Severity::light:
                $counterPatients['light'] ++;
                break;
            case Severity::middle:
                $counterPatients['middle'] ++;
                break;
            case Severity::heavy:
                $counterPatients['heavy'] ++;
                break;
        }        
    }
    return $counterPatients;
}


// 患者を生成する関数





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
                            <span>5人</span>
                        </div>
                        <div class="patients-status light-patients">
                            <img src="img/sleep_seki_man.png" alt="">
                            <span>×</span>
                            <span>2人</span>
                        </div>
                        <div class="patients-status light-patients">
                            <img src="img/medical_ecmo_man.png" alt="">
                            <span>×</span>
                            <span>3人</span>
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