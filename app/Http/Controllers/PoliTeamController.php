<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Nurse;
use App\Models\Poli;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk halaman profil Tim Medis & Fasilitas Poliklinik (/teams).
 * Mengelola informasi profil tim dokter, perawat, ruang pelayanan, jadwal praktik,
 * dan cakupan tindakan medis untuk setiap Poliklinik Spesialis.
 */
class PoliTeamController extends Controller
{
    /**
     * Menampilkan profil tim medis dan fasilitas poliklinik terpilih.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $requestedPoli = $request->query('poli') ?? $request->query('slug') ?? 'Poli Umum';

        // Dataset lengkap 10 Poliklinik Spesialis
        $allPolis = $this->getPolisDataset();

        // Cari data poliklinik aktif berdasarkan nama atau slug
        $normalizedSearch = strtolower(trim(str_replace(['-', '_'], ' ', $requestedPoli)));
        $currentPoliKey = 'poli-umum';

        foreach ($allPolis as $key => $poliData) {
            $nameMatch = strtolower($poliData['name']) === $normalizedSearch;
            $slugMatch = $poliData['slug'] === $requestedPoli || $key === $requestedPoli;
            $partialMatch = str_contains(strtolower($poliData['name']), $normalizedSearch) || str_contains($normalizedSearch, strtolower($poliData['short_name']));

            if ($nameMatch || $slugMatch || $partialMatch) {
                $currentPoliKey = $key;
                break;
            }
        }

        $currentPoli = $allPolis[$currentPoliKey];

        // Ambil data database jika tersedia (Poli, Dokter, Jadwal)
        $dbPoli = Poli::where('name_poli', 'LIKE', '%' . $currentPoli['short_name'] . '%')
            ->orWhere('kode_poli', $currentPoli['code'])
            ->first();

        // Ambil jadwal dokter aktif dari database dengan eager loading
        $doctorSchedules = DoctorSchedule::with([
            'doctor.specialization',
            'doctor.user',
            'poli',
            'room',
        ])
            ->where('status', 'Aktif')
            ->get();

        // Ambil dokter aktif dari database
        $dbDoctors = Doctor::with(['specialization', 'schedules.poli', 'schedules.room', 'user'])
            ->where('status', 'aktif')
            ->get();

        // Ambil perawat aktif dari database
        $dbNurses = Nurse::with('user')->get();

        // Siapkan daftar tab ringkas untuk selector poliklinik di UI
        $tabList = collect($allPolis)->map(function ($item) {
            return [
                'code' => $item['code'],
                'slug' => $item['slug'],
                'name' => $item['name'],
                'short_name' => $item['short_name'],
                'icon' => $item['icon_name'],
                'floor' => $item['floor'],
                'badge' => $item['badge'],
            ];
        })->values()->all();

        return Inertia::render('teams/Index', [
            'polis' => $tabList,
            'currentPoli' => $currentPoli,
            'schedules' => $doctorSchedules,
            'dbDoctors' => $dbDoctors,
            'dbNurses' => $dbNurses,
            'dbPoli' => $dbPoli,
        ]);
    }

    /**
     * Dataset komprehensif 10 Unit Poliklinik & Tim Medis Hospital Population.
     *
     * @return array
     */
    private function getPolisDataset(): array
    {
        return [
            'poli-umum' => [
                'code' => 'POL-UM',
                'slug' => 'poli-umum',
                'name' => 'Poli Umum',
                'short_name' => 'Umum',
                'badge' => 'Pelayanan Medis Primer Terpadu',
                'tagline' => 'Pintu Gerbang Pemeriksaan Kesehatan Komprehensif & Skrining Awal',
                'icon_name' => 'Stethoscope',
                'floor' => 'Lantai 1 Sayap Barat',
                'head_doctor' => 'dr. Budi Santoso',
                'head_doctor_title' => 'Kepala Instalasi Rawat Jalan & Pelayanan Medis Umum',
                'head_nurse' => 'Ns. Siti Rahmawati, S.Kep',
                'head_nurse_title' => 'Perawat Penanggung Jawab Unit Rawat Jalan Umum',
                'description' => 'Poli Umum Hospital Population memberikan layanan konsultasi kesehatan primer, pemeriksaan fisik menyeluruh, penanganan keluhan akut, manajemen penyakit kronis stabil, pembuatan rujukan spesialis, serta uji kesehatan berkala (Medical Check-Up).',
                'operating_hours' => 'Senin – Sabtu: 07.30 – 21.00 WIB | Minggu: 08.00 – 14.00 WIB',
                'metrics' => [
                    ['value' => '120+', 'label' => 'Pasien per Hari', 'desc' => 'Kapasitas pemeriksaan dokter cepat & teliti'],
                    ['value' => '< 15 Mnt', 'label' => 'Waktu Tunggu', 'desc' => 'Sistem antrean digital terintegrasi'],
                    ['value' => '99.3%', 'label' => 'Tingkat Kepuasan', 'desc' => 'Berdasarkan survei kepuasan pasien'],
                    ['value' => '100%', 'label' => 'Dokter Ber-SIP Aktif', 'desc' => 'Tersertifikasi ACLS & ATLS'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Periksa 1 & 2 (Poli Umum)', 'code' => 'R-101 / R-102', 'desc' => 'Ruang konsultasi dokter dengan tensimeter digital & bed pemeriksaan ergonomis'],
                    ['name' => 'Ruang Tindakan Bedah Minor', 'code' => 'R-105', 'desc' => 'Fasilitas jahit luka, perawatan luka diabetik, dan sterilisasi alat medis'],
                    ['name' => 'Ruang Observasi & Terapi Oksigen', 'code' => 'R-108', 'desc' => 'Tempat istirahat observasi 1–2 jam dengan monitor saturasi oksigen & nebulizer'],
                ],
                'scope_services' => [
                    'Pemeriksaan fisik umum dan konsultasi keluhan kesehatan harian',
                    'Skrining tekanan darah, gula darah sewaktu, kolesterol, dan asam urat cepat',
                    'Penanganan infeksi saluran napas atas (ISPA), demam, dan flu',
                    'Perawatan luka ringan, luka bakar derajat satu, dan ganti perban steril',
                    'Penerbitan surat keterangan sehat, tes buta warna, dan bebas narkoba',
                    'Konsultasi rujukan ke dokter spesialis & subspesialis terkait',
                ],
                'team_doctors' => [
                    [
                        'name' => 'dr. Budi Santoso',
                        'role' => 'Dokter Umum Senior & Kepala Poli',
                        'specialty' => 'Kedokteran Umum & Manajemen Rawat Jalan',
                        'sip' => 'SIP/2026/001',
                        'experience' => '12 Tahun Pengalaman',
                        'schedule' => 'Senin – Jumat (08.00 – 14.00 WIB)',
                        'badge' => 'Dokter Tetap',
                    ],
                    [
                        'name' => 'dr. Sarah Nabila, M.Biomed',
                        'role' => 'Dokter Umum & Edukator Kesehatan',
                        'specialty' => 'Kedokteran Preventif & Skrining Gaya Hidup',
                        'sip' => 'SIP/2026/014',
                        'experience' => '8 Tahun Pengalaman',
                        'schedule' => 'Senin, Rabu, Sabtu (14.00 – 20.00 WIB)',
                        'badge' => 'Dokter Tetap',
                    ],
                    [
                        'name' => 'dr. Dimas Prasetyo',
                        'role' => 'Dokter Umum & Unit Gawat Darurat',
                        'specialty' => 'Penanganan Akut & Triase Medis',
                        'sip' => 'SIP/2026/022',
                        'experience' => '6 Tahun Pengalaman',
                        'schedule' => 'Selasa, Kamis, Sabtu (08.00 – 14.00 WIB)',
                        'badge' => 'Dokter Tetap',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Ns. Siti Rahmawati, S.Kep', 'role' => 'Perawat Penanggung Jawab (PJ Shift)', 'str' => 'STR/PER/2026/089', 'cert' => 'BTCLS, Pelatihan Triase Rawat Jalan'],
                    ['name' => 'Ns. Ahmad Fauzi, S.Kep', 'role' => 'Perawat Pelaksana Tindakan Minor', 'str' => 'STR/PER/2026/112', 'cert' => 'Sertifikasi Perawatan Luka Modern'],
                    ['name' => 'Dewi Lestari, A.Md.Kep', 'role' => 'Perawat Skrining Tanda Vital', 'str' => 'STR/PER/2026/145', 'cert' => 'BHD (Bantuan Hidup Dasar), Patient Safety'],
                ],
                'faqs' => [
                    ['q' => 'Apakah pasien BPJS dapat langsung berobat ke Poli Umum?', 'a' => 'Ya, pasien dengan faskes pertama Hospital Population dapat langsung mendaftar dengan membawa KTP dan kartu BPJS aktif.'],
                    ['q' => 'Apakah Poli Umum melayani pembuatan surat keterangan sehat?', 'a' => 'Ya, kami melayani penerbitan surat keterangan sehat untuk keperluan melamar kerja, pendidikan, maupun SIM setelah pemeriksaan fisik lengkap.'],
                ],
            ],

            'poli-penyakit-dalam' => [
                'code' => 'POL-PD',
                'slug' => 'poli-penyakit-dalam',
                'name' => 'Poli Penyakit Dalam',
                'short_name' => 'Penyakit Dalam',
                'badge' => 'Pusat Diagnosis & Manajemen Metabolik',
                'tagline' => 'Tata Laksana Komprehensif Organ Dalam, Diabetes, & Gastroenterohepatologi',
                'icon_name' => 'Droplets',
                'floor' => 'Lantai 2 Sayap Timur',
                'head_doctor' => 'dr. Hendra Gunawan, Sp.PD-KGEH',
                'head_doctor_title' => 'Kepala Divisi Gastroenterohepatologi & Penyakit Dalam',
                'head_nurse' => 'Ns. Wahyu Wibowo, S.Kep',
                'head_nurse_title' => 'Perawat Kepala Unit Poliklinik Penyakit Dalam',
                'description' => 'Poli Penyakit Dalam menangani penyakit organ internal dewasa, mulai dari gangguan metabolik diabetes, hipertensi resisten, ginjal kronis, penyakit liver/hepatitis, hingga gangguan lambung dan saluran cerna.',
                'operating_hours' => 'Senin – Sabtu: 08.00 – 20.00 WIB',
                'metrics' => [
                    ['value' => '10+', 'label' => 'Dokter Konsultan', 'desc' => 'Subspesialis KGEH, K-EMD, & K-GH'],
                    ['value' => '98.7%', 'label' => 'Akurasi Diagnostik', 'desc' => 'Didukung USG Abdomen & Endoskopi HD'],
                    ['value' => '8.500+', 'label' => 'Pasien Diabetes Terkelola', 'desc' => 'Program edukasi diet & insulin terpadu'],
                    ['value' => '24 Jam', 'label' => 'Siaga Konsultasi Internal', 'desc' => 'Dukungan rawat inap & ICU/ICCU'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Konsultasi Internis 1 & 2', 'code' => 'R-201 / R-202', 'desc' => 'Dilengkapi alat USG Abdomen Doppler dan rekam medis terintegrasi'],
                    ['name' => 'Ruang Edukasi Diabetes & Gizi Klinis', 'code' => 'R-204', 'desc' => 'Konseling pola makan, pemantauan gula mandiri, dan terapi insulin'],
                    ['name' => 'Ruang Tindakan Endoskopi Saluran Cerna', 'code' => 'R-210', 'desc' => 'Tindakan Gastroskopi & Kolonoskopi dengan sedasi nyaman'],
                ],
                'scope_services' => [
                    'Diagnosis dan terapi Diabetes Melitus Tipe 1 & 2 serta komplikasi vaskular',
                    'Penanganan GERD berat, tukak lambung, sindrom dispepsia, dan radang usus',
                    'Manajemen Hipertensi refrakter dan evaluasi risiko kardiovaskular',
                    'Pemeriksaan fungsi ginjal, asam urat tinggi, dan persiapan terapi hemodialisa',
                    'Penatalaksanaan penyakit hati (Fatty Liver, Hepatitis B/C, Sirosis)',
                    'Evaluasi gangguan tiroid, autoimun, dan penyakit rematik internal',
                ],
                'team_doctors' => [
                    [
                        'name' => 'dr. Hendra Gunawan, Sp.PD-KGEH',
                        'role' => 'Konsultan Gastroenterologi & Hepatologi',
                        'specialty' => 'Endoskopi Saluran Cerna, Liver, & GERD',
                        'sip' => 'SIP/2026/045',
                        'experience' => '16 Tahun Pengalaman',
                        'schedule' => 'Senin, Rabu, Jumat (09.00 – 15.00 WIB)',
                        'badge' => 'Konsultan Subspesialis',
                    ],
                    [
                        'name' => 'dr. Ratna Kartika, Sp.PD-KEMD',
                        'role' => 'Konsultan Endokrinologi & Diabetes',
                        'specialty' => 'Diabetes, Tiroid, & Gangguan Hormon',
                        'sip' => 'SIP/2026/058',
                        'experience' => '14 Tahun Pengalaman',
                        'schedule' => 'Selasa, Kamis, Sabtu (10.00 – 16.00 WIB)',
                        'badge' => 'Konsultan Subspesialis',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Ns. Wahyu Wibowo, S.Kep', 'role' => 'Perawat Kepala Unit Penyakit Dalam', 'str' => 'STR/PER/2026/077', 'cert' => 'Sertifikasi Keperawatan Endoskopi & ICU'],
                    ['name' => 'Ns. Fitri Handayani, S.Kep', 'role' => 'Edukator Diabetes Bersertifikasi', 'str' => 'STR/PER/2026/094', 'cert' => 'Certified Diabetes Educator (CDE)'],
                ],
                'faqs' => [
                    ['q' => 'Apakah pemeriksaan gula darah memerlukan puasa terlebih dahulu?', 'a' => 'Untuk tes gula darah puasa (GDP) dan profil lipid, pasien diwajibkan berpuasa selama 8–10 jam sebelum pengambilan sampel darah.'],
                ],
            ],

            'poli-anak' => [
                'code' => 'POL-AN',
                'slug' => 'poli-anak-tumbuh-kembang',
                'name' => 'Poli Anak & Tumbuh Kembang',
                'short_name' => 'Anak & Tumbuh Kembang',
                'badge' => 'Sahabat Tumbuh Kembang Buah Hati',
                'tagline' => 'Layanan Pediatri Ramah Anak, Vaksinasi Lengkap, & Deteksi Dini Tumbuh Kembang',
                'icon_name' => 'Heart',
                'floor' => 'Lantai 2 Sayap Barat (Zona Ramah Anak)',
                'head_doctor' => 'dr. Anisa Puspita, Sp.A(K)',
                'head_doctor_title' => 'Kepala Pusat Kesehatan Anak & Tumbuh Kembang',
                'head_nurse' => 'Ns. Maya Anggraini, S.Kep',
                'head_nurse_title' => 'Perawat Kepala Ruang Pediatri & Imunisasi',
                'description' => 'Poli Anak didesain dengan suasana ceria dan bebas trauma, melayani vaksinasi bayi/anak berstandar IDAI, pemantauan kurva pertumbuhan WHO, penanganan infeksi anak, respirologi, dan klinik stimulasi sensori.',
                'operating_hours' => 'Senin – Sabtu: 08.00 – 20.00 WIB | Minggu: 09.00 – 13.00 WIB',
                'metrics' => [
                    ['value' => '99.8%', 'label' => 'Cold Chain Vaksin Terjamin', 'desc' => 'Penyimpanan suhu presisi 2–8°C'],
                    ['value' => 'Level 3', 'label' => 'Akreditasi NICU/PICU', 'desc' => 'Dukungan intensif perawatan anak kritis'],
                    ['value' => '12+', 'label' => 'Spesialis & Subspesialis Anak', 'desc' => 'Respirologi, Nutrisi, Tumbuh Kembang, Saraf'],
                    ['value' => '10.000+', 'label' => 'Balita Diimunisasi Aman', 'desc' => 'Bebas demam tinggi & minim nyeri'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Konsultasi Pediatri Warna-Warni', 'code' => 'R-205', 'desc' => 'Dilengkapi mainan edukatif dan alat pemeriksaan khusus anak'],
                    ['name' => 'Ruang Vaksinasi & Imunisasi Khusus Sehat', 'code' => 'R-207', 'desc' => 'Terpisah dari poli sakit untuk mencegah penularan kuman'],
                    ['name' => 'Ruang Laktasi & Konseling Menyusui', 'code' => 'R-208', 'desc' => 'Privasi penuh didampingi konselor laktasi bersertifikasi'],
                ],
                'scope_services' => [
                    'Imunisasi dasar dan lanjutan sesuai jadwal rekomendasi IDAI terbaru',
                    'Skrining perkembangan Denver II, deteksi speech delay, dan autisme',
                    'Penanganan batuk pilek, bronkiolitis, pneumonia anak, dan asma pediatrik',
                    'Evaluasi gizi balita, stunting, gagal tumbuh, dan masalah makan (GTM)',
                    'Penanganan demam berdarah, tipes, diare dehidrasi, dan alergi susu sapi',
                ],
                'team_doctors' => [
                    [
                        'name' => 'dr. Anisa Puspita, Sp.A(K)',
                        'role' => 'Konsultan Tumbuh Kembang Anak',
                        'specialty' => 'Perkembangan Motorik, Autisme, & Imunisasi',
                        'sip' => 'SIP/2026/088',
                        'experience' => '15 Tahun Pengalaman',
                        'schedule' => 'Senin – Kamis (08.00 – 13.00 WIB)',
                        'badge' => 'Konsultan Subspesialis',
                    ],
                    [
                        'name' => 'dr. Farhan Maulana, Sp.A',
                        'role' => 'Spesialis Respirologi & Infeksi Anak',
                        'specialty' => 'Asma Anak, Bronkopneumonia, & Alergi',
                        'sip' => 'SIP/2026/092',
                        'experience' => '10 Tahun Pengalaman',
                        'schedule' => 'Rabu – Sabtu (14.00 – 19.00 WIB)',
                        'badge' => 'Dokter Spesialis',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Ns. Maya Anggraini, S.Kep', 'role' => 'Perawat Kepala Pediatri', 'str' => 'STR/PER/2026/061', 'cert' => 'Pediatric Advanced Life Support (PALS)'],
                    ['name' => 'Rini Astuti, A.Md.Keb', 'role' => 'Bidan Konselor Imunisasi & ASI', 'str' => 'STR/BID/2026/033', 'cert' => 'Pelatihan Manajemen Vaksin & Konselor Laktasi'],
                ],
                'faqs' => [
                    ['q' => 'Apakah ruang imunisasi dipisahkan dari anak yang sedang sakit?', 'a' => 'Ya. Kami menerapkan protokol Well-Baby Clinic di mana ruang vaksinasi memiliki jalur dan ruangan tersendiri yang terisolasi dari pasien flu/batuk.'],
                ],
            ],

            'poli-jantung' => [
                'code' => 'POL-JT',
                'slug' => 'poli-jantung-pembuluh-darah',
                'name' => 'Poli Jantung & Pembuluh Darah',
                'short_name' => 'Jantung & Vaskular',
                'badge' => 'Pusat Kardiovaskular & Cath Lab 24 Jam',
                'tagline' => 'Presisi Diagnostik Jantung, Treadmill Test, & Ekokardiografi 4D',
                'icon_name' => 'HeartPulse',
                'floor' => 'Lantai 1 Sayap Timur (Dekat IGD & ICCU)',
                'head_doctor' => 'dr. Rian Hidayat, Sp.JP(K), FIHA',
                'head_doctor_title' => 'Kepala Pusat Intervensi Jantung & Aritmia',
                'head_nurse' => 'Ns. Teguh Santoso, S.Kep',
                'head_nurse_title' => 'Perawat Penanggung Jawab Kardiologi & EKG',
                'description' => 'Pelayanan komprehensif untuk deteksi dini penyakit jantung koroner, hipertensi kardiak, gagal jantung, aritmia, evaluasi pra-operasi besar, serta rehabilitasi medik pasca serangan jantung.',
                'operating_hours' => 'Senin – Sabtu: 08.00 – 20.00 WIB',
                'metrics' => [
                    ['value' => '4D', 'label' => 'Ekokardiografi Berwarna', 'desc' => 'Visualisasi katup & fungsi pompa jantung'],
                    ['value' => '24 Jam', 'label' => 'Cath Lab Siaga Pasang Ring', 'desc' => 'Door-to-balloon time < 60 menit'],
                    ['value' => '99.5%', 'label' => 'Keberhasilan Intervensi', 'desc' => 'Ribuan tindakan kateterisasi sukses'],
                    ['value' => '100%', 'label' => 'Sertifikasi ACLS/FIHA', 'desc' => 'Dukungan dokter konsultan kardiovaskular'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Pemeriksaan EKG 12-Lead', 'code' => 'R-112', 'desc' => 'Perekaman aktivitas listrik jantung cepat dan interpretasi otomatis'],
                    ['name' => 'Laboratorium Uji Latih Beban (Treadmill Test)', 'code' => 'R-114', 'desc' => 'Evaluasi iskemia miokard saat aktivitas fisik terukur'],
                    ['name' => 'Ruang Ekokardiografi & USG Vaskular', 'code' => 'R-116', 'desc' => 'Pemeriksaan struktur anatomi dan fungsi pompa jantung'],
                ],
                'scope_services' => [
                    'Perekaman Elektrokardiografi (EKG) 12 Sandapan & Holter Monitoring 24/48 Jam',
                    'Uji Latih Beban Jantung Berkelanjutan (Treadmill Stress Test)',
                    'Ekokardiografi Transtorakal (TTE) & Transesofageal (TEE)',
                    'Evaluasi nyeri dada akut dan rujukan cepat tindakan pasang stent (PCI)',
                    'Manajemen obat pengencer darah, antiaritmia, dan gagal jantung kronis',
                ],
                'team_doctors' => [
                    [
                        'name' => 'dr. Rian Hidayat, Sp.JP(K), FIHA',
                        'role' => 'Konsultan Intervensi Kardiovaskular',
                        'specialty' => 'Pemasangan Ring Jantung (PCI) & Kateterisasi',
                        'sip' => 'SIP/2026/034',
                        'experience' => '18 Tahun Pengalaman',
                        'schedule' => 'Senin, Rabu, Jumat (08.00 – 14.00 WIB)',
                        'badge' => 'Konsultan Intervensi',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Ns. Teguh Santoso, S.Kep', 'role' => 'Perawat Kardiologi Senior', 'str' => 'STR/PER/2026/029', 'cert' => 'Advanced Cardiac Life Support (ACLS), Pelatihan ICCU'],
                ],
                'faqs' => [
                    ['q' => 'Apa yang harus dipersiapkan sebelum melakukan Treadmill Test?', 'a' => 'Gunakan pakaian dan sepatu olahraga yang nyaman, hindari makan berat 2 jam sebelum tes, dan konsultasikan jadwal konsumsi obat jantung.'],
                ],
            ],

            'poli-kebidanan' => [
                'code' => 'POL-OB',
                'slug' => 'poli-kebidanan-kandungan',
                'name' => 'Poli Kebidanan & Kandungan',
                'short_name' => 'Kebidanan & Kandungan',
                'badge' => 'Pelayanan Kesehatan Ibu & Janin Terpadu',
                'tagline' => 'USG 4D HD Live, Skrining Kehamilan Risiko Tinggi, & Kesehatan Reproduksi',
                'icon_name' => 'Heart',
                'floor' => 'Lantai 2 Sayap Tengah',
                'head_doctor' => 'dr. Citra Melati, Sp.OG(K)',
                'head_doctor_title' => 'Kepala Divisi Fetomaternal & Kesehatan Reproduksi',
                'head_nurse' => 'Bdn. Sri Mulyani, S.Tr.Keb',
                'head_nurse_title' => 'Koordinator Pelayanan Rawat Jalan Kebidanan',
                'description' => 'Pelayanan pemeriksaan kehamilan komprehensif, deteksi dini kelainan janin dengan USG 4D Fetomaternal, penanganan miom/kista, program promil (kehamilan), serta skrining Pap Smear pencegahan kanker serviks.',
                'operating_hours' => 'Senin – Sabtu: 08.30 – 20.30 WIB',
                'metrics' => [
                    ['value' => '4D HD', 'label' => 'USG Live Visual', 'desc' => 'Deteksi detail organ & wajah janin'],
                    ['value' => '99.6%', 'label' => 'Kelahiran Aman', 'desc' => 'Didukung ruang bersalin & OK cito'],
                    ['value' => '100%', 'label' => 'Skrining NIPT & Anomali', 'desc' => 'Tes genetik kromosom trimester 1'],
                    ['value' => '24 Jam', 'label' => 'Kamar Bersalin Siaga', 'desc' => 'Metode persalinan nyaman & ERACS'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Konsultasi & USG 4D Fetomaternal', 'code' => 'R-220', 'desc' => 'Dilengkapi layar monitor ganda untuk kenyamanan orang tua melihat janin'],
                    ['name' => 'Ruang CTG (Kardiotokografi)', 'code' => 'R-222', 'desc' => 'Pemantauan detak jantung janin dan kontraksi rahim jelang persalinan'],
                    ['name' => 'Ruang Tindakan Ginekologi & Pap Smear', 'code' => 'R-224', 'desc' => 'Pemeriksaan serviks, pemasangan IUD, dan biopsi ginekologis steril'],
                ],
                'scope_services' => [
                    'Pemeriksaan antenatal rutin (ANC) dan pemantauan berat janin berkala',
                    'Ultrasonografi (USG) 2D, 3D, dan 4D HD Live resolusi tinggi',
                    'Skrining kelainan kromosom janin (NIPT) dan anomali scan trimester 2',
                    'Konsultasi program hamil, fertilitas, dan induksi ovulasi',
                    'Penanganan Mioma Uteri, Kista Ovarium, Endometriosis, dan PCOS',
                    'Pelayanan kontrasepsi modern (IUD, Implan, Suntik, dan Tubektomi)',
                ],
                'team_doctors' => [
                    [
                        'name' => 'dr. Citra Melati, Sp.OG(K)',
                        'role' => 'Konsultan Fetomaternal & Kebidanan',
                        'specialty' => 'Kehamilan Risiko Tinggi & USG 4D Fetomaternal',
                        'sip' => 'SIP/2026/067',
                        'experience' => '14 Tahun Pengalaman',
                        'schedule' => 'Senin – Kamis (09.00 – 15.00 WIB)',
                        'badge' => 'Konsultan Fetomaternal',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Bdn. Sri Mulyani, S.Tr.Keb', 'role' => 'Bidan Koordinator Poli Kandungan', 'str' => 'STR/BID/2026/019', 'cert' => 'Pelatihan CTG, Konselor Gentle Birth'],
                ],
                'faqs' => [
                    ['q' => 'Kapan waktu terbaik melakukan USG 4D wajah janin?', 'a' => 'Waktu paling ideal adalah pada usia kehamilan 26 hingga 30 minggu, saat air ketuban masih cukup banyak dan fitur wajah janin sudah terbentuk sempurna.'],
                ],
            ],

            'poli-bedah-ortopedi' => [
                'code' => 'POL-BD',
                'slug' => 'poli-bedah-ortopedi',
                'name' => 'Poli Bedah & Ortopedi',
                'short_name' => 'Bedah & Ortopedi',
                'badge' => 'Pusat Tulang, Sendi, & Bedah Minimal Invasif',
                'tagline' => 'Pemulihan Cedera Tulang, Ligamen Olahraga, & Artroskopi Navigasi',
                'icon_name' => 'Activity',
                'floor' => 'Lantai 1 Sayap Selatan',
                'head_doctor' => 'dr. Agung Wicaksono, Sp.OT(K)',
                'head_doctor_title' => 'Kepala Pusat Bedah Tulang, Sendi, & Rekonstruksi',
                'head_nurse' => 'Ns. Danang Prasojo, S.Kep',
                'head_nurse_title' => 'Perawat Penanggung Jawab Unit Bedah & Gips',
                'description' => 'Menangani patah tulang (fraktur), pengapuran sendi lutut/panggul (osteoarthritis), cedera ligamen olahraga (ACL/PCL), kelainan bentuk tulang belakang, serta perawatan luka pasca operasi.',
                'operating_hours' => 'Senin – Sabtu: 08.00 – 19.00 WIB',
                'metrics' => [
                    ['value' => 'C-Arm HD', 'label' => 'Fluoroskopi Realtime', 'desc' => 'Akurasi reduksi fraktur sub-milimeter'],
                    ['value' => '99.1%', 'label' => 'Penyembuhan Tulang Sempurna', 'desc' => 'Metode fiksasi internal biokompatibel'],
                    ['value' => 'Day-1', 'label' => 'Mobilisasi Pasca Ganti Sendi', 'desc' => 'Program Fast-Track Recovery'],
                    ['value' => '24 Jam', 'label' => 'Siaga Penanganan Trauma', 'desc' => 'Terhubung langsung dengan Unit Bedah Sentral'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Konsultasi Ortopedi 1', 'code' => 'R-120', 'desc' => 'Dilengkapi viewing box digital dan evaluasi rentang gerak sendi'],
                    ['name' => 'Ruang Pemasangan & Pelepasan Gips Steril', 'code' => 'R-122', 'desc' => 'Fasilitas gips fiberglass ringan, tahan air, dan nyaman'],
                    ['name' => 'Ruang Perawatan Luka Bedah & Jahitan', 'code' => 'R-125', 'desc' => 'Pergantian balutan modern dressing anti-infeksi'],
                ],
                'scope_services' => [
                    'Penanganan fraktur dan dislokasi sendi dengan fiksasi gips atau operasi',
                    'Total Knee & Hip Replacement (Ganti Sendi Lutut & Panggul)',
                    'Artroskopi rekonstruksi cedera ligamen olahraga (ACL, PCL, Meniskus)',
                    'Penyuntikan cairan pelumas sendi (Asam Hialuronat) & terapi PRP lutut',
                    'Evaluasi nyeri saraf terjepit (HNP) dan kelainan skoliosis tulang belakang',
                ],
                'team_doctors' => [
                    [
                        'name' => 'dr. Agung Wicaksono, Sp.OT(K)',
                        'role' => 'Konsultan Bedah Rekonstruksi Sendi',
                        'specialty' => 'Total Knee Replacement & Artroskopi ACL',
                        'sip' => 'SIP/2026/073',
                        'experience' => '16 Tahun Pengalaman',
                        'schedule' => 'Senin, Rabu, Jumat (09.00 – 15.00 WIB)',
                        'badge' => 'Konsultan Subspesialis',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Ns. Danang Prasojo, S.Kep', 'role' => 'Perawat Bedah & Ortopedi', 'str' => 'STR/PER/2026/041', 'cert' => 'Sertifikasi Penanganan Gips & Perawatan Luka Bedah'],
                ],
                'faqs' => [
                    ['q' => 'Apakah pemasangan gips fiberglass bisa terkena air saat mandi?', 'a' => 'Ya, kami menyediakan opsi gips sintetis fiberglass waterproof yang memungkinkan pasien tetap mandi dengan aman.'],
                ],
            ],

            'poli-gigi' => [
                'code' => 'POL-GG',
                'slug' => 'poli-gigi-mulut',
                'name' => 'Poli Gigi & Mulut',
                'short_name' => 'Gigi & Mulut',
                'badge' => 'Perawatan Gigi Modern & Dental Estetik',
                'tagline' => 'Senyum Sehat Percaya Diri dengan Dental Chair Ergonomis & Panoramic X-Ray',
                'icon_name' => 'Sparkles',
                'floor' => 'Lantai 1 Sayap Utara',
                'head_doctor' => 'drg. Kevin Pratama, Sp.BM',
                'head_doctor_title' => 'Kepala Divisi Bedah Mulut & Dental Center',
                'head_nurse' => 'Ns. Lia Indrawati, A.Md.Kep',
                'head_nurse_title' => 'Perawat Gigi & Sterilisasi Dental',
                'description' => 'Menyediakan perawatan gigi komprehensif mulai dari pembersihan karang gigi (scaling), tambal gigi estetik, perawatan saluran akar, pencabutan gigi bungsu (odontektomi), hingga pemasangan kawat gigi (ortodonti).',
                'operating_hours' => 'Senin – Sabtu: 08.30 – 20.30 WIB',
                'metrics' => [
                    ['value' => 'Autoclave B', 'label' => 'Sterilisasi Standar Eropa', 'desc' => 'Jaminan alat 100% steril anti kuman'],
                    ['value' => 'HD 3D', 'label' => 'Dental Panoramic X-Ray', 'desc' => 'Rontgen gigi akurasi tinggi radiasi rendah'],
                    ['value' => '99.4%', 'label' => 'Tindakan Bebas Sakit', 'desc' => 'Teknik anestesi lokal tanpa rasa nyeri'],
                    ['value' => '5.000+', 'label' => 'Kasus Scaling & Tambal', 'desc' => 'Ditangani dokter gigi spesialis'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Dental Suite 1 & 2', 'code' => 'R-130 / R-131', 'desc' => 'Dilengkapi Dental Chair elektrik, intraoral camera, dan lampu LED steril'],
                    ['name' => 'Ruang Bedah Mulut & Odontektomi', 'code' => 'R-135', 'desc' => 'Peralatan bedah gigi bungsu minor dengan sistem vacuum aerosol'],
                ],
                'scope_services' => [
                    'Pembersihan karang gigi (Ultrasonic Scaling) dan pemutihan gigi (Bleaching)',
                    'Penambalan gigi estetik sewarna gigi dengan sinar laser resin komposit',
                    'Pencabutan gigi sulung, gigi dewasa, dan operasi gigi bungsu miring',
                    'Perawatan saraf gigi (Endodontik) untuk menyelamatkan gigi mati',
                    'Pemasangan behel gigi (Kawat Gigi Ortodonti) dan gigi tiruan (Implan Gigi)',
                ],
                'team_doctors' => [
                    [
                        'name' => 'drg. Kevin Pratama, Sp.BM',
                        'role' => 'Spesialis Bedah Mulut & Maksilofasial',
                        'specialty' => 'Operasi Gigi Bungsu, Implan Gigi, & Trauma Rahang',
                        'sip' => 'SIP/2026/099',
                        'experience' => '11 Tahun Pengalaman',
                        'schedule' => 'Senin – Kamis (10.00 – 17.00 WIB)',
                        'badge' => 'Spesialis Bedah Mulut',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Ns. Lia Indrawati, A.Md.Kep', 'role' => 'Perawat Gigi & Asisten Dental', 'str' => 'STR/PER/2026/055', 'cert' => 'Sertifikasi Asistensi Dental & Kontrol Infeksi'],
                ],
                'faqs' => [
                    ['q' => 'Berapa lama waktu yang dibutuhkan untuk operasi gigi bungsu?', 'a' => 'Tindakan odontektomi biasanya berlangsung 30–45 menit dengan bius lokal sehingga pasien tidak merasakan nyeri selama prosedur.'],
                ],
            ],

            'poli-mata' => [
                'code' => 'POL-MT',
                'slug' => 'poli-mata',
                'name' => 'Poli Mata',
                'short_name' => 'Mata (Oftalmologi)',
                'badge' => 'Pusat Penglihatan Jernih & Bedah Katarak Modern',
                'tagline' => 'Fakoemulsifikasi Katarak Tanpa Jahitan & Pemeriksaan Refraksi Komputer',
                'icon_name' => 'Eye',
                'floor' => 'Lantai 3 Sayap Barat',
                'head_doctor' => 'dr. Nadia Utami, Sp.M(K)',
                'head_doctor_title' => 'Kepala Pusat Oftalmologi & Bedah Katarak',
                'head_nurse' => 'Ns. Ferry Kurniawan, S.Kep',
                'head_nurse_title' => 'Perawat Penanggung Jawab Unit Oftalmologi',
                'description' => 'Pelayanan diagnostik dan terapi gangguan penglihatan, operasi katarak modern metode Phacoemulsification, penanganan glaukoma, retinopati diabetik, kelainan refraksi (mata minus/silinder), serta infeksi mata.',
                'operating_hours' => 'Senin – Sabtu: 08.00 – 18.00 WIB',
                'metrics' => [
                    ['value' => '15 Mnt', 'label' => 'Operasi Katarak Phaco', 'desc' => 'Tanpa jahitan, tanpa perban menginap'],
                    ['value' => 'OCT 3D', 'label' => 'Optical Coherence Tomography', 'desc' => 'Pindaian retina & serabut saraf optik'],
                    ['value' => '99.7%', 'label' => 'Restorasi Penglihatan', 'desc' => 'Tingkat kepuasan tajam visual tinggi'],
                    ['value' => '100%', 'label' => 'Lensa Intraokular Premium', 'desc' => 'Lensa monofokal & multifokal'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Refraksi & Slit Lamp Digital', 'code' => 'R-301', 'desc' => 'Pemeriksaan tajam penglihatan komputer dan kornea mata'],
                    ['name' => 'Laboratorium Diagnostik Retina & OCT', 'code' => 'R-305', 'desc' => 'Pemindaian retina resolusi mikroskopis dan tonometri glaukoma'],
                ],
                'scope_services' => [
                    'Uji tajam penglihatan (Visus) dan peresepan kacamata komputer otomatis',
                    'Operasi Katarak Fakoemulsifikasi dengan sayatan mikro 2.2 mm',
                    'Skrining dan tata laksana Glaukoma dengan tes tekanan bola mata (Tonometri)',
                    'Pemeriksaan Retinopati Diabetik dan terapi laser fotokoagulasi',
                    'Penanganan konjungtivitis, mata kering kronis, pterigium, dan infeksi kornea',
                ],
                'team_doctors' => [
                    [
                        'name' => 'dr. Nadia Utami, Sp.M(K)',
                        'role' => 'Konsultan Katarak & Bedah Refraktif',
                        'specialty' => 'Fakoemulsifikasi Katarak & Glaukoma',
                        'sip' => 'SIP/2026/105',
                        'experience' => '13 Tahun Pengalaman',
                        'schedule' => 'Senin, Rabu, Jumat (08.30 – 14.30 WIB)',
                        'badge' => 'Konsultan Subspesialis',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Ns. Ferry Kurniawan, S.Kep', 'role' => 'Perawat Asisten Oftalmologi', 'str' => 'STR/PER/2026/081', 'cert' => 'Sertifikasi Asistensi Bedah Mata & Refraksi'],
                ],
                'faqs' => [
                    ['q' => 'Apakah operasi katarak memerlukan rawat inap di rumah sakit?', 'a' => 'Tidak. Dengan teknik fakoemulsifikasi modern, operasi hanya memakan waktu 15–20 menit dan pasien dapat langsung pulang pada hari yang sama (One Day Care).'],
                ],
            ],

            'poli-tht' => [
                'code' => 'POL-TH',
                'slug' => 'poli-tht',
                'name' => 'Poli THT',
                'short_name' => 'THT (Telinga Hidung Tenggorokan)',
                'badge' => 'Pusat Endoskopi THT & Audiometri Digital',
                'tagline' => 'Solusi Gangguan Pendengaran, Sinusitis Kronis, & Masalah Pita Suara',
                'icon_name' => 'Sparkles',
                'floor' => 'Lantai 3 Sayap Timur',
                'head_doctor' => 'dr. Bambang Irawan, Sp.THT-BKL',
                'head_doctor_title' => 'Kepala Instalasi THT & Bedah Kepala Leher',
                'head_nurse' => 'Ns. Ratna Sari, S.Kep',
                'head_nurse_title' => 'Perawat Penanggung Jawab Unit THT & Audiologi',
                'description' => 'Melayani pemeriksaan saluran telinga, hidung, dan tenggorokan dengan Video Endoskopi HD, pembersihan kotoran telinga (ear toilet) mikro-suction, tes pendengaran audiometri & timpanometri, serta bedah sinus (FESS).',
                'operating_hours' => 'Senin – Sabtu: 08.30 – 19.00 WIB',
                'metrics' => [
                    ['value' => 'HD 4K', 'label' => 'Video Nasoendoskopi', 'desc' => 'Visualisasi rongga hidung & pita suara sangat jelas'],
                    ['value' => 'Soundproof', 'label' => 'Bilik Kedap Audiometri', 'desc' => 'Uji ambang dengar akurasi tinggi'],
                    ['value' => '99.2%', 'label' => 'Ketepatan Diagnosis Sinusitis', 'desc' => 'Evaluasi komprehensif tanpa rasa nyeri'],
                    ['value' => '100%', 'label' => 'Mikro Suction Higienis', 'desc' => 'Pembersihan serumen telinga steril'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Konsultasi & Endoskopi THT', 'code' => 'R-310', 'desc' => 'Dilengkapi unit THT modern dengan monitor visual pasien'],
                    ['name' => 'Ruang Bilik Kedap Uji Dengar (Audiometri)', 'code' => 'R-312', 'desc' => 'Pemeriksaan fungsi pendengaran nada murni & timpanometri'],
                ],
                'scope_services' => [
                    'Pembersihan serumen (kotoran telinga) dengan metode irigasi & mikro-suction',
                    'Pemeriksaan Audiometri Nada Murni & Timpanometri untuk gangguan pendengaran',
                    'Diagnosis dan terapi Sinusitis kronis, polip hidung, dan deviasi septum',
                    'Evaluasi radang amandel (Tonsilitis), faringitis kronis, dan suara serak',
                    'Penanganan vertigo perifer akibat gangguan keseimbangan telinga dalam (BPPV)',
                ],
                'team_doctors' => [
                    [
                        'name' => 'dr. Bambang Irawan, Sp.THT-BKL',
                        'role' => 'Spesialis THT & Bedah Kepala Leher',
                        'specialty' => 'Endoskopi Sinus, Audiologi, & Amandel',
                        'sip' => 'SIP/2026/118',
                        'experience' => '15 Tahun Pengalaman',
                        'schedule' => 'Senin – Kamis (09.00 – 15.00 WIB)',
                        'badge' => 'Spesialis Senior',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Ns. Ratna Sari, S.Kep', 'role' => 'Perawat Audiologi THT', 'str' => 'STR/PER/2026/072', 'cert' => 'Sertifikasi Asistensi Endoskopi THT & Uji Audiometri'],
                ],
                'faqs' => [
                    ['q' => 'Apakah pembersihan serumen telinga dengan mikro-suction menimbulkan rasa sakit?', 'a' => 'Tidak. Mikro-suction adalah metode yang sangat aman, cepat, dan higienis tanpa menimbulkan rasa sakit karena tidak menggunakan tekanan air keras.'],
                ],
            ],

            'poli-saraf' => [
                'code' => 'POL-SR',
                'slug' => 'poli-saraf',
                'name' => 'Poli Saraf',
                'short_name' => 'Saraf (Neurologi)',
                'badge' => 'Pusat Brain Spine & Manajemen Stroke',
                'tagline' => 'Diagnosis Presisi Saraf Terjepit, Nyeri Kronis, EEG, & Penanganan Stroke Terpadu',
                'icon_name' => 'Activity',
                'floor' => 'Lantai 3 Sayap Selatan',
                'head_doctor' => 'dr. Maya Indriyani, Sp.N(K)',
                'head_doctor_title' => 'Kepala Pusat Neurologi & Brain Spine Care',
                'head_nurse' => 'Ns. Ilham Akbar, S.Kep',
                'head_nurse_title' => 'Perawat Penanggung Jawab Unit Neurodiagnostik',
                'description' => 'Pelayanan khusus diagnosis dan terapi stroke, epilepsi, nyeri saraf terjepit (HNP/skiatika), migrain, vertigo, neuropati diabetik, demensia/alzheimer, dan penyakit Parkinson dengan dukungan EEG & EMG modern.',
                'operating_hours' => 'Senin – Sabtu: 08.30 – 19.30 WIB',
                'metrics' => [
                    ['value' => '32-Channel', 'label' => 'Elektroensefalografi (EEG)', 'desc' => 'Pemetaan gelombang otak digital presisi'],
                    ['value' => '99.5%', 'label' => 'Presisi EMG Saraf Tepi', 'desc' => 'Uji kecepatan hantar saraf (KHS)'],
                    ['value' => '< 4.5 Jam', 'label' => 'Golden Period Stroke', 'desc' => 'Protokol trombolisis IV siaga 24 jam'],
                    ['value' => '100%', 'label' => 'Terapi Nyeri Intervensi', 'desc' => 'Injeksi saraf berpemandu USG presisi'],
                ],
                'rooms' => [
                    ['name' => 'Ruang Konsultasi Neurologi & Nyeri', 'code' => 'R-320', 'desc' => 'Evaluasi refleks neurologis, fungsi kognitif, dan saraf kranial'],
                    ['name' => 'Laboratorium Neurofisiologi (EEG & EMG)', 'code' => 'R-325', 'desc' => 'Rekam aktivitas listrik otak dan konduksi saraf motorik/sensorik'],
                ],
                'scope_services' => [
                    'Perekaman Gelombang Otak Digital (EEG) untuk diagnosis kejang & epilepsi',
                    'Uji Elektromiografi (EMG) dan Kecepatan Hantar Saraf (KHS) untuk neuropati',
                    'Penanganan stroke iskemik/perdarahan dan program rehabilitasi neuro-vaskular',
                    'Tata laksana Nyeri Kepala Kronis (Migrain, Tension Headache, Cluster)',
                    'Terapi Saraf Terjepit (HNP servikal/lumbal) dengan blok saraf perifer',
                    'Evaluasi gangguan memori (Pikun/Alzheimer) dan tremor pada Parkinson',
                ],
                'team_doctors' => [
                    [
                        'name' => 'dr. Maya Indriyani, Sp.N(K)',
                        'role' => 'Konsultan Neurologi & Neurofisiologi',
                        'specialty' => 'Stroke, Epilepsi (EEG), & Saraf Terjepit',
                        'sip' => 'SIP/2026/124',
                        'experience' => '17 Tahun Pengalaman',
                        'schedule' => 'Senin, Selasa, Kamis (08.30 – 14.30 WIB)',
                        'badge' => 'Konsultan Neurologi',
                    ],
                ],
                'team_nurses' => [
                    ['name' => 'Ns. Ilham Akbar, S.Kep', 'role' => 'Perawat Teknisi EEG & Neurologi', 'str' => 'STR/PER/2026/097', 'cert' => 'Sertifikasi Operator EEG/EMG & Neuro-Intensive Care'],
                ],
                'faqs' => [
                    ['q' => 'Kapan saya harus segera ke rumah sakit saat mencurigai gejala stroke?', 'a' => 'Ingat FAST: Face (wajah mencong), Arms (lengan lemas sebelah), Speech (bicara pelo), Time (segera ke IGD dalam kurun waktu kurang dari 4.5 jam untuk menyelamatkan jaringan otak).'],
                ],
            ],
        ];
    }
}
