<!DOCTYPE html>
<html lang="pl">
	<head>
		<title>SWiCH</title>
		<meta charset="UTF-8">
		<meta name="author" content="Patryk Banaś, Kamil Oleksiak">
		<meta name="description" content="Galeria zdjęć i filmów">
		<meta name="keywords" content="SWiCH, Galeria, PHP">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="theme-color" content="#000000" />
		<link rel="stylesheet" href="assets/css/main.css">
		<script>
			function showDetails(nazwa, komentarz, format, kategoria, histogramKolorow){
				var details = document.getElementById("details");
				var temp = "<br><b>Nazwa:</b> " + nazwa + "<br><b>Komentarz:</b> " + komentarz + "<br><b>Format:</b> " + format + "<br><b>Kategoria:</b> " + kategoria + "<br><b>Histogram kolorow:</b> " + histogramKolorow;
				details.innerHTML = temp;
			}
		</script>
	</head>
	<body>
<?php
session_start();
define('ROOT_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once ROOT_PATH.'src/PresentationLayer.class.php';

try {
	if(isset($_POST['logout'])) {
		session_unset();
		session_destroy();
		header("Refresh:0");
	}
	
	$wpClass = new Warstwa_prezentacji;
	u_wprowadz_pin($wpClass->wp_autoryzuj_pbko(), $wpClass);
	
} catch(Throwable $e) {
	printFormattedException($e);
}

function u_wprowadz_pin(?int $id_u, Warstwa_prezentacji &$class_ref): void {
	if(!is_null($id_u)) {
		if($id_u === 0) {
			$class_ref->wp_galeria_id_0();
		} elseif($id_u > 0) {
			$class_ref->wp_galeria_pbko($id_u);
		} else {
			//throw new Exception('Incorrect user ID');
			//Something went wrong... Try again.
			session_unset();
			session_destroy();
			header("Refresh:5");
		}
	}
}

?>
	</body>
</html>