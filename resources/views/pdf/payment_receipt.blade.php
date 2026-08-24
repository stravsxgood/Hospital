<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Pembayaran - {{ $billing->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            font-size: 10.5pt;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #111;
            padding-bottom: 8px;
            margin-bottom: 12px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 15pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 2px 0;
            font-size: 8.5pt;
            color: #444;
        }
        .receipt-title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin: 12px 0 2px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .invoice-pill {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 15px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .meta-table td {
            padding: 3px 5px;
            vertical-align: top;
            font-size: 10pt;
        }
        .meta-table td.label {
            width: 22%;
            font-weight: 600;
            color: #333;
        }
        .meta-table td.colon {
            width: 2%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9.5pt;
        }
        .items-table th, .items-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
        }
        .items-table th {
            background-color: #f3f4f6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .items-table td.amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9fafb;
            font-size: 10.5pt;
        }
        .paid-stamp {
            display: inline-block;
            border: 2px solid #059669;
            color: #059669;
            padding: 4px 12px;
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
            transform: rotate(-4deg);
        }
        .signatures {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .note {
            font-size: 8pt;
            color: #666;
            margin-top: 15px;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>RUMAH SAKIT UMUM POPULATION HEALTHCARE</h1>
        <p>Jl. Pelayanan Sehat No. 128, Jakarta Pusat | Telp: (021) 555-8900 | Email: billing@hospital.id</p>
        <p>Kuitansi Resmi Pembayaran Pelayanan Rawat Jalan & Farmasi</p>
    </div>

    <div class="receipt-title">KUITANSI PEMBAYARAN KASIR</div>
    <div class="invoice-pill">No. Invoice: {{ $billing->invoice_number }}</div>

    <table class="meta-table">
        <tr>
            <td class="label">Nama Pasien</td>
            <td class="colon">:</td>
            <td><strong>{{ $billing->patient?->name }}</strong></td>
            <td class="label">Tanggal Kuitansi</td>
            <td class="colon">:</td>
            <td>{{ \Carbon\Carbon::parse($billing->paid_at ?? $billing->created_at)->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="label">No. Rekam Medis</td>
            <td class="colon">:</td>
            <td>{{ $billing->patient?->resident_n }}</td>
            <td class="label">Metode Pembayaran</td>
            <td class="colon">:</td>
            <td><strong>{{ strtoupper(str_replace('_', ' ', $billing->payment_method ?? 'CASH')) }}</strong></td>
        </tr>
        <tr>
            <td class="label">Poliklinik</td>
            <td class="colon">:</td>
            <td>{{ $billing->reservation?->doctorSchedule?->poli?->name_poli ?? 'Rawat Jalan' }}</td>
            <td class="label">Status Pembayaran</td>
            <td class="colon">:</td>
            <td>
                @if($billing->status === 'paid')
                    <span class="paid-stamp">LUNAS</span>
                @else
                    <strong style="color: #d97706;">BELUM LUNAS</strong>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Dokter Pemeriksa</td>
            <td class="colon">:</td>
            <td colspan="4">{{ $billing->reservation?->doctorSchedule?->doctor?->name ?? 'dr. Spesialis' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 50%;">Uraian Item / Layanan Medis</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 17%; text-align: right;">Tarif Satuan</th>
                <th style="width: 18%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($billing->items as $idx => $item)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>
                        {{ $item->item_name }}
                    </td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td class="amount">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                    <td class="amount">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #777;">Tidak ada rincian item tagihan.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">TOTAL PEMBAYARAN:</td>
                <td class="amount">Rp {{ number_format((float) $billing->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Pembayar / Pasien,<br><br><br><br>
                <strong>({{ $billing->patient?->name }})</strong>
            </td>
            <td>
                Jakarta, {{ \Carbon\Carbon::parse($billing->paid_at ?? now())->translatedFormat('d F Y') }}<br>
                Kasir / Petugas Penerima,<br><br><br><br>
                <strong>({{ $billing->processedByNurse?->name ?? auth()->user()?->nurse?->name ?? 'Kasir Rumah Sakit' }})</strong><br>
                <small>NIP: {{ $billing->processedByNurse?->registration_number ?? '-' }}</small>
            </td>
        </tr>
    </table>

    <div class="note">
        * Kuitansi ini merupakan bukti pembayaran resmi yang sah dari RS Umum Population Healthcare.<br>
        * Mohon simpan kuitansi ini dengan baik untuk klaim asuransi atau riwayat administrasi pasien.
    </div>

</body>
</html>
