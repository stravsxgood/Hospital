<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Sakit - {{ $patient->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 25mm 20mm 25mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            font-size: 11pt;
            line-height: 1.6;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 15pt;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            font-size: 9pt;
            color: #444;
        }
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin: 20px 0 5px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .doc-number {
            text-align: center;
            font-size: 10pt;
            color: #555;
            margin-bottom: 25px;
        }
        .content {
            text-align: justify;
            margin-bottom: 20px;
        }
        .identity-table {
            width: 100%;
            margin: 15px 0 20px 20px;
            border-collapse: collapse;
        }
        .identity-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .identity-table td.label {
            width: 25%;
            font-weight: 500;
        }
        .identity-table td.colon {
            width: 3%;
        }
        .signature-section {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .signature-section td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 10.5pt;
        }
        .stamp-placeholder {
            height: 70px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>RUMAH SAKIT UMUM POPULATION HEALTHCARE</h1>
        <p>Jl. Pelayanan Sehat No. 128, Jakarta Pusat | Telp: (021) 555-8900 | Email: layanan@hospital.id</p>
        <p>Surat Keterangan Medis & Pelayanan Kesehatan</p>
    </div>

    <div class="doc-title">SURAT KETERANGAN SAKIT</div>
    <div class="doc-number">Nomor: SKD/{{ date('Ym') }}/{{ str_pad((string)$appointment->appointment_id, 4, '0', STR_PAD_LEFT) }}</div>

    <div class="content">
        Yang bertanda tangan di bawah ini, Dokter Pemeriksa Rumah Sakit Umum Population Healthcare menerangkan dengan sebenarnya bahwa:
    </div>

    <table class="identity-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="colon">:</td>
            <td><strong>{{ $patient->name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nomor Induk Kependudukan</td>
            <td class="colon">:</td>
            <td>{{ $patient->resident_n }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin / Usia</td>
            <td class="colon">:</td>
            <td>{{ $patient->gender ?? '-' }} ({{ \Carbon\Carbon::parse($patient->birthday_date)->age }} Tahun)</td>
        </tr>
        <tr>
            <td class="label">Alamat / Domisili</td>
            <td class="colon">:</td>
            <td>{{ $patient->address ?? 'Jakarta' }}</td>
        </tr>
        <tr>
            <td class="label">Diagnosis Klinis</td>
            <td class="colon">:</td>
            <td><strong>{{ $medicalRecord?->assessment ?? 'Gangguan Kesehatan / Memerlukan Istirahat' }}</strong></td>
        </tr>
    </table>

    <div class="content">
        Berdasarkan hasil pemeriksaan klinis yang telah dilakukan, pasien tersebut di atas berada dalam keadaan sakit dan memerlukan <strong>istirahat medis selama {{ $days ?? 3 }} (tiga) hari</strong>, terhitung mulai tanggal <strong>{{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d F Y') }}</strong> sampai dengan <strong>{{ \Carbon\Carbon::parse($appointment->appointment_date)->addDays(($days ?? 3) - 1)->translatedFormat('d F Y') }}</strong>.
    </div>

    <div class="content">
        Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.
    </div>

    <table class="signature-section">
        <tr>
            <td></td>
            <td>
                Jakarta, {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}<br>
                Dokter Pemeriksa,<br>
                <div class="stamp-placeholder"></div>
                <strong><u>{{ $appointment->doctorSchedule?->doctor?->name ?? 'dr. Spesialis' }}</u></strong><br>
                <small>SIP: {{ $appointment->doctorSchedule?->doctor?->sip_number ?? 'SIP-MED-2026' }}</small>
            </td>
        </tr>
    </table>

</body>
</html>
