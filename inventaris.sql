CREATE DATABASE IF NOT EXISTS db_inventaris_tubes CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_inventaris_tubes;

DROP TABLE IF EXISTS barang_keluar;
DROP TABLE IF EXISTS barang_masuk;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS lokasi;
DROP TABLE IF EXISTS kategori;

CREATE TABLE kategori (
    id_kategori    INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE lokasi (
    id_lokasi    INT AUTO_INCREMENT PRIMARY KEY,
    nama_lokasi  VARCHAR(100) NOT NULL,
    keterangan   VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE supplier (
    id_supplier    INT AUTO_INCREMENT PRIMARY KEY,
    nama_supplier  VARCHAR(100) NOT NULL,
    telepon        VARCHAR(20),
    alamat         VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE barang (
    id_barang    INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang  VARCHAR(30)   NOT NULL UNIQUE,
    nama_barang  VARCHAR(150)  NOT NULL,
    id_kategori  INT NOT NULL,
    id_lokasi    INT NOT NULL,
    satuan       VARCHAR(20)   NOT NULL DEFAULT 'pcs',
    stok         INT           NOT NULL DEFAULT 0,
    harga        DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT chk_stok  CHECK (stok >= 0),
    CONSTRAINT chk_harga CHECK (harga >= 0),
    CONSTRAINT fk_barang_kategori FOREIGN KEY (id_kategori)
        REFERENCES kategori(id_kategori) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_barang_lokasi   FOREIGN KEY (id_lokasi)
        REFERENCES lokasi(id_lokasi)     ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE barang_masuk (
    id_masuk     INT AUTO_INCREMENT PRIMARY KEY,
    tanggal      DATE NOT NULL,
    id_barang    INT NOT NULL,
    id_supplier  INT NOT NULL,
    jumlah       INT NOT NULL,
    CONSTRAINT chk_masuk CHECK (jumlah > 0),
    CONSTRAINT fk_masuk_barang   FOREIGN KEY (id_barang)
        REFERENCES barang(id_barang)     ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_masuk_supplier FOREIGN KEY (id_supplier)
        REFERENCES supplier(id_supplier) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE barang_keluar (
    id_keluar   INT AUTO_INCREMENT PRIMARY KEY,
    tanggal     DATE NOT NULL,
    id_barang   INT NOT NULL,
    jumlah      INT NOT NULL,
    keterangan  VARCHAR(255),
    CONSTRAINT chk_keluar CHECK (jumlah > 0),
    CONSTRAINT fk_keluar_barang FOREIGN KEY (id_barang)
        REFERENCES barang(id_barang) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO kategori (id_kategori, nama_kategori) VALUES
  (1, 'Elektronik'),
  (2, 'Alat Tulis'),
  (3, 'Perabot');
INSERT INTO lokasi (id_lokasi, nama_lokasi, keterangan) VALUES
  (1, 'Gudang A', 'Lantai 1'),
  (2, 'Gudang B', 'Lantai 2'),
  (3, 'Rak Kantor', 'Ruang administrasi');
INSERT INTO supplier (id_supplier, nama_supplier, telepon, alamat) VALUES
  (1, 'PT Maju Jaya', '081234567890', 'Bengkulu'),
  (2, 'CV Sumber Rejeki', '082345678901', 'Padang');
INSERT INTO barang (id_barang, kode_barang, nama_barang, id_kategori, id_lokasi, satuan, stok, harga) VALUES
  (1, 'BRG-001', 'Laptop 14 inci', 1, 1, 'unit', 10, 7500000),
  (2, 'BRG-002', 'Pulpen Hitam', 2, 3, 'pcs', 200, 3000),
  (3, 'BRG-003', 'Kursi Kantor', 3, 2, 'unit', 15, 450000),
  (4, 'BRG-004', 'Mouse Wireless', 1, 1, 'unit', 8, 150000),
  (5, 'BRG-005', 'Kertas A4', 2, 3, 'rim', 50, 55000),
  (6, 'BRG-006', 'Meja Kerja', 3, 2, 'unit', 5, 1200000);
INSERT INTO barang_masuk (id_masuk, tanggal, id_barang, id_supplier, jumlah) VALUES
  (1, '2026-10-01', 1, 1, 5),
  (2, '2026-10-05', 2, 2, 100),
  (3, '2026-10-08', 4, 1, 10),
  (4, '2026-10-15', 5, 2, 60);
INSERT INTO barang_keluar (id_keluar, tanggal, id_barang, jumlah, keterangan) VALUES
  (1, '2026-10-10', 1, 2, 'Dipakai bagian IT'),
  (2, '2026-10-12', 2, 20, 'Kebutuhan kantor'),
  (3, '2026-10-18', 3, 3, 'Ruang rapat'),
  (4, '2026-10-20', 4, 2, 'Dipakai bagian IT');

INSERT INTO kategori (nama_kategori) VALUES ('Kategori Contoh');
UPDATE kategori SET nama_kategori = 'Kategori Contoh 2' WHERE nama_kategori = 'Kategori Contoh';
DELETE FROM kategori WHERE nama_kategori = 'Kategori Contoh 2';

SELECT b.kode_barang, b.nama_barang, k.nama_kategori, l.nama_lokasi, b.stok, b.satuan, b.harga
FROM barang b
JOIN kategori k ON b.id_kategori = k.id_kategori
JOIN lokasi l   ON b.id_lokasi   = l.id_lokasi
ORDER BY k.nama_kategori, b.nama_barang;

SELECT k.nama_kategori, COUNT(b.id_barang) AS jumlah_barang, COALESCE(SUM(b.stok), 0) AS total_stok
FROM kategori k LEFT JOIN barang b ON b.id_kategori = k.id_kategori
GROUP BY k.id_kategori, k.nama_kategori ORDER BY k.nama_kategori;

SELECT k.nama_kategori, COALESCE(SUM(b.stok * b.harga), 0) AS nilai_persediaan
FROM kategori k LEFT JOIN barang b ON b.id_kategori = k.id_kategori
GROUP BY k.id_kategori, k.nama_kategori ORDER BY nilai_persediaan DESC;

SELECT b.nama_barang, COUNT(m.id_masuk) AS jumlah_transaksi, SUM(m.jumlah) AS total_masuk
FROM barang_masuk m JOIN barang b ON m.id_barang = b.id_barang
GROUP BY b.id_barang, b.nama_barang ORDER BY total_masuk DESC;

SELECT b.nama_barang, COUNT(k.id_keluar) AS jumlah_transaksi, SUM(k.jumlah) AS total_keluar
FROM barang_keluar k JOIN barang b ON k.id_barang = b.id_barang
GROUP BY b.id_barang, b.nama_barang ORDER BY total_keluar DESC;

SELECT s.nama_supplier, COUNT(m.id_masuk) AS jumlah_transaksi, SUM(m.jumlah) AS total_dipasok
FROM supplier s JOIN barang_masuk m ON m.id_supplier = s.id_supplier
GROUP BY s.id_supplier, s.nama_supplier ORDER BY total_dipasok DESC;

SELECT kode_barang, nama_barang, stok, satuan FROM barang WHERE stok <= 10 ORDER BY stok;
