<!-- Summary Cards untuk Transaksi
<div class="summary-cards">
    <div class="summary-card">
        <h3>Total Transaksi</h3>
        <div class="value">{{ number_format($summaryData['total_transaksi'] ?? 0) }}</div>
    </div>
    <div class="summary-card">
        <h3>Total Pendapatan</h3>
        <div class="value text-success">Rp {{ number_format($summaryData['total_pendapatan'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="summary-card">
        <h3>Rata-rata</h3>
        <div class="value text-info">Rp {{ number_format($summaryData['rata_rata_transaksi'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="summary-card">
        <h3>Selesai</h3>
        <div class="value text-warning">{{ number_format($summaryData['transaksi_selesai'] ?? 0) }}</div>
    </div>
</div> -->

<!-- Tabel Detail Transaksi -->
<table class="data-table">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="12%">ID Transaksi</th>
            <th width="15%">Pelanggan</th>
            <th width="12%">Tanggal</th>
            <th width="12%">Total</th>
            <th width="12%">Status</th>
            <th width="12%">Pembayaran</th>
            <th width="12%">Pengiriman</th>
            <th width="8%">Metode</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($transaksi) && $transaksi->count() > 0)
            @foreach($transaksi as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>#{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</strong><br>
                    @if($item->merchant_ref)
                        <small class="text-muted">{{ $item->merchant_ref }}</small>
                    @endif
                </td>
                <td>
                    <strong>{{ $item->user->nama ?? '-' }}</strong><br>
                    <small class="text-muted">{{ $item->user->username ?? '-' }}</small>
                </td>
                <td class="text-center">
                    {{ $item->tanggal_transaksi ? $item->tanggal_transaksi->format('d/m/Y') : '-' }}<br>
                    <small class="text-muted">{{ $item->tanggal_transaksi ? $item->tanggal_transaksi->format('H:i') : '' }}</small>
                </td>
                <td class="text-right">
                    <strong>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</strong>
                    @if($item->pengiriman && $item->pengiriman->biaya > 0)
                        <br><small class="text-muted">+Ongkir: {{ number_format($item->pengiriman->biaya, 0, ',', '.') }}</small>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge badge-{{ 
                        $item->status === 'selesai' ? 'success' : 
                        ($item->status === 'dikirim' ? 'info' : 
                        ($item->status === 'dibayar' || $item->status === 'berhasil' ? 'warning' : 
                        ($item->status === 'pending' ? 'secondary' : 'danger'))) 
                    }}">
                        {{ ucfirst($item->status) }}
                    </span>
                </td>
                <td class="text-center">
                    @if($item->pembayaran)
                        <span class="badge badge-{{ 
                            in_array($item->pembayaran->status, ['berhasil', 'dibayar']) ? 'success' : 
                            ($item->pembayaran->status === 'pending' ? 'warning' : 'danger') 
                        }}">
                            {{ ucfirst($item->pembayaran->status) }}
                        </span>
                        @if($item->pembayaran->waktu_bayar)
                            <br><small class="text-muted">{{ $item->pembayaran->waktu_bayar->format('d/m H:i') }}</small>
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($item->pengiriman)
                        <span class="badge badge-{{ 
                            $item->pengiriman->status === 'diterima' ? 'success' : 
                            ($item->pengiriman->status === 'dikirim' ? 'info' : 
                            ($item->pengiriman->status === 'diproses' ? 'warning' : 'secondary'))
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $item->pengiriman->status)) }}
                        </span>
                        <br><small class="text-muted">{{ $item->pengiriman->kurir ?? '-' }}</small>
                        @if($item->pengiriman->resi)
                            <br><small class="text-info">{{ $item->pengiriman->resi }}</small>
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($item->pembayaran)
                        <strong>{{ $item->pembayaran->metode ?? '-' }}</strong>
                        @if($item->pembayaran->payment_code)
                            <br><small class="text-info">{{ $item->pembayaran->payment_code }}</small>
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="9" class="text-center">
                    <div style="padding: 40px; color: #6c757d;">
                        <strong>Tidak Ada Data Transaksi</strong><br>
                        <small>Tidak ada transaksi ditemukan untuk periode dan filter yang dipilih</small>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>

@if(isset($transaksi) && $transaksi->count() > 0)
    <div style="margin-top: 20px; font-size: 11px; color: #6c757d;">
        <strong>Total Data:</strong> {{ $transaksi->count() }} transaksi ditampilkan
    </div>
@endif
