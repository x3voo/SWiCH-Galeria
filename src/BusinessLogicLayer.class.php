<?php
# ******
# * Business Logic Layer
# * v1.0
# *
# *  This layer is used to process data between 
# * user and database.
# *
# * Author: Patryk Banaś, Kamil Oleksiak
# ******


require_once 'DataAccessLayer.class.php';

class Warstwa_biznesowa extends Warstwa_dostepu_do_danych {
	private $zapytanie_SQL 		= ""; // string
	private $lokacja_plikow 	= "dane_pbko"; // string
	
	public function wb_autoryzuj_pbko(string $pin_string): int {
		$this->zapytanie_SQL = "SELECT id_u FROM uzytkownicy_pbko WHERE pin = '".$this->realEscapeString($this->wb_sprawdz_tresc_zmien_z_form_pbko($pin_string, false, 20))."'";
		
		return (int)$this->wd_sprawdz_autoryzacje_pbko($this->zapytanie_SQL);
	}
	
	public function wb_przygotuj_galeria_pbko(int $id_u): array {
		$this->zapytanie_SQL 	= "SELECT * FROM zdjecia_i_filmy WHERE wlasnosc_pbko & ".pow(2, $id_u)." = ".pow(2, $id_u);
		$resources  			= $this->wd_wykonaj_zapytanieSQL_pbko($this->zapytanie_SQL);
		$galery 				= array();
		
		foreach($resources as &$resource) {
			$temp = '
			<div class="item">';
				if(explode('/', $resource['format'])[0] == "image"){
					$temp .= '
					<a href="?pokaz_zdj&id_m='.$resource['id_m'].'" target="_blank" class="itemPreview">
						<img src="data:'.$resource['format'].';base64,'.base64_encode($resource['zdjecie_pbko']).'" alt="'.$resource['nazwa'].'"/>
					</a>';
				} else {
					$temp .= '
					<a href="..'.$resource['referencja_do_filmu'].'" target="_blank" class="itemPreview">
						<img src="assets/images/videoPlaceholder.png" alt="'.$resource['nazwa'].'"/>
					</a>';
				}
				$temp .= '
				<div class="itemDescription">';
				if(explode('/', $resource['format'])[0] == "image") {
					$temp .= '<img src="assets/images/pictureIcon.svg" alt="Zdjęcie"/>';
				} else {
					$temp .= '<img src="assets/images/videoIcon.svg" alt="Film"/>';
				}
				$temp .= '
					<p style="float: left; width: 76.5%; cursor: pointer;" onclick="showDetails('."'".''.$resource['nazwa']."', '".$resource['komentarz']."', '".$resource['format']."', '".$resource['kategoria']."', '".$resource['histogram_kolorow'].''."'".')">
					'.$resource['nazwa'].'
					</p>
					<form action="" method="POST">
						<input type="hidden" value="'.$resource['id_m'].'" name="id_m"/>
						<input type="submit" value="✖" name="usun"/>
					</form>
				</div>
			</div>';
			array_push($galery, $temp);
		}
		
		return $galery;
	}
	
	public function wb_dodaj_zdj_pbko(int $id_u, bool $pryw_gr): array {
		$results 			= array();
		
		$imageName 			= $this->wb_sprawdz_tresc_zmien_z_form_pbko($_POST['imageName'], true, 20);
		$imageDescription 	= $this->wb_sprawdz_tresc_zmien_z_form_pbko($_POST['imageDescription'], true, 200);
		$imageCategory 		= $this->wb_sprawdz_tresc_zmien_z_form_pbko($_POST['imageCategory'], true, 60);
		
		if ($imageName == 'EMPTY_FIELDS' or $imageDescription == 'EMPTY_FIELDS' or $imageCategory == 'EMPTY_FIELDS') {
			array_push($results, "EMPTY_FIELDS");
		}
		
		if ($imageName == 'TOO_LONG' or $imageDescription == 'TOO_LONG' or $imageCategory == 'TOO_LONG') {
			array_push($results, "TOO_LONG");
		}
		
		$imageFile		= "";
		$imageFormat	= "";
		
		if(is_uploaded_file($_FILES["imageFile"]["tmp_name"])){
			$info = getimagesize($_FILES["imageFile"]["tmp_name"]);
			if($info["mime"] == "image/jpeg" or $info["mime"] == "image/png" or $info["mime"] == "image/gif") {
				$imageFile = getImage($_FILES["imageFile"]);
				//$imageFormat = strtoupper(explode('/', $info["mime"])[1]);
				$imageFormat = $info["mime"];
			} else {
				array_push($results, "INVALID_FORMAT");
			}
		} else {
			array_push($results, "NO_FILE");
		}
		
		
		if(!empty($results)) {
			return $results;
		}
		
		$imageColorHistogram = getImageColorHistogram($imageFile);
		
		$wlasnosc = null;
		if($pryw_gr == true) {
			$wlasnosc = pow(2, $id_u);
		} else {
			$wlasnosc = $this->wd_ustal_wlasnosc_pbko($id_u);
		}
		
		$this->zapytanie_SQL = "INSERT INTO zdjecia_i_filmy (nazwa, komentarz, format, kategoria, histogram_kolorow, referencja_do_filmu, zdjecie_pbko, wlasnosc_pbko) VALUES ('".$this->realEscapeString($imageName)."' , '".$this->realEscapeString($imageDescription)."', '".$this->realEscapeString($imageFormat)."', '".$this->realEscapeString($imageCategory)."', '".$this->realEscapeString($imageColorHistogram)."', null, '".$imageFile."', ".$this->realEscapeString($wlasnosc).")";
		$this->wd_wykonaj_zapytanieSQL_pbko($this->zapytanie_SQL);
		
		array_push($results, "OK");
		
		return $results;
	}
	
	public function wb_dodaj_film_pbko(int $id_u, bool $pr_gr): array {
		$results 			= array();
		
		$videoName 			= $this->wb_sprawdz_tresc_zmien_z_form_pbko($_POST['videoName'], true, 20);
		$videoDescription 	= $this->wb_sprawdz_tresc_zmien_z_form_pbko($_POST['videoDescription'], true, 200);
		$videoCategory 		= $this->wb_sprawdz_tresc_zmien_z_form_pbko($_POST['videoCategory'], true, 60);
		
		if ($videoName == 'EMPTY_FIELDS' or $videoDescription == 'EMPTY_FIELDS' or $videoCategory == 'EMPTY_FIELDS') {
			array_push($results, "EMPTY_FIELDS");
		}
		
		if ($videoName == 'TOO_LONG' or $videoDescription == 'TOO_LONG' or $videoCategory == 'TOO_LONG') {
			array_push($results, "TOO_LONG");
		}
		
		$videoFormat		= "";
		
		if(is_uploaded_file($_FILES['videoFile']['tmp_name'])){
			$mime = mime_content_type($_FILES["videoFile"]["tmp_name"]);
			if($mime == "video/webm" or $mime == "video/mp4" or $mime == "video/mov") {
				$videoFormat = $mime;
			} else {
				array_push($results, "INVALID_FORMAT");
				return $results;
			}
		} else {
			array_push($results, "FILE_UPLOAD_FAILED");
			return $results;
		}
		
		$nr = $this->wd_ustal_id();
		
		$ext = explode('/', $videoFormat)[1];
		
		$videoPath = ROOT_PATH.$this->lokacja_plikow.DIRECTORY_SEPARATOR.$nr.'.'.$ext;
		$videoPathLink = DIRECTORY_SEPARATOR.$this->lokacja_plikow.DIRECTORY_SEPARATOR.$nr.'.'.$ext;
		
		if(!move_uploaded_file($_FILES['videoFile']['tmp_name'], $videoPath)) {
			array_push($results, "NO_FILE");
			return $results;
		}
		
		//$imageColorHistogram = getImageColorHistogram($imageFile);
		
		$wlasnosc = null;
		if($pr_gr == true) {
			$wlasnosc = pow(2, $id_u);
		} else {
			$wlasnosc = $this->wd_ustal_wlasnosc_pbko($id_u);
		}
		
		
		$this->zapytanie_SQL = "INSERT INTO zdjecia_i_filmy (nazwa, komentarz, format, kategoria, histogram_kolorow, referencja_do_filmu, zdjecie_pbko, wlasnosc_pbko) VALUES ('".$this->realEscapeString($videoName)."' , '".$this->realEscapeString($videoDescription)."', '".$this->realEscapeString($videoFormat)."', '".$this->realEscapeString($videoCategory)."', null, '".$this->realEscapeString($videoPathLink)."', null, ".$this->realEscapeString($wlasnosc).")";
		$this->wd_wykonaj_zapytanieSQL_pbko($this->zapytanie_SQL);
		
		array_push($results, "OK");
		
		return $results;
	}
	
	public function wb_usun_pbko(int $id_u, int $id_m): void {
		$this->zapytanie_SQL = "UPDATE zdjecia_i_filmy SET wlasnosc_pbko = wlasnosc_pbko - ".pow(2, $id_u)." WHERE id_m = ".$id_m;
		$this->wd_wykonaj_zapytanieSQL_pbko($this->zapytanie_SQL);
		
		$this->zapytanie_SQL = "DELETE FROM zdjecia_i_filmy WHERE wlasnosc_pbko = 0";
		$this->wd_wykonaj_zapytanieSQL_pbko($this->zapytanie_SQL);
	}
	
	private function wb_sprawdz_tresc_zmien_z_form_pbko(string &$referencja_do_zmiennej, bool $NotEmpty, int $allowedStringSize = 0): string {
		if(isset($referencja_do_zmiennej)) {
			if($NotEmpty == true){
				if(strlen($referencja_do_zmiennej) === 0){
					return 'EMPTY_FIELDS';
				}
			}
			$referencja_do_zmiennej = htmlspecialchars($referencja_do_zmiennej);
			if($allowedStringSize != 0) {
				if(strlen($referencja_do_zmiennej) > $allowedStringSize) {
					return 'TOO_LONG';
				}
			}
		} else {
			return 'EMPTY_FIELDS';
		}
		
		return $referencja_do_zmiennej;
	}
	
	public function pobierz_zdjecie(int $id_m): string {
		if($_SESSION['id_u'] === null) {
			ob_clean();
			header("HTTP/1.1 401 Unauthorized");
			exit(0);
		} else {
			$this->zapytanie_SQL = "SELECT * FROM zdjecia_i_filmy WHERE id_m = ".$id_m." AND wlasnosc_pbko & ".pow(2, $_SESSION['id_u'])." = ".pow(2, $_SESSION['id_u']);
			$image = $this->wd_wykonaj_zapytanieSQL_pbko($this->zapytanie_SQL);
			
			$resImage = "";
			
			if(empty($image) or explode('/', $image[0]['format'])[0] != "image") {
				header("HTTP/1.1 403 Forbidden");
				$file 		= dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'403-access-for-biden.png';
				$resImage = file_get_contents($file);
			} else {
				$resImage = $image[0]['zdjecie_pbko'];
			}
			
			return $resImage; // blob in string
		}
	}
}



?>