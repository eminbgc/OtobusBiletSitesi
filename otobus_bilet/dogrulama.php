<?php
session_start();
if (!isset($_SESSION['gecici_kullanici'])) {
    header("Location: giris.php");
    exit;
}

$hata = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $girilen_kod = $_POST['kod'];
    
    // Kod doğru mu kontrol et
    if ($girilen_kod == $_SESSION['dogrulama_kodu']) {
        // KOD DOĞRU! 
        // Kullanıcıyı bilet_detay.php'ye verileriyle birlikte aktarmamız lazım.
        // Bunu yapmanın en temiz yolu, görünmez bir form oluşturup JavaScript ile otomatik göndermektir.
        ?>
        <!DOCTYPE html>
        <html>
        <body onload="document.getElementById('redirectForm').submit()">
            <p>Kod doğrulandı, yönlendiriliyorsunuz...</p>
            <form id="redirectForm" action="bilet_detay.php" method="POST">
                <input type="hidden" name="islem" value="sorgula">
                <input type="hidden" name="ad" value="<?php echo $_SESSION['gecici_kullanici']['ad']; ?>">
                <input type="hidden" name="soyad" value="<?php echo $_SESSION['gecici_kullanici']['soyad']; ?>">
                <input type="hidden" name="email" value="<?php echo $_SESSION['gecici_kullanici']['email']; ?>">
            </form>
        </body>
        </html>
        <?php
        // Session'daki kodu temizleyelim ki tekrar kullanılmasın
        unset($_SESSION['dogrulama_kodu']);
        unset($_SESSION['gecici_kullanici']);
        exit;
    } else {
        $hata = "Hatalı kod girdiniz! Lütfen mailinizi kontrol ediniz.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Doğrulama</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width: 400px;">
    <h2>🔒 Güvenlik Doğrulaması</h2>
    <p>Lütfen <strong><?php echo $_SESSION['gecici_kullanici']['email']; ?></strong> adresine gönderilen 6 haneli kodu giriniz.</p>
    <hr>
    
    <?php if($hata): ?>
        <p style="color:red; font-weight:bold;"><?php echo $hata; ?></p>
    <?php endif; ?>

    <form method="POST">
        <div style="display:flex; flex-direction:column; gap:15px; margin-top:20px;">
            <input type="text" name="kod" placeholder="123456" required 
                   style="width:100%; text-align:center; font-size:24px; letter-spacing:5px; padding:10px;" maxlength="6">
            
            <button type="submit" class="btn" style="width:100%; background-color:#27ae60;">Doğrula ve Giriş Yap</button>
        </div>
    </form>
    <br>
    <a href="giris.php" style="color:#7f8c8d; text-decoration:none;">← Bilgileri Düzenle</a>
</div>
</body>
</html>