// Utility JavaScript Functions

// Show alert message
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Confirm deletion
function confirmDelete(id, type = 'item') {
    return confirm(`Are you sure you want to delete this ${type}?`);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Validate email
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validate password
function isValidPassword(password) {
    return password && password.length >= 8;
}

// Trim whitespace from fields
function trimFormInputs(formId) {
    const form = document.getElementById(formId);
    if (form) {
        const inputs = form.querySelectorAll('input[type="text"], input[type="email"], textarea');
        inputs.forEach(input => {
            input.value = input.value.trim();
        });
    }
}

// Toggle modal
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.toggle('show');
    }
}

// Open modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

// Close modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

// AJAX request
function makeRequest(url, method = 'GET', data = null, callback) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    fetch(url, options)
        .then(response => response.json())
        .then(result => {
            if (callback) {
                callback(result);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred', 'danger');
        });
}

// Logout functionality
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

// Debounce function for search
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
}

// Search books
const searchBooks = debounce(function(query) {
    if (query.length < 2) return;
    
    makeRequest('/lbms/api/search-books.php?q=' + encodeURIComponent(query), 'GET', null, function(result) {
        if (result.success) {
            displaySearchResults(result.data);
        }
    });
}, 300);

// Display search results
function displaySearchResults(books) {
    const resultsDiv = document.getElementById('searchResults');
    if (!resultsDiv) return;
    
    if (books.length === 0) {
        resultsDiv.innerHTML = '<p>No books found</p>';
        return;
    }
    
    let html = '<ul>';
    books.forEach(book => {
        html += `<li><a href="book-details.php?id=${book.id}">${book.title}</a> by ${book.authors || 'Unknown'}</li>`;
    });
    html += '</ul>';
    
    resultsDiv.innerHTML = html;
}

// Get notification count
function updateNotificationCount() {
    makeRequest('/lbms/api/get-notifications.php?count=1', 'GET', null, function(result) {
        if (result.success) {
            const count = result.count;
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'inline' : 'none';
            }
        }
    });
}

// Update notification count every 30 seconds
setInterval(updateNotificationCount, 30000);

// Set active menu item
function setActiveMenu(menuItemId) {
    const menuItems = document.querySelectorAll('.sidebar a');
    menuItems.forEach(item => {
        item.classList.remove('active');
    });
    
    const activeItem = document.getElementById(menuItemId);
    if (activeItem) {
        activeItem.classList.add('active');
    }
}

// Validate form
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'red';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });
    
    return isValid;
}

// Print functionality
function printPage(elementId = null) {
    if (elementId) {
        const element = document.getElementById(elementId);
        const printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(element.innerHTML);
        printWindow.print();
        printWindow.close();
    } else {
        window.print();
    }
}

// Export table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        let csvRow = [];
        const cols = row.querySelectorAll('td, th');
        
        cols.forEach(col => {
            csvRow.push('"' + col.textContent.trim() + '"');
        });
        
        csv.push(csvRow.join(','));
    });
    
    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const link = document.createElement('a');
    link.setAttribute('href', encodeURI(csvContent));
    link.setAttribute('download', filename);
    link.click();
}

// Initialize tooltips
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(elem => {
        elem.addEventListener('mouseover', function() {
            const tooltip = document.createElement('div');
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.className = 'tooltip';
            tooltip.style.cssText = 'position:absolute;background:#333;color:#fff;padding:5px 10px;border-radius:3px;font-size:12px;z-index:1000;';
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = (rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            
            this.tooltip = tooltip;
        });
        
        elem.addEventListener('mouseout', function() {
            if (this.tooltip) {
                this.tooltip.remove();
            }
        });
    });
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    updateNotificationCount();

    // mobile nav toggle
    const toggle = document.querySelector('.nav-toggle');
    const menu = document.querySelector('.nav-menu');
    function updateMenu() {
        if (!menu) return;
        if (window.innerWidth <= 768) {
            menu.style.display = menu.classList.contains('active') ? 'flex' : 'none';
        } else {
            menu.style.display = 'flex';
        }
    }
    if (toggle && menu) {
        console.log('nav toggle found, attaching handler');
        toggle.addEventListener('click', function() {
            console.log('nav toggle clicked');
            menu.classList.toggle('active');
            updateMenu();
        });
        // update on load/resize
        window.addEventListener('resize', updateMenu);
        updateMenu();
    } else {
        console.log('nav toggle or menu not found', toggle, menu);
    }

    // highlight current header link
    const currentUrl = window.location.pathname;
    document.querySelectorAll('.nav-menu a').forEach(a => {
        const linkUrl = new URL(a.href, window.location.origin).pathname;
        if (linkUrl === currentUrl) {
            a.classList.add('active');
        }
    });
});
