-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Apr 2026 pada 08.28
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web_arsip_digital`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activities`
--

CREATE TABLE `activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `archives`
--

CREATE TABLE `archives` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `letter_number` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `original_file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `kode_surat` varchar(10) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'letter',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `kode_surat`, `slug`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Surat Masuk', NULL, 'surat-masuk', 'letter', '2025-11-26 05:53:42', '2025-11-26 05:53:42'),
(2, 'Surat Keluar', NULL, 'surat-keluar', 'letter', '2025-11-26 05:53:42', '2025-11-26 05:53:42'),
(5, 'Perizinan', NULL, 'perizinan', 'archive', '2026-01-06 16:02:12', '2026-01-06 16:02:12'),
(7, 'Keuangan', NULL, 'keuangan', 'archive', '2026-01-06 16:17:11', '2026-01-06 16:17:11'),
(8, 'Data Diri', NULL, 'data-diri', 'archive', '2026-01-07 05:29:37', '2026-01-07 05:29:37'),
(9, 'Surat Keputusan (SK)', '01', 'surat-keputusan-sk', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(10, 'Surat Undangan (SU)', '02', 'surat-undangan-su', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(11, 'Surat Permohonan (SPm)', '03', 'surat-permohonan-spm', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(12, 'Surat Pemberitahuan (SPb)', '04', 'surat-pemberitahuan-spb', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(13, 'Surat Peminjaman (SPp)', '05', 'surat-peminjaman-spp', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(14, 'Surat Pernyataan (SPn)', '06', 'surat-pernyataan-spn', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(15, 'Surat Mandat (SM)', '07', 'surat-mandat-sm', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(16, 'Surat Tugas (ST)', '08', 'surat-tugas-st', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(17, 'Surat Keterangan (SKet)', '09', 'surat-keterangan-sket', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(18, 'Surat Rekomendasi (SR)', '10', 'surat-rekomendasi-sr', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(19, 'Surat Balasan (SB)', '11', 'surat-balasan-sb', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(20, 'Surat Perintah Perjalanan Dinas (SPPD)', '12', 'surat-perintah-perjalanan-dinas-sppd', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(21, 'Sertifikat (SRT)', '13', 'sertifikat-srt', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(22, 'Perjanjian Kerja (PK)', '14', 'perjanjian-kerja-pk', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47'),
(23, 'Surat Pengantar (SPeng)', '15', 'surat-pengantar-speng', 'letter', '2026-01-22 06:00:47', '2026-01-22 06:00:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `letters`
--

CREATE TABLE `letters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) DEFAULT NULL,
  `letter_number` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `need_action` tinyint(1) NOT NULL DEFAULT 0,
  `action_status` varchar(255) DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `reply_to_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `original_file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `draft_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `letters`
--

INSERT INTO `letters` (`id`, `uuid`, `letter_number`, `category_id`, `need_action`, `action_status`, `admin_note`, `reply_to_id`, `name`, `original_file_name`, `file_path`, `draft_path`, `uploaded_at`, `created_at`, `updated_at`) VALUES
(69, '443d9bf3-98f7-432e-bf12-eb3805acaa65', '08/003/UMS/IV/2026', 1, 1, 'pending', 'Mohon berkenan untuk memberikan surat balasan untuk menanggapi surat ini', NULL, 'Surat Biasa', 'Log Konsultasi.pdf', 'letters/1775890529_surat-biasa.pdf', NULL, '2026-04-11 06:55:31', '2026-04-11 06:54:06', '2026-04-11 06:55:31'),
(71, '5ed8f547-a7d9-4b87-b706-f2dfdbd6d030', '09/001/Pem-Bat/IV/2026', 2, 1, 'pending', 'Mohon berkenan untuk memberikan tanda tangan untuk megesahkan surat ini', NULL, 'Surat Penting', 'Surat Kesepakatan Bimbingan.pdf', 'letters/1775892321_surat-penting.pdf', NULL, '2026-04-11 07:25:23', '2026-04-11 07:24:05', '2026-04-11 07:25:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_role` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `document_type` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `message_reads`
--

CREATE TABLE `message_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_26_122818_create_categories_table', 1),
(5, '2025_11_26_123010_create_letters_table', 1),
(6, '2025_11_26_134938_add_original_file_name_to_letters_table', 2),
(7, '2025_12_09_194822_create_archives_table', 3),
(8, '2025_12_10_233119_add_role_to_users_table', 4),
(9, '2025_12_29_204715_add_action_columns_to_letters_table', 5),
(10, '2026_01_06_224636_add_type_to_categories_table', 6),
(11, '2026_01_10_200515_create_activities_table', 7),
(12, '2026_01_19_203822_add_uuid_to_letters_table', 8),
(13, '2026_01_19_204315_add_uuid_to_archives_table', 9),
(14, '2026_01_21_090244_add_letter_number_to_letters_table', 10),
(15, '2026_01_21_124046_add_letter_number_to_archives_table', 11),
(16, '2026_01_21_233908_create_messages_table', 12),
(17, '2026_01_22_125247_add_kode_surat_to_categories_table', 13),
(18, '2026_02_03_002226_create_message_reads_table', 14),
(19, '2026_04_09_154441_add_draft_path_to_letters_table', 15);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('bagas111@gmail.com', '$2y$12$sGRca7ApT4EHRy2daINVKuwCdyArduTbVwccxDfRGMWMhm.S8Rc0a', '2025-12-10 16:23:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('AWmLMPvpDDxNYZY0BhFYMkPn3OGq42iYQkfHL9Me', 9, '192.168.1.17', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicWZQQkdlU1RvZzdwQkpFQ0Y1bzByaW1oTmhvS2Q2S0NlN0RwaXUwYSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xOTIuMTY4LjEuMTc6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6OTt9', 1775965839),
('E0hMsAE4FPaDDu9ZrJEgglG0GIc8C8Wlx05J3p6a', 9, '192.168.1.17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMm9wMXFxVXVwRUFaNk55QnJBSmwzVXFvZThyS1lBS3NqeUZnZlhMayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xOTIuMTY4LjEuMTc6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6OTt9', 1775966501),
('eVhwjbYV7Kor11PtmpbkXaKzgifWUSXkKo0EmiHQ', 10, '192.168.1.17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicE9aMmRtRkFDN09VZlBDTTFHSFZsYU9Pb3cxaGdnSTNKSXZ3NFlodSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly8xOTIuMTY4LjEuMTc6ODAwMC9rYWRlcy9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6MTU6ImthZGVzLmRhc2hib2FyZCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEwO30=', 1775974373),
('sn0pzXu8IevOuKpRgXb1LZGbTRzfU3q06srpSXkh', 9, '192.168.1.17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidkx4RFpLQ3ZRVjc2UzF2MEptZkJreTBSZ3l2azVaNDJHWVZwWG16USI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xOTIuMTY4LjEuMTc6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6OTt9', 1775965906);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(7, 'Bagas', 'bagas@gmail.com', 'admin', NULL, '$2y$12$6KapX56H7Fq2C8fr7xxzFuKYpMmVnXGBkxM1wRqbQaAKz5F4Av1ge', NULL, '2026-04-12 03:24:00', '2026-04-12 03:24:00'),
(8, 'Arief', 'yanto@gmail.com', 'super_role', NULL, '$2y$12$ygQIBcX302xzGhNM.9.Z1eNe.d67DI5B2ivmR0pxJl41mXIIi6B4C', NULL, '2026-04-12 03:25:03', '2026-04-12 03:25:03'),
(10, 'Arief', 'arief@gmail.com', 'kepala_desa', NULL, '$2y$12$CRO9OLHG1p0pMKQNByKvtOYnKsl4G1OWnvLBIVLgEZ6L2IAmrFnFS', NULL, '2026-04-12 04:08:02', '2026-04-12 04:08:02');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activities_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `archives`
--
ALTER TABLE `archives`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `archives_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `archives_letter_number_unique` (`letter_number`),
  ADD KEY `archives_category_id_foreign` (`category_id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_name_unique` (`name`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `letters`
--
ALTER TABLE `letters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `letters_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `letters_letter_number_unique` (`letter_number`),
  ADD KEY `letters_category_id_foreign` (`category_id`),
  ADD KEY `letters_reply_to_id_foreign` (`reply_to_id`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_sender_id_foreign` (`sender_id`),
  ADD KEY `messages_receiver_id_foreign` (`receiver_id`);

--
-- Indeks untuk tabel `message_reads`
--
ALTER TABLE `message_reads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_reads_message_id_foreign` (`message_id`),
  ADD KEY `message_reads_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activities`
--
ALTER TABLE `activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT untuk tabel `archives`
--
ALTER TABLE `archives`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `letters`
--
ALTER TABLE `letters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `message_reads`
--
ALTER TABLE `message_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `archives`
--
ALTER TABLE `archives`
  ADD CONSTRAINT `archives_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `letters`
--
ALTER TABLE `letters`
  ADD CONSTRAINT `letters_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `letters_reply_to_id_foreign` FOREIGN KEY (`reply_to_id`) REFERENCES `letters` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `message_reads`
--
ALTER TABLE `message_reads`
  ADD CONSTRAINT `message_reads_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
