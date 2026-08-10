        </div><!-- .health-layout-container -->
    </div><!-- .health-theme -->

    <!-- JavaScript CDNs -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables & Extensions -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Flatpickr (DatePicker) -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Custom Main Application Script -->
    <script src="<?= url('js/app.js') ?>?v=<?= time() ?>"></script>

    <!-- Toast message flash renderer -->
    <script>
        $(document).ready(function() {
            // Flash messages using SweetAlert2 toast style
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            <?php if ($successMsg = flash('success')): ?>
                Toast.fire({
                    icon: 'success',
                    title: '<?= escape(addslashes($successMsg)) ?>'
                });
            <?php endif; ?>

            <?php if ($errorMsg = flash('error')): ?>
                Toast.fire({
                    icon: 'error',
                    title: '<?= escape(addslashes($errorMsg)) ?>'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
