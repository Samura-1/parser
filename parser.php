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

class ParserCurl {

	public $dirCacheFile = __DIR__ . "/cache_files/";

	/* ДЛЯ ОТПРАВКИ ЗАПРОСОВ */
	public function parserQuery($url) {

		if($this->cacheCheckStatus($url)) {
			return $this->cacheGetData($url);
		}

		$headers = array(
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
		);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookie.txt');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$resultQuery = curl_exec($ch);

		if($resultQuery == false) {
			if ($errno = curl_errno($ch)) {
				$message = curl_strerror($errno);
				$dataReturn = "cURL error ({$errno}):\n {$message}";
			}
			else {
				$dataReturn = $html;
			}
		}
		else {
			$dataReturn = $resultQuery;
		}

		curl_close($ch);

		$this->cacheSetData($url, $dataReturn); 

		return $dataReturn;
	}
	/* ========================= */
	

	/* ПРОВЕРЯЕМ АКТУАЛЬНОСТЬ КЭША */
	public function cacheCheckStatus($urlData) {

		$urlCacheFile = md5($urlData);
		/* путь до файла с кэшем */
		$urlFileCache = $this->dirCacheFile . $urlCacheFile . ".txt";

		/* проверяем существование файла кэша */
		if(file_exists($urlFileCache)) {

			$dataUpdateFile = filemtime($urlFileCache);

			/* если кэш просрочился */
			if((time() - $dataUpdateFile) > 86400) {
				return false;
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	}
	/* ======================= */



	/* ЗАПИСЫВАЕМ ДАННЫЕ В CACHE */
	public function cacheSetData($urlData, $dataSave) {
		$urlCacheFile = md5($urlData);
		/* путь до файла с кэшем */
		$urlFileCache = $this->dirCacheFile . $urlCacheFile . ".txt";

		$fh = fopen($urlFileCache, 'w');
		fwrite($fh, $dataSave);
		fclose($fh);

		return $dataSave;
	}
	/* ======================= */


	/* ПОЛУЧАЕМ ДАННЫЕ ИЗ CACHE */
	public function cacheGetData($urlData) {
		
		$urlCacheFile = md5($urlData);
		/* путь до файла с кэшем */
		$urlFileCache = $this->dirCacheFile . $urlCacheFile . ".txt";

		/* проверяем существование файла кэша и его актуальность */
		if($this->cacheCheckStatus($urlData)) {
			$dataCachFile = file_get_contents($urlFileCache);
			return $dataCachFile;
		}
		else {
			return false;
		}
		
	}
	/* ======================= */


}


$parser = new ParserCurl();

$dataResultCat = $parser->parserQuery("https://www.lotusite.ru/catalogue/");
$pq = phpQuery::newDocument($dataResultCat);
$dataLinksCat = $pq->find('.block-products .contaner-category a');
foreach ($dataLinksCat as $item) {
    $dataCat[] = pq($item)->attr('href');
}
//записываем категории в бд
// foreach ($dataCat as $dataCatItem) {
//     $newCat = trim(strrchr($dataCatItem, '/'), '/');
//     $res = $connect->prepare("SELECT `name` FROM `category` WHERE `name` = :name");
//     $res->bindParam(':name',$newCat);
//     $res->execute();
//     $rowcount = $res->rowCount();

//     if ($rowcount <= 0) {
//         $db->query('INSERT INTO `category` SET ?As', ['name' => $newCat]);
//     }
// }

function getCountCat($dataCat) {
	$parser = new ParserCurl();
    $url = 'https://www.lotusite.ru' . $dataCat;
    $dataResult = $parser->parserQuery($url);
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
for ($i = 10; $i <= 11; $i++) {
    $count = getCountCat($dataCat[$i]);
    for ($j = 1; $j <= $count; $j++) {
    	unset($data);
    	$data = [];
        $url = 'https://www.lotusite.ru'.$dataCat[$i].'/'.$j;
        $dataResult = $parser->parserQuery($url);
        $pq = phpQuery::newDocument($dataResult);
        $dataLinks = $pq->find('.product .title a');
        $pages = $pq->find('.pages li');
        foreach ($dataLinks as $item) {
            $data[] = pq($item)->attr('href');
        }
	   echo "<pre>";
	   var_dump($data);    
    }
    foreach ($data as $cats) {
       $linkPage = 'https://www.lotusite.ru'.$cats;
       $resultProduct = $parser->parserQuery($linkPage);
       $pq = phpQuery::newDocument($resultProduct);
        if (!empty($pq->find('#product-contaner h1')->html())) {
            $dataList[] = [
                'name' => $pq->find('#product-contaner h1')->html(),
                'price' => $pq->find('#block-shop .price')->text(),
                'url' => $linkPage,
                'img' => 'https://www.lotusite.ru'.$pq->find('.big-image a')->attr('href'),
                'catagery' => trim(strrchr($dataCat[$i], '/'), '/'),
            ];
        }
    }    
}
// $jsonData = json_encode($dataList);
// file_put_contents('product.txt', $jsonData);

// foreach ($dataList as $itemBd) {
//     $res = $connect->prepare("SELECT `url` FROM `itemproduct` WHERE `url` = :url");
//     $res->bindParam(':url',$itemBd['url']);
//     $res->execute();
//     $rowcount = $res->rowCount();
//     if ($rowcount <= 0) {
//         $db->query('INSERT INTO `itemProduct` SET ?As', $itemBd);
//     }
// }