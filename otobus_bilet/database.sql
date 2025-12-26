
DROP DATABASE IF EXISTS otobus_db;
CREATE DATABASE otobus_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE otobus_db;

-- 1. FİRMALAR TABLOSU

CREATE TABLE firmalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firma_adi VARCHAR(100) NOT NULL,
    telefon VARCHAR(20),
    mail VARCHAR(100) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. OTOBÜSLER TABLOSU

CREATE TABLE otobusler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firma_id INT NOT NULL,
    plaka VARCHAR(20) NOT NULL UNIQUE,
    marka_model VARCHAR(100) NOT NULL,
    koltuk_kapasitesi INT DEFAULT 40 CHECK (koltuk_kapasitesi > 0),
    FOREIGN KEY (firma_id) REFERENCES firmalar(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. SEFERLER TABLOSU

CREATE TABLE seferler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firma_id INT NOT NULL,
    otobus_id INT NOT NULL,
    kalkis_yeri VARCHAR(50) NOT NULL,
    varis_yeri VARCHAR(50) NOT NULL,
    tarih DATE NOT NULL,
    saat TIME NOT NULL,
    fiyat DECIMAL(10, 2) NOT NULL CHECK (fiyat >= 0),
    FOREIGN KEY (firma_id) REFERENCES firmalar(id) ON DELETE CASCADE,
    FOREIGN KEY (otobus_id) REFERENCES otobusler(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. YOLCULAR TABLOSU 
CREATE TABLE yolcular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    cinsiyet ENUM('Erkek', 'Kadın') NOT NULL,
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. BİLETLER TABLOSU
CREATE TABLE biletler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sefer_id INT NOT NULL,
    yolcu_id INT NOT NULL,
    koltuk_no INT NOT NULL,
    satis_fiyati DECIMAL(10, 2) NOT NULL,
    islem_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_koltuk (sefer_id, koltuk_no),
    FOREIGN KEY (sefer_id) REFERENCES seferler(id) ON DELETE CASCADE,
    FOREIGN KEY (yolcu_id) REFERENCES yolcular(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. LOGLAMA TABLOSU 
CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tablo_adi VARCHAR(50),
    islem_tipi VARCHAR(20), 
    aciklama TEXT,
    islem_zamani DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Veri Ekleme 
INSERT INTO firmalar (firma_adi, telefon, mail, sifre) VALUES 
('Kamil Koç', '08501234567', 'info@kamilkoc.com', '123456'),
('Pamukkale', '08509999999', 'info@pamukkale.com', '123456'),
('Metro Turizm', '08501111111', 'iletisim@metro.com', '123456');

INSERT INTO otobusler (firma_id, plaka, marka_model, koltuk_kapasitesi) VALUES 
(1, '34 KK 101', 'Mercedes Travego', 46),
(1, '35 KK 202', 'Neoplan Tourliner', 50),
(2, '20 PA 303', 'Mercedes Tourismo', 40);

INSERT INTO seferler (firma_id, otobus_id, kalkis_yeri, varis_yeri, tarih, saat, fiyat) VALUES 
(1, 1, 'İstanbul', 'Ankara', CURDATE() + INTERVAL 1 DAY, '10:00:00', 500.00),
(1, 2, 'İzmir', 'İstanbul', CURDATE() + INTERVAL 2 DAY, '22:00:00', 750.00),
(2, 3, 'İstanbul', 'Antalya', CURDATE() + INTERVAL 3 DAY, '14:30:00', 900.00);

INSERT INTO yolcular (ad, soyad, email, cinsiyet) VALUES 
('Ahmet', 'Yılmaz', 'ahmet@mail.com', 'Erkek'),
('Ayşe', 'Demir', 'ayse@mail.com', 'Kadın'),
('Mehmet', 'Kaya', 'mehmet@mail.com', 'Erkek');

INSERT INTO biletler (sefer_id, yolcu_id, koltuk_no, satis_fiyati) VALUES 
(1, 1, 5, 500.00),
(1, 2, 6, 500.00); 

-- Veri Güncelleme 
UPDATE seferler SET fiyat = 550.00 WHERE id = 1;

-- Veri Silme (DELETE)
-- Önce bileti silelim (FK hatası almamak için, gerçi Cascade var ama örnek olsun)
DELETE FROM biletler WHERE id = 2; 



-- 1. JOIN ve ORDER BY: Bilet alan yolcuların detaylı listesi
SELECT 
    b.id AS BiletNo,
    y.ad, y.soyad,
    f.firma_adi,
    s.kalkis_yeri, s.varis_yeri,
    b.koltuk_no, b.satis_fiyati
FROM biletler b
JOIN yolcular y ON b.yolcu_id = y.id
JOIN seferler s ON b.sefer_id = s.id
JOIN firmalar f ON s.firma_id = f.id
ORDER BY b.islem_tarihi DESC;

-- 2. GROUP BY ve HAVING: Toplam cirosu 400 TL üzerinde olan firmalar
SELECT 
    f.firma_adi, 
    COUNT(b.id) as satilan_bilet, 
    SUM(b.satis_fiyati) as toplam_ciro
FROM firmalar f
JOIN seferler s ON f.id = s.firma_id
JOIN biletler b ON s.id = b.sefer_id
GROUP BY f.firma_adi
HAVING toplam_ciro > 400;

-- 3. SUBQUERY (Alt Sorgu): Ortalama bilet fiyatından daha pahalıya bilet alanlar
SELECT * FROM biletler 
WHERE satis_fiyati > (SELECT AVG(satis_fiyati) FROM biletler);

-- 4. LEFT JOIN: Hiç seferi olmayan otobüsleri bulma
SELECT o.plaka, o.marka_model
FROM otobusler o
LEFT JOIN seferler s ON o.id = s.otobus_id
WHERE s.id IS NULL;

-- 5. DATE FUNCTION: Önümüzdeki 7 gün içindeki seferler
SELECT * FROM seferler 
WHERE tarih BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY);

-- 6. COUNT ve DISTINCT: Hangi firma kaç farklı şehre sefer düzenliyor?
SELECT 
    f.firma_adi, 
    COUNT(DISTINCT s.varis_yeri) as farkli_rota_sayisi
FROM firmalar f
JOIN seferler s ON f.id = s.firma_id
GROUP BY f.firma_adi;

-- 7. CONCAT ve String İşlemleri: Yolcu isimlerini birleştirip gösterme
SELECT 
    CONCAT(UPPER(ad), ' ', UPPER(soyad)) as tam_isim, 
    email 
FROM yolcular;

-- 8. MAX/MIN: En pahalı ve en ucuz bilet fiyatları
SELECT 
    MAX(fiyat) as en_pahali, 
    MIN(fiyat) as en_ucuz 
FROM seferler;

-- 9. CASE WHEN: Bilet fiyatlarını kategorize etme
SELECT 
    kalkis_yeri, varis_yeri, fiyat,
    CASE 
        WHEN fiyat < 500 THEN 'Ekonomik'
        WHEN fiyat BETWEEN 500 AND 800 THEN 'Standart'
        ELSE 'Lüks'
    END as fiyat_kategorisi
FROM seferler;

-- 10. 3 TABLO JOIN: "Mercedes" marka otobüsle seyahat eden yolcular
SELECT y.ad, y.soyad, o.marka_model
FROM yolcular y
JOIN biletler b ON y.id = b.yolcu_id
JOIN seferler s ON b.sefer_id = s.id
JOIN otobusler o ON s.otobus_id = o.id
WHERE o.marka_model LIKE '%Mercedes%';


DELIMITER //

-- 1. Prosedür: Yeni Sefer Ekleme (Parametreli)
CREATE PROCEDURE sp_SeferEkle(
    IN p_firma_id INT,
    IN p_otobus_id INT,
    IN p_kalkis VARCHAR(50),
    IN p_varis VARCHAR(50),
    IN p_tarih DATE,
    IN p_saat TIME,
    IN p_fiyat DECIMAL(10,2)
)
BEGIN
    INSERT INTO seferler (firma_id, otobus_id, kalkis_yeri, varis_yeri, tarih, saat, fiyat)
    VALUES (p_firma_id, p_otobus_id, p_kalkis, p_varis, p_tarih, p_saat, p_fiyat);
END //

-- 2. Prosedür: Bir firmanın toplam kazancını hesaplama
CREATE PROCEDURE sp_FirmaCiroGetir(IN p_firma_id INT)
BEGIN
    SELECT 
        f.firma_adi,
        COALESCE(SUM(b.satis_fiyati), 0) as toplam_kazanc
    FROM firmalar f
    LEFT JOIN seferler s ON f.id = s.firma_id
    LEFT JOIN biletler b ON s.id = b.sefer_id
    WHERE f.id = p_firma_id
    GROUP BY f.id;
END //

-- 3. Prosedür: Bilet İptal Etme (Loglayarak siler)
CREATE PROCEDURE sp_BiletIptal(IN p_bilet_id INT)
BEGIN
    DELETE FROM biletler WHERE id = p_bilet_id;
END //

DELIMITER ;

-- Kullanım Örnekleri:
-- CALL sp_SeferEkle(1, 1, 'Bursa', 'Çanakkale', '2025-06-01', '10:00:00', 400);
-- CALL sp_FirmaCiroGetir(1);



-- 1. View: Detaylı Bilet Listesi (Tabloları birleştirip kolay okuma sağlar)
CREATE VIEW vw_BiletDetaylari AS
SELECT 
    b.id as bilet_id,
    y.ad, y.soyad,
    f.firma_adi,
    s.kalkis_yeri, s.varis_yeri, s.tarih, s.saat,
    b.koltuk_no
FROM biletler b
JOIN yolcular y ON b.yolcu_id = y.id
JOIN seferler s ON b.sefer_id = s.id
JOIN firmalar f ON s.firma_id = f.id;

-- 2. View: Aktif (Gelecek) Seferler
CREATE VIEW vw_GelecekSeferler AS
SELECT * FROM seferler 
WHERE tarih >= CURDATE();

-- 3. View: Firma Bazlı Otobüs Sayıları
CREATE VIEW vw_FirmaOtobusSayilari AS
SELECT f.firma_adi, COUNT(o.id) as otobus_sayisi
FROM firmalar f
LEFT JOIN otobusler o ON f.id = o.firma_id
GROUP BY f.firma_adi;


-- Senaryo 1: Yeni Bilet Satışı (Yolcu Kaydı + Bilet Kaydı)
-- Yolcu kaydedilir, hata yoksa bilet kesilir.
START TRANSACTION;
    INSERT INTO yolcular (ad, soyad, email, cinsiyet) 
    VALUES ('Can', 'Yücel', 'can@test.com', 'Erkek');
    
    -- Son eklenen yolcunun ID'sini al
    SET @yeni_yolcu_id = LAST_INSERT_ID();
    
    INSERT INTO biletler (sefer_id, yolcu_id, koltuk_no, satis_fiyati) 
    VALUES (1, @yeni_yolcu_id, 10, 500.00);
COMMIT;

-- Senaryo 2: Sefer İptali
-- Önce o sefere ait biletler silinmeli, sonra sefer silinmeli.
START TRANSACTION;
    DELETE FROM biletler WHERE sefer_id = 3;
    DELETE FROM seferler WHERE id = 3;
COMMIT; -- Eğer hata olursa ROLLBACK; kullanılabilir.

-- Senaryo 3: Fiyat Güncelleme (Toplu Zam)
-- İşlem sırasında hata olursa geri almak için.
START TRANSACTION;
    UPDATE seferler SET fiyat = fiyat * 1.10 WHERE firma_id = 1; -- %10 zam
    -- (Burada bir hata kontrolü simülasyonu yapılabilir)
COMMIT;


DELIMITER //

-- 1. Trigger: Yeni bilet satıldığında logla
CREATE TRIGGER trg_BiletEkle AFTER INSERT ON biletler
FOR EACH ROW
BEGIN
    INSERT INTO system_logs (tablo_adi, islem_tipi, aciklama)
    VALUES ('biletler', 'INSERT', CONCAT('Yeni bilet satıldı. SeferID: ', NEW.sefer_id, ' Koltuk: ', NEW.koltuk_no));
END //

-- 2. Trigger: Bilet güncellendiğinde (Fiyat veya Sefer değişirse) logla
DELIMITER //
CREATE TRIGGER trg_BiletGuncelle AFTER UPDATE ON biletler
FOR EACH ROW
BEGIN
    DECLARE aciklama_metni TEXT;
    
    -- Eğer sadece Sefer (Tarih) değiştiyse
    IF OLD.sefer_id != NEW.sefer_id THEN
        SET aciklama_metni = CONCAT('Bilet No: ', OLD.id, ' Tarih/Sefer Değişti. Eski SeferID: ', OLD.sefer_id, ' Yeni SeferID: ', NEW.sefer_id);
    -- Eğer Fiyat değiştiyse
    ELSEIF OLD.satis_fiyati != NEW.satis_fiyati THEN
        SET aciklama_metni = CONCAT('Bilet No: ', OLD.id, ' Fiyatı değişti. Eski: ', OLD.satis_fiyati, ' Yeni: ', NEW.satis_fiyati);
    ELSE
        SET aciklama_metni = CONCAT('Bilet No: ', OLD.id, ' güncellendi.');
    END IF;

    INSERT INTO system_logs (tablo_adi, islem_tipi, aciklama)
    VALUES ('biletler', 'UPDATE', aciklama_metni);
END //
DELIMITER ;