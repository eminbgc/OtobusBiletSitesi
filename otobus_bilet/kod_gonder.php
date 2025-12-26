<?php
// kod_gonder.php
session_start();
// Hataları görelim
ini_set('display_errors', 1);
error_reporting(E_ALL);

// PHPMailer dosyalarını dahil et
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Formdan gelen verileri session'a al
    $_SESSION['gecici_kullanici'] = [
        'ad' => $_POST['ad'],
        'soyad' => $_POST['soyad'],
        'email' => $_POST['email'],
        'islem' => 'sorgula'
    ];

    // Kod üret
    $dogrulamaKodu = rand(100000, 999999);
    $_SESSION['dogrulama_kodu'] = $dogrulamaKodu;

    $mail = new PHPMailer(true);
    
    try {
        // --- SUNUCU AYARLARI ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // --- BURAYA KENDİ BİLGİLERİNİ GİR ---
        $mail->Username   = 'otobus.bileti.rezervasyonu@gmail.com'; 
        $mail->Password   = 'ayevxjwdxfauzftz'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // --- HATA ÇÖZÜCÜ KOD BLOĞU (SSL SORUNLARI İÇİN) ---
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        // ----------------------------------------------------

        // Gönderen ve Alıcı
        $mail->setFrom('otobus.bileti.rezervasyonu@gmail.com', 'Otobüs Bilet Sistemi'); 
        $mail->addAddress($_POST['email'], $_POST['ad'] . ' ' . $_POST['soyad']); 

        // İçerik
        $mail->isHTML(true);
        $mail->Subject = 'Giriş Doğrulama Kodunuz';
        $mail->Body    = "
            <div style='background-color:#f9f9f9; padding:20px; border:1px solid #ddd; border-radius:10px; font-family: Arial, sans-serif;'>
                <h3 style='color:#333;'>Merhaba, {$_POST['ad']}</h3>
                <p>Bilet sorgulama işlemi için doğrulama kodunuz:</p>
                <div style='font-size:32px; font-weight:bold; color:#e74c3c; letter-spacing:5px; margin:20px 0;'>
                    $dogrulamaKodu
                </div>
                <p style='color:#777; font-size:12px;'>Bu işlem güvenliğiniz için yapılmaktadır.</p>
            </div>
        ";

        $mail->send();
        
        // Başarılıysa yönlendir
        header("Location: dogrulama.php");
        exit();

    } catch (Exception $e) {
        echo "<div style='color:red; padding:20px; font-family:sans-serif;'>";
        echo "<h3>Mail Gönderilemedi!</h3>";
        echo "<b>Hata Mesajı:</b> " . $mail->ErrorInfo;
        echo "<br><br><b>Lütfen Şunları Kontrol Et:</b>";
        echo "<ul>";
        echo "<li>İnternet bağlantın var mı?</li>";
        echo "<li>XAMPP'ı kapatıp açtın mı?</li>";
        echo "<li>Antivirüs programın giden postaları engelliyor olabilir.</li>";
        echo "</ul>";
        echo "</div>";
    }
} else {
    header("Location: index.php");
}
?>