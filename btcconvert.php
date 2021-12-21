<?php 

$incomeData = json_decode(file_get_contents('php://input'),TRUE);
require_once('token.php');
$errPath = __dir__."\\errorlog.txt";
ini_set('log_errors',1);
ini_set('error_log', $errPath);

$btcJson = file_get_contents("https://chain.so/api/v2/get_info/BTC");
$btcJson = json_decode($btcJson, TRUE);
$btcUsdprice = floatval($btcJson["data"]["price"]);
$usdPrice = floatval(file_get_contents("https://api.coingate.com/v2/rates/merchant/USD/RUB"));

$btcRubprice = $usdPrice*$btcUsdprice;

$incomeCommand = $incomeData['message']['text'];

if ($incomeCommand == '/start') {

   $method = 'sendMessage';
   $send_data = [
   'text' => 'Отправьте сумму чтобы сконвертировать, или нажмите кнопку, чтобы узнать курс',
   'reply_markup'  => [
      'resize_keyboard' => true,
      'keyboard' => [
            [
               ['text' => 'BTC - RUB'],
               
               ['text' => 'BTC - USD'],
            ],
         ]
      ]
               
       ];
}

elseif ($incomeCommand == 'BTC - RUB') {
   $method = 'sendMessage';
   $send_data = [
   'text' =>  "1BTC - ".round($btcRubprice,2)." руб."
       ];
}

elseif ($incomeCommand == 'BTC - USD') {
   $method = 'sendMessage';
   $send_data = [
   'text' => '1 BTC - '.$btcUsdprice.' $',
       ];
}
elseif ($incomeCommand>3) {
   $method = 'sendMessage';
   $send_data = [
   'text' => ($incomeCommand/$btcRubprice)." btc",    
       ];
}
elseif ($incomeCommand>0&$incomeCommand<3) {
   $method = 'sendMessage';
   $send_data = [
   'text' =>round(($incomeCommand*$btcRubprice),2)." руб"    
       ];
}

else {
   $method = 'sendMessage';
   $send_data = [
      'text' => 'такой команды нет',  
          ];
}

$send_data['chat_id']=$incomeData['message']['chat']['id'];


 sendBack($send_data,$method);

 function sendBack($send_data,$method){

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://api.telegram.org/'.$token.'/'.$method,
        CURLOPT_POSTFIELDS => json_encode($send_data),
        CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"))
    ]);
    $result = curl_exec($curl);
    curl_close($curl);
    return (json_decode($result, 1) ? json_decode($result, 1) : $result);

 }


function logger() {

$date = date('m/d/Y h:i:s a', time());

$log_string = "\r".$date."\r".$incomeCommand;

file_put_contents('convert_log.txt', print_r($log_string, 1), FILE_APPEND);

}

register_shutdown_function('logger');


?>
