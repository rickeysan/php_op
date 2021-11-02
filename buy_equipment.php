<?php

debug(' 設備購入ページ---------------------------------');

?>



<body>
    <h1>病院経営ゲーム</h1>
    <h3>ゲームクリア条件：患者を20人治療する    　ゲームオーバー条件：死者が3人出る
    </h3>
    <div class="contents-wrap">
        <div class="main-area">
            <div class="head">
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
                <div class="item-buy-select">
                    <h4>購入画面</h4>
                    <form action="" method="post">
                    <div class="buy-item-wrap">
                            <img src="img/medical_vaccine_covid19.png" alt="">
                            <span>×<?php echo $drug->getAmount();?></span>
                            <div class="item-info">        
                                <p>効果：<?php echo $drug->getInfo();?>
                                <br>値段：<?php echo $drug->getPrice();?>万円
                                </p>
                                <br>
                           <span> 購入数：</span><select name="buy_drug" id="drug_select">
                                <option value="0">0個</option>
                                <option value="1">1個</option>
                                <option value="2">2個</option>
                                <option value="3">3個</option>
                                <option value="4">4個</option>
                            </select>
                            </div>
                        </div>

                        <div class="buy-item-wrap">
                            <img src="img/medical_ecmo_machine.png" alt="">
                            <span>×<?php echo $ecmo->getAmount();?></span>
                            <div class="item-info">        
                                <p>効果：<?php echo $ecmo->getInfo();?>
                                <br>値段：<?php echo $ecmo->getPrice();?>万円
                                </p>
                                <br>
                           <span> 購入数：</span><select name="buy_ecmo" id="ecmo_select">
                                <option value="0">0個</option>
                                <option value="1">1個</option>
                                <option value="2">2個</option>
                                <option value="3">3個</option>
                                <option value="4">4個</option>
                            </select>
                            </div>
                        </div>

                        <div class="buy-item-wrap">
                            <img src="img/kaigo_bed.png" alt="">
                            <span>×<?php echo $bed->getAmount();?></span>
                            <div class="item-info">        
                                <p>効果：<?php echo $bed->getInfo();?>
                                <br>値段：<?php echo $bed->getPrice();?>万円
                                </p>
                                <br>
                           <span> 購入数：</span><select name="buy_bed" id="bed_select">
                                <option value="0">0個</option>
                                <option value="1">1個</option>
                                <option value="2">2個</option>
                                <option value="3">3個</option>
                                <option value="4">4個</option>
                            </select>
                            </div>
                        </div>

                        <div class="buy-item-footer">
                            <p>所持金 ￥<?php echo $_SESSION['money'];?>万円</p>
                            <div>
                                <p style="display:block;">合計金額 ￥<span id="total_amount"></span>万円</p>
                                <p id="total_amount_err" style="display:block;"></p>
                            </div>
                            <input type="submit" id="buy_item_button" name="buy_item" value="購入する">
                            <input type="submit" name="return_main" value="戻る">
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


