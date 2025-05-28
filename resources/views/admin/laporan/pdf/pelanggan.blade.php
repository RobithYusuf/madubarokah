<!-- Summary Cards untuk Pelanggan -->
<div class="summary-cards">
    <div class="summary-card">
        <h3>Total Pelanggan</h3>
        <div class="value">{{ $pelangganData->count() ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <h3>Pelanggan Aktif</h3>
        <div class="value text-success">{{ $pelangganData->where('jumlah_transaksi', '>', 0)->count() ?? 0 }}</div>
    </div>
    <div class="summary-card">
        <h3>Total Belanja</h3>
        <div class="value text-info">Rp {{ number_format($pelangganData->sum('total_belanja') ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="summary-card">
        <h3>Rata-rata Belanja</h3>
        <div class="value text-warning">
            @php 
                $totalPelangganAktif = $pelangganData->where('jumlah_transaksi', '>', 0)->count();
                $avgBelanja = $totalPelangganAktif > 0 ? $pelangganData->sum('total_belanja') / $totalPelangganAktif : 0;
            @endphp
            Rp {{ number_format($avgBelanja, 0, ',', '.') }}
        </div>
    </div>
</div>

<!-- Segmentasi Pelanggan -->
<h3 style="margin-bottom: 15px; color: #2c3e50;">👥 Segmentasi Pelanggan</h3>
<div style="display: flex; justify-content: space-between; margin-bottom: 25px; gap: 10px;">
    @php
        $vip = $pelangganData->where('total_belanja', '>=', 5000000)->count();
        $premium = $pelangganData->where('total_belanja', '>=', 2000000)->where('total_belanja', '<', 5000000)->count();
        $regular = $pelangganData->where('total_belanja', '>=', 500000)->where('total_belanja', '<', 2000000)->count();
        $basic = $pelangganData->where('jumlah_transaksi', '>', 0)->where('total_belanja', '<', 500000)->count();
        $inactive = $pelangganData->where('jumlah_transaksi', 0)->count();
    @endphp
    
    <div style="flex: 1; text-align: center; padding: 10px; background: #fff5f5; border-radius: 8px;">
        <div style="font-size: 24px; font-weight: bold; color: #dc3545;">{{ $vip }}</div>
        <div style="font-weight: bold; color: #dc3545;">VIP</div>
        <small style="color: #6c757d;">≥ Rp 5jt</small>
    </div>
    <div style="flex: 1; text-align: center; padding: 10px; background: #fff3cd; border-radius: 8px;">
        <div style="font-size: 24px; font-weight: bold; color: #ffc107;">{{ $premium }}</div>
        <div style="font-weight: bold; color: #ffc107;">Premium</div>
        <small style="color: #6c757d;">Rp 2jt - 5jt</small>
    </div>
    <div style="flex: 1; text-align: center; padding: 10px; background: #d4edda; border-radius: 8px;">
        <div style="font-size: 24px; font-weight: bold; color: #28a745;">{{ $regular }}</div>
        <div style="font-weight: bold; color: #28a745;">Regular</div>
        <small style="color: #6c757d;">Rp 500rb - 2jt</small>
    </div>
    <div style="flex: 1; text-align: center; padding: 10px; background: #d1ecf1; border-radius: 8px;">
        <div style="font-size: 24px; font-weight: bold; color: #17a2b8;">{{ $basic }}</div>
        <div style="font-weight: bold; color: #17a2b8;">Basic</div>
        <small style="color: #6c757d;">< Rp 500rb</small>
    </div>
    <div style="flex: 1; text-align: center; padding: 10px; background: #e2e3e5; border-radius: 8px;">
        <div style="font-size: 24px; font-weight: bold; color: #6c757d;">{{ $inactive }}</div>
        <div style="font-weight: bold; color: #6c757d;">Inactive</div>
        <small style="color: #6c757d;">Belum transaksi</small>
    </div>
</div>

<!-- Tabel Detail Pelanggan -->
<h3 style="margin-bottom: 15px; color: #2c3e50;">📋 Detail Pelanggan</h3>
<table class="data-table">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="20%">Nama Pelanggan</th>
            <th width="15%">Kontak</th>
            <th width="10%">Transaksi</th>
            <th width="15%">Total Belanja</th>
            <th width="15%">Rata-rata</th>
            <th width="12%">Terakhir</th>
            <th width="8%">Segmen</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($pelangganData) && $pelangganData->count() > 0)
            @foreach($pelangganData->take(50) as $index => $pelanggan)
            @php
                // Segmentasi pelanggan
                if ($pelanggan->total_belanja >= 5000000) {
                    $segmen = ['text' => 'VIP', 'class' => 'danger'];
                } elseif ($pelanggan->total_belanja >= 2000000) {
                    $segmen = ['text' => 'Premium', 'class' => 'warning'];
                } elseif ($pelanggan->total_belanja >= 500000) {
                    $segmen = ['text' => 'Regular', 'class' => 'success'];
                } elseif ($pelanggan->jumlah_transaksi > 0) {
                    $segmen = ['text' => 'Basic', 'class' => 'info'];
                } else {
                    $segmen = ['text' => 'Inactive', 'class' => 'secondary'];
                }
                
                // Status aktivitas
                $statusAktivitas = $pelanggan->transaksi_terakhir ? 
                    (Carbon\Carbon::parse($pelanggan->transaksi_terakhir)->diffInDays(now()) <= 30 ? 'Aktif' : 'Tidak Aktif') : 
                    'Belum Transaksi';
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $pelanggan->nama }}</strong><br>
                    <small class="text-muted">({{ $pelanggan->username }})</small><br>
                    @if($statusAktivitas === 'Aktif')
                        <small class="text-success">● {{ $statusAktivitas }}</small>
                    @elseif($statusAktivitas === 'Tidak Aktif')
                        <small class="text-warning">● {{ $statusAktivitas }}</small>
                    @else
                        <small class="text-muted">● {{ $statusAktivitas }}</small>
                    @endif
                </td>
                <td>
                    @if($pelanggan->email)
                        <small>✉ {{ $pelanggan->email }}</small><br>
                    @endif
                    @if($pelanggan->nohp)
                        <small>📞 {{ $pelanggan->nohp }}</small>
                    @endif
                    @if(!$pelanggan->email && !$pelanggan->nohp)
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center">
                    <strong class="text-info">{{ number_format($pelanggan->jumlah_transaksi) }}</strong>
                    @if($pelanggan->jumlah_transaksi > 0)
                        <br><small class="text-muted">transaksi</small>
                    @endif
                </td>
                <td class="text-right">
                    <strong class="text-success">Rp {{ number_format($pelanggan->total_belanja, 0, ',', '.') }}</strong>
                    @if($pelanggan->total_belanja > 0)
                        <br><small class="text-muted">{{ number_format(($pelanggan->total_belanja / $pelangganData->sum('total_belanja')) * 100, 1) }}% total</small>
                    @endif
                </td>
                <td class="text-right">
                    <strong>Rp {{ number_format($pelanggan->rata_rata_belanja, 0, ',', '.') }}</strong>
                </td>
                <td class="text-center">
                    @if($pelanggan->transaksi_terakhir)
                        {{ Carbon\Carbon::parse($pelanggan->transaksi_terakhir)->format('d/m/Y') }}<br>
                        <small class="text-muted">{{ Carbon\Carbon::parse($pelanggan->transaksi_terakhir)->diffForHumans() }}</small>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge badge-{{ $segmen['class'] }}">{{ $segmen['text'] }}</span>
                </td>
            </tr>
            @endforeach
            
            @if($pelangganData->count() > 50)
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; background-color: #f8f9fa;">
                        <strong>Catatan:</strong> Menampilkan 50 pelanggan teratas berdasarkan total belanja.<br>
                        <small class="text-muted">Total {{ $pelangganData->count() }} pelanggan dalam periode ini.</small>
                    </td>
                </tr>
            @endif
        @else
            <tr>
                <td colspan="8" class="text-center">
                    <div style="padding: 40px; color: #6c757d;">
                        <strong>Tidak Ada Data Pelanggan</strong><br>
                        <small>Tidak ada data pelanggan untuk periode yang dipilih</small>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>

@if(isset($pelangganData) && $pelangganData->count() > 0)
    <div style="margin-top: 20px; font-size: 11px; color: #6c757d;">
        <strong>Ringkasan:</strong> 
        {{ $pelangganData->count() }} total pelanggan, 
        {{ $pelangganData->where('jumlah_transaksi', '>', 0)->count() }} pelanggan aktif, 
        Rp {{ number_format($pelangganData->sum('total_belanja'), 0, ',', '.') }} total belanja
    </div>
@endif
