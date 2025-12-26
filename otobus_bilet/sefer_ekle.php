<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Yönetim Paneli</title>
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Bu sayfaya özel ufak düzenlemeler */
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { font-weight: bold; color: #2c3e50; display: block; margin-bottom: 8px; }
        .form-group input { width: 100%; box-sizing: border-box; }
        
        /* Yan yana duracak kutular için (Saat ve Fiyat gibi) */
        .row { display: flex; gap: 20px; }
        .col { flex: 1; }
    </style>
</head>
<body>

    <a href="index.php" class="top-left-btn">Güvenli Çıkış</a>

    <div class="container">
        <h2>🚍 Yeni Sefer Ekle</h2>
        <p>Otobüsünü sefere çıkarmak için aşağıdaki bilgileri doldur.</p>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <form method="POST" action="">
            
            <div class="row">
                <div class="col form-group">
                    <label>Nereden (Kalkış)</label>
                    <input type="text" name="kalkis" placeholder="Örn: İstanbul" required>
                </div>
                <div class="col form-group">
                    <label>Nereye (Varış)</label>
                    <input type="text" name="varis" placeholder="Örn: Ankara" required>
                </div>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label>Sefer Tarihi</label>
                    <input type="date" name="tarih" required>
                </div>
                <div class="col form-group">
                    <label>Kalkış Saati</label>
                    <input type="time" name="saat" required>
                </div>
            </div>

            <div class="form-group">
                <label>Bilet Fiyatı (TL)</label>
                <input type="number" name="fiyat" placeholder="Örn: 750" required>
            </div>

            <button type="submit" name="sefer_ekle" class="btn" style="width: 100%;">✅ Seferi Sisteme Kaydet</button>

        </form>

        <?php
        if (isset($_POST['sefer_ekle'])) {
            $servername = "localhost";
            $username = "root";
            $password = ""; 
            $dbname = "otobus_db"; 

            try {
                // Veritabanı bağlantısı
                $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Formdan gelen verileri al
                $kalkis = $_POST['kalkis'];
                $varis  = $_POST['varis'];
                $tarih  = $_POST['tarih'];
                $saat   = $_POST['saat'];
                $fiyat  = $_POST['fiyat'];

                // Veritabanına ekle
                // Eğer veritabanında bu sütunlar yoksa aşağıda vereceğim SQL kodunu çalıştırman gerekir.
                $sql = "INSERT INTO seferler (kalkis_yeri, varis_yeri, tarih, saat, fiyat) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$kalkis, $varis, $tarih, $saat, $fiyat]);

                echo "<br><div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 10px;'>
                        <strong>Başarılı!</strong> Yeni sefer listeye eklendi.
                      </div>";

            } catch(PDOException $e) {
                echo "<br><div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px;'>
                        Hata: " . $e->getMessage() . "
                      </div>";
            }
        }
        ?>
        
        <br>
        <a href="seferler.php" style="text-decoration: none; color: #3498db; font-weight: bold;">
            Eklenen Seferleri Görüntüle →
        </a>

    </div>

</body>
</html>