<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'baglan.php';

if($_SERVER['REQUEST_METHOD'] != 'POST') { 
    header("Location: index.php"); exit; 
}

// Seferler.php'den gelen Sefer ID'si kritik!
$sefer_id = $_POST['sefer_id'];

// Görsel veriler
$nereden = $_POST['nereden'];
$nereye  = $_POST['nereye'];
$tarih   = $_POST['tarih'];
$saat    = $_POST['saat'];
$firma   = $_POST['firma'];
$fiyat   = $_POST['fiyat'];

// --- DOLU KOLTUKLARI ÇEKME (Normalize Tabloya Uygun) ---
// Artık direkt sefer_id ile sorguluyoruz. Çok daha performanslı.
// Ancak cinsiyeti öğrenmek için YOLCULAR tablosuna JOIN atmamız lazım.
$sorgu = $db->prepare("
    SELECT b.koltuk_no, y.cinsiyet 
    FROM biletler b
    JOIN yolcular y ON b.yolcu_id = y.id
    WHERE b.sefer_id = ?
");
$sorgu->execute([$sefer_id]);

$doluKoltuklar = [];
while($row = $sorgu->fetch(PDO::FETCH_ASSOC)) {
    $doluKoltuklar[$row['koltuk_no']] = $row['cinsiyet'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Koltuk Seçimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2><?php echo $firma; ?> - Koltuk Seçimi</h2>
    <p><?php echo "$nereden > $nereye | $tarih | Saat: $saat"; ?></p>
    
    <div class="bus-container">
        <div class="driver"></div>
        <?php
        $koltukNo = 1;
        for($i=0; $i<12; $i++) {
            echo '<div class="row">';
            
            // --- SOL TEKLİ KOLTUK ---
            if (array_key_exists($koltukNo, $doluKoltuklar)) {
                $cinsiyet = $doluKoltuklar[$koltukNo];
                $durum = ($cinsiyet == 'Erkek') ? 'occupied-male' : 'occupied-female';
                echo "<div class='seat $durum' data-no='$koltukNo'>$koltukNo</div>";
            } else {
                echo "<div class='seat available' data-no='$koltukNo' onclick='selectSeat(this)'>$koltukNo</div>";
            }
            $koltukNo++;
            
            // --- SAĞ ÇİFTLİ KOLTUKLAR ---
            echo "<div style='display:flex; gap:5px;'>";
                if (array_key_exists($koltukNo, $doluKoltuklar)) {
                    $cinsiyet = $doluKoltuklar[$koltukNo];
                    $durum = ($cinsiyet == 'Erkek') ? 'occupied-male' : 'occupied-female';
                    echo "<div class='seat $durum' data-no='$koltukNo'>$koltukNo</div>";
                } else {
                    echo "<div class='seat available' data-no='$koltukNo' onclick='selectSeat(this)'>$koltukNo</div>";
                }
                $koltukNo++;

                if (array_key_exists($koltukNo, $doluKoltuklar)) {
                    $cinsiyet = $doluKoltuklar[$koltukNo];
                    $durum = ($cinsiyet == 'Erkek') ? 'occupied-male' : 'occupied-female';
                    echo "<div class='seat $durum' data-no='$koltukNo'>$koltukNo</div>";
                } else {
                    echo "<div class='seat available' data-no='$koltukNo' onclick='selectSeat(this)'>$koltukNo</div>";
                }
                $koltukNo++;
            echo "</div>";
            echo '</div>'; 
        }
        ?>
    </div>

    <div id="passenger-info">
        <h3>Yolcu Bilgileri</h3>
        <p>Seçilen Koltuk: <strong><span id="display-seat-no">-</span></strong></p>
        
        <form action="sonuc.php" method="POST">
            <input type="hidden" name="sefer_id" value="<?php echo $sefer_id; ?>">
            
            <input type="hidden" name="nereden" value="<?php echo $nereden; ?>">
            <input type="hidden" name="nereye" value="<?php echo $nereye; ?>">
            <input type="hidden" name="tarih" value="<?php echo $tarih; ?>">
            <input type="hidden" name="saat" value="<?php echo $saat; ?>">
            <input type="hidden" name="firma" value="<?php echo $firma; ?>">
            <input type="hidden" name="fiyat" value="<?php echo $fiyat; ?>">
            <input type="hidden" name="secilen_koltuk" id="input-seat-no" required>
            
            <div class="gender-select">
                <label><input type="radio" name="cinsiyet" value="Erkek" required> Erkek</label>
                <label><input type="radio" name="cinsiyet" value="Kadın" required> Kadın</label>
            </div>
            
            <div style="display:flex; flex-direction:column; gap:10px; align-items:center;">
                <input type="text" name="ad" placeholder="Adınız" required>
                <input type="text" name="soyad" placeholder="Soyadınız" required>
                <input type="email" name="email" placeholder="E-posta Adresiniz" required>
            </div>
            
            <br>
            <button type="submit" class="btn">BİLET SATIN AL (<?php echo $fiyat; ?> TL)</button>
        </form>
    </div>
</div>
<script>
    function selectSeat(element) {
        if(element.classList.contains('occupied-male') || element.classList.contains('occupied-female')) {
            alert("Bu koltuk maalesef dolu!");
            return;
        }
        const allSeats = document.querySelectorAll('.seat');
        allSeats.forEach(seat => seat.classList.remove('selected'));
        
        element.classList.add('selected');
        const seatNo = element.getAttribute('data-no');
        
        document.getElementById('passenger-info').style.display = 'block';
        document.getElementById('display-seat-no').innerText = seatNo;
        document.getElementById('input-seat-no').value = seatNo;
        
        document.getElementById('passenger-info').scrollIntoView({behavior: "smooth"});
    }
</script>
</body>
</html>