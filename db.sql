-- =========================================================
-- CSDL: QUẢN LÝ VẬN TẢI VÀ GIAO NHẬN HÀNG HÓA
-- Dùng cho XAMPP / phpMyAdmin (MySQL / MariaDB)
-- Gồm 16 bảng
-- =========================================================

CREATE DATABASE IF NOT EXISTS QuanLyVanTai
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE QuanLyVanTai;


-- =========================================================
-- 1. BẢNG VAI TRÒ
-- =========================================================

CREATE TABLE VaiTro (
    MaVaiTro INT AUTO_INCREMENT PRIMARY KEY,
    TenVaiTro VARCHAR(50) NOT NULL,
    MoTa VARCHAR(255)
) ENGINE=InnoDB;


-- =========================================================
-- 2. BẢNG TÀI KHOẢN
-- =========================================================

CREATE TABLE TaiKhoan (
    MaTaiKhoan INT AUTO_INCREMENT PRIMARY KEY,
    MaVaiTro INT NOT NULL,
    TenDangNhap VARCHAR(100) NOT NULL UNIQUE,
    MatKhau VARCHAR(255) NOT NULL,
    TrangThai VARCHAR(30) DEFAULT 'Hoat dong',
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_TaiKhoan_VaiTro
        FOREIGN KEY (MaVaiTro)
        REFERENCES VaiTro(MaVaiTro)
) ENGINE=InnoDB;


-- =========================================================
-- 3. BẢNG KHÁCH HÀNG
-- =========================================================

CREATE TABLE KhachHang (
    MaKhachHang INT AUTO_INCREMENT PRIMARY KEY,
    MaTaiKhoan INT NOT NULL UNIQUE,
    HoTen VARCHAR(100) NOT NULL,
    SoDienThoai VARCHAR(20),
    Email VARCHAR(100),
    DiaChi VARCHAR(255),
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_KhachHang_TaiKhoan
        FOREIGN KEY (MaTaiKhoan)
        REFERENCES TaiKhoan(MaTaiKhoan)
) ENGINE=InnoDB;


-- =========================================================
-- 4. BẢNG NHÂN VIÊN
-- =========================================================

CREATE TABLE NhanVien (
    MaNhanVien INT AUTO_INCREMENT PRIMARY KEY,
    MaTaiKhoan INT NOT NULL UNIQUE,
    HoTen VARCHAR(100) NOT NULL,
    SoDienThoai VARCHAR(20),
    Email VARCHAR(100),
    ChucVu VARCHAR(100),
    TrangThai VARCHAR(30) DEFAULT 'Dang lam viec',

    CONSTRAINT FK_NhanVien_TaiKhoan
        FOREIGN KEY (MaTaiKhoan)
        REFERENCES TaiKhoan(MaTaiKhoan)
) ENGINE=InnoDB;


-- =========================================================
-- 5. BẢNG PHƯƠNG TIỆN
-- =========================================================

CREATE TABLE PhuongTien (
    MaPhuongTien INT AUTO_INCREMENT PRIMARY KEY,
    BienSo VARCHAR(20) NOT NULL UNIQUE,
    LoaiPhuongTien VARCHAR(50),
    TaiTrong DECIMAL(10,2),
    TrangThai VARCHAR(30) DEFAULT 'San sang',
    MoTa VARCHAR(255)
) ENGINE=InnoDB;


-- =========================================================
-- 6. BẢNG TÀI XẾ
-- =========================================================

CREATE TABLE TaiXe (
    MaTaiXe INT AUTO_INCREMENT PRIMARY KEY,
    MaTaiKhoan INT NOT NULL UNIQUE,
    MaPhuongTien INT,
    HoTen VARCHAR(100) NOT NULL,
    SoDienThoai VARCHAR(20),
    SoBangLai VARCHAR(50),
    KhuVucHienTai VARCHAR(100),
    ThoiGianCapNhatViTri DATETIME,
    TrangThai VARCHAR(30) DEFAULT 'San sang',
    DiaChi VARCHAR(255),
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_TaiXe_TaiKhoan
        FOREIGN KEY (MaTaiKhoan)
        REFERENCES TaiKhoan(MaTaiKhoan),

    CONSTRAINT FK_TaiXe_PhuongTien
        FOREIGN KEY (MaPhuongTien)
        REFERENCES PhuongTien(MaPhuongTien)
) ENGINE=InnoDB;


-- =========================================================
-- 7. BẢNG HÀNG HÓA
-- =========================================================

CREATE TABLE HangHoa (
    MaHangHoa INT AUTO_INCREMENT PRIMARY KEY,
    TenHangHoa VARCHAR(150) NOT NULL,
    LoaiHang VARCHAR(100),
    DonViTinh VARCHAR(30),
    KhoiLuong DECIMAL(10,2),
    MoTa VARCHAR(255),
    TrangThai VARCHAR(30) DEFAULT 'Dang su dung'
) ENGINE=InnoDB;


-- =========================================================
-- 8. BẢNG ĐIỂM NHẬN
-- =========================================================

CREATE TABLE DiemNhan (
    MaDiemNhan INT AUTO_INCREMENT PRIMARY KEY,
    DiaChi VARCHAR(255) NOT NULL,
    KhuVuc VARCHAR(100),
    TinhThanh VARCHAR(100),
    QuanHuyen VARCHAR(100),
    PhuongXa VARCHAR(100),
    SoDienThoai VARCHAR(20),
    GhiChu VARCHAR(255)
) ENGINE=InnoDB;


-- =========================================================
-- 9. BẢNG ĐIỂM GIAO
-- =========================================================

CREATE TABLE DiemGiao (
    MaDiemGiao INT AUTO_INCREMENT PRIMARY KEY,
    TenNguoiNhan VARCHAR(100) NOT NULL,
    SoDienThoai VARCHAR(20),
    DiaChi VARCHAR(255) NOT NULL,
    KhuVuc VARCHAR(100),
    TinhThanh VARCHAR(100),
    QuanHuyen VARCHAR(100),
    PhuongXa VARCHAR(100),
    GhiChu VARCHAR(255)
) ENGINE=InnoDB;


-- =========================================================
-- 10. BẢNG TUYẾN GIAO
-- =========================================================

CREATE TABLE TuyenGiao (
    MaTuyenGiao INT AUTO_INCREMENT PRIMARY KEY,
    TenTuyen VARCHAR(100) NOT NULL,
    KhuVucDi VARCHAR(100),
    KhuVucDen VARCHAR(100),
    MoTa VARCHAR(255)
) ENGINE=InnoDB;


-- =========================================================
-- 11. BẢNG PHÍ VẬN CHUYỂN
-- =========================================================

CREATE TABLE PhiVanChuyen (
    MaPhi INT AUTO_INCREMENT PRIMARY KEY,
    TenMucPhi VARCHAR(100) NOT NULL,
    KhuVucDi VARCHAR(100),
    KhuVucDen VARCHAR(100),
    KhoiLuongTu DECIMAL(10,2),
    KhoiLuongDen DECIMAL(10,2),
    PhiCoBan DECIMAL(18,2),
    PhiVuotKhoiLuong DECIMAL(18,2),
    TrangThai VARCHAR(30) DEFAULT 'Dang ap dung',
    NgayApDung DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- =========================================================
-- 12. BẢNG ĐƠN HÀNG
-- =========================================================

CREATE TABLE DonHang (
    MaDonHang INT AUTO_INCREMENT PRIMARY KEY,

    MaKhachHang INT NOT NULL,
    MaDiemNhan INT NOT NULL,
    MaDiemGiao INT NOT NULL,
    MaTuyenGiao INT,
    MaPhi INT,

    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,

    TienHang DECIMAL(18,2) DEFAULT 0,
    TongKhoiLuong DECIMAL(10,2) DEFAULT 0,

    -- Phí thực tế đã áp dụng cho đơn
    PhiVanChuyen DECIMAL(18,2) DEFAULT 0,

    PhiHoan DECIMAL(18,2) DEFAULT 0,
    TongPhi DECIMAL(18,2) DEFAULT 0,

    TrangThai VARCHAR(50) DEFAULT 'Cho xac nhan',

    LyDoHuy VARCHAR(255),
    LyDoHoan VARCHAR(255),

    NguoiChiuPhi VARCHAR(50),

    CONSTRAINT FK_DonHang_KhachHang
        FOREIGN KEY (MaKhachHang)
        REFERENCES KhachHang(MaKhachHang),

    CONSTRAINT FK_DonHang_DiemNhan
        FOREIGN KEY (MaDiemNhan)
        REFERENCES DiemNhan(MaDiemNhan),

    CONSTRAINT FK_DonHang_DiemGiao
        FOREIGN KEY (MaDiemGiao)
        REFERENCES DiemGiao(MaDiemGiao),

    CONSTRAINT FK_DonHang_TuyenGiao
        FOREIGN KEY (MaTuyenGiao)
        REFERENCES TuyenGiao(MaTuyenGiao),

    CONSTRAINT FK_DonHang_PhiVanChuyen
        FOREIGN KEY (MaPhi)
        REFERENCES PhiVanChuyen(MaPhi)
) ENGINE=InnoDB;


-- =========================================================
-- 13. BẢNG CHI TIẾT ĐƠN HÀNG
-- =========================================================

CREATE TABLE ChiTietDonHang (
    MaChiTiet INT AUTO_INCREMENT PRIMARY KEY,
    MaDonHang INT NOT NULL,
    MaHangHoa INT NOT NULL,

    SoLuong INT NOT NULL,
    DonGia DECIMAL(18,2) NOT NULL,
    KhoiLuong DECIMAL(10,2) NOT NULL,
    ThanhTien DECIMAL(18,2) NOT NULL,

    CONSTRAINT FK_ChiTietDonHang_DonHang
        FOREIGN KEY (MaDonHang)
        REFERENCES DonHang(MaDonHang)
        ON DELETE CASCADE,

    CONSTRAINT FK_ChiTietDonHang_HangHoa
        FOREIGN KEY (MaHangHoa)
        REFERENCES HangHoa(MaHangHoa)
) ENGINE=InnoDB;


-- =========================================================
-- 14. BẢNG PHÂN CÔNG
-- =========================================================

CREATE TABLE PhanCong (
    MaPhanCong INT AUTO_INCREMENT PRIMARY KEY,
    MaDonHang INT NOT NULL,
    MaTaiXe INT NOT NULL,
    MaNhanVien INT NULL,

    ThoiGianPhanCong DATETIME DEFAULT CURRENT_TIMESTAMP,
    TrangThai VARCHAR(30) DEFAULT 'Dang phan cong',
    GhiChu VARCHAR(255),

    CONSTRAINT FK_PhanCong_DonHang
        FOREIGN KEY (MaDonHang)
        REFERENCES DonHang(MaDonHang)
        ON DELETE CASCADE,

    CONSTRAINT FK_PhanCong_TaiXe
        FOREIGN KEY (MaTaiXe)
        REFERENCES TaiXe(MaTaiXe),

    CONSTRAINT FK_PhanCong_NhanVien
        FOREIGN KEY (MaNhanVien)
        REFERENCES NhanVien(MaNhanVien)
) ENGINE=InnoDB;


-- =========================================================
-- 15. BẢNG LỊCH SỬ TRẠNG THÁI
-- =========================================================

CREATE TABLE LichSuTrangThai (
    MaLichSu INT AUTO_INCREMENT PRIMARY KEY,
    MaDonHang INT NOT NULL,

    TrangThai VARCHAR(50) NOT NULL,
    ThoiGian DATETIME DEFAULT CURRENT_TIMESTAMP,

    MaTaiKhoan INT,
    GhiChu VARCHAR(255),

    CONSTRAINT FK_LichSuTrangThai_DonHang
        FOREIGN KEY (MaDonHang)
        REFERENCES DonHang(MaDonHang)
        ON DELETE CASCADE,

    CONSTRAINT FK_LichSuTrangThai_TaiKhoan
        FOREIGN KEY (MaTaiKhoan)
        REFERENCES TaiKhoan(MaTaiKhoan)
) ENGINE=InnoDB;


-- =========================================================
-- 16. BẢNG COD
-- =========================================================

CREATE TABLE COD (
    MaCOD INT AUTO_INCREMENT PRIMARY KEY,
    MaDonHang INT NOT NULL UNIQUE,

    SoTienCOD DECIMAL(18,2) NOT NULL DEFAULT 0,

    TrangThai VARCHAR(30) DEFAULT 'Chua thu',
    ThoiGianThu DATETIME,
    GhiChu VARCHAR(255),

    CONSTRAINT FK_COD_DonHang
        FOREIGN KEY (MaDonHang)
        REFERENCES DonHang(MaDonHang)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- KẾT THÚC TẠO CSDL
-- =========================================================
USE QuanLyVanTai;


-- =========================================================
-- 1. DỮ LIỆU BẢNG VAI TRÒ
-- =========================================================

INSERT INTO VaiTro (MaVaiTro, TenVaiTro, MoTa) VALUES
(1, 'Khach hang', 'Người sử dụng dịch vụ vận chuyển'),
(2, 'Nhan vien dieu phoi', 'Tiếp nhận và điều phối đơn hàng'),
(3, 'Tai xe', 'Thực hiện nhận và giao hàng'),
(4, 'Quan tri vien', 'Quản lý hệ thống và tài khoản');


-- =========================================================
-- 2. DỮ LIỆU BẢNG PHƯƠNG TIỆN
-- =========================================================

INSERT INTO PhuongTien
(MaPhuongTien, BienSo, LoaiPhuongTien, TaiTrong, TrangThai, MoTa)
VALUES
(1, '29A-12345', 'Xe tai nho', 1000.00, 'San sang', 'Xe tải nhỏ phục vụ giao hàng nội thành'),
(2, '29B-67890', 'Xe tai', 2500.00, 'San sang', 'Xe tải phục vụ vận chuyển hàng hóa'),
(3, '30C-24680', 'Xe ban tai', 1500.00, 'Dang chay', 'Xe bán tải phục vụ giao hàng'),
(4, '30D-13579', 'Xe tai', 3000.00, 'Bao tri', 'Xe tải đang bảo trì');


-- =========================================================
-- 3. DỮ LIỆU BẢNG TÀI KHOẢN
-- =========================================================

INSERT INTO TaiKhoan
(MaTaiKhoan, MaVaiTro, TenDangNhap, MatKhau, TrangThai, NgayTao)
VALUES
(1, 4, 'admin', '123456', 'Hoat dong', '2026-09-01 08:00:00'),
(2, 2, 'dieuphoi01', '123456', 'Hoat dong', '2026-09-01 08:10:00'),
(3, 3, 'taixe01', '123456', 'Hoat dong', '2026-09-01 08:20:00'),
(4, 3, 'taixe02', '123456', 'Hoat dong', '2026-09-01 08:30:00'),
(5, 1, 'khachhang01', '123456', 'Hoat dong', '2026-09-01 09:00:00'),
(6, 1, 'khachhang02', '123456', 'Hoat dong', '2026-09-01 09:10:00');


-- =========================================================
-- 4. DỮ LIỆU BẢNG NHÂN VIÊN
-- =========================================================

INSERT INTO NhanVien
(MaNhanVien, MaTaiKhoan, HoTen, SoDienThoai, Email, ChucVu, TrangThai)
VALUES
(1, 2, 'Nguyen Van Hung', '0901234567',
 'hung@quanlyvantai.vn', 'Nhan vien dieu phoi', 'Dang lam viec');


-- =========================================================
-- 5. DỮ LIỆU BẢNG TÀI XẾ
-- =========================================================

INSERT INTO TaiXe
(MaTaiXe, MaTaiKhoan, MaPhuongTien, HoTen, SoDienThoai,
 SoBangLai, KhuVucHienTai, ThoiGianCapNhatViTri,
 TrangThai, DiaChi, NgayTao)
VALUES
(1, 3, 1, 'Tran Van Nam', '0912345678',
 'B2-123456', 'Thanh Xuan',
 '2026-09-20 08:00:00',
 'San sang', 'Ha Noi', '2026-09-01 08:20:00'),

(2, 4, 2, 'Le Van Minh', '0923456789',
 'C1-234567', 'Cau Giay',
 '2026-09-20 08:15:00',
 'San sang', 'Ha Noi', '2026-09-01 08:30:00');


-- =========================================================
-- 6. DỮ LIỆU BẢNG KHÁCH HÀNG
-- =========================================================

INSERT INTO KhachHang
(MaKhachHang, MaTaiKhoan, HoTen, SoDienThoai, Email, DiaChi, NgayTao)
VALUES
(1, 5, 'Nguyen Thi Lan', '0987654321',
 'lan@gmail.com', 'Thanh Xuan, Ha Noi',
 '2026-09-01 09:00:00'),

(2, 6, 'Pham Van An', '0976543210',
 'an@gmail.com', 'Cau Giay, Ha Noi',
 '2026-09-01 09:10:00');


-- =========================================================
-- 7. DỮ LIỆU BẢNG HÀNG HÓA
-- =========================================================

INSERT INTO HangHoa
(MaHangHoa, TenHangHoa, LoaiHang, DonViTinh,
 KhoiLuong, MoTa, TrangThai)
VALUES
(1, 'Thung nuoc uong', 'Do uong', 'Thung',
 10.00, 'Nuoc dong chai', 'Dang su dung'),

(2, 'Thung hang gia dung', 'Gia dung', 'Thung',
 15.00, 'Do gia dung thong thuong', 'Dang su dung'),

(3, 'May in', 'Thiet bi dien tu', 'Cai',
 8.00, 'May in van phong', 'Dang su dung'),

(4, 'Quan ao', 'Thoi trang', 'Kg',
 5.00, 'Quan ao dong goi', 'Dang su dung');


-- =========================================================
-- 8. DỮ LIỆU BẢNG ĐIỂM NHẬN
-- =========================================================

INSERT INTO DiemNhan
(MaDiemNhan, DiaChi, KhuVuc, TinhThanh,
 QuanHuyen, PhuongXa, SoDienThoai, GhiChu)
VALUES
(1, 'So 10 Nguyen Trai', 'Thanh Xuan', 'Ha Noi',
 'Thanh Xuan', 'Thuong Dinh', '0901111111',
 'Nhan hang tai cua'),

(2, 'So 25 Xuan Thuy', 'Cau Giay', 'Ha Noi',
 'Cau Giay', 'Dich Vong Hau', '0902222222',
 'Lien he truoc khi den'),

(3, 'So 50 Tran Duy Hung', 'Cau Giay', 'Ha Noi',
 'Cau Giay', 'Trung Hoa', '0903333333',
 'Nhan hang trong gio hanh chinh');


-- =========================================================
-- 9. DỮ LIỆU BẢNG ĐIỂM GIAO
-- =========================================================

INSERT INTO DiemGiao
(MaDiemGiao, TenNguoiNhan, SoDienThoai, DiaChi,
 KhuVuc, TinhThanh, QuanHuyen, PhuongXa, GhiChu)
VALUES
(1, 'Do Van Binh', '0931111111',
 'So 20 Le Van Luong', 'Thanh Xuan', 'Ha Noi',
 'Thanh Xuan', 'Nhan Chinh', 'Giao trong buoi sang'),

(2, 'Hoang Thi Mai', '0932222222',
 'So 15 Cau Giay', 'Cau Giay', 'Ha Noi',
 'Cau Giay', 'Quan Hoa', 'Goi dien truoc khi giao'),

(3, 'Vu Van Long', '0933333333',
 'So 100 Giai Phong', 'Hai Ba Trung', 'Ha Noi',
 'Hai Ba Trung', 'Dong Tam', 'Giao gio hanh chinh');


-- =========================================================
-- 10. DỮ LIỆU BẢNG TUYẾN GIAO
-- =========================================================

INSERT INTO TuyenGiao
(MaTuyenGiao, TenTuyen, KhuVucDi, KhuVucDen, MoTa)
VALUES
(1, 'Thanh Xuan - Cau Giay',
 'Thanh Xuan', 'Cau Giay',
 'Tuyen giao noi thanh Ha Noi'),

(2, 'Cau Giay - Hai Ba Trung',
 'Cau Giay', 'Hai Ba Trung',
 'Tuyen giao noi thanh Ha Noi'),

(3, 'Thanh Xuan - Hai Ba Trung',
 'Thanh Xuan', 'Hai Ba Trung',
 'Tuyen giao noi thanh Ha Noi');


-- =========================================================
-- 11. DỮ LIỆU BẢNG PHÍ VẬN CHUYỂN
-- =========================================================

INSERT INTO PhiVanChuyen
(MaPhi, TenMucPhi, KhuVucDi, KhuVucDen,
 KhoiLuongTu, KhoiLuongDen, PhiCoBan,
 PhiVuotKhoiLuong, TrangThai, NgayApDung)
VALUES
(1, 'Noi thanh duoi 5kg',
 'Thanh Xuan', 'Cau Giay',
 0.00, 5.00, 30000.00,
 5000.00, 'Dang ap dung', '2026-09-01 00:00:00'),

(2, 'Noi thanh 5kg - 10kg',
 'Thanh Xuan', 'Cau Giay',
 5.01, 10.00, 40000.00,
 5000.00, 'Dang ap dung', '2026-09-01 00:00:00'),

(3, 'Noi thanh duoi 10kg',
 'Cau Giay', 'Hai Ba Trung',
 0.00, 10.00, 35000.00,
 5000.00, 'Dang ap dung', '2026-09-01 00:00:00'),

(4, 'Noi thanh tren 10kg',
 'Thanh Xuan', 'Hai Ba Trung',
 10.01, 30.00, 50000.00,
 5000.00, 'Dang ap dung', '2026-09-01 00:00:00');


-- =========================================================
-- 12. DỮ LIỆU BẢNG ĐƠN HÀNG
-- =========================================================

INSERT INTO DonHang
(MaDonHang, MaKhachHang, MaDiemNhan, MaDiemGiao,
 MaTuyenGiao, MaPhi, NgayTao,
 TienHang, TongKhoiLuong, PhiVanChuyen,
 PhiHoan, TongPhi, TrangThai,
 LyDoHuy, LyDoHoan, NguoiChiuPhi)
VALUES

-- Đơn đã hoàn tất
(1, 1, 1, 2, 1, 1,
 '2026-09-20 08:30:00',
 500000.00, 4.00, 30000.00,
 0.00, 30000.00, 'Hoan tat',
 NULL, NULL, 'Khach hang'),

-- Đơn đang vận chuyển
(2, 2, 2, 3, 2, 3,
 '2026-09-20 09:00:00',
 1200000.00, 8.00, 35000.00,
 0.00, 35000.00, 'Dang van chuyen',
 NULL, NULL, 'Khach hang'),

-- Đơn chờ phân công
(3, 1, 3, 1, 1, 2,
 '2026-09-20 10:00:00',
 800000.00, 7.00, 40000.00,
 0.00, 40000.00, 'Cho phan cong',
 NULL, NULL, 'Khach hang'),

-- Đơn đã hủy
(4, 2, 1, 3, 3, 1,
 '2026-09-20 10:30:00',
 300000.00, 3.00, 30000.00,
 0.00, 30000.00, 'Da huy',
 'Khach hang yeu cau huy don',
 NULL, 'Khach hang'),

-- Đơn hoàn hàng
(5, 1, 2, 1, 2, 3,
 '2026-09-20 11:00:00',
 650000.00, 6.00, 35000.00,
 20000.00, 55000.00, 'Hoan hang',
 NULL, 'Nguoi nhan tu choi nhan hang',
 'Khach hang');


-- =========================================================
-- 13. DỮ LIỆU CHI TIẾT ĐƠN HÀNG
-- =========================================================

INSERT INTO ChiTietDonHang
(MaChiTiet, MaDonHang, MaHangHoa,
 SoLuong, DonGia, KhoiLuong, ThanhTien)
VALUES

(1, 1, 1,
 2, 250000.00, 4.00, 500000.00),

(2, 2, 3,
 1, 1200000.00, 8.00, 1200000.00),

(3, 3, 2,
 2, 400000.00, 7.00, 800000.00),

(4, 4, 4,
 1, 300000.00, 3.00, 300000.00),

(5, 5, 2,
 1, 650000.00, 6.00, 650000.00);


-- =========================================================
-- 14. DỮ LIỆU PHÂN CÔNG
-- LƯU Ý:
-- Không có MaPhuongTien.
-- Phương tiện lấy từ TaiXe.MaPhuongTien.
-- =========================================================

INSERT INTO PhanCong
(MaPhanCong, MaDonHang, MaTaiXe, MaNhanVien,
 ThoiGianPhanCong, TrangThai, GhiChu)
VALUES

(1, 1, 1, 1,
 '2026-09-20 09:00:00',
 'Hoan tat',
 'Da giao hang thanh cong'),

(2, 2, 2, 1,
 '2026-09-20 10:00:00',
 'Dang thuc hien',
 'Tai xe dang van chuyen'),

(3, 5, 1, 1,
 '2026-09-20 12:00:00',
 'Hoan hang',
 'Don hang khong giao thanh cong');


-- =========================================================
-- 15. DỮ LIỆU LỊCH SỬ TRẠNG THÁI
-- =========================================================

INSERT INTO LichSuTrangThai
(MaLichSu, MaDonHang, TrangThai,
 ThoiGian, MaTaiKhoan, GhiChu)
VALUES

-- Đơn 1
(1, 1, 'Cho xac nhan',
 '2026-09-20 08:30:00', 5,
 'Khach hang tao don'),

(2, 1, 'Da xac nhan',
 '2026-09-20 08:40:00', 5,
 'Khach hang xac nhan don'),

(3, 1, 'Da phan cong',
 '2026-09-20 09:00:00', 2,
 'Nhan vien dieu phoi phan cong tai xe'),

(4, 1, 'Da nhan hang',
 '2026-09-20 10:00:00', 3,
 'Tai xe da nhan hang'),

(5, 1, 'Dang van chuyen',
 '2026-09-20 10:30:00', 3,
 'Dang van chuyen den diem giao'),

(6, 1, 'Da giao hang',
 '2026-09-20 11:30:00', 3,
 'Giao hang thanh cong'),

(7, 1, 'Hoan tat',
 '2026-09-20 11:35:00', 3,
 'Hoan tat don hang'),


-- Đơn 2
(8, 2, 'Cho xac nhan',
 '2026-09-20 09:00:00', 6,
 'Khach hang tao don'),

(9, 2, 'Da xac nhan',
 '2026-09-20 09:10:00', 6,
 'Khach hang xac nhan don'),

(10, 2, 'Da phan cong',
 '2026-09-20 10:00:00', 2,
 'Da phan cong tai xe'),

(11, 2, 'Da nhan hang',
 '2026-09-20 11:00:00', 4,
 'Tai xe da nhan hang'),

(12, 2, 'Dang van chuyen',
 '2026-09-20 11:30:00', 4,
 'Dang van chuyen'),


-- Đơn 4
(13, 4, 'Cho xac nhan',
 '2026-09-20 10:30:00', 6,
 'Khach hang tao don'),

(14, 4, 'Da huy',
 '2026-09-20 11:00:00', 6,
 'Khach hang yeu cau huy'),


-- Đơn 5
(15, 5, 'Cho xac nhan',
 '2026-09-20 11:00:00', 5,
 'Khach hang tao don'),

(16, 5, 'Da xac nhan',
 '2026-09-20 11:10:00', 5,
 'Khach hang xac nhan don'),

(17, 5, 'Da phan cong',
 '2026-09-20 12:00:00', 2,
 'Nhan vien phan cong tai xe'),

(18, 5, 'Dang giao hang',
 '2026-09-20 13:00:00', 3,
 'Tai xe dang giao hang'),

(19, 5, 'Giao khong thanh cong',
 '2026-09-20 14:00:00', 3,
 'Nguoi nhan tu choi nhan hang'),

(20, 5, 'Hoan hang',
 '2026-09-20 14:30:00', 3,
 'Chuyen sang hoan hang');


-- =========================================================
-- 16. DỮ LIỆU COD
-- =========================================================

INSERT INTO COD
(MaCOD, MaDonHang, SoTienCOD,
 TrangThai, ThoiGianThu, GhiChu)
VALUES

-- Đơn 1: đã thu COD
(1, 1, 500000.00,
 'Da thu',
 '2026-09-20 11:35:00',
 'Da thu COD khi giao hang'),

-- Đơn 2: chưa thu
(2, 2, 1200000.00,
 'Chua thu',
 NULL,
 'Chua giao hang'),

-- Đơn 3: chưa thu
(3, 3, 800000.00,
 'Chua thu',
 NULL,
 'Don hang chua phan cong'),

-- Đơn 5: không thu do hoàn hàng
(4, 5, 650000.00,
 'Khong thu',
 NULL,
 'Nguoi nhan tu choi nhan hang');