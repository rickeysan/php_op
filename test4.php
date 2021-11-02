<?php
require('function.php');
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
    const BED = 'すべての人間の治療効率が1.1倍になります(複数効果あり）';
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
            }
        }
    }
}

$drug = new Drug(itemName::DRUG,itemInfo::DRUG,'img/medical_vaccine_covid19.png',0,100);


$_SESSION['drug'] = $drug;
debug('$_SESSIONの中身2：'.print_r($_SESSION,true));



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    テストページです
</body>
</html>