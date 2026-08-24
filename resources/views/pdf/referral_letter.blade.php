<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Rujukan Pasien - {{ $patient->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 20mm 20mm 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            font-size: 10.5pt;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
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
            font-size: 12.5pt;
            margin: 15px 0 5px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .doc-number {
            text-align: center;
            font-size: 9.5pt;
            color: #555;
            margin-bottom: 15px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-table td {
            padding: 3px 5px;
            vertical-align: top;
            font-size: 10pt;
        }
        .meta-table td.label {
            width: 25%;
            font-weight: 500;
        }
        .meta-table td.colon {
            width: 2%;
        }
        .box {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 10px;
            background: #fafafa;
            font-size: 10pt;
        }
        .box-title {
            font-weight: bold;
            color: #065f46;
            margin-bottom: 4px;
        }
        .signature-section {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-section td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 10pt;
        }
        .stamp-box {
            height: 60px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>RUMAH SAKIT UMUM POPULATION HEALTHCARE</h1>
        <p>Jl. Pelayanan Sehat No. 128, Jakarta Pusat | Telp: (021) 555-8900 | Email: layanan@hospital.id</p>
        <p>Pelayanan Rujukan Medis Antar Rumah Sakit & Fasilitas Kesehatan Lanjutan</p>
    </div>

    <div class="doc-title">SURAT RUJUKAN PASIEN RAWAT JALAN</div>
    <div class="doc-number">Nomor: SRJ/{{ date('Ym') }}/{{ str_pad((string)$appointment->appointment_id, 4, '0', STR_PAD_LEFT) }}</div>

    <p>
        Kepada Yth. <strong>TS Dokter Spesialis Konsultan / Rumah Sakit Rujukan Lanjutan</strong><br>
        Di Tempat
    </p>

    <p>Dengan hormat,</p>
    <p>Bersama ini kami mohon pemeriksaan dan penanganan lebih lanjut terhadap pasien dengan identitas berikut:</p>

    <table class="meta-table">
        <tr>
            <td class="label">Nama Pasien</td>
            <td class="colon">:</td>
            <td><strong>{{ $patient->name }}</strong></td>
            <td class="label">Nomor Rekam Medis</td>
            <td class="colon">:</td>
            <td>{{ $patient->resident_n }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin / Usia</td>
            <td class="colon">:</td>
            <td>{{ $patient->gender ?? '-' }} ({{ \Carbon\Carbon::parse($patient->birthday_date)->age }} tahun)</td>
            <td class="label">Tanggal Kunjungan</td>
            <td class="colon">:</td>
            <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <div class="box">
        <div class="box-title">ANAMNESIS & KELUHAN PASIEN:</div>
        {{ $medicalRecord?->subjective ?? $appointment->complaint ?? 'Keluhan rawat jalan.' }}
    </div>

    <div class="box">
        <div class="box-title">PEMERIKSAAN FISIK & TANDA VITAL:</div>
        @php
            $vitals = is_array($medicalRecord?->objective) ? $medicalRecord->objective : (json_decode($medicalRecord?->objective ?? '[]', true) ?? []);
        @endphp
        TD: {{ $vitals['systolic'] ?? '-' }}/{{ $vitals['diastolic'] ?? '-' }} mmHg | Nadi: {{ $vitals['pulse'] ?? '-' }} bpm | Suhu: {{ $vitals['temperature'] ?? '-' }} &deg;C | RR: {{ $vitals['respiratory_rate'] ?? '-' }} x/m<br>
        Catatan Fisik: {{ $medicalRecord?->physical_check ?? 'Dalam batas observasi klinis.' }}
    </div>

    <div class="box">
        <div class="box-title">DIAGNOSIS KERJA:</div>
        <strong>{{ $medicalRecord?->assessment ?? 'Memerlukan pemeriksaan sub-spesialistik lanjutan' }}</strong>
    </div>

    <div class="box">
        <div class="box-title">TERAPI / TINDAKAN YANG TELAH DIBERIKAN:</div>
        {{ $medicalRecord?->plan ?? 'Terapi medikamentosa awal rawat jalan.' }}
    </div>

    <div class="box">
        <div class="box-title">ALASAN RUJUKAN:</div>
        Evaluasi diagnostik lanjutan, tatalaksana sub-spesialistik, dan penanganan fasilitas medik terpadu.
    </div>

    <p>Demikian surat rujukan ini kami sampaikan. Atas bantuan dan kerjasama rekan sejawat, kami ucapkan terima kasih.</p>

    <table class="signature-section">
        <tr>
            <td>
                Perawat Pendamping / Triage,<br>
                <div class="stamp-box"></div>
                <strong>({{ $nurse?->name ?? 'Staf Rawat Jalan' }})</strong>
            </td>
            <td>
                Jakarta, {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}<br>
                Salam Sejawat,<br>
                <div class="stamp-box"></div>
                <strong><u>{{ $appointment->doctorSchedule?->doctor?->name ?? 'dr. Spesialis' }}</u></strong><br>
                <small>SIP: {{ $appointment->doctorSchedule?->doctor?->sip_number ?? 'SIP-MED-2026' }}</small>
            </td>
        </tr>
    </table>

</body>
</html>
