<?php
# ******
# * Presentation Layer
# * v1.0
# *
# *  This the layer that user is interaction with
# * and decidec what system user sees.
# *
# * Author: Patryk Banaś, Kamil Oleksiak
# ******

require_once 'BusinessLogicLayer.class.php';

class Warstwa_prezentacji extends Warstwa_biznesowa {
	private $form_galeria_pbko 		= ""; // string
	private $form_dodaj_zdj_pbko 	= ""; // string
	private $form_dodaj_film_pbko 	= ""; // string
	private $form_autoryzuj_pbko 	= ""; // string
	
	public $id_u 					= NULL;  // int
	
	public function __construct() {
		if(isset($_SESSION['id_u'])) {
			$this->id_u = $_SESSION['id_u'];
		}
		
		$this->form_autoryzuj_pbko 	= file_get_contents(ROOT_PATH.'src/html/form_autoryzuj_pbko.html');
		$this->form_dodaj_zdj_pbko 	= file_get_contents(ROOT_PATH.'src/html/form_dodaj_zdj_pbko.html');
		$this->form_dodaj_film_pbko = file_get_contents(ROOT_PATH.'src/html/form_dodaj_film_pbko.html');	
		$this->form_galeria_pbko 	= file_get_contents(ROOT_PATH.'src/html/form_galeria_pbko.DEMO.html');
	}
	
	private function wp_naglowek_pbko(): void {
		echo '
		<header>
			<p>Galeria</p>
			<form action="" method="POST">
				<button type="submit" value="1" name="logout"><p>Wyloguj</p></button>
			</form>
		</header>';
	}
	
	private function wp_stopka_pbko(): void {
		echo "<footer><p>SWiCH 2022</p></footer>";
	}
	
	public function wp_autoryzuj_pbko(): ?int {
		if(is_null($this->id_u)) {
			if(isset($_POST['pin'])) {
				//is it safe???
				$_SESSION['id_u'] = $this->id_u = $this->wb_autoryzuj_pbko($_POST['pin']);
			} else {
				echo $this -> form_autoryzuj_pbko;
			}
		}
		
		return $this->id_u;
	}
	
	public function wp_galeria_id_0(): void {
		//418 I'm a teapot
		echo '<div class="galery-main">';
		$this->wp_naglowek_pbko();
		
		echo '
		<nav>
			User '.$this->id_u.' &nbsp;|&nbsp; 
			<form action="" method="POST">
				<button type="submit"><p>Galeria</p></button>
			</form>
		</nav>';
		
		if (isset($_GET['pokaz_zdj'])) {
			echo $this->wp_pokaz_zdj($this->pobierz_zdjecie($_GET['id_m']));
			
		} else {
			echo '<main>';
			echo '<section>';
			echo '<p>Pliki</p>
					<div class="grid">';
			$this->form_galeria_pbko = $this->wb_przygotuj_galeria_pbko($this->id_u);
			
			foreach($this->form_galeria_pbko as &$image){
				echo $image;
			}
			
			echo '</div>';
			echo '</section><aside>Details<br><div id="details"></div></aside></main>';
		}
		
		$this->wp_stopka_pbko();
		echo '</div>';
	}
	
	public function wp_galeria_pbko(int $id_u): void {
		echo '<div class="galery-main">';
		$this->wp_naglowek_pbko();
		
		echo '
		<nav>
			User '.$this->id_u.' &nbsp;|&nbsp; 
			<form action="" method="POST">
				<button type="submit"><p>Galeria</p></button>
			&nbsp;|&nbsp; 
			<button type="submit" value="Dodaj zdjęcie" name="form_dodaj_zdjecie">
				<img src="assets/images/addPictureIcon.svg" alt="Dodaj zdjęcie"/>
				<p>Dodaj zdjęcie</p>
			</button> &nbsp;|&nbsp; 
			<button type="submit" value="Dodaj film" name="form_dodaj_film">
				<img src="assets/images/addVideoIcon.svg" alt="Dodaj film"/>
				<p>Dodaj film</p>
			</button>
			</form> 
		</nav>';
		
		echo '<main>';
		
		if (isset($_POST['usun'])) {
			$this->wb_usun_pbko($this->id_u, $_POST['id_m']);
		}
		
		if(isset($_POST['form_dodaj_zdjecie'])) {
			echo $this->form_dodaj_zdj_pbko;
			
		} elseif (isset($_POST['form_dodaj_film'])) {
			echo $this->form_dodaj_film_pbko;
			
		} elseif (isset($_POST['dodaj_zdjecie'])){
			echo $this->form_dodaj_zdj_pbko;
			$this->wp_dodaj_zdj_pbko($this->id_u);
			
		} elseif (isset($_POST['dodaj_film'])) {
			echo $this->form_dodaj_film_pbko;
			$this->wp_dodaj_film_pbko($this->id_u);
			
		} elseif (isset($_GET['pokaz_zdj'])) {
			echo $this->wp_pokaz_zdj($this->pobierz_zdjecie($_GET['id_m']));
			
		} else {
			echo '<section>';
			echo '<p>Pliki</p>
					<div class="grid">';
			$this->form_galeria_pbko = $this->wb_przygotuj_galeria_pbko($this->id_u);
			
			foreach($this->form_galeria_pbko as &$image){
				echo $image;
			}
			
			echo '</div>';
			echo '</section><aside>Details<br><div id="details"></div></aside>';
		}
		
		echo '</main>';
		$this->wp_stopka_pbko();
		echo '</div>';
	}
	
	public function wp_dodaj_zdj_pbko(int $id_u): void {
		$result 		= null;
		$pryw_gr 		= true;
		//$imageAccess 	= $this->wb_sprawdz_tresc_zmien_z_form_pbko($_POST['imageAccess'], true); // Cant be done!!
		$imageAccess	= null;
		
		if(isset( $_POST['imageAccess'])) {
			$imageAccess = $_POST['imageAccess'];
		}
		
		if($imageAccess == "private") {
			$pryw_gr = true;
		} elseif ($imageAccess == "public") {
			$pryw_gr = false;
		} else {
			$result = 'ACCESS_NOT_SPECIFIED';
		}
		
		$results = $this->wb_dodaj_zdj_pbko($id_u, $pryw_gr);
		
		if($result != null){
			array_push($results, $result);
		}
		
		echo "<div>";
		
		foreach($results as &$r) {
			switch($r) {
				case 'ACCESS_NOT_SPECIFIED':
					echo '<br><font color="red">Nie ustawiono uprawnień.</font>';
					break;
				case 'EMPTY_FIELDS':
					echo '<br><font color="red">Nie wszystkie pola zostały wypełnione.</font>';
					break;
				case 'TOO_LONG':
					echo '<br><font color="red">Wprowadzono za dużo znaków w jednym z pól.</font>';
					break;
				case 'FILE_UPLOAD_FAILED':
					echo '<br><font color="red">Nie udało się pobrać zdjęcia.</font>';
					break;
				case 'NO_FILE':
					echo '<br><font color="red">Nie wybrano pliku.</font>';
					break;
				case 'INVALID_FORMAT':
					echo '<br><font color="red">Nie prawidłowy format pliku.</font>';
					break;
				case 'OK':
					echo '<br><font color="green">Dodano zdjęcie.</font>';
					break;
			}
		}
		
		echo "</div>";
	}
	
	public function wp_dodaj_film_pbko(int $id_u): void {
		$result 		= null;
		$pryw_gr 		= true;
		//$videoAccess 	= $this->wb_sprawdz_tresc_zmien_z_form_pbko($_POST['videoAccess'], true); // Cant be done!!
		$videoAccess	= $_POST['videoAccess'];
		
		if($videoAccess == "private") {
			$pryw_gr = true;
		} elseif ($videoAccess == "public") {
			$pryw_gr = false;
		} else {
			$result = 'ACCESS_NOT_SPECIFIED';
		}
		
		$results = $this->wb_dodaj_film_pbko($id_u, $pryw_gr);
		
		if($result != null){
			array_push($results, $result);
		}
		
		echo "<div>";
		
		foreach($results as &$r) {
			switch($r) {
				case 'ACCESS_NOT_SPECIFIED':
					echo '<br><font color="red">Nie ustawiono uprawnień.</font>';
					break;
				case 'EMPTY_FIELDS':
					echo '<br><font color="red">Nie wszystkie pola zostały wypełnione.</font>';
					break;
				case 'TOO_LONG':
					echo '<br><font color="red">Wprowadzono za dużo znaków w jednym z pól.</font>';
					break;
				case 'FILE_UPLOAD_FAILED':
					echo '<br><font color="red">Nie udało się pobrać filmu.</font>';
					break;
				case 'NO_FILE':
					echo '<br><font color="red">Nie wybrano pliku.</font>';
					break;
				case 'INVALID_FORMAT':
					echo '<br><font color="red">Nie prawidłowy format pliku.</font>';
					break;
				case 'OK':
					echo '<br><font color="green">Dodano film.</font>';
					break;
			}
		}
		
		echo "</div>";
	}
	
	public function wp_pokaz_zdj(string $dane_atrybutu_zdjecie): void {
		ob_clean();
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		header('Content-Type: '.$finfo->buffer($dane_atrybutu_zdjecie));
		echo $dane_atrybutu_zdjecie;
		exit(0);
	}
}

?>