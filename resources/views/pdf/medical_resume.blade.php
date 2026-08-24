<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Resume Medis Rawat Jalan - {{ $patient->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            font-size: 11pt;
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
            font-size: 16pt;
            letter-spacing: 0.5px;
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
            margin: 12px 0 15px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .meta-table, .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table td {
            padding: 3px 6px;
            vertical-align: top;
            font-size: 10pt;
        }
        .meta-table td.label {
            width: 25%;
            font-weight: 600;
            color: #333;
        }
        .meta-table td.colon {
            width: 2%;
        }
        .section-header {
            background-color: #f2f5f3;
            border-left: 4px solid #10b981;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10.5pt;
            margin: 12px 0 6px 0;
        }
        .soap-box {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 8px;
            background-color: #fafafa;
            font-size: 10pt;
        }
        .prescription-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 9.5pt;
        }
        .prescription-table th, .prescription-table td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            text-align: left;
        }
        .prescription-table th {
            background-color: #f3f4f6;
            font-weight: 600;
        }
        .vitals-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 9.5pt;
        }
        .vitals-grid td {
            border: 1px solid #e5e7eb;
            padding: 4px 8px;
            background: #fff;
        }
        .footer-signatures {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .footer-signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
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
        <p>Instalasi Rawat Jalan & Rekam Medis Elektronik Terpadu</p>
    </div>

    <div class="doc-title">RESUME MEDIS RAWAT JALAN (EMR)</div>

    <table class="meta-table">
        <tr>
            <td class="label">Nomor Rekam Medis</td>
            <td class="colon">:</td>
            <td><strong>{{ $patient->resident_n }}</strong></td>
            <td class="label">Tanggal Kunjungan</td>
            <td class="colon">:</td>
            <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Nama Pasien</td>
            <td class="colon">:</td>
            <td>{{ $patient->name }}</td>
            <td class="label">Nomor Antrean</td>
            <td class="colon">:</td>
            <td>{{ $appointment->queue_number }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin / Usia</td>
            <td class="colon">:</td>
            <td>{{ $patient->gender ?? '-' }} ({{ \Carbon\Carbon::parse($patient->birthday_date)->age }} tahun)</td>
            <td class="label">Poliklinik Tujuan</td>
            <td class="colon">:</td>
            <td>{{ $appointment->doctorSchedule?->poli?->name_poli ?? 'Poliklinik' }}</td>
        </tr>
        <tr>
            <td class="label">Dokter Penanggung Jawab</td>
            <td class="colon">:</td>
            <td colspan="4"><strong>{{ $appointment->doctorSchedule?->doctor?->name ?? 'dr. Spesialis' }}</strong> ({{ $appointment->doctorSchedule?->doctor?->specialization?->name_specialization ?? 'Umum' }})</td>
        </tr>
    </table>

    <div class="section-header">1. TANDA-TANDA VITAL & FISIK</div>
    @php
        $vitals = is_array($medicalRecord?->objective) ? $medicalRecord->objective : (json_decode($medicalRecord?->objective ?? '[]', true) ?? []);
    @endphp
    <table class="vitals-grid">
        <tr>
            <td><strong>Tekanan Darah:</strong> {{ $vitals['systolic'] ?? '-' }}/{{ $vitals['diastolic'] ?? '-' }} mmHg</td>
            <td><strong>Denyut Nadi:</strong> {{ $vitals['pulse'] ?? '-' }} bpm</td>
            <td><strong>Suhu Tubuh:</strong> {{ $vitals['temperature'] ?? '-' }} &deg;C</td>
        </tr>
        <tr>
            <td><strong>Laju Napas:</strong> {{ $vitals['respiratory_rate'] ?? '-' }} x/menit</td>
            <td><strong>Tinggi / Berat:</strong> {{ $vitals['height'] ?? '-' }} cm / {{ $vitals['weight'] ?? '-' }} kg</td>
            <td><strong>SpO2:</strong> {{ $vitals['oxygen_saturation'] ?? 98 }}%</td>
        </tr>
    </table>

    <div class="section-header">2. CATATAN MEDIS SOAP</div>
    <div class="soap-box">
        <strong>Subjective (Anamnesis & Keluhan Utama):</strong><br>
        {{ $medicalRecord?->subjective ?? $appointment->complaint ?? 'Tidak ada keluhan tercatat.' }}
    </div>
    <div class="soap-box">
        <strong>Objective (Pemeriksaan Fisik & Penunjang):</strong><br>
        {{ $medicalRecord?->physical_check ?? 'Pemeriksaan fisik dalam batas normal.' }}
    </div>
    <div class="soap-box">
        <strong>Assessment (Diagnosis Medis / ICD-10):</strong><br>
        <strong>{{ $medicalRecord?->assessment ?? 'Observasi Klinis Rawat Jalan' }}</strong>
    </div>
    <div class="soap-box">
        <strong>Plan (Rencana Tindakan & Terapi):</strong><br>
        {{ $medicalRecord?->plan ?? 'Edukasi dan terapi rawat jalan.' }}
    </div>

    @if($medicalRecord?->prescription && $medicalRecord->prescription->items && $medicalRecord->prescription->items->isNotEmpty())
        <div class="section-header">3. RESEP OBAT ELEKTRONIK (E-PRESCRIPTION)</div>
        <table class="prescription-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 45%;">Nama Obat & Sediaan</th>
                    <th style="width: 15%;">Jumlah</th>
                    <th style="width: 35%;">Aturan Pakai (Signa)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicalRecord->prescription->items as $idx => $item)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>{{ $item->medicine?->name_medicine ?? $item->medicine_id }}</td>
                        <td style="text-align: center;">{{ $item->quantity }} {{ $item->medicine?->unit ?? 'Unit' }}</td>
                        <td>{{ $item->dosage }} - {{ $item->instructions ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="footer-signatures">
        <tr>
            <td>
                Petugas Verifikator / Perawat,<br>
                <div class="stamp-box"></div>
                <strong>({{ $nurse?->name ?? 'Staf Rawat Jalan' }})</strong><br>
                <small>NIP/NIRA: {{ $nurse?->registration_number ?? '-' }}</small>
            </td>
            <td>
                Jakarta, {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}<br>
                Dokter Pemeriksa,<br>
                <div class="stamp-box"></div>
                <strong>({{ $appointment->doctorSchedule?->doctor?->name ?? 'dr. Spesialis' }})</strong><br>
                <small>SIP: {{ $appointment->doctorSchedule?->doctor?->sip_number ?? '-' }}</small>
            </td>
        </tr>
    </table>

</body>
</html>
