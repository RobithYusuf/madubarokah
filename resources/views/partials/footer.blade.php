<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container-fluid my-auto">
        <div class="copyright text-center my-auto">
            <span class="text-muted">© {{ date('Y') }} <strong>Toko Madu Barokah</strong>. All Rights Reserved.</span>
        </div>
    </div>
</footer>
<!-- End of Footer -->

<!-- CSS Tambahan untuk Footer -->
<style>
/* Footer Styling */
.sticky-footer {
    padding: 1rem 0; /* Padding atas dan bawah 1rem */
    background-color: #f8f9fc !important;
    flex-shrink: 0;
}

.sticky-footer .copyright {
    font-size: 0.875rem; /* Ukuran font standar SB Admin */
}

/* Pastikan content wrapper menggunakan flex untuk sticky footer */
#content-wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

#content {
    flex: 1 0 auto; /* Content akan mengambil sisa ruang */
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .sticky-footer {
        padding: 0.75rem 0;
    }
    
    .sticky-footer .copyright {
        font-size: 0.75rem;
    }
}
</style>