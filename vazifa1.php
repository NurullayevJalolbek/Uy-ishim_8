<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ish vaqti kiritish</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">        
        <label>
                Ish vaqti  08:00 dan<br>
                Tugashi   17:00 gacha shu oraliqda hisoblanadi
        </label><br>
        <div class="container">
            <h1> Malumotlarni kiriting </h1>
            </div>

        <form action="vazifa1.php" method="POST">
            <div class="row g-3">
                <div class="col-sm-4">
                <input type="date" id="sana" name="sana" class="form-control">
            </div>
            <div class="col-sm">
                <input type="time" id="kelgan_vaqt" name="kelgan_vaqt" class="form-control">
            </div>
            <div class="col-sm">
                <input type="time" id="ketgan_vaqt" name="ketgan_vaqt" class="form-control">
            </div>
            </div>
            <button type="submit" class="btn btn-primary">Yuborish</button>
            <br>
        </form>

        <?php
        if(!empty($_POST)){
            if ($_POST['kelgan_vaqt'] != ""  &&  $_POST['ketgan_vaqt'] && $_POST['sana'] != ""){
                $kelvaqt = $_POST['kelgan_vaqt'];
                $ketvaqt = $_POST['ketgan_vaqt'];
                
                // Define Ish_reja class and methods here
                class Ish_reja{
            public string $kelgan_vaqt1;
            public string $ketgan_vaqt1;
            public function __construct($KELGAN_VAQT,$KETGAN_VAQT)
            {
                $this->kelgan_vaqt1 = $KELGAN_VAQT; 
                $this->ketgan_vaqt1 = $KETGAN_VAQT;
            }
            public function Ishlagan_soati()
            {
            $vaqt1 = new DateTime($this->kelgan_vaqt1);
            $vaqt2 = new DateTime($this->ketgan_vaqt1);
            $oraliq_vaqt = $vaqt1 -> diff($vaqt2);
            return strval("$oraliq_vaqt->h : $oraliq_vaqt->i");

            }
            public function Qarz_soati()
            {   
                $boshlanish_vaqti = new DateTime('08:00');
                $tugash_vaqti = new DateTime('17:00');

                $vaqt1 = new DateTime($this->kelgan_vaqt1);
                $vaqt2 = new DateTime($this->ketgan_vaqt1);

                $oraliq_vaqt1 = $boshlanish_vaqti -> diff($vaqt1);
                $oraliq_vaqt2 = $vaqt2 -> diff($tugash_vaqti);
                
                $soat1 = $oraliq_vaqt1->h;
                $minut1 = $oraliq_vaqt1->i;

                $soat2 = $oraliq_vaqt2->h;
                $minut2 = $oraliq_vaqt2->i;
                $a1 = "$soat1:$minut1";
                $b1 = "$soat2:$minut2";

                list($soatt1,$minutt1)=explode(":",$a1);
                $a1SOAT = $soatt1 * 60 + $minutt1;

                list($soatt2,$minutt2)=explode(":",$b1);
                $b1SOAT = $soatt2 * 60 + $minutt2;

                $javob = $a1SOAT + $b1SOAT;

                $javobSOAT = floor($javob / 60);
                $javobMINUT = $javob % 60;
                $soatMinut = sprintf("%02d:%02d",$javobSOAT,$javobMINUT);
                echo "\n";
                return $soatMinut;
            }
        }
                $vaqt = new Ish_reja($kelvaqt,$ketvaqt);
                $ishlagansoati = $vaqt->Ishlagan_soati();
                $qarzvaqti = $vaqt->Qarz_soati();

                $pdo = new PDO(
                    $dsn = 'mysql:host=localhost;dbname=birinchi_databse',
                    $username = 'root',
                    $password = '@jalol2004');

                $kelvaqt = (new DateTime($kelvaqt))->format('H:i:s');
                $ketvaqt = (new DateTime($ketvaqt))->format('H:i:s');

                $query = "INSERT INTO ish_soati (sana,kelgan_vaqt, ketgan_vaqt, ishlagan_soati, qarz_vaqti)
                        VALUES (:sana,:kelgan_vaqt, :ketgan_vaqt, :ishlagan_soati, :qarz_vaqti)";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':sana', $_POST['sana']);
                $stmt->bindParam(':kelgan_vaqt', $kelvaqt);
                $stmt->bindParam(':ketgan_vaqt', $ketvaqt);
                $stmt->bindParam(':ishlagan_soati', $ishlagansoati);
                $stmt->bindParam(':qarz_vaqti', $qarzvaqti);
                $stmt->execute();

                $query = $pdo->query("SELECT * FROM ish_soati")->fetchAll();

                echo "<h3> Malumotlar Jadval ko'rinishida </h3>";
                echo '<table class="table table-dark table-striped-columns">';
                echo '<thead class="thead-dark">';
                echo '<tr>';
                echo '<th>ID</th>';
                echo '<th>Sanaaa</th>';
                echo '<th>Kelgan vaqti</th>';
                echo '<th>Ketgan vaqti</th>';
                echo '<th>Ishlagan soati</th>';
                echo '<th>Qarz vaqti</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                foreach($query as $row){
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['sana'] . '</td>';
                    echo '<td>' . $row['kelgan_vaqt'] . '</td>';
                    echo '<td>' . $row['ketgan_vaqt'] . '</td>';
                    echo '<td>' . $row['ishlagan_soati'] . '</td>';
                    echo '<td>' . $row['qarz_vaqti'] . '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
                $umumiyMINUT = 0;
                foreach($query as $row){
                    $time_parts = explode(":", $row['qarz_vaqti']);
                    $umumiyMINUT += ($time_parts[0] * 60) + $time_parts[1];
                }
                $soat = floor($umumiyMINUT / 60);
                $daqiqa = $umumiyMINUT % 60;

                echo "<p>Umumiy qarzingiz: $soat soat $daqiqa daqiqa</p>";

            } else {
                echo "<p>Malumotlar kiritilmadi</p>";
            }
        }
        ?>

    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
