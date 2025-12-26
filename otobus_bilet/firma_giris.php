<?php
// Sayfa yönlendirme hatalarını önlemek için
ob_start(); 
include 'baglan.php';

// Zaten giriş yapmışsa direkt panele (firma_panel.php) gönder
if(isset($_SESSION['firma_giris']) && $_SESSION['firma_giris'] === true){
    header("Location: firma_panel.php");
    exit;
}

$hata = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firma_adi = $_POST['firma_adi'];
    $sifre = $_POST['sifre'];

    // Veritabanı Kontrolü ($db değişkeni baglan.php'den gelir)
    // Sadece adı ve şifresi eşleşen firmayı bul
    $sorgu = $db->prepare("SELECT * FROM firmalar WHERE firma_adi = ? AND sifre = ?");
    $sorgu->execute([$firma_adi, $sifre]);
    $firma = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($firma) {
        // --- GİRİŞ BAŞARILI ---
        $_SESSION['firma_giris'] = true;
        $_SESSION['firma_id'] = $firma['id'];
        $_SESSION['firma_adi'] = $firma['firma_adi'];
        
        // Kullanıcıyı içerideki panele yönlendir
        header("Location: firma_panel.php");
        exit;
    } else {
        // --- GİRİŞ HATALI ---
        $hata = "Hatalı firma adı veya şifre!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Girişi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <a href="index.php" class="top-right-btn" style="background-color:#e74c3c; border-color:#e74c3c;">Ana Sayfa</a>

    <div class="container" style="max-width: 400px; margin-top: 50px;">
        <h2>🏢 Firma Giriş Paneli</h2>
        <p>Seferlerinizi yönetmek için giriş yapınız.</p>
        <hr>
        
        <?php if($hata): ?>
            <p style="color:red; font-weight:bold; background-color: #fce4e4; padding: 10px; border-radius: 5px;">
                <?php echo $hata; ?>
            </p>
        <?php endif; ?>
        
        <?php if(isset($_GET['durum']) && $_GET['durum'] == 'kayitbasarili'): ?>
            <p style="color:green; font-weight:bold; background-color: #e4fce4; padding: 10px; border-radius: 5px;">
                Kayıt başarılı! Şimdi giriş yapabilirsiniz.
            </p>
        <?php endif; ?>

        <form method="POST">
            <div style="display:flex; flex-direction:column; gap:15px; margin-top:20px;">
                
                <label style="text-align:left; font-weight:bold;">Firma Adı:</label>
                <input type="text" name="firma_adi" required style="width:100%; box-sizing:border-box; padding: 10px;" placeholder="Örn: Kamil Koç">
                
                <label style="text-align:left; font-weight:bold;">Şifre:</label>
                <input type="password" name="sifre" required style="width:100%; box-sizing:border-box; padding: 10px;" placeholder="******">
                
                <br>
                <button type="submit" class="btn" style="width:100%; background-color:#8e44ad; padding: 12px;">Giriş Yap</button>
                
                <a href="firma_kayit.php" class="btn" style="width:100%; box-sizing:border-box; background-color:#27ae60; text-align:center; padding: 12px;">
                    Hesabınız yok mu? Kayıt Ol
                </a>
            </div>
        </form>
    </div>
</body>
</html>