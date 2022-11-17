<?php
# ******
# * Data Access Layer
# * v1.0
# *
# *  This layer consists of the definitions of
# * database tables and columns and the computer logic
# * that is needed to navigate the database.
# *
# * Author: Patryk Banaś, Kamil Oleksiak
# ******

require 'Common.php';

class Warstwa_dostepu_do_danych {
	// Zwraca nowe ID dla filmu/zdjecia
	public function wd_ustal_id(): int {
		require 'DataAccessLayerConfig.php';
		$dbHook = mysqli_connect($adres_ip_serwera_BD, $login_BD, $haslo_BD, $nazwa_BD);
		
		if (mysqli_connect_errno()) {
			throw new Exception("Couldn't connect to the database.");
		}

		$res = mysqli_query($dbHook, "SELECT AUTO_INCREMENT as last_id FROM information_schema.tables WHERE table_name = 'zdjecia_i_filmy' and table_schema = 'mm_pbko'");
		
		if(!$res) {
			throw new Exception('Error in "'.mb_strimwidth($ref_do_zapytaniaSQL, 0, 300, "...").'". '.$dbHook->error.'.');
		}
		
		$row 	= mysqli_fetch_array($res);
		$id_m 	= $row['last_id'];

		mysqli_close($dbHook);
		return (int)$id_m;
	}
	
	// Zwracana jest wartość dla atrybutu 'wlasnosc' (zgodnie z opisem w
	// rozdziale 1.).
	public function wd_ustal_wlasnosc_pbko(int $id_u): int {
		require 'DataAccessLayerConfig.php';
		$dbHook = mysqli_connect($adres_ip_serwera_BD, $login_BD, $haslo_BD, $nazwa_BD);
		
		if (mysqli_connect_errno()) {
			throw new Exception("Couldn't connect to the database.");
		}
		
		$res1 = mysqli_query($dbHook, "SELECT id_grupy FROM uzytkownicy_pbko WHERE id_u=" . $id_u);
		
		if(!$res1) {
			throw new Exception('Error in "'.mb_strimwidth($ref_do_zapytaniaSQL, 0, 300, "...").'". '.$dbHook->error.'.');
		}
		
		$wynik = mysqli_query($dbHook, "SELECT * FROM uzytkownicy_pbko WHERE id_grupy=" . mysqli_fetch_array($res1)['id_grupy']);
		
		if(!$wynik) {
			throw new Exception('Error in "'.mb_strimwidth($ref_do_zapytaniaSQL, 0, 300, "...").'". '.$dbHook->error.'.');
		}

		$i = 1;
		$wlasnosc = 0;
		while ($row = mysqli_fetch_array($wynik)) {
			$wlasnosc = $wlasnosc + pow(2, $row["id_u"]);
			$i = $i + 1;
		};
		
		mysqli_close($dbHook);
		return (int)$wlasnosc;
	}
	
	// Zwracany jest id_u, w przypadku nieudanej autoryzacji jest to id_u=0.
	// W przypadku wykorzystania sesji, alternatywnie - ustawienie zmiennej
	// lub zmiennych (id_u i id_grupy) sesyjnej.
	public function wd_sprawdz_autoryzacje_pbko(string &$ref_do_zapytaniaSQL): int {
		require 'DataAccessLayerConfig.php';
		$dbHook = mysqli_connect($adres_ip_serwera_BD, $login_BD, $haslo_BD, $nazwa_BD);
		
		if (mysqli_connect_errno()) {
			throw new Exception("Couldn't connect to the database.");
		}
		
		// alternative ... $dbHook->query("sql..");
		$wynik = mysqli_query($dbHook, $ref_do_zapytaniaSQL);
		
		if(!$wynik) {
			throw new Exception('Error in "'.$ref_do_zapytaniaSQL.'". '.$dbHook->error.'.');
		}
		
		$row = mysqli_fetch_array($wynik);
		
		$id_u = null;
		
		if(isset($row)) {
			$id_u = $row['id_u'];
		} else {
			$id_u = 0;
		}
		
		mysqli_close($dbHook);
		return (int)$id_u;
	}
	
	public function wd_wykonaj_zapytanieSQL_pbko(string &$ref_do_zapytaniaSQL): array {
		require 'DataAccessLayerConfig.php';
		$dbHook = mysqli_connect($adres_ip_serwera_BD, $login_BD, $haslo_BD, $nazwa_BD);
		
		if (mysqli_connect_errno()) {
			throw new Exception("Couldn't connect to the database.");
		}
		
		$wynik = mysqli_query($dbHook, $ref_do_zapytaniaSQL);
		
		if(!$wynik) {
			throw new Exception('Error in "'.mb_strimwidth($ref_do_zapytaniaSQL, 0, 300, "...").'". '.$dbHook->error.'.');
		}
		
		$resultArray = Array();
		
		if($wynik !== true){
			while($row = mysqli_fetch_array($wynik)) {
				array_push($resultArray, $row);
			}
		}
		
		mysqli_close($dbHook);
		return (array)$resultArray;
	}
	
	public function wd_pobierz_wszystko_dla_pbko(int $id_u): void {
		require 'DataAccessLayerConfig.php';
		$dbHook = mysqli_connect($adres_ip_serwera_BD, $login_BD, $haslo_BD, $nazwa_BD);
		
		mysqli_close($dbHook);
	}
	
	public function realEscapeString($string): string {
		require 'DataAccessLayerConfig.php';
		$dbHook = mysqli_connect($adres_ip_serwera_BD, $login_BD, $haslo_BD, $nazwa_BD);
		$newString = mysqli_real_escape_string($dbHook, $string);
		
		mysqli_close($dbHook);
		return $newString;
	}
}

?>