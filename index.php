<?php
require_once __DIR__ . "/simple_html_dom.php";

$url = "https://www.knizka.pl/ru_RU/c/%D0%94%D0%BB%D1%8F-%D0%B4%D0%B5%D1%82%D0%B5%D0%B9/14";
$articles = [];
$data = [];

function getCurl($url){
$output = curl_init();	//підключаємо
curl_setopt($output, CURLOPT_URL, $url);	//відправляємо адресу сторінки
curl_setopt($output, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($output, CURLOPT_HEADER, 0);
$out = curl_exec($output);		//поміщаємо html  в рядок
curl_close($output);	//закриваємо підключення
return $out;
}

$page = getCurl($url); //відкриваємо за допомогою curl
$document = new DOMDocument(); 
$document->loadHTML($page);//розбираємо сторінку за допомогою DOMDOcument
$xpath = new DOMXPath($document); //підключаємо DOMXpath для парсингу з допомогою xml-формату
$elements = $xpath->query(".//div[@class='product-inner-wrap']/a[1]"); //витягуємо посилання на сторінки
if (count($elements)!=0) {
	foreach ($elements as $element) {
		$articles[] = "https://www.knizka.pl" . $element->getAttribute('href'); // створюємо url кожної сторінки
	}
} else {
	echo "no pages";
}
foreach ($articles as $article) { //перебираємо url
	$pages = getCurl($article); // відкриваємо за допомогою curl
	$html = str_get_html($pages); //відкриваємо за допомогою simplehtmldom
	$title = $html->find(".boxhead h1", 0)->innertext ?? '';
	$image = $html->find(".productdetailsimgsize img, .productdetailsimgsize a img", 0)->attr['src'] ?? '';
	$price = $html->find(".price .main-price", 0)->innertext ?? '';
	$avialable = $html->find(".availability > .second", 0)->innertext ?? '';
	$data[] = [ //створюємо масив з описами книг
		"title" => trim($title), 
		"image" => "https://www.knizka.pl" . $image,
		"price" => $price,
		"avialable" => $avialable
	];
}
var_dump($data);
$fp = fopen('file.csv', 'w'); //записуємо в csv
foreach ($data as $name => $values) {
	fputcsv($fp, $values);
}
fclose($fp);
