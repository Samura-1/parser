<?php
$connect = new PDO('mysql:host=localhost;dbname=testProduct', 'root', '');
    if (isset($_GET['limit'])) {
 		$res = $connect->prepare("SELECT * FROM `itemproduct` LIMIT :lim");
		$res->bindValue(':lim', $_GET['limit'], PDO::PARAM_INT);
    	$res->execute(); 
    	$dataCard = $res->FETCHALL(PDO::FETCH_ASSOC);   	
    } else {
 		$res = $connect->prepare("SELECT * FROM `itemproduct`");
    	$res->execute(); 
    	$dataCard = $res->FETCHALL(PDO::FETCH_ASSOC);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<!-- CSS only -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h2><a href="?limit=10">Вывести 10</a></h2>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<?php foreach ($dataCard as $item): ?>
			<div class="col-md-4">
				<p><img src="<?= $item['img']?>" alt="" width="200"></p>
				<p><?= $item['name'] ?></p>
				<p><?= $item['price']?> рублей</p>
			</div>
			<?php endforeach ?>
		</div>
	</div>
</body>
</html>