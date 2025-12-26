<?php
// Hata raporlamayı aç
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'baglan.php';

// Değişkenler
$mesaj = "";
$biletler = [];

// POST işlemi var mı kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $islem = $_POST['islem'];

    // 1. İŞLEM: BİLET SORGULAMA
    if ($islem == 'sorgula') {
        $ad = htmlspecialchars($_POST['ad']);
        $soyad = htmlspecialchars($_POST['soyad']);
        $email = htmlspecialchars($_POST['email']);
        
        // JOIN ile verileri çekiyoruz
        $sql = "SELECT 
                    b.id, b.koltuk_no, b.satis_fiyati as fiyat,
                    y.ad, y.soyad, y.email,
                    s.id as sefer_id, s.kalkis_yeri as nereden, s.varis_yeri as nereye, s.tarih, s.saat, s.firma_id,
                    f.firma_adi as firma
                FROM biletler b
                JOIN yolcular y ON b.yolcu_id = y.id
                JOIN seferler s ON b.sefer_id = s.id
                JOIN firmalar f ON s.firma_id = f.id
                WHERE y.ad=? AND y.soyad=? AND y.email=?
                ORDER BY b.id DESC";

        $sorgu = $db->prepare($sql);
        $sorgu->execute([$ad, $soyad, $email]);
        $biletler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($biletler) == 0) {
            $mesaj = "<div style='color:red; font-weight:bold;'>Girdiğiniz bilgilerle eşleşen bir bilet bulunamadı!</div>";
        }
    }

    // 2. İŞLEM: BİLET İPTAL ETME
    if ($islem == 'iptal') {
        $id = $_POST['bilet_id'];
        // Trigger sayesinde bu silme işlemi otomatik loglanacak
        $sil = $db->prepare("DELETE FROM biletler WHERE id=?");
        $sil->execute([$id]);
        
        echo "<script>alert('Biletiniz başarıyla iptal edildi.'); window.location.href='index.php';</script>";
        exit;
    }

    // 3. İŞLEM: TARİH GÜNCELLEME (Geri Getirilen Özellik)
    if ($islem == 'guncelle') {
        $bilet_id = $_POST['bilet_id'];
        $yeni_tarih = $_POST['yeni_tarih'];
        $mevcut_sefer_id = $_POST['mevcut_sefer_id'];

        try {
            // A. Mevcut sefer bilgilerini al (Nereden, Nereye, Firma ID)
            $stmtSefer = $db->prepare("SELECT kalkis_yeri, varis_yeri, firma_id, fiyat FROM seferler WHERE id = ?");
            $stmtSefer->execute([$mevcut_sefer_id]);
            $mevcutSefer = $stmtSefer->fetch(PDO::FETCH_ASSOC);

            if($mevcutSefer) {
                // B. Yeni tarihte aynı firmanın aynı güzergaha seferi var mı?
                $stmtYeni = $db->prepare("SELECT id FROM seferler 
                                          WHERE firma_id = ? 
                                          AND kalkis_yeri = ? 
                                          AND varis_yeri = ? 
                                          AND tarih = ? 
                                          LIMIT 1");
                $stmtYeni->execute([
                    $mevcutSefer['firma_id'],
                    $mevcutSefer['kalkis_yeri'],
                    $mevcutSefer['varis_yeri'],
                    $yeni_tarih
                ]);
                $yeniSefer = $stmtYeni->fetch(PDO::FETCH_ASSOC);

                if($yeniSefer) {
                    // C. Harika! Sefer bulundu. Bileti bu yeni sefere transfer et.
                    $yeni_sefer_id = $yeniSefer['id'];
                    
                    // Transaction ile güvenli güncelleme
                    $db->beginTransaction();
                    $guncelle = $db->prepare("UPDATE biletler SET sefer_id = ? WHERE id = ?");
                    $basari = $guncelle->execute([$yeni_sefer_id, $bilet_id]);
                    $db->commit();

                    if($basari){
                        $mesaj = "<div style='color:green; font-weight:bold; background-color:#d4edda; padding:10px; border-radius:5px;'>
                                    ✅ Tarih başarıyla güncellendi! Biletiniz yeni sefere ($yeni_tarih) aktarıldı. Lütfen tekrar sorgulayınız.
                                  </div>";
                        // Listeyi boşaltalım ki kullanıcı tekrar sorgulama yapsın ve yeni hali görsün
                        $biletler = []; 
                    }
                } else {
                    $mesaj = "<div style='color:red; font-weight:bold; background-color:#f8d7da; padding:10px; border-radius:5px;'>
                                ❌ Üzgünüz, seçtiğiniz tarihte ($yeni_tarih) bu firmanın seferi bulunmamaktadır.
                              </div>";
                }
            }
        } catch (Exception $e) {
            $db->rollBack();
            $mesaj = "Hata: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Detayları</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .ticket-card { margin-bottom: 30px; border: 2px solid #eee; }
        /* Güncelleme alanı stili */
        .update-area { background-color: #f1f2f6; padding: 15px; border-radius: 8px; margin-top: 15px; border: 1px dashed #ccc; }
    </style>
</head>
<body>
<div class="container">
    <h2>🎫 Bilet İşlemleri</h2>
    
    <?php echo $mesaj; ?>
    
    <?php if (count($biletler) > 0): ?>
        
        <h3 style="color:green;">Giriş Başarılı! (<?php echo count($biletler); ?> Bilet Bulundu)</h3>
        
        <?php foreach ($biletler as $bilet): ?>
            <div class="ticket-card">
                <div class="ticket-header">
                    <span><?php echo $bilet['firma']; ?></span>
                    <span><?php echo $bilet['tarih']; ?> - <?php echo $bilet['saat']; ?></span>
                </div>
                <div class="ticket-info">
                    <p><strong>Yolcu:</strong> <?php echo $bilet['ad'] . " " . $bilet['soyad']; ?></p>
                    <p><strong>Güzergah:</strong> <?php echo $bilet['nereden'] . " > " . $bilet['nereye']; ?></p>
                    <p><strong>Koltuk No:</strong> <span style="font-size:1.2em; color:red;"><?php echo $bilet['koltuk_no']; ?></span></p>
                    <p><strong>Fiyat:</strong> <?php echo $bilet['fiyat']; ?> TL</p>
                </div>
                
                <hr>
                
                <div id="date-update-area-<?php echo $bilet['id']; ?>" class="update-area" style="display:none;">
                    <form action="bilet_detay.php" method="POST">
                        <input type="hidden" name="islem" value="guncelle">
                        <input type="hidden" name="bilet_id" value="<?php echo $bilet['id']; ?>">
                        <input type="hidden" name="mevcut_sefer_id" value="<?php echo $bilet['sefer_id']; ?>">
                        
                        <label style="font-weight:bold; color:#2c3e50;">Yeni Tarih Seçiniz:</label><br>
                        <div style="display:flex; gap:10px; margin-top:5px;">
                            <input type="date" name="yeni_tarih" required min="<?php echo date('Y-m-d'); ?>" style="flex:1;">
                            <button type="submit" class="btn-guncelle" style="flex:0.5; border:none; color:white; border-radius:5px; cursor:pointer;">Kaydet</button>
                        </div>
                        <small style="color:#7f8c8d;">* Sadece aynı firmanın seferleri aranır.</small>
                    </form>
                </div>

                <div class="action-buttons" style="margin-top:15px; display:flex; justify-content:space-between;">
                    <form action="bilet_detay.php" method="POST" onsubmit="return confirm('Bu bileti iptal etmek istediğinize emin misiniz?');">
                        <input type="hidden" name="islem" value="iptal">
                        <input type="hidden" name="bilet_id" value="<?php echo $bilet['id']; ?>">
                        <button type="submit" class="btn-cancel">🚫 İptal Et</button>
                    </form>
                    
                    <button onclick="toggleUpdate(<?php echo $bilet['id']; ?>)" class="btn-update">📅 Tarihi Güncelle</button>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <?php if($_SERVER['REQUEST_METHOD'] == 'POST' && $islem == 'sorgula') : ?>
            <br>
            <a href="index.php" class="btn" style="background-color:#333;">Ana Sayfaya Dön</a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    // Güncelleme alanını açıp kapatan fonksiyon
    function toggleUpdate(id) {
        var x = document.getElementById("date-update-area-" + id);
        if (x.style.display === "block") {
            x.style.display = "none";
        } else {
            x.style.display = "block";
        }
    }
</script>
</body>
</html>