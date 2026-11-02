-- =========================================================
-- Sistem Informasi Manajemen Inventaris Barang Kantor
-- Tugas Besar Basis Data - Kelas A2
-- =========================================================

CREATE DATABASE IF NOT EXISTS db_inventaris
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE db_inventaris;

DROP VIEW  IF EXISTS v_riwayat_transaksi;
DROP TABLE IF EXISTS barang_keluar;
DROP TABLE IF EXISTS barang_masuk;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS lokasi;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS pengguna;

-- ---------------------------------------------------------
-- Tabel pengguna (admin yang boleh login ke sistem)
-- ---------------------------------------------------------
CREATE TABLE pengguna (
  id_pengguna  INT AUTO_INCREMENT PRIMARY KEY,
  username     VARCHAR(50)  NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,          -- disimpan sebagai hash (password_hash)
  nama_lengkap VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel master
-- ---------------------------------------------------------
CREATE TABLE kategori (
  id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE lokasi (
  id_lokasi   INT AUTO_INCREMENT PRIMARY KEY,
  nama_lokasi VARCHAR(100) NOT NULL UNIQUE,
  keterangan  VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE supplier (
  id_supplier   INT AUTO_INCREMENT PRIMARY KEY,
  nama_supplier VARCHAR(100) NOT NULL,
  telepon       VARCHAR(20)  NULL,
  alamat        VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE barang (
  id_barang    INT AUTO_INCREMENT PRIMARY KEY,
  kode_barang  VARCHAR(30)   NOT NULL UNIQUE,
  nama_barang  VARCHAR(150)  NOT NULL,
  satuan       VARCHAR(20)   NOT NULL DEFAULT 'pcs',
  stok         INT           NOT NULL DEFAULT 0 CHECK (stok >= 0),
  stok_minimum INT           NOT NULL DEFAULT 5 CHECK (stok_minimum >= 0),
  harga        DECIMAL(12,2) NOT NULL DEFAULT 0 CHECK (harga >= 0),
  id_kategori  INT NOT NULL,
  id_lokasi    INT NOT NULL,
  CONSTRAINT fk_barang_kategori
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_barang_lokasi
    FOREIGN KEY (id_lokasi) REFERENCES lokasi(id_lokasi)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel transaksi
-- ---------------------------------------------------------
CREATE TABLE barang_masuk (
  id_masuk    INT AUTO_INCREMENT PRIMARY KEY,
  tanggal     DATE NOT NULL,
  jumlah      INT  NOT NULL CHECK (jumlah > 0),
  keterangan  VARCHAR(255) NULL,
  id_barang   INT NOT NULL,
  id_supplier INT NOT NULL,
  CONSTRAINT fk_masuk_barang
    FOREIGN KEY (id_barang) REFERENCES barang(id_barang)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_masuk_supplier
    FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE barang_keluar (
  id_keluar  INT AUTO_INCREMENT PRIMARY KEY,
  tanggal    DATE NOT NULL,
  jumlah     INT  NOT NULL CHECK (jumlah > 0),
  keterangan VARCHAR(255) NULL,
  id_barang  INT NOT NULL,
  CONSTRAINT fk_keluar_barang
    FOREIGN KEY (id_barang) REFERENCES barang(id_barang)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- View: gabungan barang masuk & keluar untuk halaman laporan
-- ---------------------------------------------------------
CREATE VIEW v_riwayat_transaksi AS
  SELECT 'Masuk' AS jenis, m.id_masuk AS id_transaksi, m.tanggal, m.jumlah,
         m.keterangan, b.kode_barang, b.nama_barang, b.satuan,
         s.nama_supplier AS pihak
  FROM barang_masuk m
  JOIN barang b   ON m.id_barang   = b.id_barang
  JOIN supplier s ON m.id_supplier = s.id_supplier
  UNION ALL
  SELECT 'Keluar', k.id_keluar, k.tanggal, k.jumlah,
         k.keterangan, b.kode_barang, b.nama_barang, b.satuan,
         NULL
  FROM barang_keluar k
  JOIN barang b ON k.id_barang = b.id_barang;

-- ---------------------------------------------------------
-- Data awal
-- ---------------------------------------------------------
-- Login: admin / admin123  (ganti setelah deploy)
INSERT INTO pengguna (username, password, nama_lengkap) VALUES
  ('admin', '$2y$10$xPwxHQbCrEmPWM2YY3C4IetlcxyGwC8jp.QVYoRi/bJG.C3Fey1/q', 'Administrator');

INSERT INTO kategori (nama_kategori) VALUES
  ('Elektronik'), ('Alat Tulis'), ('Perabot'), ('Kebersihan');

INSERT INTO lokasi (nama_lokasi, keterangan) VALUES
  ('Gudang A', 'Lantai 1'),
  ('Gudang B', 'Lantai 2'),
  ('Rak Kantor', 'Ruang administrasi');

INSERT INTO supplier (nama_supplier, telepon, alamat) VALUES
  ('PT Maju Jaya', '081234567890', 'Bengkulu'),
  ('CV Sumber Rejeki', '082345678901', 'Padang'),
  ('Toko ATK Sejahtera', '083456789012', 'Bengkulu');

-- Stok di bawah sudah memperhitungkan transaksi contoh di bagian bawah
INSERT INTO barang (kode_barang, nama_barang, satuan, stok, stok_minimum, harga, id_kategori, id_lokasi) VALUES
  ('BRG-001', 'Laptop 14 inci',    'unit', 10,  3, 7500000, 1, 1),
  ('BRG-002', 'Pulpen Hitam',      'pcs',  200, 50, 3000,   2, 3),
  ('BRG-003', 'Kursi Kantor',      'unit', 15,  5, 450000,  3, 2),
  ('BRG-004', 'Kertas HVS A4',     'rim',  4,   10, 55000,  2, 3),
  ('BRG-005', 'Printer Inkjet',    'unit', 3,   2, 2100000, 1, 1),
  ('BRG-006', 'Cairan Pembersih',  'botol',12,  6, 25000,   4, 2);

INSERT INTO barang_masuk (tanggal, jumlah, keterangan, id_barang, id_supplier) VALUES
  ('2026-10-01', 5,   'Pengadaan semester ganjil', 1, 1),
  ('2026-10-05', 100, 'Stok ATK bulanan',          2, 2),
  ('2026-10-08', 10,  NULL,                        4, 3),
  ('2026-10-15', 12,  NULL,                        6, 2);

INSERT INTO barang_keluar (tanggal, jumlah, keterangan, id_barang) VALUES
  ('2026-10-10', 2,  'Dipakai bagian IT',   1),
  ('2026-10-12', 20, 'Kebutuhan kantor',    2),
  ('2026-10-20', 6,  'Ruang rapat',         4);
