document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('employeeSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#employeeTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if(!row.querySelector('td[colspan]')) {
                    row.style.display = text.includes(filter) ? '' : 'none';
                }
            });
        });
    }
});