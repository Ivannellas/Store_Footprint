
/*==============================================================
//   User Permission Sync                                     //
=============================================================*/   

document.addEventListener('DOMContentLoaded', function() {
    
    const statusSpan = document.getElementById('syncStatus');

    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        
        checkbox.addEventListener('change', function() {
            
            const moduleId = this.getAttribute('data-module');
            const columnName = this.getAttribute('data-column');
            
            const state = this.checked ? 1 : 0;

            statusSpan.textContent = "Saving changes...";
            statusSpan.className = "small text-primary ms-2 fw-semibold";

            const formData = new URLSearchParams();
            formData.append('module_id', moduleId);
            formData.append('column_name', columnName);
            formData.append('state', state);

            fetch(`user_permission_account.php?id=${targetUserId}&action=update_permission`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(response => response.json()) // Parse the raw text response from the server as a JSON object
                .then(data => {
                    if (data.success === true) {
                        statusSpan.textContent = "Saved.";
                        statusSpan.className = "small text-success ms-2 fw-semibold";
                        
                        setTimeout(() => {
                            statusSpan.textContent = "";
                        }, 1500);
                    } else {
                        statusSpan.textContent = "Error saving changes.";
                        statusSpan.className = "small text-danger ms-2 fw-semibold";
                    }
                })
                .catch(err => {
                    console.error('AJAX sync break:', err);
                    statusSpan.textContent = "Connection error.";
                    statusSpan.className = "small text-danger ms-2 fw-semibold";
                });
        });
    });
});