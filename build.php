<?php
require 'src/DataAccessLayerConfig.php';
require 'src/Common.php';

try {
	echo $adres_ip_serwera_BD.'<br>';
	echo $login_BD.'<br>'; 
	echo $haslo_BD.'<br>'; 
	echo $nazwa_BD.'<br><br>';
	
	$query = "";
	
	$dbHook = mysqli_connect($adres_ip_serwera_BD, $login_BD, $haslo_BD, $nazwa_BD);
		
	if (mysqli_connect_errno()) {
		throw new Exception("Couldn't connect to the database.");
	}
	
	//create uzytkownicy_pbko
	$query = "
		CREATE TABLE `".$nazwa_BD."`.`uzytkownicy_pbko` ( `id_u` INT NOT NULL AUTO_INCREMENT , `pseudonim` VARCHAR(30) NOT NULL , `pin` VARCHAR(20) NOT NULL , `id_grupy` INT NOT NULL , PRIMARY KEY (`id_u`)) ENGINE = InnoDB;
	";
	$res = mysqli_query($dbHook, $query);
	
	if(!$res) {
		throw new Exception('Error in "'.mb_strimwidth($query, 0, 300, "...").'". '.$dbHook->error.'.');
	} else {
		echo 'Created "uzytkownicy_pbko" table.<br>';
	}
	
	//create zdjecia_i_filmy
	$query = "
		CREATE TABLE `".$nazwa_BD."`.`zdjecia_i_filmy` ( `id_m` INT NOT NULL AUTO_INCREMENT , `nazwa` VARCHAR(20) NOT NULL , `komentarz` VARCHAR(200) NOT NULL , `format` VARCHAR(20) NOT NULL , `kategoria` VARCHAR(60) NOT NULL , `histogram_kolorow` BLOB NULL , `referencja_do_filmu` TEXT NULL , `zdjecie_pbko` LONGBLOB NULL , `wlasnosc_pbko` INT NOT NULL , PRIMARY KEY (`id_m`)) ENGINE = InnoDB;
	";
	$res = mysqli_query($dbHook, $query);
	
	if(!$res) {
		throw new Exception('Error in "'.mb_strimwidth($query, 0, 300, "...").'". '.$dbHook->error.'.');
	} else {
		echo 'Created "zdjecia_i_filmy" table.<br>';
	}
	
	//create test users
	$query = "
		INSERT INTO `uzytkownicy_pbko` (`id_u`, `pseudonim`, `pin`, `id_grupy`) VALUES (1, 'Liam', '1111', 1), (2, 'Lucas', '2222', 1), (3, 'Ava', '3333', 2), (4, 'Admin', '', 0);
	";
	$res = mysqli_query($dbHook, $query);
	
	if(!$res) {
		throw new Exception('Error in "'.mb_strimwidth($query, 0, 300, "...").'". '.$dbHook->error.'.');
	} else {
		echo 'Added test users [Liam][1111], [Lucas][2222] in group 1 and [Ava][3333] in group 2 to "uzytkownicy_pbko".<br>';
	}
	
	//create fake user_error
	$query = "
		UPDATE `uzytkownicy_pbko` SET `id_u` = '0' WHERE `uzytkownicy_pbko`.`id_u` = 4
	";
	$res = mysqli_query($dbHook, $query);
	
	if(!$res) {
		throw new Exception('Error in "'.mb_strimwidth($query, 0, 300, "...").'". '.$dbHook->error.'.');
	} else {
		echo 'Changed one user to fake user with new ID:0 to "uzytkownicy_pbko".<br>';
	}
	
	//add populate zdjecia_i_filmy for fake user
	$file 		= dirname(__FILE__).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'secret0.jpg';
	$filename 	= addslashes($file);
	$handle 	= fopen($filename, "rb");
	$imageFile 	= addslashes(fread($handle, filesize($filename)));
	fclose($handle);
	
	$query = "
		INSERT INTO `zdjecia_i_filmy` (`id_m`, `nazwa`, `komentarz`, `format`, `kategoria`, `histogram_kolorow`, `referencja_do_filmu`, `zdjecie_pbko`, `wlasnosc_pbko`) VALUES (NULL, 'Sekretne zdjecie', 'actual data', 'image/jpeg', 'Real', 0, NULL, '".$imageFile."', '1')
	";
	$res = mysqli_query($dbHook, $query);
	
	if(!$res) {
		throw new Exception('Error in "'.mb_strimwidth($query, 0, 300, "...").'". '.$dbHook->error.'.');
	} else {
		echo 'Added "secret0.jpg" for user with ID:0 to "zdjecia_i_filmy".<br>';
	}
	
	
	//broken !!!
	//$vidPath = dirname(__FILE__).DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'secret0.mp4';;
	
	$query = "
		INSERT INTO `zdjecia_i_filmy` (`id_m`, `nazwa`, `komentarz`, `format`, `kategoria`, `histogram_kolorow`, `referencja_do_filmu`, `zdjecie_pbko`, `wlasnosc_pbko`) VALUES (NULL, 'Sekretny film', 'pog', 'video/mp4', 'Real', 0, '\\storage\\secret0.mp4', NULL, '1')
	";
	$res = mysqli_query($dbHook, $query);
	
	if(!$res) {
		throw new Exception('Error in "'.mb_strimwidth($query, 0, 300, "...").'". '.$dbHook->error.'.');
	} else {
		echo 'Added "secret0.mp4" for user with ID:0 to "zdjecia_i_filmy".<br>';
	}
	
	mysqli_close($dbHook);
	
} catch (Exception $e) {
	printFormattedException($e);
}
