    </main>

    <!-- Admin Scripts -->
    <script>
        // Universal delete confirmation dialog
        document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const message = btn.getAttribute('data-confirm') || 'Are you sure you want to permanently delete this item? This action cannot be undone.';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });

        // Auto preview image inputs
        document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
            input.addEventListener('change', function() {
                const targetId = this.getAttribute('data-preview');
                const targetImg = document.getElementById(targetId);
                if (targetImg && this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        targetImg.src = e.target.result;
                        targetImg.style.display = 'block';
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    </script>
</body>
</html>
