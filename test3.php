<?php
$param = array(
	"名前" => "山田太郎",
	"住所" => "不定",
	"電話番号"  => "090xxxxxxxx"
);
$param_json = json_encode( $param );  //JSONエンコード
?>
 
<script>
	var param = JSON.parse('<?php echo $param_json; ?>');  //JSONデコード
	console.log( param );
</script>