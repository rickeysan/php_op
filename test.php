<?php

class Human{
    private $sample1;
    protected $sample2;
    public $sample3;
    public function __construct(){
        $sample1 = 1;
        $sample2 = 2;
        $sample3 = 3;
    }

}

class Doctor extends Human{
    public function __construct(){
        parent::__construct();
    }
    public function setSample($num){
        $this->sample1 = 2;
        $this->sample2 = 3;
        $this->sample3 = 4;
    }
    public function getSample1(){
        return $this->sample1;
    }
    public function getSample2(){
        return $this->sample2;
    }
    public function getSample3(){
        return $this->sample3;
    }
}

echo 'テスト';

$doctor = new Doctor();

echo $doctor->getSample2();
echo $doctor->getSample3();




?>