<?php
ob_start();
include 'baglan.php';

if (!isset($_SESSION['firma_giris']) || $_SESSION['firma_giris'] !== true) {
    header("Location: firma_giris.php"); exit;
}
if (isset($_GET['cikis'])) {
    session_destroy(); header("Location: firma_giris.php"); exit;
}

$mesaj = "";
$aktif_firma_id = $_SESSION['firma_id']; 
$aktif_firma_adi = $_SESSION['firma_adi'];

// --- 1. RAPORLARI HESAPLA (JOIN KULLANIMI) ---
// Toplam Sefer Sayısı
$sorguSefer = $db->prepare("SELECT COUNT(*) FROM seferler WHERE firma_id = ?");
$sorguSefer->execute([$aktif_firma_id]);
$toplam_sefer = $sorguSefer->fetchColumn();

// Toplam Bilet ve Ciro (Normalize Tabloya Göre)
// Biletler -> Seferler tablosuna bağlanarak sadece bu firmaya ait biletleri sayıyoruz.
$sqlSatis = "SELECT 
                COUNT(b.id) as bilet_sayisi, 
                SUM(b.satis_fiyati) as toplam_ciro 
             FROM biletler b
             JOIN seferler s ON b.sefer_id = s.id
             WHERE s.firma_id = ?";
             
$sorguSatis = $db->prepare($sqlSatis);
$sorguSatis->execute([$aktif_firma_id]);
$veri = $sorguSatis->fetch(PDO::FETCH_ASSOC);

$toplam_bilet = $veri['bilet_sayisi'];
$toplam_ciro  = $veri['toplam_ciro'] ? $veri['toplam_ciro'] : 0;


// --- 2. YENİ SEFER KAYIT İŞLEMİ (TRANSACTION KULLANIMI) ---
if (isset($_POST['hepsini_kaydet'])) {
    try {
        $db->beginTransaction();
        
        // 1. Otobüs Ekle
        $sqlOtobus = "INSERT INTO otobusler (firma_id, plaka, marka_model, koltuk_kapasitesi) VALUES (?, ?, ?, ?)";
        $stmtOtobus = $db->prepare($sqlOtobus);
        $plaka = mb_strtoupper($_POST['plaka'], 'UTF-8');
        $stmtOtobus->execute([$aktif_firma_id, $plaka, $_POST['marka'], $_POST['kapasite']]);
        $yeni_otobus_id = $db->lastInsertId();
        
        // 2. Sefer Ekle
        $sqlSefer = "INSERT INTO seferler (firma_id, otobus_id, kalkis_yeri, varis_yeri, tarih, saat, fiyat) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtSefer = $db->prepare($sqlSefer);
        $sonuc = $stmtSefer->execute([
            $aktif_firma_id, $yeni_otobus_id, $_POST['kalkis'], $_POST['varis'], $_POST['tarih'], $_POST['saat'], $_POST['fiyat']
        ]);
        
        $db->commit(); // İki işlem de başarılıysa onayla
        if ($sonuc) {
            $mesaj = "<div class='alert-success'>✅ İşlem Başarılı! Sefer ve otobüs eklendi.</div>";
            header("Refresh: 2; url=firma_panel.php");
        }
    } catch(PDOException $e) {
        $db->rollBack();
        $mesaj = "<div class='alert-error'>Hata: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Yönetim Paneli</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* RAPOR KARTLARI TASARIMI */
        .dashboard-stats { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
        .stat-card { flex: 1; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #ddd; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-info h3 { margin: 0; font-size: 28px; font-weight: 800; color: #2c3e50; }
        .stat-info p { margin: 5px 0 0 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .stat-icon { font-size: 40px; opacity: 0.2; }
        .card-blue { border-left-color: #3498db; } .card-blue .stat-icon { color: #3498db; }
        .card-orange { border-left-color: #e67e22; } .card-orange .stat-icon { color: #e67e22; }
        .card-green { border-left-color: #2ecc71; } .card-green .stat-icon { color: #2ecc71; } .card-green h3 { color: #27ae60; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .form-section { background: #fff; padding: 30px; border-radius: 15px; border: 1px solid #eee; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .row { display: flex; gap: 20px; } .col { flex: 1; }
        .form-group { margin-bottom: 15px; text-align: left; } .form-group label { font-weight: bold; color: #2c3e50; display: block; margin-bottom: 5px; } .form-group input { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 10px; overflow: hidden; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; } th { background-color: #2c3e50; color: white; text-transform: uppercase; font-size: 13px; }
    </style>
</head>
<body>
    <a href="firma_panel.php?cikis=1" class="top-left-btn" style="background-color: #c0392b;">Çıkış Yap</a>
    
    <div class="container" style="max-width: 1000px;"> 
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="margin:0;">👋 Hoşgeldiniz, <?php echo htmlspecialchars($_SESSION['firma_adi']); ?></h2>
            <span style="background:#ecf0f1; padding:5px 10px; border-radius:20px; font-size:12px; color:#7f8c8d;">Yönetim Paneli v3.0 (3NF)</span>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card card-blue">
                <div class="stat-info">
                    <h3><?php echo $toplam_sefer; ?></h3>
                    <p>Aktif Sefer</p>
                </div>
                <div class="stat-icon"><i class="fas fa-bus"></i></div>
            </div>

            <div class="stat-card card-orange">
                <div class="stat-info">
                    <h3><?php echo $toplam_bilet; ?></h3>
                    <p>Satılan Bilet</p>
                </div>
                <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
            </div>

            <div class="stat-card card-green">
                <div class="stat-info">
                    <h3><?php echo number_format($toplam_ciro, 2, ',', '.'); ?> ₺</h3>
                    <p>Toplam Kazanç</p>
                </div>
                <div class="stat-icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>

        <?php echo $mesaj; ?>
        
        <div class="form-section">
            <h3 style="margin-top:0; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">
                🚀 Yeni Sefer Oluştur
            </h3>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col form-group">
                        <label>Kalkış Yeri</label>
                        <input type="text" name="kalkis" placeholder="Örn: İstanbul" required>
                    </div>
                    <div class="col form-group">
                        <label>Varış Yeri</label>
                        <input type="text" name="varis" placeholder="Örn: Antalya" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col form-group">
                        <label>Tarih</label>
                        <input type="date" name="tarih" required>
                    </div>
                    <div class="col form-group">
                        <label>Saat</label>
                        <input type="time" name="saat" required>
                    </div>
                    <div class="col form-group">
                        <label>Bilet Fiyatı (TL)</label>
                        <input type="number" name="fiyat" placeholder="Örn: 900" required>
                    </div>
                </div>
                <div style="margin-top: 15px; background: #f9f9f9; padding: 15px; border-radius: 8px;">
                    <label style="font-weight:bold; color:#7f8c8d;">Otobüs Bilgileri:</label>
                    <div class="row" style="margin-top:10px;">
                        <div class="col form-group">
                            <input type="text" name="plaka" placeholder="Plaka (34 AB 123)" required>
                        </div>
                        <div class="col form-group">
                            <input type="text" name="marka" placeholder="Marka (Mercedes)" required>
                        </div>
                        <div class="col form-group">
                            <input type="number" name="kapasite" placeholder="Koltuk Sayısı" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="hepsini_kaydet" class="btn" style="width: 100%; margin-top: 15px; background: linear-gradient(135deg, #27ae60, #2ecc71); font-size: 1.1em;">
                    ✅ Seferi ve Otobüsü Kaydet
                </button>
            </form>
        </div>

        <h3>📋 Sefer Listesi</h3>
        <table>
            <thead>
                <tr>
                    <th>Güzergah</th>
                    <th>Tarih / Saat</th>
                    <th>Otobüs</th>
                    <th>Fiyat</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // LEFT JOIN ile Otobüs Bilgilerini de çekiyoruz
                $sqlList = "SELECT s.*, o.plaka, o.marka_model 
                            FROM seferler s 
                            LEFT JOIN otobusler o ON s.otobus_id = o.id 
                            WHERE s.firma_id = ? 
                            ORDER BY s.id DESC";
                $sorguList = $db->prepare($sqlList);
                $sorguList->execute([$aktif_firma_id]);
                
                if ($sorguList->rowCount() == 0) {
                    echo "<tr><td colspan='4' style='text-align:center; padding: 20px; color:#7f8c8d;'>Henüz sefer eklemediniz.</td></tr>";
                }
                while ($row = $sorguList->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td><b>" . $row['kalkis_yeri'] . " > " . $row['varis_yeri'] . "</b></td>";
                    echo "<td>" . $row['tarih'] . " <span style='color:#ccc'>|</span> " . $row['saat'] . "</td>";
                    echo "<td>" . $row['plaka'] . " <small>(" . $row['marka_model'] . ")</small></td>";
                    echo "<td><span style='color:#27ae60; font-weight:bold;'>" . $row['fiyat'] . " ₺</span></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>