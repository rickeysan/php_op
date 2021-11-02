<?php

debug('メインページ---------------------------------');
debug('メインページの$_SESSIONの中身5：'.print_r($_SESSION,true));

?>



<body>
    <h1>病院経営ゲーム</h1>
    <h3>ゲームクリア条件：患者を20人治療する    　ゲームオーバー条件：死者が3人出る
    </h3>
    <div class="contents-wrap">
        <div class="main-area">
            <div class="head">
                <div class="people-status">
                    <ul>
                        <li>経過日数：<?php echo $_SESSION['day'];?>日</li>
                        <li>感染状況：中</li>
                        <li>治療した患者：<?php echo Patient::countLevel($totalPatients)['recover'];?>人</li>
                        <li>死者：<?php echo Patient::countLevel($totalPatients)['dead'];?>人</li>
                        <li>病院の資金：<?php echo $_SESSION['money'];?>万円 </li>
                    </ul>
                </div>
                <div class="equipment-status">
                    <span>所有設備</span>
                    <img src="img/medical_vaccine_covid19.png" alt="">
                    <span>×<?php echo $_SESSION['drug']->getAmount();?></span>
                    <img src="img/medical_ecmo_machine.png" alt="">
                    <span>×<?php echo $_SESSION['ecmo']->getAmount();?></span>
                    <img src="img/kaigo_bed.png" alt="">
                    <span>×<?php echo $_SESSION['bed']->getAmount();?></span>
                </div>
            </div>
            <div class="game-main">
                <div class="charactor-area">
                    <div class="patients-area">
                        <span>患者の数と状態</span>
                        <div class="patients-status light-patients">
                            <img src="img/sick_hatsunetsu_man.png" alt="">
                            <span>×</span>
                            <span><?php echo Patient::countLevel($_SESSION['milds'])['sick'];?>人</span>
                        </div>
                        <div class="patients-status light-patients">
                            <img src="img/sleep_seki_man.png" alt="">
                            <span>×</span>
                            <span><?php echo Patient::countLevel($_SESSION['moderates'])['sick'];?>人</span>
                        </div>
                        <div class="patients-status light-patients">
                            <img src="img/medical_ecmo_man.png" alt="">
                            <span>×</span>
                            <span><?php echo Patient::countLevel($_SESSION['criticallys'])['sick'];?>人</span>
                        </div>
                    </div>


                    <div class="doctor-select-wrap">
                    <form action="" method="post">
                        <div class="doctor-wrap">

                            <div class="doctor-status">
                                
                                <span>医者１</span>
                                <img src="img/doctor.png" alt="">
                                <span>医療能力：<?php echo $_SESSION['doctors'][0]->getSkill();?></span>
                                <select name="doctor_action1" id="doctor_action1">
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
                                <span>医療能力：<?php echo $_SESSION['doctors'][1]->getSkill();?></span>
                                <select name="doctor_action2" id="doctor_action2">
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
                            <select name="use_drug" id="">
                                <option value="0">使わない</option>
                                <option value="1">使う</option>
                            </select>
                        </div>
                        <div class="select-wrap">
                            <input type="submit" id="submit_button" name="doctor_action" value="決定する">
                            <input type="submit" name="buy" value="設備拡張">
                            <input type="submit" name="start" value="再スタート">
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


