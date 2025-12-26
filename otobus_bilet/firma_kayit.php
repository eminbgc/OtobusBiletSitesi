<?php
include 'baglan.php'; // Merkezi bağlantı dosyası (otobus_db)
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Kayıt</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-group { margin-bottom: 15px; text-align: left; max-width: 300px; margin: 0 auto 15px auto; }
        .form-group label { display: block; margin-bottom: 5px; color: #2c3e50; font-weight: 600; }
        .form-group input { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>

    <a href="index.php" class="top-left-btn">← Ana Sayfa</a>

    <div class="container">
        <h2>Firma Kayıt Formu</h2>

        <form method="POST" action="">
            <div class="form-group">
                <label>Firma Adı:</label>
                <input type="text" name="firma_adi" placeholder="Firma adını giriniz" required>
            </div>
            <div class="form-group">
                <label>Telefon Numarası:</label>
                <input type="text" name="telefon" placeholder="05XX..." required>
            </div>
            <div class="form-group">
                <label>Mail Adresi:</label>
                <input type="email" name="mail" placeholder="ornek@firma.com" required>
            </div>
            <div class="form-group">
                <label>Şifre Oluşturun:</label>
                <input type="password" name="sifre" placeholder="Şifrenizi belirleyin" required>
            </div>
            <button type="submit" name="kaydet" class="btn">Kaydet</button>
        </form>

        <?php
        if (isset($_POST['kaydet'])) {
            // $db değişkeni baglan.php dosyasından geliyor
            try {
                $sql = "INSERT INTO firmalar (firma_adi, telefon, mail, sifre) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $_POST['firma_adi'], 
                    $_POST['telefon'], 
                    $_POST['mail'], 
                    $_POST['sifre']
                ]);

                echo "<p style='color:green; font-weight:bold; margin-top:20px;'>
                        ✅ Kayıt Başarılı! <br>
                        <a href='firma_giris.php' style='color:#3498db;'>Giriş Yapmak İçin Tıklayın</a>
                      </p>";

            } catch(PDOException $e) {
                echo "<p style='color:red; font-weight:bold; margin-top:15px;'>❌ Hata: " . $e->getMessage() . "</p>";
            }
        }
        ?>
    </div>
</body>
</html>