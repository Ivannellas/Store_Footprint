
/*==============================================================
//   User Accounts Manager                                    //
=============================================================*/   

document.addEventListener('DOMContentLoaded', function() {
    
    const modalElement = new bootstrap.Modal(document.getElementById('viewUserModal'));
    
    // ==========================================
    // MODULE PERMISSION ROUTING PROTECTION
    // ==========================================
    document.querySelectorAll('.action-btn').forEach(element => {
        element.addEventListener('click', function(event) {
            
            const checkPermission = parseInt(this.getAttribute('data-permission'));
            
            if (checkPermission !== 1) {
                event.preventDefault();   
                event.stopPropagation(); 
                alert("Access Denied: You do not have permission.");
                return false;
            }
        });
    });

    // =================================================//
    // USER STATUS TOGGLE HANDLING (ACTIVE / INACTIVE)
    // =================================================//
    document.addEventListener('click', function(event) {
        const button = event.target.closest(".action-btn[id^='status-display-']");
        if (!button) return; 

        const userPermission = parseInt(button.getAttribute('data-permission'));
        if (userPermission !== 1) {
            alert("Access Denied: You do not have permission.");
            return; 
        }

        const userId = button.getAttribute('data-id');
        button.disabled = true; 
        
        fetch(`user_accounts.php?action=toggle_status&id=${userId}`)
            .then(response => response.json()) 
            .then(data => {
                if (data !== null && data.success === true) {
                    if (parseInt(data.newStatus) === 1) {
                        button.textContent = 'Active';
                        button.className = 'action-btn border-0 bg-transparent text-success';
                    } else {
                        button.textContent = 'InActive';
                        button.className = 'action-btn border-0 bg-transparent text-danger ';
                    }
                } else {
                    alert("Transaction Failed: Could not modify user status.");
                }
            })
            .catch(err => {
                console.error('Error conducting status toggle:', err);
            })
            .finally(() => {
                button.disabled = false;
            });
    });

    // ==========================================
    //  ASYNCHRONOUS USER INFOMODAL POPULATOR
    // ==========================================
    document.querySelectorAll('.btn-view-user').forEach(button => {
        button.addEventListener('click', function(event) {
            
            const userPermission = parseInt(this.getAttribute('data-permission'));
            if (userPermission !== 1) {
                return;
            }

            const userId = this.getAttribute('data-id');
            
            fetch(`user_accounts.php?action=view_user&id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data !== null && data.oUserid) {
                        
                        document.getElementById('modalUserTitle').textContent = "View User: " + data.oUserid;
                        document.getElementById('mUserId').textContent = data.oUserid;
                        document.getElementById('mUsername').textContent = data.oUsername;
                        document.getElementById('mFullname').textContent = data.oFullname;
                        
                        if (data.oPosition) {
                            document.getElementById('mPosition').textContent = data.oPosition;
                        } else {
                            document.getElementById('mPosition').textContent = 'N/A';
                        }
                        
                        if (data.oPostcode) {
                            document.getElementById('mPostcode').textContent = data.oPostcode;
                        } else {
                            document.getElementById('mPostcode').textContent = '0';
                        }
                        
                        if (parseInt(data.oActive) === 1) {
                            document.getElementById('mStatus').textContent = 'Active';
                        } else {
                            document.getElementById('mStatus').textContent = 'Inactive';
                        }
                        
                        modalElement.show();
                    }
                })
                .catch(err => {
                    console.error('Error handling transaction payload:', err);
                });
        });
    });
});


    // ==========================================
    //  LIVE REAL-TIME SEARCH ENGINE
    // =========================================

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const tableBody = document.getElementById('userTableBody');
    let debounceTimer;

    if (!searchInput || !tableBody) return;

    function performSearch() {
        const queryValue = encodeURIComponent(searchInput.value.trim());
        
        fetch(`user_accounts.php?search=${queryValue}`)
            .then(response => response.text())
            .then(htmlString => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlString, 'text/html');
                const freshTableBody = doc.getElementById('userTableBody');
                
                if (freshTableBody) {
                    tableBody.innerHTML = freshTableBody.innerHTML;
                }
            })
            .catch(error => console.error('Real-time routing evaluation error:', error));
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 200); 
    });

    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            performSearch();
        });
    }
});

