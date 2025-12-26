<?php
// baglan.php dosyasını çağır (otobus_db bağlantısı)
include 'baglan.php';

// Arama yapılmamışsa ana sayfaya at
if(!isset($_GET['nereden']) || !isset($_GET['nereye'])) { 
    header("Location: index.php"); 
    exit; 
}

$nereden = htmlspecialchars($_GET['nereden']);
$nereye  = htmlspecialchars($_GET['nereye']);
$tarih   = htmlspecialchars($_GET['tarih']);

// Filtre değerleri var mı?
$min_fiyat = isset($_GET['min_fiyat']) && $_GET['min_fiyat'] != '' ? $_GET['min_fiyat'] : 0;
$max_fiyat = isset($_GET['max_fiyat']) && $_GET['max_fiyat'] != '' ? $_GET['max_fiyat'] : 99999;

// --- DİNAMİK SQL SORGUSU ---
try {
    // Temel sorgumuz (Firma ismini de çekiyoruz)
    $sql = "SELECT seferler.*, firmalar.firma_adi 
            FROM seferler 
            JOIN firmalar ON seferler.firma_id = firmalar.id 
            WHERE seferler.kalkis_yeri = ? AND seferler.varis_yeri = ? AND seferler.tarih = ?";
    
    // Parametre dizimiz
    $params = [$nereden, $nereye, $tarih];

    // Eğer kullanıcı filtre kullandıysa sorguya ekleme yapıyoruz
    if(isset($_GET['min_fiyat']) && $_GET['min_fiyat'] != "") {
        $sql .= " AND seferler.fiyat >= ?";
        $params[] = $min_fiyat;
    }

    if(isset($_GET['max_fiyat']) && $_GET['max_fiyat'] != "") {
        $sql .= " AND seferler.fiyat <= ?";
        $params[] = $max_fiyat;
    }
    
    // Fiyata göre sırala (Ucuzdan pahalıya)
    $sql .= " ORDER BY seferler.fiyat ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $sonuclar = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sefer Seçimi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Filtre alanı açılış animasyonu */
        #filter-area { display: none; margin-bottom: 20px; animation: slideDown 0.3s ease-out; }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<a href="index.php" class="top-left-btn">← Yeni Arama</a>

<div class="container">
    <h3>🚍 <?php echo "$nereden > $nereye"; ?></h3>
    <p>Tarih: <strong><?php echo $tarih; ?></strong></p>
    
    <hr>

    <div style="margin: 20px 0; display:flex; justify-content:center;">
        <button onclick="toggleFilter()" class="btn-filter-toggle">
            <span style="font-size: 1.2em;">🔍</span> Fiyata Göre Filtrele
        </button>
    </div>

    <div id="filter-area" class="filter-box">
        <form action="seferler.php" method="GET" style="display:flex; gap:10px; justify-content:center; align-items:center;">
            <input type="hidden" name="nereden" value="<?php echo $nereden; ?>">
            <input type="hidden" name="nereye" value="<?php echo $nereye; ?>">
            <input type="hidden" name="tarih" value="<?php echo $tarih; ?>">
            
            <input type="number" name="min_fiyat" placeholder="Min TL" value="<?php echo isset($_GET['min_fiyat']) ? $_GET['min_fiyat'] : ''; ?>" class="filter-input">
            <span style="font-weight:bold; color:#7f8c8d;">-</span>
            <input type="number" name="max_fiyat" placeholder="Max TL" value="<?php echo isset($_GET['max_fiyat']) ? $_GET['max_fiyat'] : ''; ?>" class="filter-input">
            
            <button type="submit" class="btn-filter-apply">Uygula</button>
            
            <?php if(isset($_GET['min_fiyat'])): ?>
                <a href="seferler.php?nereden=<?php echo $nereden; ?>&nereye=<?php echo $nereye; ?>&tarih=<?php echo $tarih; ?>" class="btn-filter-clear">Temizle</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (count($sonuclar) > 0): ?>
        
        <?php foreach($sonuclar as $sefer): ?>
            <div class="company-card">
                <div style="text-align:left;">
                    <strong style="font-size: 1.3em; color:#2c3e50;">
                        <?php echo htmlspecialchars($sefer['firma_adi']); ?>
                    </strong>
                    <br>
                    <small style="color:#7f8c8d;">
                        Kalkış Saati: <strong style="color:#333;"><?php echo $sefer['saat']; ?></strong> (2+1 Rahat)
                    </small>
                </div>
                
                <div style="text-align:right;">
                    <div style="font-weight:bold; font-size:20px; color:#27ae60; margin-bottom:5px;">
                        <?php echo $sefer['fiyat']; ?> TL
                    </div>
                    
                    <form action="koltuk.php" method="POST">
                        <input type="hidden" name="sefer_id" value="<?php echo $sefer['id']; ?>">
                        <input type="hidden" name="nereden" value="<?php echo $nereden; ?>">
                        <input type="hidden" name="nereye" value="<?php echo $nereye; ?>">
                        <input type="hidden" name="tarih" value="<?php echo $tarih; ?>">
                        <input type="hidden" name="saat" value="<?php echo $sefer['saat']; ?>">
                        <input type="hidden" name="fiyat" value="<?php echo $sefer['fiyat']; ?>">
                        <input type="hidden" name="firma" value="<?php echo $sefer['firma_adi']; ?>">
                        
                        <button type="submit" class="btn" style="background-color:#3498db;">KOLTUK SEÇ ></button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div style="padding: 30px; color: #e74c3c; font-weight: bold; background-color:#fff5f5; border-radius:10px; border:1px solid #fadbd8;">
            <p style="font-size:1.2em;">😔 Aradığınız kriterlere uygun sefer bulunamadı.</p>
            <?php if(isset($_GET['min_fiyat'])): ?>
                <a href="seferler.php?nereden=<?php echo $nereden; ?>&nereye=<?php echo $nereye; ?>&tarih=<?php echo $tarih; ?>" style="color:#3498db; text-decoration:underline;">Filtreleri Temizle ve Tekrar Dene</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleFilter() {
        var filterArea = document.getElementById('filter-area');
        if (filterArea.style.display === 'block') {
            filterArea.style.display = 'none';
        } else {
            filterArea.style.display = 'block';
        }
    }
    
    // Filtreleme yapıldıysa kutu açık kalsın
    <?php if(isset($_GET['min_fiyat']) || isset($_GET['max_fiyat'])): ?>
        document.getElementById('filter-area').style.display = 'block';
    <?php endif; ?>
</script>

</body>
</html>