@extends('layouts.app')
@include('partials.navbarLandingpage')
@section('content')
    <div class="container">
        <h2 class="mb-4">Keranjang Belanja</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($cartItems->isEmpty())
            <div class="alert alert-warning">
                Keranjang belanja Anda masih kosong.
            </div>
        @else
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @dd($cartItems)
                    @foreach ($cartItems as $item)
                        <tr>
                            <td>{{ $item->produk->nama }}</td>
                            <td>Rp{{ number_format($item->produk->price, 0, ',', '.') }}</td>
                            <td>
                                <input type="number" class="form-control update-quantity" data-id="{{ $item->id }}"
                                    value="{{ $item->quantity }}" min="1">
                            </td>
                            <td>Rp{{ number_format($item->produk->harga * $item->quantity, 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-between">
                <h4>Total Harga:
                    <strong>Rp{{ number_format($cartItems->sum(fn($item) => $item->produk->price * $item->quantity), 0, ',', '.') }}</strong>
                </h4>
                <a href="#" class="btn btn-success">Checkout</a>
            </div>
        @endif
    </div>
    @push('scripts')
        {{-- <script>
            $(document).ready(function() {
                $('.update-quantity').change(function() {
                    let cart_id = $(this).data('id');
                    let quantity = $(this).val();

                    $.ajax({
                        url: "{{ route('cart.update') }}",
                        type: "POST",
                        data: {
                            id: cart_id,
                            quantity: quantity,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            location.reload();
                        }
                    });
                });
            });
        </script> --}}
    @endpush
@endsection
