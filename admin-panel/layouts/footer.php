</div> <!-- Close wrapper -->
</div> <!-- Close page-container -->

<!-- Core Scripts - Include first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Initialize Bootstrap 5 components when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Initialize popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

        // Enable dismissible alerts
        const alertList = document.querySelectorAll('.alert.alert-dismissible');
        [...alertList].forEach(alert => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    alert.classList.add('fade');
                    setTimeout(() => alert.remove(), 150);
                });
            }
        });
    });
</script>

</body>

</html>