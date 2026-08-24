<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk halaman publik Layanan & Sub-Spesialisasi Medis (/specializations).
 * Menampilkan rincian komprehensif profil spesialisasi, kondisi & penyakit yang ditangani,
 * prosedur medis unggulan, dokter penanggung jawab, jadwal praktik terintegrasi, dan FAQ.
 */
class SpecializationController extends Controller
{
    /**
     * Menampilkan katalog spesialisasi medis dan detail sub-spesialisasi aktif.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $selectedSlug = $request->query('slug', 'pulmonologi');

        // Master data katalog sub-spesialisasi klinis (Evergreen Siloam style)
        $allSpecializations = $this->getSpecializationsDataset();

        // Cari data spesialisasi aktif berdasarkan slug, default ke pulmonologi
        $currentSpecialization = $allSpecializations[$selectedSlug] ?? $allSpecializations['pulmonologi'];

        // Ambil jadwal praktik dokter aktif dari database dengan eager loading mencegah N+1
        $doctorSchedules = DoctorSchedule::with([
            'doctor.specialization',
            'doctor.user',
            'poli',
            'room',
        ])
            ->where('status', 'Aktif')
            ->get();

        // Ambil dokter terkait spesialisasi dari database atau dokter aktif
        $doctors = Doctor::with(['specialization', 'schedules.poli', 'schedules.room'])
            ->where('status', 'aktif')
            ->get();

        // List navigasi tab spesialisasi ringan untuk selector UI
        $tabList = collect($allSpecializations)->map(function ($item) {
            return [
                'slug' => $item['slug'],
                'name' => $item['name'],
                'short_name' => $item['short_name'],
                'category' => $item['category'],
                'icon' => $item['icon_name'],
                'badge' => $item['badge'],
            ];
        })->values()->all();

        return Inertia::render('Specializations/Index', [
            'specializations' => $tabList,
            'currentSpecialization' => $currentSpecialization,
            'schedules' => $doctorSchedules,
            'doctors' => $doctors,
        ]);
    }

    /**
     * Dataset komprehensif layanan dan sub-spesialisasi medis berstandar internasional.
     *
     * @return array
     */
    private function getSpecializationsDataset(): array
    {
        return [
            'pulmonologi' => [
                'slug' => 'pulmonologi',
                'name' => 'Sub-Spesialis Asma, Paru, & Saluran Pernapasan (PPOK)',
                'short_name' => 'Paru & Respirasi',
                'category' => 'Pulmonologi & Kedokteran Respirasi',
                'icon_name' => 'Activity',
                'badge' => 'Pusat Rujukan Respirasi Paripurna',
                'tagline' => 'Napas Lega, Hidup Sehat Berkualitas',
                'description' => 'Pusat Keunggulan Paru dan Saluran Pernapasan Hospital Population menghadirkan pendekatan medis terintegrasi untuk diagnosis, tata laksana, dan rehabilitasi berbagai gangguan sistem respirasi. Didukung oleh teknologi Spirometri Digital, Bronkoskopi Fleksibel HD 4K, unit perawatan intensif respiratori, dan tim dokter konsultan pulmonologi berpengalaman.',
                'quote' => 'Penanganan tepat pada saluran pernapasan bukan sekadar meredakan sesak, namun mengembalikan kapasitas vital paru agar pasien dapat kembali beraktivitas optimal tanpa kekhawatiran.',
                'metrics' => [
                    [
                        'value' => '99.2%',
                        'label' => 'Akurasi Diagnostik',
                        'desc' => 'Didukung tes fungsi paru terkalibrasi',
                    ],
                    [
                        'value' => '10+',
                        'label' => 'Dokter Subspesialis & Konsultan',
                        'desc' => 'Spesialis Paru (Sp.P) bersertifikasi',
                    ],
                    [
                        'value' => '24 Jam',
                        'label' => 'Layanan Gawat Darurat Paru',
                        'desc' => 'Siaga penanganan asma akut & PPOK',
                    ],
                    [
                        'value' => '5.200+',
                        'label' => 'Pasien Tertangani per Tahun',
                        'desc' => 'Rawat jalan & rehabilitasi pernapasan',
                    ],
                ],
                'conditions' => [
                    [
                        'title' => 'Asma Bronkial & Alergi Saluran Napas',
                        'category' => 'Asma & Alergi',
                        'severity' => 'Tinggi',
                        'desc' => 'Penyempitan dan peradangan kronis saluran napas yang memicu serangan sesak berulang akibat hipersensitivitas bronkus.',
                        'symptoms' => [
                            'Mengi (bunyi ngik-ngik saat mengembuskan napas)',
                            'Sesak napas mendadak saat malam atau dini hari',
                            'Batuk kering kronis yang dipicu cuaca dingin atau debu',
                            'Rasa berat dan tertekan di bagian dada',
                        ],
                    ],
                    [
                        'title' => 'Penyakit Paru Obstruktif Kronis (PPOK)',
                        'category' => 'Obstruktif Kronis',
                        'severity' => 'Sangat Tinggi',
                        'desc' => 'Hambatan aliran udara progresif akibat paparan asap rokok atau polusi jangka panjang yang merusak alveolus paru.',
                        'symptoms' => [
                            'Sesak napas bertahap yang makin memberat saat aktivitas',
                            'Produksi dahak berlebih setiap pagi',
                            'Infeksi pernapasan yang sering kambuh',
                            'Kelelahan fisik akibat penurunan saturasi oksigen',
                        ],
                    ],
                    [
                        'title' => 'Pneumonia & Infeksi Paru Akut',
                        'category' => 'Infeksi Respirasi',
                        'severity' => 'Gawat Darurat',
                        'desc' => 'Infeksi bakteri atau virus pada kantung udara (alveoli) yang menyebabkan peradangan bernanah dan penurunan pertukaran gas.',
                        'symptoms' => [
                            'Demam tinggi disertai menggigil',
                            'Batuk produktif berdahak kuning-kehijauan atau berdarah',
                            'Nyeri dada tajam saat menarik napas dalam',
                            'Napas cepat dangkal dan saturasi oksigen menurun',
                        ],
                    ],
                    [
                        'title' => 'Tuberkulosis (TBC) & Mikobakterium Paru',
                        'category' => 'Infeksi Tropis',
                        'severity' => 'Tinggi',
                        'desc' => 'Infeksi bakteri Mycobacterium tuberculosis yang menyerang jaringan parenkim paru dan memerlukan terapi OAT terpadu.',
                        'symptoms' => [
                            'Batuk lebih dari 2 minggu tanpa henti',
                            'Keringat dingin di malam hari tanpa aktivitas fisik',
                            'Penurunan berat badan drastis tanpa sebab jelas',
                            'Batuk bercampur bercak darah (hemoptisis)',
                        ],
                    ],
                    [
                        'title' => 'Nodul Paru & Skrining Kanker Paru',
                        'category' => 'Onkologi Paru',
                        'severity' => 'Kritis',
                        'desc' => 'Deteksi lesi bulat pada jaringan paru melalui Low-Dose CT Scan untuk skrining dini keganasan paru.',
                        'symptoms' => [
                            'Batuk kronis yang tidak merespons obat biasa',
                            'Suara serak menetap lebih dari 3 minggu',
                            'Nyeri konstan pada tulang dada atau punggung',
                            'Sesak napas progresif tanpa riwayat asma',
                        ],
                    ],
                    [
                        'title' => 'Sleep Apnea & Gangguan Napas Saat Tidur',
                        'category' => 'Sleep Medicine',
                        'severity' => 'Sedang',
                        'desc' => 'Penyumbatan jalan napas atas saat tidur yang menyebabkan henti napas periodik dan penurunan kualitas oksigenasi otak.',
                        'symptoms' => [
                            'Mendengkur keras disertai jeda henti napas',
                            'Terbangun mendadak dengan sensasi tercekik',
                            'Rasa kantuk berlebih di siang hari (daytime sleepiness)',
                            'Sakit kepala di pagi hari dan sulit konsentrasi',
                        ],
                    ],
                ],
                'procedures' => [
                    [
                        'title' => 'Spirometri Digital Presisi Tinggi',
                        'category' => 'Diagnostik Fungsi Paru',
                        'duration' => '15–20 Menit',
                        'desc' => 'Uji fungsi ventilasi paru untuk mengukur volume dan kecepatan aliran udara pernapasan, serta respons reversibilitas bronkodilator.',
                        'benefits' => [
                            'Deteksi dini asma dan PPOK sebelum gejala klinis berat',
                            'Prosedur non-invasif tanpa rasa sakit',
                            'Grafik interpretasi otomatis berstandar ATS/ERS',
                        ],
                    ],
                    [
                        'title' => 'Bronkoskopi Fleksibel Video HD 4K',
                        'category' => 'Endoskopi Respirasi',
                        'duration' => '30–45 Menit',
                        'desc' => 'Evaluasi visual saluran trakeobronkial dengan kamera mikro resolusi tinggi untuk biopsi jaringan, bilasan bronkoalveolar (BAL), dan terapi intervensi.',
                        'benefits' => [
                            'Visualisasi mukosa saluran napas sangat detail',
                            'Dilengkapi panduan fluoroskopi untuk biopsi lesi perifer',
                            'Prosedur aman dengan anestesi lokal dan sedasi nyaman',
                        ],
                    ],
                    [
                        'title' => 'Pusat Terapi Inhalasi & Nebulisasi Presisi',
                        'category' => 'Terapi Medikamentosa',
                        'duration' => '15 Menit',
                        'desc' => 'Unit terapi aerosol modern yang mengantarkan obat bronkodilator dan kortikosteroid langsung ke saluran napas bawah dengan ukuran partikel mikro.',
                        'benefits' => [
                            'Peredaan cepat pada serangan sesak asma akut',
                            'Efek samping sistemik sangat minimal',
                            'Didampingi edukasi penggunaan inhaler mandiri yang benar',
                        ],
                    ],
                    [
                        'title' => 'Program Rehabilitasi Paru & Latihan Pernapasan',
                        'category' => 'Rehabilitasi Medik',
                        'duration' => '45–60 Menit per Sesi',
                        'desc' => 'Program latihan fisik, pernapasan diafragma, dan manajemen energi yang dirancang khusus untuk meningkatkan toleransi aktivitas pasien PPOK kronis.',
                        'benefits' => [
                            'Meningkatkan kapasitas latihan fisik dan efisiensi napas',
                            'Mengurangi frekuensi rawat inap akibat eksaserbasi',
                            'Meningkatkan kemandirian dan kualitas hidup pasien',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Kapan waktu yang tepat untuk memeriksakan diri ke dokter spesialis paru?',
                        'a' => 'Segera konsultasikan jika Anda mengalami batuk lebih dari 2 minggu, sesak napas saat aktivitas ringan, batuk berdarah, mengi, nyeri dada saat bernapas, atau memiliki riwayat merokok lebih dari 10 tahun.',
                    ],
                    [
                        'q' => 'Apakah pemeriksaan fungsi paru (Spirometri) memerlukan persiapan khusus?',
                        'a' => 'Pasien disarankan tidak merokok minimal 1 jam sebelum tes, tidak mengonsumsi makanan berat 2 jam sebelumnya, dan berkonsultasi mengenai penghentian sementara obat pelega napas (inhaler) jika diperlukan.',
                    ],
                    [
                        'q' => 'Apakah layanan poliklinik paru melayani pasien BPJS dan asuransi swasta?',
                        'a' => 'Ya, Hospital Population melayani pasien umum, BPJS Kesehatan (dengan rujukan aktif dari faskes pertama), serta lebih dari 50+ mitra asuransi swasta dan korporasi terkemuka.',
                    ],
                    [
                        'q' => 'Bagaimana alur pendaftaran jadwal praktik dokter spesialis paru?',
                        'a' => 'Anda dapat memilih dokter pada tabel jadwal di bawah, lalu klik tombol "Ambil Antrean". Bagi pasien yang telah memiliki akun, reservasi tiket antrean akan langsung diterbitkan secara otomatis.',
                    ],
                ],
            ],
            'kardiologi' => [
                'slug' => 'kardiologi',
                'name' => 'Pusat Jantung, Vaskular, & Aritmia Terpadu',
                'short_name' => 'Jantung & Pembuluh Darah',
                'category' => 'Kardiologi & Kedokteran Vaskular',
                'icon_name' => 'HeartPulse',
                'badge' => 'Pusat Layanan Unggulan Jantung (Cath Lab 24 Jam)',
                'tagline' => 'Detak Jantung Sehat, Kunci Kehidupan Bermakna',
                'description' => 'Layanan komprehensif pencegahan, diagnostik intervensi, dan bedah jantung dengan fasilitas Cath Lab canggih, pemetaan 3D elektrofisiologi, ekokardiografi 4D, dan unit perawatan intensif koroner (ICCU) berstandar global.',
                'quote' => 'Penanganan penyakit jantung adalah perlombaan dengan waktu. Protokol respons cepat kami memastikan setiap menit dimanfaatkan untuk menyelamatkan otot jantung Anda.',
                'metrics' => [
                    [
                        'value' => '< 60 Mnt',
                        'label' => 'Door-to-Balloon Time',
                        'desc' => 'Standar emas penanganan serangan jantung',
                    ],
                    [
                        'value' => '99.4%',
                        'label' => 'Keberhasilan Angioplasti',
                        'desc' => 'Ribuan prosedur pemasangan stent sukses',
                    ],
                    [
                        'value' => '24 Jam',
                        'label' => 'Cath Lab & ICCU Siaga',
                        'desc' => 'Dukungan tim intervensi kardiovaskular',
                    ],
                    [
                        'value' => '6.800+',
                        'label' => 'Pasien Jantung Tertangani',
                        'desc' => 'Klinik rawat jalan & intervensi kateter',
                    ],
                ],
                'conditions' => [
                    [
                        'title' => 'Penyakit Jantung Koroner (PJK)',
                        'category' => 'Iskemik Jantung',
                        'severity' => 'Gawat Darurat',
                        'desc' => 'Penyumbatan pembuluh darah arteri koroner oleh plak aterosklerosis yang mengurangi suplai darah ke otot jantung.',
                        'symptoms' => [
                            'Nyeri dada seperti tertindih beban berat (angina)',
                            'Nyeri menjalar ke lengan kiri, leher, atau rahang',
                            'Keringat dingin dan sesak napas mendadak',
                            'Mual dan pusing berputar saat beraktivitas',
                        ],
                    ],
                    [
                        'title' => 'Aritmia Jantung & Gangguan Irama',
                        'category' => 'Elektrofisiologi',
                        'severity' => 'Tinggi',
                        'desc' => 'Gangguan pada sistem konduksi listrik jantung yang menyebabkan detak jantung terlalu cepat, terlalu lambat, atau tidak beraturan.',
                        'symptoms' => [
                            'Jantung berdebar kencang (palpitasi) tiba-tiba',
                            'Pingsan berulang (sinkop) tanpa sebab jelas',
                            'Rasa melayang dan lemas ekstrem',
                            'Sesak napas saat istirahat',
                        ],
                    ],
                    [
                        'title' => 'Gagal Jantung Kongestif (Heart Failure)',
                        'category' => 'Miokardium',
                        'severity' => 'Tinggi',
                        'desc' => 'Kondisi saat ventrikel jantung tidak mampu memompa darah secara efektif untuk memenuhi kebutuhan metabolisme tubuh.',
                        'symptoms' => [
                            'Sesak napas memberat saat posisi tidur terlentang',
                            'Pembengkakan pada kedua tungkai dan pergelangan kaki',
                            'Cepat lelah saat melakukan aktivitas ringan',
                            'Kenaikan berat badan mendadak akibat retensi cairan',
                        ],
                    ],
                    [
                        'title' => 'Hipertensi Resisten & Komplikasi Vaskular',
                        'category' => 'Vaskular',
                        'severity' => 'Sedang',
                        'desc' => 'Tekanan darah tinggi persisten yang tidak terkontrol dengan kombinasi tiga obat antihipertensi dosis optimal.',
                        'symptoms' => [
                            'Sakit kepala berdenyut di bagian tengkuk',
                            'Penglihatan buram atau berkunang-kunang',
                            'Nyeri dada ringan dan telinga berdenging',
                            'Mudah cemas dan detak jantung tidak stabil',
                        ],
                    ],
                ],
                'procedures' => [
                    [
                        'title' => 'Kateterisasi Jantung & Pasang Stent (PCI)',
                        'category' => 'Intervensi Koroner',
                        'duration' => '45–90 Menit',
                        'desc' => 'Tindakan minimal invasif melalui arteri radialis di pergelangan tangan untuk membuka sumbatan arteri koroner dengan balon dan stent generasi terbaru.',
                        'benefits' => [
                            'Pemulihan cepat, pasien dapat duduk dalam 2 jam',
                            'Bebas luka sayatan bedah terbuka',
                            'Melindungi otot jantung dari kerusakan permanen',
                        ],
                    ],
                    [
                        'title' => 'Ablasi Kateter 3D Elektrofisiologi',
                        'category' => 'Tata Laksana Aritmia',
                        'duration' => '60–120 Menit',
                        'desc' => 'Pemetaan 3D konduksi listrik jantung untuk mengeliminasi titik fokus aritmia dengan energi frekuensi radio presisi tinggi.',
                        'benefits' => [
                            'Menyembuhkan aritmia secara permanen tanpa obat jangka panjang',
                            'Tingkat keberhasilan mencapai lebih dari 95%',
                            'Mengurangi risiko stroke pada atrial fibrilasi',
                        ],
                    ],
                    [
                        'title' => 'Ekokardiografi 4D Doppler Berwarna',
                        'category' => 'Ultrasonografi Jantung',
                        'duration' => '20–30 Menit',
                        'desc' => 'Pemeriksaan USG jantung real-time untuk mengevaluasi struktur katup, fungsi ejeksi fraksi ventrikel, dan ketebalan dinding jantung.',
                        'benefits' => [
                            'Pemeriksaan aman tanpa radiasi',
                            'Akurasi visualisasi ruang jantung dimensi tinggi',
                            'Dapat dilakukan pada pasien segala usia',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Apa perbedaan serangan jantung dengan nyeri lambung biasa?',
                        'a' => 'Nyeri serangan jantung umumnya berupa rasa tertekan atau terbakar di dada tengah yang menjalar ke lengan kiri atau rahang, disertai keringat dingin dan sesak napas. Jika ragu, segera periksakan ke IGD 24 Jam.',
                    ],
                    [
                        'q' => 'Berapa lama waktu pemulihan pasca pemasangan stent jantung?',
                        'a' => 'Sebagian besar pasien dapat pulang dalam kurun waktu 24 hingga 48 jam pasca tindakan kateterisasi dan dapat kembali beraktivitas ringan secara bertahap.',
                    ],
                ],
            ],
            'ortopedi' => [
                'slug' => 'ortopedi',
                'name' => 'Pusat Bedah Ortopedi, Sendi, & Cedera Olahraga',
                'short_name' => 'Ortopedi & Sendi',
                'category' => 'Bedah Ortopedi & Traumatologi',
                'icon_name' => 'Activity',
                'badge' => 'Pusat Navigasi Sendi & Artroskopi',
                'tagline' => 'Bergerak Bebas Nyeri, Kembali Aktif Produktif',
                'description' => 'Layanan terpadu rekonstruksi sendi panggul dan lutut (Total Joint Replacement), artroskopi cedera ligamen olahraga (ACL/PCL), operasi tulang belakang minimal invasif, serta rehabilitasi fisioterapi terintegrasi.',
                'quote' => 'Setiap langkah Anda berharga. Inovasi bedah minimal invasif kami dirancang untuk memulihkan fungsi gerak alami sendi dalam waktu sesingkat mungkin.',
                'metrics' => [
                    [
                        'value' => '98.8%',
                        'label' => 'Tingkat Keberhasilan Implan',
                        'desc' => 'Penggantian sendi lutut & panggul',
                    ],
                    [
                        'value' => 'Day-1',
                        'label' => 'Mobilisasi Pasca Operasi',
                        'desc' => 'Pasien dapat berdiri di hari pertama',
                    ],
                    [
                        'value' => '3.400+',
                        'label' => 'Operasi Sendi Sukses',
                        'desc' => 'Rekonstruksi & artroskopi ligamen',
                    ],
                    [
                        'value' => '100%',
                        'label' => 'Implan Medis Berstandar FDA',
                        'desc' => 'Ketahanan jangka panjang hingga 25 tahun',
                    ],
                ],
                'conditions' => [
                    [
                        'title' => 'Osteoarthritis Lutut & Panggul (Pengapuran Sendi)',
                        'category' => 'Degeneratif Sendi',
                        'severity' => 'Tinggi',
                        'desc' => 'Kerusakan tulang rawan sendi akibat penuaan atau beban berlebih yang menimbulkan nyeri kaku saat berjalan.',
                        'symptoms' => [
                            'Nyeri tumpul pada sendi saat menaiki tangga atau berdiri',
                            'Bunyi gemeretak (krepitasi) pada lutut',
                            'Kekakuan sendi di pagi hari lebih dari 30 menit',
                            'Bentuk kaki berubah menyerupai huruf O atau X',
                        ],
                    ],
                    [
                        'title' => 'Cedera Ligamen Lutut (ACL, PCL, Meniskus)',
                        'category' => 'Cedera Olahraga',
                        'severity' => 'Sedang',
                        'desc' => 'Robekan pada ligamen penstabil sendi lutut akibat benturan atau gerakan memutar mendadak saat berolahraga.',
                        'symptoms' => [
                            'Sensasi bunyi "pop" di lutut saat cedera terjadi',
                            'Lutut membengkak cepat dalam beberapa jam',
                            'Lutut terasa tidak stabil atau goyang saat bertumpu',
                            'Sulit meluruskan lutut secara penuh',
                        ],
                    ],
                ],
                'procedures' => [
                    [
                        'title' => 'Total Knee Replacement (TKR) Berbantuan Navigasi',
                        'category' => 'Rekonstruksi Sendi',
                        'duration' => '60–90 Menit',
                        'desc' => 'Penggantian bantalan sendi lutut yang rusak dengan implan presisi tinggi berteknologi navigasi digital untuk akurasi sub-milimeter.',
                        'benefits' => [
                            'Menghilangkan rasa nyeri lutut menahun secara tuntas',
                            'Rentang gerak sendi kembali lentur dan stabil',
                            'Pasien dilatih berjalan sejak 24 jam pasca operasi',
                        ],
                    ],
                    [
                        'title' => 'Artroskopi Rekonstruksi ACL Minimal Invasif',
                        'category' => 'Bedah Teropong Sendi',
                        'duration' => '45–60 Menit',
                        'desc' => 'Rekonstruksi ligamen lutut melalui sayatan mikro dengan kamera serat optik beresolusi tinggi.',
                        'benefits' => [
                            'Sayatan sangat kecil dengan bekas luka minimal',
                            'Nyeri pasca bedah jauh lebih ringan',
                            'Waktu pemulihan kembali ke aktivitas olahraga lebih cepat',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Berapa lama ketahanan implan penggantian sendi lutut?',
                        'a' => 'Dengan teknologi implan biokompatibel modern dan penempatan presisi berbantuan navigasi, implan sendi dapat bertahan 20 hingga 25 tahun dengan perawatan yang baik.',
                    ],
                ],
            ],
            'onkologi' => [
                'slug' => 'onkologi',
                'name' => 'Pusat Onkologi & Kanker Terpadu (Integrated Cancer Care)',
                'short_name' => 'Onkologi & Kanker',
                'category' => 'Onkologi Medis & Bedah Tumor',
                'icon_name' => 'ShieldAlert',
                'badge' => 'Pusat Perawatan Kanker Komprehensif',
                'tagline' => 'Harapan Baru Melalui Ketepatan Terapi Bertarget',
                'description' => 'Penanganan kanker multidisiplin (Tumor Board) mencakup deteksi dini, bedah onkologi presisi, kemoterapi modern, imunoterapi bertarget, dan pendampingan psikososial komprehensif bagi pasien dan keluarga.',
                'quote' => 'Di balik setiap diagnosis terdapat sebuah perjuangan hidup. Kami memadukan sains tercanggih dengan kehangatan empati untuk mendampingi Anda melewati setiap tahap.',
                'metrics' => [
                    [
                        'value' => '94.5%',
                        'label' => 'Remisi Stadium Dini',
                        'desc' => 'Melalui protokol skrining & deteksi berkala',
                    ],
                    [
                        'value' => '100%',
                        'label' => 'Multidisiplin Tumor Board',
                        'desc' => 'Rencana terapi diputuskan tim konsultan',
                    ],
                    [
                        'value' => '24/7',
                        'label' => 'Unit Kemoterapi & Paliatif',
                        'desc' => 'Kenyamanan dan keselamatan terjamin',
                    ],
                    [
                        'value' => '2.100+',
                        'label' => 'Pasien Kanker Terdampingi',
                        'desc' => 'Program terapi personal & imunoterapi',
                    ],
                ],
                'conditions' => [
                    [
                        'title' => 'Kanker Payudara & Tumor Jinak Payudara',
                        'category' => 'Onkologi Mammae',
                        'severity' => 'Tinggi',
                        'desc' => 'Pertumbuhan sel abnormal pada jaringan payudara yang dideteksi melalui skrining Mammografi 3D dan biopsi presisi.',
                        'symptoms' => [
                            'Benjolan tanpa nyeri pada payudara atau ketiak',
                            'Perubahan bentuk atau tekstur kulit payudara',
                            'Cairan abnormal atau darah keluar dari puting',
                            'Puting tertarik ke dalam (retraksi)',
                        ],
                    ],
                ],
                'procedures' => [
                    [
                        'title' => 'Breast Conserving Surgery & Onkoplasti',
                        'category' => 'Bedah Payudara Presisi',
                        'duration' => '60–120 Menit',
                        'desc' => 'Operasi pengangkatan jaringan kanker payudara dengan mempertahankan bentuk alami payudara serta batas sayatan yang bersih dari sel ganas.',
                        'benefits' => [
                            'Mempertahankan estetika bentuk payudara',
                            'Tingkat kesembuhan setara mastektomi total pada stadium dini',
                            'Mendukung kepercayaan diri dan pemulihan mental pasien',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Kapan perempuan dianjurkan melakukan Mammografi?',
                        'a' => 'Perempuan berusia 40 tahun ke atas dianjurkan melakukan skrining Mammografi rutin setiap 1–2 tahun, atau lebih dini jika memiliki riwayat keluarga dengan kanker payudara.',
                    ],
                ],
            ],
            'pediatri' => [
                'slug' => 'pediatri',
                'name' => 'Kesehatan Ibu, Anak, & Tumbuh Kembang (Pediatri)',
                'short_name' => 'Ibu & Anak',
                'category' => 'Pediatri & Kesehatan Neonatal',
                'icon_name' => 'Heart',
                'badge' => 'Unit Rawat Intensif NICU/PICU Level 3',
                'tagline' => 'Tumbuh Kembang Optimal Buah Hati Tercinta',
                'description' => 'Layanan ramah anak mencakup imunisasi lengkap, skrining tumbuh kembang, penanganan infeksi anak, gizi klinis, serta unit perawatan intensif bayi baru lahir (NICU) dan anak (PICU) dengan tim dokter subspesialis pediatri.',
                'quote' => 'Kesehatan anak adalah investasi masa depan. Kami menciptakan lingkungan medis yang ramah, nyaman, dan bebas trauma bagi si kecil.',
                'metrics' => [
                    [
                        'value' => '99.7%',
                        'label' => 'Kepuasan Ibu & Keluarga',
                        'desc' => 'Fasilitas ramah anak & perawat hangat',
                    ],
                    [
                        'value' => 'Level 3',
                        'label' => 'Akreditasi NICU & PICU',
                        'desc' => 'Siaga ventilator neonatus 24 jam',
                    ],
                    [
                        'value' => '15+',
                        'label' => 'Dokter Spesialis Anak',
                        'desc' => 'Subspesialis respirologi, alergi, & saraf',
                    ],
                    [
                        'value' => '8.000+',
                        'label' => 'Anak & Balita Terlayani',
                        'desc' => 'Vaksinasi, tumbuh kembang, & rawat inap',
                    ],
                ],
                'conditions' => [
                    [
                        'title' => 'Pneumonia Anak & Infeksi Saluran Napas Akut',
                        'category' => 'Respirologi Anak',
                        'severity' => 'Gawat Darurat',
                        'desc' => 'Infeksi paru pada bayi dan balita yang dapat memicu napas cepat dan penurunan saturasi oksigen.',
                        'symptoms' => [
                            'Napas cepat disertai tarikan dinding dada (retraksi)',
                            'Demam tinggi dan nafsu makan/minum menurun drastis',
                            'Batuk grok-grok yang tidak kunjung reda',
                            'Bayi tampak rewel, lemas, atau bibir kebiruan',
                        ],
                    ],
                ],
                'procedures' => [
                    [
                        'title' => 'Skrining Tumbuh Kembang Denver II & Terapi Stimulasi',
                        'category' => 'Tumbuh Kembang Anak',
                        'duration' => '30–45 Menit',
                        'desc' => 'Pemeriksaan komprehensif motorik kasar, motorik halus, bahasa, dan sosial personal untuk deteksi dini keterlambatan tumbuh kembang.',
                        'benefits' => [
                            'Intervensi dini keterlambatan bicara (speech delay)',
                            'Panduan stimulasi sensorik sesuai usia anak',
                            'Konsultasi terpadu dokter spesialis anak & psikolog',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Apakah jadwal imunisasi anak di Hospital Population mengikuti rekomendasi IDAI?',
                        'a' => 'Ya, kami menyediakan vaksinasi lengkap wajib dan tambahan sesuai jadwal terbaru Ikatan Dokter Anak Indonesia (IDAI) dengan rantai dingin (cold chain) vaksin yang terjamin mutunya.',
                    ],
                ],
            ],
            'penyakit-dalam' => [
                'slug' => 'penyakit-dalam',
                'name' => 'Pusat Penyakit Dalam, Diabetes, & Saluran Cerna (Gastroenterohepatologi)',
                'short_name' => 'Penyakit Dalam',
                'category' => 'Penyakit Dalam & Endokrinologi',
                'icon_name' => 'Droplets',
                'badge' => 'Pusat Endoskopi Saluran Cerna Modern',
                'tagline' => 'Keseimbangan Metabolik & Kesehatan Organ Dalam',
                'description' => 'Pelayanan menyeluruh untuk penyakit diabetes melitus, hipertensi, gangguan tiroid, penyakit hati/liver, ginjal, serta endoskopi saluran cerna atas dan bawah (EGD/Kolonoskopi) dengan visualisasi resolusi tinggi.',
                'quote' => 'Kesehatan organ dalam berakar pada keseimbangan metabolik dan diagnosis presisi untuk mencegah komplikasi jangka panjang.',
                'metrics' => [
                    [
                        'value' => '99.1%',
                        'label' => 'Akurasi Endoskopi HD',
                        'desc' => 'Deteksi lesi mukosa lambung & usus',
                    ],
                    [
                        'value' => '12+',
                        'label' => 'Konsultan Subspesialis',
                        'desc' => 'Gastroenterologi, Ginjal-Hipertensi, Endokrin',
                    ],
                    [
                        'value' => '24/7',
                        'label' => 'Layanan Hemodialisa Modern',
                        'desc' => 'Kenyamanan cuci darah dengan mesin terbaru',
                    ],
                    [
                        'value' => '7.500+',
                        'label' => 'Pasien Metabolik Terkelola',
                        'desc' => 'Program pemantauan gula darah & diet klinis',
                    ],
                ],
                'conditions' => [
                    [
                        'title' => 'GERD Berat & Esofagitis Refluks',
                        'category' => 'Saluran Cerna Atas',
                        'severity' => 'Sedang',
                        'desc' => 'Naiknya asam lambung ke kerongkongan yang menyebabkan iritasi kronis dan sensasi terbakar di dada.',
                        'symptoms' => [
                            'Rasa panas/terbakar di dada (heartburn) setelah makan',
                            'Sensasi asam atau pahit di pangkal tenggorokan',
                            'Sulit menelan atau terasa ada ganjalan di leher',
                            'Batuk kronis yang memburuk saat berbaring',
                        ],
                    ],
                ],
                'procedures' => [
                    [
                        'title' => 'Endoskopi Saluran Cerna Atas (EGD) & Kolonoskopi',
                        'category' => 'Diagnostik Saluran Cerna',
                        'duration' => '20–40 Menit',
                        'desc' => 'Pemeriksaan visual mukosa lambung dan usus besar dengan kamera serat optik HD untuk evaluasi luka, polip, atau sumber pendarahan.',
                        'benefits' => [
                            'Prosedur nyaman dengan sedasi ringan tanpa rasa nyeri',
                            'Dapat langsung dilakukan biopsi atau pengangkatan polip',
                            'Mendeteksi dini kanker lambung dan kolorektal',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Apakah tindakan endoskopi lambung menimbulkan rasa sakit?',
                        'a' => 'Tidak. Pasien akan diberikan anestesi tenggorokan dan sedasi ringan yang aman sehingga proses berlangsung nyaman tanpa rasa nyeri.',
                    ],
                ],
            ],
        ];
    }
}
