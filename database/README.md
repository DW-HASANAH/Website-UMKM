SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `admin` (`id_admin`, `nama`, `username`, `password_hash`, `nama_lengkap`) VALUES
(2, '', 'admin', 'password123', 'Adminkembangcerita');

CREATE TABLE `detail_keranjang` (
  `id_detail_keranjang` int(11) NOT NULL,
  `id_keranjang` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `no_hp`, `email`, `password_hash`, `alamat`) VALUES
(1, 'Azizah Naa\'ilah', '085162749968', 'azizahnaailah011@gmail', '$2y$12$I5OvEuGxTn7XyMRPbXDIWuMVOzUI97D1ah08moUHscM3W8qZf5zPG', 'Makassar');

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `tanggal_bayar` datetime DEFAULT CURRENT_TIMESTAMP,
  `status_verifikasi` varchar(30) DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `tanggal_pesanan` datetime DEFAULT CURRENT_TIMESTAMP,
  `nama_penerima` varchar(100) NOT NULL,
  `no_hp_penerima` varchar(20) NOT NULL,
  `alamat_penerima` text NOT NULL,
  `ongkos_kirim` decimal(12,2) DEFAULT '0.00',
  `catatan_pesanan` text,
  `total_harga` decimal(12,2) NOT NULL,
  `status_pesanan` varchar(50) DEFAULT 'Menunggu Pembayaran'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi` text,
  `harga` decimal(12,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT '0',
  `foto` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `produk` (`id_produk`, `id_kategori`, `id_admin`, `nama_produk`, `deskripsi`, `harga`, `stok`, `foto`, `status`) VALUES
(2, NULL, NULL, 'Krisan Tunggal', 'Bunga dengan kelopak rapet dan bentuk yang serlah. Tersedia dalam berbagai warna', 12000.00, 20, 'image_941c7aab.jpg', 'Aktif'),
(3, NULL, NULL, 'Krisan Spray', 'Memiliki beberapa kantum bunga dengan satu tangkai. Memberikan kesan lebih penuh dan ceria', 12000.00, 15, 'image_47a3a5ea.jpg', 'Aktif'),
(4, NULL, NULL, 'Calimero', 'Bunga kecil berbentuk bulat dengan kesan imut dan elegan', 14000.00, 12, 'image_e1564ffc.jpg', 'Aktif'),
(5, NULL, NULL, 'Gerbera Lokal', 'Bunga besar dengan warna cerah dan bentuk yang unik. Cocok untuk hadiah dan dekorasi.', 14000.00, 25, 'image_8cd8ffa8.jpg', 'Aktif'),
(6, NULL, NULL, 'Gerbera Premium', 'Ukuran lebih besar, kelopak lebih rapi dan warna lebih variatif. Memberikan kesan lebih mewah.', 20000.00, 24, 'image_40bcb1d3.jpg', 'Aktif'),
(7, NULL, NULL, 'Mawar Lokal', 'Simbol cinta dan kehangatan yang selalu bermakna. Tersedia dalam berbagai warna.', 18000.00, 14, 'image_9e33bfdf.jpg', 'Aktif'),
(8, NULL, NULL, 'Gompie', 'Bunga bulat kecil dengan warna yang cerah dan tahan lama. Cocok untuk buket atau rangkaian bunga.', 15000.00, 27, 'image_430f93d9.jpg', 'Aktif'),
(9, NULL, NULL, 'Aster Eco', 'Bunga kecil dengan kelopak lembut dan warna yang menenangkan. Memberikan kesan manis dan fresh.', 15000.00, 28, 'image_acbc5a81.jpg', 'Aktif'),
(10, NULL, NULL, 'Zhen Yang', 'Bunga dengan bentuk elegan dan harum yang khas. Cocok untuk acara spesial dan hadiah istimewa.', 25000.00, 20, 'image_6a71c150.jpg', 'Aktif'),
(11, NULL, NULL, 'Pikok', 'Bunga kecil dengan warna ungu, putih, atau pink. Cocok untuk buket minimalis.', 10000.00, 25, 'image_b6e725c4.jpg', 'Aktif');

CREATE TABLE `profil_toko` (
  `id_profil` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `nama_toko` varchar(150) NOT NULL,
  `deskripsi` text,
  `alamat` text,
  `no_whatsapp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto_logo` varchar(255) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `status_toko` varchar(20) DEFAULT 'Buka'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `profil_toko` (`id_profil`, `id_admin`, `nama_toko`, `deskripsi`, `alamat`, `no_whatsapp`, `email`, `foto_logo`, `instagram`, `status_toko`) VALUES
(1, NULL, 'Kembang Cerita Flowers', 'Rangkaian Kisah, Penuh Makna.', 'Stand Toko Bunga Kembang Cerita', '6281234567890', 'admin@kembangcerita.com', 'logo.jpg', 'kembangcerita.flowers', 'Buka');

CREATE TABLE `promo` (
  `id_promo` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `nama_promo` varchar(150) NOT NULL,
  `deskripsi` text,
  `jenis_diskon` varchar(20) NOT NULL,
  `nilai_diskon` decimal(12,2) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` varchar(20) DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `promo_produk` (
  `id_promo` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `testimoni` (
  `id_testimoni` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `isi_testimoni` text NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `status` varchar(20) DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `detail_keranjang`
  ADD PRIMARY KEY (`id_detail_keranjang`),
  ADD UNIQUE KEY `unik_produk_keranjang` (`id_keranjang`,`id_produk`),
  ADD KEY `id_produk` (`id_produk`);

ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD UNIQUE KEY `unik_produk_pesanan` (`id_pesanan`,`id_produk`),
  ADD KEY `id_produk` (`id_produk`);

ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`),
  ADD KEY `id_admin` (`id_admin`);

ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD UNIQUE KEY `id_pelanggan` (`id_pelanggan`);

ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_admin` (`id_admin`);

ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_admin` (`id_admin`);

ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `id_admin` (`id_admin`);

ALTER TABLE `profil_toko`
  ADD PRIMARY KEY (`id_profil`),
  ADD KEY `id_admin` (`id_admin`);

ALTER TABLE `promo`
  ADD PRIMARY KEY (`id_promo`),
  ADD KEY `id_admin` (`id_admin`);

ALTER TABLE `promo_produk`
  ADD PRIMARY KEY (`id_promo`,`id_produk`),
  ADD KEY `id_produk` (`id_produk`);

ALTER TABLE `testimoni`
  ADD PRIMARY KEY (`id_testimoni`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_admin` (`id_admin`);


ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `detail_keranjang`
  MODIFY `id_detail_keranjang` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `profil_toko`
  MODIFY `id_profil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `promo`
  MODIFY `id_promo` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `testimoni`
  MODIFY `id_testimoni` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `detail_keranjang`
  ADD CONSTRAINT `detail_keranjang_ibfk_1` FOREIGN KEY (`id_keranjang`) REFERENCES `keranjang` (`id_keranjang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON UPDATE CASCADE;

ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON UPDATE CASCADE;

ALTER TABLE `kategori`
  ADD CONSTRAINT `kategori_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE,
  ADD CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `profil_toko`
  ADD CONSTRAINT `profil_toko_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `promo`
  ADD CONSTRAINT `promo_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON UPDATE CASCADE;

ALTER TABLE `promo_produk`
  ADD CONSTRAINT `promo_produk_ibfk_1` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id_promo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `promo_produk_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `testimoni`
  ADD CONSTRAINT `testimoni_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `testimoni_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;
