<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'baglan.php';

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php"); exit;
}

// Formdan gelen veriler
$sefer_id = $_POST['sefer_id']; // Koltuk.php'den gelen ID
$koltuk   = $_POST['secilen_koltuk'];
$fiyat    = $_POST['fiyat'];
$cinsiyet = $_POST['cinsiyet'];
$ad       = htmlspecialchars($_POST['ad']);
$soyad    = htmlspecialchars($_POST['soyad']);
$email    = htmlspecialchars($_POST['email']);

// Görsel amaçlı değişkenler (Veritabanına artık ID ile kaydedeceğiz ama ekranda göstermek için alıyoruz)
$nereden = $_POST['nereden'];
$nereye  = $_POST['nereye'];
$tarih   = $_POST['tarih'];
$saat    = $_POST['saat'];
$firma   = $_POST['firma'];

try {
    // TRANSACTION BAŞLAT (Veri bütünlüğü için - Madde 8)
    $db->beginTransaction();

    // 1. ADIM: Koltuk Dolu mu Kontrolü (Sefer ID'sine göre)
    $kontrol = $db->prepare("SELECT id FROM biletler WHERE sefer_id = ? AND koltuk_no = ?");
    $kontrol->execute([$sefer_id, $koltuk]);

    if($kontrol->rowCount() > 0) {
        $db->rollBack(); // İşlemi geri al
        echo "<h2 style='color:red; text-align:center; margin-top:50px;'>HATA: Seçtiğiniz koltuk ($koltuk) az önce başkası tarafından alındı!</h2>";
        echo "<p style='text-align:center;'><a href='index.php'>Ana Sayfaya Dön</a></p>";
        exit;
    }

    // 2. ADIM: Yolcu Zaten Var mı? (Email kontrolü - 3NF Kuralı)
    $sorguYolcu = $db->prepare("SELECT id FROM yolcular WHERE email = ?");
    $sorguYolcu->execute([$email]);
    $yolcu = $sorguYolcu->fetch(PDO::FETCH_ASSOC);

    $yolcu_id = 0;

    if ($yolcu) {
        // Yolcu varsa ID'sini al
        $yolcu_id = $yolcu['id'];
    } else {
        // Yolcu yoksa YOLCULAR tablosuna ekle
        $ekleYolcu = $db->prepare("INSERT INTO yolcular (ad, soyad, email, cinsiyet) VALUES (?, ?, ?, ?)");
        $ekleYolcu->execute([$ad, $soyad, $email, $cinsiyet]);
        $yolcu_id = $db->lastInsertId();
    }

    // 3. ADIM: Bileti Kaydet (ID'ler ile ilişkilendirerek)
    $ekleBilet = $db->prepare("INSERT INTO biletler (sefer_id, yolcu_id, koltuk_no, satis_fiyati) VALUES (?, ?, ?, ?)");
    $insert = $ekleBilet->execute([$sefer_id, $yolcu_id, $koltuk, $fiyat]);

    // Her şey yolundaysa işlemi onayla
    $db->commit();
    $mesaj = "Biletiniz başarıyla oluşturuldu.";

} catch (PDOException $e) {
    $db->rollBack(); // Hata varsa her şeyi iptal et
    echo "Veritabanı Hatası: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İşlem Başarılı</title>
    <link rel="stylesheet" href="style.css">
    <style> .success-icon { font-size: 50px; color: #2ecc71; } </style>
</head>
<body>
<div class="container">
    <div class="success-icon">✅</div>
    <h2>İyi Yolculuklar!</h2>
    <p style="color:green; font-weight:bold;"><?php echo $mesaj; ?></p>
    <hr>
    
    <div class="ticket-card" style="text-align:left; background:#f9f9f9; padding:20px; border-radius:10px; margin-top:20px;">
        <p><strong>Yolcu:</strong> <?php echo "$ad $soyad"; ?></p>
        <p><strong>Firma:</strong> <?php echo $firma; ?></p>
        <p><strong>Güzergah:</strong> <?php echo "$nereden > $nereye"; ?></p>
        <p><strong>Tarih / Saat:</strong> <?php echo "$tarih - $saat"; ?></p>
        <p><strong>Koltuk No:</strong> <span style="font-size:1.5em; color:red; font-weight:bold;"><?php echo $koltuk; ?></span></p>
        <p><strong>Fiyat:</strong> <?php echo $fiyat; ?> TL</p>
    </div>
    
    <br>
    <a href="index.php" class="btn" style="background-color:#333;">Yeni İşlem</a>
</div>
</body>
</html>