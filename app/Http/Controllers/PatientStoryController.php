<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk halaman publik "Cerita Pasien" (Patient Stories).
 * Menyajikan kisah inspiratif, testimoni medis nyata, dan perjalanan kesembuhan pasien
 * di Hospital Population dengan pendekatan desain editorial Evergreen.
 */
class PatientStoryController extends Controller
{
    /**
     * Menampilkan katalog cerita pasien dan kisah unggulan (featured story).
     */
    public function index(Request $request): Response
    {
        // 1. Kisah Pilihan Utama (Featured Story)
        $featuredStory = [
            'id' => 1,
            'title' => 'Kembali Memeluk Harapan: Perjalanan Ibu Rahayu Menaklukkan Aritmia Jantung Kompleks',
            'excerpt' => 'Setelah bertahun-tahun hidup dalam kecemasan akibat detak jantung tak beraturan yang tiba-tiba kambuh, tindakan ablasi kateter 3D presisi tinggi di Pusat Jantung Hospital Population mengembalikan senyum dan kualitas hidup Ibu Rahayu.',
            'full_content' => "Detak jantung yang tiba-tiba berdegup kencang hingga 180 detak per menit kerap membuat Ibu Rahayu (48 tahun) merasa cemas setiap kali beraktivitas. Berbagai pengobatan medikamentosa selama dua tahun belum memberikan hasil optimal, hingga beliau dirujuk ke Pusat Jantung & Vaskular Hospital Population.\n\nDi bawah penanganan dr. Budi Santoso, Sp.JP(K) bersama tim elektrofisiologi, Ibu Rahayu menjalani pemeriksaan pemetaan 3D elektroanatomis jantung beresolusi tinggi. Melalui prosedur ablasi kateter minimal invasif berstandar internasional, titik fokus aritmia berhasil diatasi dengan presisi tinggi tanpa perlu tindakan bedah terbuka.\n\nHanya dalam kurun waktu 48 jam pasca tindakan, Ibu Rahayu telah dapat mobilisasi mandiri dan diizinkan pulang dengan irama jantung yang kembali stabil normal. Bagi beliau dan keluarga, keberhasilan ini adalah anugerah kedua yang memulihkan impian untuk terus mendampingi putra-putrinya.",
            'patient_name' => 'Ibu Rahayu',
            'patient_age' => '48 Tahun',
            'diagnosis' => 'Aritmia Jantung (Atrial Fibrilasi Paroksismal)',
            'doctor_name' => 'dr. Budi Santoso, Sp.JP(K)',
            'poli_name' => 'Poli Jantung & Pembuluh Darah',
            'category' => 'Jantung & Vaskular',
            'read_time' => '4 menit baca',
            'published_at' => '18 Agustus 2026',
            'quote' => '"Saya merasa seperti terlahir kembali. Terima kasih untuk dokter dan tim perawat Hospital Population yang begitu teliti, ramah, dan menenangkan hati saya sejak hari pertama konsultasi."',
            'badge' => 'Ablasi Kateter 3D Minimal Invasif',
            'image_url' => 'https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?auto=format&fit=crop&w=1200&q=80',
        ];

        // 2. Daftar Katalog Cerita Pasien Berdasarkan Kategori
        $stories = [
            [
                'id' => 2,
                'title' => 'Langkah Bebas Nyeri: Kisah Pak Hendra Kembali Berjalan Pasca Operasi Penggantian Sendi Lutut',
                'excerpt' => 'Osteoarthritis stadium 4 sempat menghentikan hobi berkebun Pak Hendra. Berkat Total Knee Replacement berteknologi navigasi modern, beliau kini dapat berjalan tanpa rasa sakit.',
                'full_content' => "Rasa nyeri hebat di kedua lutut telah membatasi aktivitas Pak Hendra (62 tahun) selama hampir 3 tahun. Berjalan beberapa meter saja menjadi siksaan berat karena ausnya bantalan sendi (osteoarthritis grade 4).\n\nMelalui konsultasi mendalam di Orthopedic Center Hospital Population bersama dr. Faisal Akbar, Sp.OT, diputuskan tindakan Total Knee Replacement (TKR) berbantuan navigasi digital untuk memastikan penempatan implan dengan akurasi sub-milimeter.\n\nDengan protokol rehabilitasi medik terpadu yang dimulai sejak hari pertama pasca operasi, Pak Hendra mengalami pemulihan yang sangat cepat. Tiga minggu pasca tindakan, beliau sudah dapat berjalan lancar tanpa tongkat dan kini kembali menikmati rutinitas paginya.",
                'patient_name' => 'Pak Hendra Kusuma',
                'patient_age' => '62 Tahun',
                'diagnosis' => 'Osteoarthritis Genu Bilateral Stadium 4',
                'doctor_name' => 'dr. Faisal Akbar, Sp.OT',
                'poli_name' => 'Poli Bedah & Ortopedi',
                'category' => 'Ortopedi',
                'read_time' => '3 menit baca',
                'published_at' => '15 Agustus 2026',
                'quote' => '"Awalnya saya takut operasi sendi, namun penjelasan dokter yang sangat jelas dan fasilitas fisioterapi yang modern membuat saya optimis. Sekarang nyeri lutut benar-benar hilang."',
                'badge' => 'Total Knee Replacement Navigasi',
                'image_url' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id' => 3,
                'title' => 'Senyum Sehat Si Kecil Kenzo: Kemenangan Melawan Pneumonia Berat di Ruang PICU',
                'excerpt' => 'Ketangkasan tim IGD Anak dan perawatan intensif PICU Hospital Population menyelamatkan bayi Kenzo dari gagal napas akibat pneumonia akut.',
                'full_content' => "Malam itu suhu tubuh Kenzo (2 tahun) melonjak drastis disertai napas yang cepat dan tarikan dinding dada yang dalam. Panik melihat kondisi putranya, orang tua Kenzo segera membawanya ke IGD 24 Jam Hospital Population.\n\nTim dokter spesialis anak dan perawat intensif langsung melakukan stabilisasi oksigenasi dan penanganan ventilator non-invasif di unit PICU Level 3. Diagnosis menunjukkan pneumonia lobaris akut dengan saturasi oksigen yang sempat turun di bawah 85%.\n\nBerkat pemantauan continuous hemodinamik 24 jam dan terapi antibiotik bertarget, kondisi Kenzo menunjukkan perbaikan signifikan pada hari ketiga. Senyum ceria balita tampan ini kembali menyinari keluarga saat ia diperbolehkan pulang dalam keadaan sehat sepenuhnya.",
                'patient_name' => 'Ananda Kenzo & Keluarga',
                'patient_age' => '2 Tahun',
                'diagnosis' => 'Pneumonia Lobaris Akut & Distres Pernapasan',
                'doctor_name' => 'dr. Siti Rahmawati, Sp.A',
                'poli_name' => 'Poli Anak & Tumbuh Kembang',
                'category' => 'Ibu & Anak',
                'read_time' => '4 menit baca',
                'published_at' => '10 Agustus 2026',
                'quote' => '"Respons cepat IGD dan kehangatan dokter serta perawat PICU menyelamatkan buah hati kami. Fasilitas ruang rawat anak di sini sangat ramah dan menenangkan bagi orang tua."',
                'badge' => 'Perawatan Intensif PICU Level 3',
                'image_url' => 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id' => 4,
                'title' => 'Cahaya di Ujung Terowongan: Kisah Bu Maya Sembuh dari Kanker Payudara Stadium Dini',
                'excerpt' => 'Deteksi dini melalui Mammografi 3D Tomosynthesis dan operasi Breast Conserving Surgery menyelamatkan Bu Maya dari ancaman kanker payudara tanpa kehilangan rasa percaya diri.',
                'full_content' => "Pemeriksaan rutin skrining payudara (Mammografi 3D) di Unit Radiologi Hospital Population membawa Bu Maya (42 tahun) pada temuan mikrokalsifikasi mencurigakan yang tak teraba saat pemeriksaan fisik mandiri.\n\nBiopsi jarum halus memastikan lesi merupakan karsinoma duktal in situ (stadium dini). Tim Multidisiplin Onkologi yang dipimpin dr. Hendra Wijaya, Sp.B(K)Onk merekomendasikan tindakan Breast Conserving Surgery (lumpektomi) yang dikombinasikan dengan onkoplasti guna mempertahankan estetika payudara.\n\nOperasi berjalan sukses dengan batas sayatan yang bersih dari sel kanker. Didukung terapi hormonal lanjutan, Bu Maya kini telah dinyatakan remisi total dan aktif mengedukasi perempuan lain tentang pentingnya deteksi dini berkala.",
                'patient_name' => 'Ibu Maya Anggraini',
                'patient_age' => '42 Tahun',
                'diagnosis' => 'Kanker Payudara Stadium 1A (Ductal Carcinoma)',
                'doctor_name' => 'dr. Hendra Wijaya, Sp.B(K)Onk',
                'poli_name' => 'Poli Onkologi & Bedah Tumor',
                'category' => 'Onkologi',
                'read_time' => '5 menit baca',
                'published_at' => '05 Agustus 2026',
                'quote' => '"Deteksi dini menyelamatkan hidup saya. Pelayanan tim onkologi Hospital Population bukan hanya mengobati fisik, tapi juga memberi kekuatan mental yang luar biasa bagi saya."',
                'badge' => 'Breast Conserving Surgery & Onkoplasti',
                'image_url' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id' => 5,
                'title' => 'Kelahiran Nyaman & Penuh Bahagia: Pengalaman Ibu Desi Melahirkan dengan Metode ERACS',
                'excerpt' => 'Persalinan caesar dengan metode Enhanced Recovery After Cesarean Surgery (ERACS) memungkinkan Ibu Desi duduk dan menggendong bayinya hanya 2 jam pasca operasi.',
                'full_content' => "Sebagai ibu baru, Ibu Desi (29 tahun) memiliki kekhawatiran besar terhadap rasa nyeri dan lamanya masa pemulihan pasca operasi caesar. Namun, tim Kebidanan & Kandungan Hospital Population menawarkan protokol ERACS modern.\n\nDengan manajemen anestesi spinal dosis presisi tinggi, mobilisasi dini, dan pencegahan mual muntah yang terencana, operasi berjalan lancar tanpa rasa sakit yang berarti.\n\nKurang dari 2 jam setelah keluar dari ruang operasi, Ibu Desi sudah dapat duduk mandiri, menyusui sang buah hati (Inisiasi Menyusu Dini), dan berjalan perlahan tanpa rasa lemas. Suasana kamar rawat inap VIP yang tenang melengkapi momen bahagia keluarga kecil ini.",
                'patient_name' => 'Ibu Desi & Baby Arka',
                'patient_age' => '29 Tahun',
                'diagnosis' => 'Persalinan Sesar Elektif Metode ERACS',
                'doctor_name' => 'dr. Nurul Hidayah, Sp.OG',
                'poli_name' => 'Poli Kebidanan & Kandungan',
                'category' => 'Ibu & Anak',
                'read_time' => '3 menit baca',
                'published_at' => '01 Agustus 2026',
                'quote' => '"Saya tidak menyangka pemulihan caesar bisa secepat dan seringan ini. Dua jam setelah melahirkan sudah bisa gendong dan susui baby Arka dengan nyaman."',
                'badge' => 'Persalinan Modern ERACS Care',
                'image_url' => 'https://images.unsplash.com/photo-1555252333-9f8e92e65df9?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id' => 6,
                'title' => 'Bebas dari Gerd & Gastritis Kronis: Terapi Endoskopi Presisi Mengubah Pola Hidup Mas Dimas',
                'excerpt' => 'Keluhan asam lambung parah dan sesak ulu hati yang menahun akhirnya teratasi secara komprehensif lewat pemeriksaan Endoskopi Saluran Cerna beresolusi tinggi.',
                'full_content' => "Sebagai pekerja kantoran dengan mobilitas tinggi, Mas Dimas (35 tahun) kerap mengabaikan pola makan hingga mengalami GERD kronis disertai rasa terbakar di dada (heartburn) dan kesulitan menelan.\n\nDi Poliklinik Penyakit Dalam Hospital Population, dokter spesialis gastroenterologi melakukan evaluasi endoskopi saluran cerna atas (EGD) tanpa rasa sakit berkat sedasi ringan yang aman.\n\nHasil pemeriksaan menemukan adanya esofagitis erosif dan infeksi bakteri lambung yang kemudian diterapi dengan rejimen eradikasi presisi serta pendampingan nutrisi klinis. Dalam 4 minggu, seluruh keluhan lambung mereda total dan kualitas tidur Mas Dimas kembali prima.",
                'patient_name' => 'Dimas Prasetyo',
                'patient_age' => '35 Tahun',
                'diagnosis' => 'GERD Berat & Esofagitis Refluks Grade B',
                'doctor_name' => 'dr. Agus Triyono, Sp.PD-KGEH',
                'poli_name' => 'Poli Penyakit Dalam',
                'category' => 'Penyakit Dalam',
                'read_time' => '3 menit baca',
                'published_at' => '28 Juli 2026',
                'quote' => '"Endoskopi ternyata tidak seseram yang saya bayangkan. Prosedurnya sangat nyaman dan penanganan dokter benar-benar menyentuh akar masalah kesehatan saya."',
                'badge' => 'Endoskopi Saluran Cerna HD',
                'image_url' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'id' => 7,
                'title' => 'Pulih dari Trauma Batu Empedu: Operasi Laparoskopi Mini dengan Sayatan Mikro',
                'excerpt' => 'Rasa nyeri melilit di perut kanan atas akibat batu saluran empedu teratasi tuntas lewat laparoskopi minimal invasif dengan bekas luka minim dan rawat inap singkat.',
                'full_content' => "Serangan nyeri kolik bilier berulang kerap membuat Ibu Ratna (51 tahun) harus dilarikan ke instalasi gawat darurat. Hasil USG abdomen mengonfirmasi adanya multipel batu kandung empedu (kolesistolitiasis) yang meradang.\n\nTim Bedah Digestif Hospital Population melakukan tindakan kolesistektomi laparoskopi berteknologi kamera 4K melalui 3 sayatan mikro (kurang dari 1 cm). Prosedur memakan waktu kurang dari 45 menit tanpa pendarahan berarti.\n\nPasca operasi, Ibu Ratna tidak merasakan nyeri berarti dan diperbolehkan makan lunak pada sore hari. Keesokan harinya beliau telah dapat pulang dan beraktivitas ringan di rumah.",
                'patient_name' => 'Ibu Ratna Susanti',
                'patient_age' => '51 Tahun',
                'diagnosis' => 'Kolesistolitiasis Akut (Batu Empedu Meradang)',
                'doctor_name' => 'dr. Rudi Hartono, Sp.B-KBD',
                'poli_name' => 'Poli Bedah Umum & Digestif',
                'category' => 'Bedah Umum',
                'read_time' => '4 menit baca',
                'published_at' => '22 Juli 2026',
                'quote' => '"Bekas operasinya sangat kecil dan nyaris tidak sakit sama sekali. Terima kasih untuk tim bedah dan staf rawat inap yang luar biasa ramah."',
                'badge' => 'Laparoskopi Minimal Invasif 4K',
                'image_url' => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        // 3. Daftar Kategori Filter
        $categories = [
            'Semua',
            'Jantung & Vaskular',
            'Ibu & Anak',
            'Ortopedi',
            'Onkologi',
            'Penyakit Dalam',
            'Bedah Umum',
        ];

        return Inertia::render('PatientStory', [
            'featuredStory' => $featuredStory,
            'stories' => $stories,
            'categories' => $categories,
        ]);
    }
}
