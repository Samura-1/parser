<?php
require_once __DIR__ . '/phpQuery-onefile.php';
require_once  __DIR__ . './vendor/autoload.php';

use Krugozor\Database\Mysql;

$connect = new PDO('mysql:host=localhost;dbname=testProduct', 'root', '');
$db = Mysql::create("localhost", "root", "")
      // Выбор базы данных
      ->setDatabaseName("testProduct")
      // Выбор кодировки
      ->setCharset("utf8");

function parser($url)
{
    $headers = [
        'cache-control: max-age=0',
        'upgrade-insecure-requests: 1',
        'user-agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36',
        'sec-fetch-user: ?1',
        'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
        'x-compress: null',
        'sec-fetch-site: none',
        'sec-fetch-mode: navigate',
        'accept-encoding: deflate, br',
        'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_REFERER, "http://google.com");
    curl_setopt($ch, CURLOPT_HEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

//достали категории
$dataResultCat = parser('https://www.lotusite.ru/catalogue/');
$pq = phpQuery::newDocument($dataResultCat);
$dataLinksCat = $pq->find('.block-products .contaner-category a');
foreach ($dataLinksCat as $item) {
    $dataCat[] = pq($item)->attr('href');
}

//записываем категории в бд
foreach ($dataCat as $dataCatItem) {
    $newCat = trim(strrchr($dataCatItem, '/'), '/');
    $res = $connect->prepare("SELECT `name` FROM `category` WHERE `name` = :name");
    $res->bindParam(':name',$newCat);
    $res->execute();
    $rowcount = $res->rowCount();

    if ($rowcount <= 0) {
        $db->query('INSERT INTO `category` SET ?As', ['name' => $newCat]);
    }
}

function getCountCat($dataCat) {
    $url = 'https://www.lotusite.ru' . $dataCat;
    $dataResult = parser($url);
    $pq = phpQuery::newDocument($dataResult);
    $dataLinks = $pq->find('.pages li a');;
    foreach ($dataLinks as $item) {
        $count[] = pq($item)->attr('href');
    }
    if (!empty($count)) {
        return count($count);
    } else {
        return 1;
    }
}

$countCategory = count($dataCat) - 1;
for ($i = 0; $i <= $countCategory; $i++) {
    $count = getCountCat($dataCat[$i]);
    for ($j = 1; $j <= $count; $j++) {
        $url = 'https://www.lotusite.ru'.$dataCat[$i].'/'.$j;
        $dataResult = parser($url);
        $pq = phpQuery::newDocument($dataResult);
        $dataLinks = $pq->find('.product .title a');
        foreach ($dataLinks as $item) {
            $data[] = pq($item)->attr('href');
        }
    }
}

foreach ($data as $cats) {
   $linkPage = 'https://www.lotusite.ru'.$cats;
   $resultProduct = parser($linkPage);
   $pq = phpQuery::newDocument($resultProduct);
    if (!empty($pq->find('#product-contaner h1')->html())) {
        $dataList[] = [
            'name' => $pq->find('#product-contaner h1')->html(),
            'price' => $pq->find('#block-shop .price')->text(),
            'url' => $linkPage,
            'img' => 'https://www.lotusite.ru'.$pq->find('.big-image a')->attr('href'),
            'catagery' => 1,
        ];
    }
}


// $jsonDataFile = json_encode($dataList);
// file_put_contents('./product.txt', $jsonDataFile);

// $file = file_get_contents('product.txt');
// $dataList = json_decode($file, true);

foreach ($dataList as $itemBd) {
    $res = $connect->prepare("SELECT `url` FROM `itemproduct` WHERE `url` = :url");
    $res->bindParam(':url',$itemBd['url']);
    $res->execute();
    $rowcount = $res->rowCount();
    if ($rowcount <= 0) {
        $db->query('INSERT INTO `itemProduct` SET ?As', $itemBd);
    }
}

// echo "<hr>";
// echo "<hr>";
// // echo 'Это датафулл ->';
// // var_dump($dataFull);
echo "<hr>";
echo "<pre>";
echo 'Это даталист ->';
var_dump($dataList);
// // echo "<hr>";
// echo "<hr>";
// echo 'Это дата ->';
// var_dump($data);
