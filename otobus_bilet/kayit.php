<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Kayıt</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; margin-top: 50px; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 5px; width: 300px; }
        input { width: 100%; padding: 8px; margin: 5px 0 15px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>

    <form method="POST" action="">
        <h3 style="text-align:center;">Firma Kayıt Formu</h3>

        <label>Firma Adı:</label>
        <input type="text" name="firma_adi" required>

        <label>Şifre:</label>
        <input type="password" name="sifre" required>

        <label>Telefon Numarası:</label>
        <input type="text" name="telefon">

        <label>Mail Adresi:</label>
        <input type="email" name="mail">

        <button type="submit" name="kaydet">Kaydet</button>

        <?php
        if (isset($_POST['kaydet'])) {
            // 1. Veritabanı Bağlantısı
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "otobus_db"; // Buraya kendi veritabanı adını yaz

            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // 2. Verileri Al
                $ad = $_POST['firma_adi'];
                $sifre = $_POST['sifre']; // Güvenlik için password_hash($sifre, PASSWORD_DEFAULT) kullanılabilir.
                $telefon = $_POST['telefon'];
                $mail = $_POST['mail'];

                // 3. Veritabanına Ekle (Yeni tablo yapısına göre)
                $sql = "INSERT INTO firmalar (firma_adi, sifre, telefon, mail) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$ad, $sifre, $telefon, $mail]);

                echo "<p style='color:green; text-align:center;'>Yeni firma başarıyla kaydedildi!</p>";

            } catch(PDOException $e) {
                echo "<p style='color:red; text-align:center;'>Hata: " . $e->getMessage() . "</p>";
            }
        }
        ?>
    </form>

</body>
</html>