        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Admin JS
        $(document).ready(function() {
            // Delete confirmation
            $('[data-delete]').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            });
            
            // Toggle select all
            $('#selectAll').on('change', function() {
                $('input[name="ids[]"]').prop('checked', this.checked);
            });
            
            // Bulk actions
            $('#bulkAction').on('change', function() {
                const action = $(this).val();
                if (action && $('input[name="ids[]"]:checked').length > 0) {
                    if (confirm('Apply this action to selected items?')) {
                        $('#bulkForm').submit();
                    }
                }
            });
        });
    </script>
</body>
</html>
