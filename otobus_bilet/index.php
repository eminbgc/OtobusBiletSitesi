<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Otobüs Bileti Ara</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<a href="firma_giris.php" class="top-left-btn">🏢 Firma Girişi</a>

<a href="giris.php" style="
    position: fixed;
    top: 25px;
    right: 30px;
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 10px 20px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    border: 2px solid rgba(255,255,255,0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    font-family: sans-serif;">
    Giriş Yap / Biletlerim
</a>

<div class="container">
    <h2>🚌 Otobüs Bileti Ara</h2>
    <form action="seferler.php" method="GET" class="search-box">
        
        <select name="nereden" required>
            <option value="" disabled selected>Nereden</option>
            <?php
            // 81 İLİN TAM LİSTESİ
            $iller = [
                "Adana", "Adıyaman", "Afyonkarahisar", "Ağrı", "Amasya", "Ankara", "Antalya", "Artvin", "Aydın", "Balıkesir", 
                "Bilecik", "Bingöl", "Bitlis", "Bolu", "Burdur", "Bursa", "Çanakkale", "Çankırı", "Çorum", "Denizli", 
                "Diyarbakır", "Edirne", "Elazığ", "Erzincan", "Erzurum", "Eskişehir", "Gaziantep", "Giresun", "Gümüşhane", "Hakkari", 
                "Hatay", "Isparta", "Mersin", "İstanbul", "İzmir", "Kars", "Kastamonu", "Kayseri", "Kırklareli", "Kırşehir", 
                "Kocaeli", "Konya", "Kütahya", "Malatya", "Manisa", "Kahramanmaraş", "Mardin", "Muğla", "Muş", "Nevşehir", 
                "Niğde", "Ordu", "Rize", "Sakarya", "Samsun", "Siirt", "Sinop", "Sivas", "Tekirdağ", "Tokat", 
                "Trabzon", "Tunceli", "Şanlıurfa", "Uşak", "Van", "Yozgat", "Zonguldak", "Aksaray", "Bayburt", "Karaman", 
                "Kırıkkale", "Batman", "Şırnak", "Bartın", "Ardahan", "Iğdır", "Yalova", "Karabük", "Kilis", "Osmaniye", "Düzce"
            ];
            
            foreach($iller as $il){
                echo "<option value='$il'>$il</option>";
            }
            ?>
        </select>
        <select name="nereye" required>
            <option value="" disabled selected>Nereye</option>
            <?php
            foreach($iller as $il){
                echo "<option value='$il'>$il</option>";
            }
            ?>
        </select>
        <input type="date" name="tarih" required min="<?php echo date('Y-m-d'); ?>">
        <button type="submit" class="btn">OTOBÜS ARA 🔎</button>
    </form>
</div>
</body>
</html>