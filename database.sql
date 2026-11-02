CREATE DATABASE IF NOT EXISTS db_inventaris_tubes
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE db_inventaris_tubes;

DROP TABLE IF EXISTS barang_keluar;
DROP TABLE IF EXISTS barang_masuk;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS lokasi;
DROP TABLE IF EXISTS kategori;

CREATE TABLE kategori (
  id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE lokasi (
  id_lokasi   INT AUTO_INCREMENT PRIMARY KEY,
  nama_lokasi VARCHAR(100) NOT NULL,
  keterangan  VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE supplier (
  id_supplier   INT AUTO_INCREMENT PRIMARY KEY,
  nama_supplier VARCHAR(100) NOT NULL,
  telepon       VARCHAR(20)  NULL,
  alamat        VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE barang (
  id_barang   INT AUTO_INCREMENT PRIMARY KEY,
  kode_barang VARCHAR(30)  NOT NULL UNIQUE,
  nama_barang VARCHAR(150) NOT NULL,
  satuan      VARCHAR(20)  NOT NULL DEFAULT 'pcs',
  stok        INT          NOT NULL DEFAULT 0,
  harga       DECIMAL(12,2) NOT NULL DEFAULT 0,
  id_kategori INT NOT NULL,
  id_lokasi   INT NOT NULL,
  CONSTRAINT fk_barang_kategori
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_barang_lokasi
    FOREIGN KEY (id_lokasi) REFERENCES lokasi(id_lokasi)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE barang_masuk (
  id_masuk    INT AUTO_INCREMENT PRIMARY KEY,
  tanggal     DATE NOT NULL,
  jumlah      INT  NOT NULL CHECK (jumlah > 0),
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

INSERT INTO kategori (nama_kategori) VALUES
  ('Elektronik'), ('Alat Tulis'), ('Perabot');

INSERT INTO lokasi (nama_lokasi, keterangan) VALUES
  ('Gudang A', 'Lantai 1'),
  ('Gudang B', 'Lantai 2'),
  ('Rak Kantor', 'Ruang administrasi');

INSERT INTO supplier (nama_supplier, telepon, alamat) VALUES
  ('PT Maju Jaya', '081234567890', 'Bengkulu'),
  ('CV Sumber Rejeki', '082345678901', 'Padang');

INSERT INTO barang (kode_barang, nama_barang, satuan, stok, harga, id_kategori, id_lokasi) VALUES
  ('BRG-001', 'Laptop 14 inci', 'unit', 10, 7500000, 1, 1),
  ('BRG-002', 'Pulpen Hitam',   'pcs', 200, 3000,    2, 3),
  ('BRG-003', 'Kursi Kantor',   'unit', 15, 450000,  3, 2);

INSERT INTO barang_masuk (tanggal, jumlah, id_barang, id_supplier) VALUES
  ('2026-10-01', 5,   1, 1),
  ('2026-10-05', 100, 2, 2);

INSERT INTO barang_keluar (tanggal, jumlah, keterangan, id_barang) VALUES
  ('2026-10-10', 2, 'Dipakai bagian IT', 1),
  ('2026-10-12', 20, 'Kebutuhan kantor', 2);
