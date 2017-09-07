<?php
if(isset($_GET['id'])){
	$id = $_GET['id'];
?>
<!doctype html>
	<html>
		<head>
			<meta charset="UTF-8" />
			<link rel="stylesheet" type="text/css" href="style.css">
		</head>
		<body>
<form method="post">
    <p>nazwa<span style="color:brown">*</span>: </p><input type="text" name="nazwa"/><br><br>
    <p>lokalizacja: </p><textarea name="lokalizacja"></textarea><br><br>
    <p>uwagi: </p><textarea name="uwagi"></textarea><br><br>
    <input class="do-lewej przycisk dodaj" type="submit" value="dodaj"/>
</form>
<p></p>
<a class="do-lewej przycisk" href="z_xml_do_bazy_oop.php"><p>Powrót do tabelki</p></a>
<?php        
if((isset($_POST['nazwa']) && !empty($_POST['nazwa'])) && 
	preg_match('@^[^ąęćśżźńłóĄĆĘŁŃÓŚŹŻ\s]+$@', $_POST['nazwa'])){
    
class Dodaj{
    private $nowa_nazwa;
    private $nowa_lokalizacja;
    private $nowe_uwagi;
    private $id;
    public $db;
    private $class_db_file;
    
    public function __construct(){
        $this->nowa_nazwa = $_POST['nazwa'];
		if((isset($_POST['lokalizacja']) && !empty($_POST['lokalizacja']))){
			$this->nowa_lokalizacja = $_POST['lokalizacja'];
		}
		if(isset($_POST['uwagi']) && !empty($_POST['uwagi'])){
			$this->nowe_uwagi = $_POST['uwagi'];
		}
        $this->id = $_GET['id'];
        $this->class_db_file = 'db.php';

        if(file_exists($this->class_db_file)){
            require_once($this->class_db_file);
            $this->db = new db();
        }else{
            echo "brak pliku z klasą do łączenia z db";
        }
    }
    
    public function pobierzPozostaleDane(){
		$zapytanie_nowe = "SELECT * FROM roznice WHERE id_nowego_hosta=$this->id";
		$rezultat_nowe = mysqli_query($this->db->connection, $zapytanie_nowe);
		$row = mysqli_fetch_array($rezultat_nowe);
        return $row;
    }
    
    public function dodaj_do_pliku(){
        $row = $this->pobierzPozostaleDane();
        $path = "confy/dhcpd-vlan".$row['VLAN'].".conf";
        //$wiersz="";
        $wiersz= "host ".$this->nowa_nazwa." {fixed-address ".$row['nowy_ip'].
        ";hardware ethernet ".$row['nowy_mac'].";}"."\r\n";
		$this->zapiszDoLoga("dodaje do $path, $wiersz");
        file_put_contents($path, $wiersz, FILE_APPEND);
		$this->kasujTabeleRoznice();
    }
	
	private function kasujTabeleRoznice(){
		$sql_kasuj_roznice = "DELETE FROM roznice";
		$sql_zeruj_roznice = "ALTER TABLE roznice AUTO_INCREMENT=0";
		
		$rezultat_czysc_roznice = mysqli_query($this->db->connection, $sql_kasuj_roznice);        
        $rezultat_zeruj_roznice = mysqli_query($this->db->connection, $sql_zeruj_roznice);
	}

	private function zapiszDoLoga($komunikat){
		file_put_contents('roznice_oop_log.txt', $komunikat."\r\n",  FILE_APPEND);
	}	
}
$dodaj = new Dodaj();
$dodaj->dodaj_do_pliku();
header('location: z_xml_do_bazy_oop.php');
}else{
    echo "<p style=\"color:brown\">* wypełnij wymagane pole, nie używaj spacji ani polskich znaków</p>";
}
}else{
	header('location: z_xml_do_bazy_oop.php');
	exit();
}

