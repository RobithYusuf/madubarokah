<!-- Summary Cards untuk Pengiriman -->
<div class="summary-cards">
    <div class="summary-card">
        <h3>Total Pengiriman</h3>
        <div class="value">{{ $pengirimanData->count() ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <h3>Total Biaya</h3>
        <div class="value text-success">Rp {{ number_format($pengirimanData->sum('total_biaya') ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="summary-card">
        <h3>Rata-rata Biaya</h3>
        <div class="value text-info">Rp {{ $pengirimanData->count() > 0 ? number_format($pengirimanData->avg('rata_rata_biaya'), 0, ',', '.') : 0 }}</div>
    </div>
    <div class="summary-card">
        <h3>Total Berat</h3>
        <div class="value text-warning">{{ number_format($pengirimanData->sum('total_berat') / 1000, 1) }} kg</div>
    </div>
</div>

<!-- Tabel Performa Kurir -->
<h3 style="margin-bottom: 15px; color: #2c3e50;">🚚 Performa Kurir & Layanan</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="15%">Kurir</th>
            <th width="20%">Layanan</th>
            <th width="12%">Penggunaan</th>
            <th width="18%">Total Biaya</th>
            <th width="15%">Rata-rata</th>
            <th width="12%">Total Berat</th>
            <th width="8%">%</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($pengirimanData) && $pengirimanData->count() > 0)
            @php
                $totalPenggunaan = $pengirimanData->sum('jumlah_penggunaan');
            @endphp
            @foreach($pengirimanData as $data)
            @php
                $popularitas = $totalPenggunaan > 0 ? ($data->jumlah_penggunaan / $totalPenggunaan) * 100 : 0;
                
                // Warna kurir
                $kurirColors = [
                    'jne' => '#4e73df',
                    'tiki' => '#28a745', 
                    'pos' => '#ffc107',
                    'j&t' => '#dc3545',
                    'sicepat' => '#17a2b8',
                    'anteraja' => '#343a40'
                ];
                $kurirColor = $kurirColors[strtolower($data->kurir)] ?? '#6c757d';
            @endphp
            <tr>
                <td class="text-center">
                    <span class="badge" style="background-color: {{ $kurirColor }}; color: white;">
                        {{ strtoupper($data->kurir) }}
                    </span>
                </td>
                <td><strong>{{ $data->layanan }}</strong></td>
                <td class="text-center">
                    <strong>{{ number_format($data->jumlah_penggunaan) }}</strong><br>
                    <small class="text-muted">kali</small>
                </td>
                <td class="text-right">
                    <strong class="text-success">Rp {{ number_format($data->total_biaya, 0, ',', '.') }}</strong><br>
                    <small class="text-muted">{{ number_format(($data->total_biaya / $pengirimanData->sum('total_biaya')) * 100, 1) }}% total</small>
                </td>
                <td class="text-right">
                    <strong>Rp {{ number_format($data->rata_rata_biaya, 0, ',', '.') }}</strong>
                </td>
                <td class="text-center">
                    <strong>{{ number_format($data->total_berat / 1000, 1) }} kg</strong><br>
                    <small class="text-muted">{{ number_format($data->total_berat) }}g</small>
                </td>
                <td class="text-center">
                    <strong>{{ number_format($popularitas, 1) }}%</strong>
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="7" class="text-center">
                    <div style="padding: 40px; color: #6c757d;">
                        <strong>Tidak Ada Data Pengiriman</strong><br>
                        <small>Tidak ada data pengiriman untuk periode yang dipilih</small>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>

<div style="margin: 30px 0;"></div>

<!-- Status Pengiriman -->
<h3 style="margin-bottom: 15px; color: #2c3e50;">📊 Status Pengiriman</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="40%">Status</th>
            <th width="20%">Jumlah</th>
            <th width="20%">Persentase</th>
            <th width="20%">Visual</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($statusPengiriman) && $statusPengiriman->count() > 0)
            @php
                $totalStatus = $statusPengiriman->sum('jumlah');
                $statusColors = [
                    'menunggu_pembayaran' => '#6c757d',
                    'diproses' => '#ffc107',
                    'dikirim' => '#17a2b8',
                    'diterima' => '#28a745',
                    'dibatalkan' => '#dc3545'
                ];
            @endphp
            @foreach($statusPengiriman as $status)
            @php
                $percentage = $totalStatus > 0 ? ($status->jumlah / $totalStatus) * 100 : 0;
                $statusColor = $statusColors[$status->status] ?? '#6c757d';
            @endphp
            <tr>
                <td>
                    <span class="badge" style="background-color: {{ $statusColor }}; color: white;">
                        {{ ucfirst(str_replace('_', ' ', $status->status)) }}
                    </span>
                </td>
                <td class="text-center"><strong>{{ number_format($status->jumlah) }}</strong></td>
                <td class="text-center"><strong>{{ number_format($percentage, 1) }}%</strong></td>
                <td class="text-center">
                    <div style="background-color: #e9ecef; border-radius: 10px; height: 10px; position: relative;">
                        <div style="background-color: {{ $statusColor }}; height: 10px; border-radius: 10px; width: {{ $percentage }}%;"></div>
                    </div>
                </td>
            </tr>
            @endforeach
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <th>TOTAL</th>
                <th class="text-center">{{ number_format($totalStatus) }}</th>
                <th class="text-center">100%</th>
                <th></th>
            </tr>
        @else
            <tr>
                <td colspan="4" class="text-center">
                    <div style="padding: 40px; color: #6c757d;">
                        <strong>Tidak Ada Data Status</strong><br>
                        <small>Tidak ada data status pengiriman untuk periode yang dipilih</small>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>

@if(isset($pengirimanData) && $pengirimanData->count() > 0)
    <div style="margin-top: 20px; font-size: 11px; color: #6c757d;">
        <strong>Ringkasan:</strong> 
        {{ $pengirimanData->count() }} layanan pengiriman, 
        Rp {{ number_format($pengirimanData->sum('total_biaya'), 0, ',', '.') }} total biaya, 
        {{ number_format($pengirimanData->sum('total_berat') / 1000, 1) }} kg total berat
    </div>
@endif
