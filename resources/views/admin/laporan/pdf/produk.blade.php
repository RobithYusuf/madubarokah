<!-- Summary Cards untuk Produk -->
<div class="summary-cards">
    <div class="summary-card">
        <h3>Total Produk</h3>
        <div class="value">{{ $produkData->count() ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <h3>Produk Terjual</h3>
        <div class="value text-success">{{ $produkData->where('total_terjual', '>', 0)->count() ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <h3>Total Stok</h3>
        <div class="value text-info">{{ number_format($produkData->sum('stok') ?? 0) }}</div>
    </div>
    <div class="summary-card">
        <h3>Total Terjual</h3>
        <div class="value text-warning">{{ number_format($produkData->sum('total_terjual') ?? 0) }}</div>
    </div>
</div>

<!-- Tabel Detail Produk -->
<h3 style="margin-bottom: 15px; color: #2c3e50;">📦 Detail Produk</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="25%">Nama Produk</th>
            <th width="12%">Kategori</th>
            <th width="10%">Harga</th>
            <th width="8%">Stok</th>
            <th width="10%">Terjual</th>
            <th width="8%">Transaksi</th>
            <th width="15%">Pendapatan</th>
            <th width="7%">Status</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($produkData) && $produkData->count() > 0)
            @foreach($produkData->take(100) as $index => $produk)
            @php
                $persentaseTerjual = $produk->stok > 0 ? ($produk->total_terjual / ($produk->stok + $produk->total_terjual)) * 100 : 0;
                $statusStok = $produk->stok <= 5 ? 'danger' : ($produk->stok <= 20 ? 'warning' : 'success');
                $statusPenjualan = $produk->total_terjual > 0 ? 'success' : 'secondary';
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $produk->nama_produk }}</strong>
                    @if($produk->total_terjual > 0)
                        <br><small class="text-success">🔥 {{ number_format($persentaseTerjual, 1) }}% terjual</small>
                    @endif
                </td>
                <td class="text-center">
                    @if($produk->nama_kategori)
                        <span class="badge" style="background-color: {{ $produk->warna ?? '#6C757D' }}; color: white;">
                            {{ $produk->nama_kategori }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-right">
                    <strong>Rp {{ number_format($produk->harga, 0, ',', '.') }}</strong>
                </td>
                <td class="text-center">
                    <span class="badge badge-{{ $statusStok }}">{{ number_format($produk->stok) }}</span>
                    @if($produk->stok <= 5)
                        <br><small class="text-danger">⚠ Rendah</small>
                    @endif
                </td>
                <td class="text-center">
                    <strong class="text-{{ $statusPenjualan }}">{{ number_format($produk->total_terjual) }}</strong>
                    @if($produk->total_terjual > 0 && $produk->jumlah_transaksi > 0)
                        <br><small class="text-muted">~{{ number_format($produk->total_terjual / $produk->jumlah_transaksi, 1) }}/tx</small>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge badge-info">{{ number_format($produk->jumlah_transaksi) }}</span>
                </td>
                <td class="text-right">
                    <strong class="text-success">Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}</strong>
                    @if($produk->total_terjual > 0)
                        <br><small class="text-muted">~Rp {{ number_format($produk->total_pendapatan / $produk->total_terjual, 0, ',', '.') }}/unit</small>
                    @endif
                </td>
                <td class="text-center">
                    @if($produk->total_terjual > 0)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-secondary">Belum</span>
                    @endif
                    @if($produk->stok <= 5)
                        <br><span class="badge badge-danger">Stok !</span>
                    @endif
                </td>
            </tr>
            @endforeach
            
            @if($produkData->count() > 100)
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; background-color: #f8f9fa;">
                        <strong>Catatan:</strong> Menampilkan 100 produk teratas berdasarkan total terjual.<br>
                        <small class="text-muted">Total {{ $produkData->count() }} produk dalam periode ini.</small>
                    </td>
                </tr>
            @endif
        @else
            <tr>
                <td colspan="9" class="text-center">
                    <div style="padding: 40px; color: #6c757d;">
                        <strong>Tidak Ada Data Produk</strong><br>
                        <small>Tidak ada produk ditemukan untuk filter yang dipilih</small>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>

<div style="margin: 30px 0;"></div>

<!-- Analisis Stok -->
<h3 style="margin-bottom: 15px; color: #2c3e50;">📊 Analisis Stok</h3>
<div style="display: flex; justify-content: space-between; margin-bottom: 25px; gap: 10px;">
    @php
        $stokHabis = $produkData->where('stok', 0)->count();
        $stokRendah = $produkData->where('stok', '>', 0)->where('stok', '<=', 5)->count();
        $stokSedang = $produkData->where('stok', '>', 5)->where('stok', '<=', 20)->count();
        $stokTinggi = $produkData->where('stok', '>', 20)->count();
    @endphp
    
    <div style="flex: 1; text-align: center; padding: 15px; background: #fff5f5; border-radius: 8px;">
        <div style="font-size: 24px; font-weight: bold; color: #dc3545;">{{ $stokHabis }}</div>
        <div style="font-weight: bold; color: #dc3545;">Stok Habis</div>
        <small style="color: #6c757d;">0 unit</small>
    </div>
    <div style="flex: 1; text-align: center; padding: 15px; background: #fff3cd; border-radius: 8px;">
        <div style="font-size: 24px; font-weight: bold; color: #ffc107;">{{ $stokRendah }}</div>
        <div style="font-weight: bold; color: #ffc107;">Stok Rendah</div>
        <small style="color: #6c757d;">1-5 unit</small>
    </div>
    <div style="flex: 1; text-align: center; padding: 15px; background: #d1ecf1; border-radius: 8px;">
        <div style="font-size: 24px; font-weight: bold; color: #17a2b8;">{{ $stokSedang }}</div>
        <div style="font-weight: bold; color: #17a2b8;">Stok Sedang</div>
        <small style="color: #6c757d;">6-20 unit</small>
    </div>
    <div style="flex: 1; text-align: center; padding: 15px; background: #d4edda; border-radius: 8px;">
        <div style="font-size: 24px; font-weight: bold; color: #28a745;">{{ $stokTinggi }}</div>
        <div style="font-weight: bold; color: #28a745;">Stok Tinggi</div>
        <small style="color: #6c757d;">> 20 unit</small>
    </div>
</div>

<!-- Top Performing Products -->
@php
    $topPerformers = $produkData->where('total_terjual', '>', 0)->sortByDesc('total_pendapatan')->take(10);
@endphp
@if($topPerformers->count() > 0)
<h3 style="margin-bottom: 15px; color: #2c3e50;">🏆 Top 10 Produk Terbaik</h3>
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
        @php
            $totalPendapatanProduk = $topPerformers->sum('total_pendapatan');
        @endphp
        @foreach($topPerformers as $index => $produk)
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
                @if($produk->nama_kategori)
                    <span class="badge" style="background-color: {{ $produk->warna ?? '#6C757D' }}; color: white;">
                        {{ $produk->nama_kategori }}
                    </span>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
            <td class="text-center"><strong>{{ number_format($produk->total_terjual) }}</strong></td>
            <td class="text-right"><strong class="text-success">Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}</strong></td>
            <td class="text-center">{{ $totalPendapatanProduk > 0 ? number_format(($produk->total_pendapatan / $totalPendapatanProduk) * 100, 1) : 0 }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if(isset($produkData) && $produkData->count() > 0)
    <div style="margin-top: 20px; font-size: 11px; color: #6c757d;">
        <strong>Ringkasan:</strong> 
        {{ $produkData->count() }} total produk, 
        {{ $produkData->where('total_terjual', '>', 0)->count() }} produk terjual, 
        {{ number_format($produkData->sum('stok')) }} total stok,
        {{ number_format($produkData->sum('total_terjual')) }} total terjual
    </div>
@endif
