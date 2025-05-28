<!-- Summary Cards untuk Penjualan -->
<div class="summary-cards">
    <div class="summary-card">
        <h3>Total Hari</h3>
        <div class="value">{{ $penjualanHarian->count() ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <h3>Total Transaksi</h3>
        <div class="value text-info">{{ number_format($penjualanHarian->sum('jumlah_transaksi') ?? 0) }}</div>
    </div>
    <div class="summary-card">
        <h3>Total Penjualan</h3>
        <div class="value text-success">Rp {{ number_format($penjualanHarian->sum('total_penjualan') ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="summary-card">
        <h3>Rata-rata Harian</h3>
        <div class="value text-warning">Rp {{ $penjualanHarian->count() > 0 ? number_format($penjualanHarian->sum('total_penjualan') / $penjualanHarian->count(), 0, ',', '.') : 0 }}</div>
    </div>
</div>

<!-- Tabel Penjualan Harian -->
<h3 style="margin-bottom: 15px; color: #2c3e50;">📈 Penjualan Harian</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="15%">Tanggal</th>
            <th width="20%">Jumlah Transaksi</th>
            <th width="25%">Total Penjualan</th>
            <th width="25%">Rata-rata per Transaksi</th>
            <th width="15%">% dari Total</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($penjualanHarian) && $penjualanHarian->count() > 0)
            @php 
                $totalPenjualanKeseluruhan = $penjualanHarian->sum('total_penjualan');
            @endphp
            @foreach($penjualanHarian as $item)
            <tr>
                <td class="text-center">{{ Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                <td class="text-center"><strong>{{ number_format($item->jumlah_transaksi) }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($item->total_penjualan, 0, ',', '.') }}</strong></td>
                <td class="text-right">Rp {{ number_format($item->rata_rata, 0, ',', '.') }}</td>
                <td class="text-center">{{ $totalPenjualanKeseluruhan > 0 ? number_format(($item->total_penjualan / $totalPenjualanKeseluruhan) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <th>TOTAL</th>
                <th class="text-center">{{ number_format($penjualanHarian->sum('jumlah_transaksi')) }}</th>
                <th class="text-right">Rp {{ number_format($penjualanHarian->sum('total_penjualan'), 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ $penjualanHarian->sum('jumlah_transaksi') > 0 ? number_format($penjualanHarian->sum('total_penjualan') / $penjualanHarian->sum('jumlah_transaksi'), 0, ',', '.') : 0 }}</th>
                <th class="text-center">100%</th>
            </tr>
        @else
            <tr>
                <td colspan="5" class="text-center">
                    <div style="padding: 40px; color: #6c757d;">
                        <strong>Tidak Ada Data Penjualan</strong><br>
                        <small>Tidak ada data penjualan untuk periode yang dipilih</small>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>

<div style="margin: 30px 0;"></div>

<!-- Top Produk -->
<h3 style="margin-bottom: 15px; color: #2c3e50;">🏆 Top 10 Produk Terlaris</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="8%">Rank</th>
            <th width="35%">Nama Produk</th>
            <th width="15%">Kategori</th>
            <th width="12%">Terjual</th>
            <th width="20%">Pendapatan</th>
            <th width="10%">% Total</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($topProduk) && $topProduk->count() > 0)
            @php
                $totalPendapatanProduk = $topProduk->sum('total_pendapatan');
            @endphp
            @foreach($topProduk->take(10) as $index => $produk)
            <tr>
                <td class="text-center">
                    @if($index == 0)
                        🥇
                    @elseif($index == 1)
                        🥈
                    @elseif($index == 2)
                        🥉
                    @else
                        {{ $index + 1 }}
                    @endif
                </td>
                <td><strong>{{ $produk->nama_produk }}</strong></td>
                <td class="text-center">
                    <span class="badge" style="background-color: {{ $produk->warna ?? '#6C757D' }}; color: white;">
                        {{ $produk->nama_kategori }}
                    </span>
                </td>
                <td class="text-center"><strong>{{ number_format($produk->total_terjual) }}</strong></td>
                <td class="text-right"><strong class="text-success">Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}</strong></td>
                <td class="text-center">{{ $totalPendapatanProduk > 0 ? number_format(($produk->total_pendapatan / $totalPendapatanProduk) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="6" class="text-center">
                    <div style="padding: 40px; color: #6c757d;">
                        <strong>Tidak Ada Data Produk</strong><br>
                        <small>Tidak ada data produk untuk periode yang dipilih</small>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>

<div style="margin: 30px 0;"></div>

<!-- Top Kategori -->
<h3 style="margin-bottom: 15px; color: #2c3e50;">🏷️ Performa Kategori</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="25%">Kategori</th>
            <th width="15%">Jumlah Produk</th>
            <th width="15%">Total Terjual</th>
            <th width="25%">Pendapatan</th>
            <th width="20%">% dari Total</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($topKategori) && $topKategori->count() > 0)
            @php
                $totalPendapatanKategori = $topKategori->sum('total_pendapatan');
            @endphp
            @foreach($topKategori as $kategori)
            <tr>
                <td>
                    <span class="badge" style="background-color: {{ $kategori->warna ?? '#6C757D' }}; color: white;">
                        {{ $kategori->nama_kategori }}
                    </span>
                </td>
                <td class="text-center"><strong>{{ number_format($kategori->jumlah_produk) }}</strong></td>
                <td class="text-center"><strong>{{ number_format($kategori->total_terjual) }}</strong></td>
                <td class="text-right"><strong class="text-success">Rp {{ number_format($kategori->total_pendapatan, 0, ',', '.') }}</strong></td>
                <td class="text-center">{{ $totalPendapatanKategori > 0 ? number_format(($kategori->total_pendapatan / $totalPendapatanKategori) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="5" class="text-center">
                    <div style="padding: 40px; color: #6c757d;">
                        <strong>Tidak Ada Data Kategori</strong><br>
                        <small>Tidak ada data kategori untuk periode yang dipilih</small>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>

@if(isset($penjualanHarian) && $penjualanHarian->count() > 0)
    <div style="margin-top: 20px; font-size: 11px; color: #6c757d;">
        <strong>Ringkasan:</strong> 
        {{ $penjualanHarian->count() }} hari, 
        {{ number_format($penjualanHarian->sum('jumlah_transaksi')) }} transaksi, 
        Rp {{ number_format($penjualanHarian->sum('total_penjualan'), 0, ',', '.') }} total penjualan
    </div>
@endif
