(function() {
    'use strict';

    let currentPage = 1;
    let totalPages = 1;
    let logsPerPage = 15;
    let allLogs = [];
    let filteredLogs = [];

    function init() {
        loadAuditLogs();
        setupEventListeners();
    }

    function setupEventListeners() {
        const searchInput = document.getElementById('searchInput');
        const actionTypeFilter = document.getElementById('actionTypeFilter');
        const dateFilter = document.getElementById('dateFilter');
        const btnApplyFilter = document.getElementById('btnApplyFilter');

        btnApplyFilter.addEventListener('click', applyFilters);
        searchInput.addEventListener('input', debounce(applyFilters, 300));
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });

        actionTypeFilter.addEventListener('change', applyFilters);
        dateFilter.addEventListener('change', applyFilters);
    }

    async function loadAuditLogs() {
        showLoading(true);
        
        try {
            const response = await fetch(window.BASE_URL + 'api/teacher/get-overview.php');
            const data = await response.json();

            if (data.success) {
                allLogs = data.logs;
                filteredLogs = [...allLogs];
                renderLogs();
            } else {
                showError('Failed to load activity logs');
            }
        } catch (error) {
            console.error('Error loading logs:', error);
            showError('An error occurred while loading activity');
        } finally {
            showLoading(false);
        }
    }

    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
        const actionType = document.getElementById('actionTypeFilter').value;
        const dateValue = document.getElementById('dateFilter').value;

        filteredLogs = allLogs.filter(log => {
            let matchesSearch = !searchTerm;
            
            if (searchTerm) {
                const searchableText = [
                    log.full_name,
                    log.email,
                    log.action,
                    log.description,
                    log.role,
                    log.action_type
                ].filter(Boolean).join(' ').toLowerCase();
                
                matchesSearch = searchableText.includes(searchTerm);
            }

            let matchesActionType = !actionType;
            
            if (actionType) {
                if (actionType === 'unenroll') {
                    // Match both action_type='unenroll' OR action containing 'unenroll'
                    matchesActionType = log.action_type === 'unenroll' || 
                                       (log.action && log.action.toLowerCase().includes('unenroll'));
                } else if (actionType === 'update') {
                    // When filtering 'update', exclude unenroll actions
                    matchesActionType = log.action_type === 'update' && 
                                       !(log.action && log.action.toLowerCase().includes('unenroll'));
                } else {
                    // Normal matching for other action types
                    matchesActionType = log.action_type === actionType;
                }
            }

            // Date filter - match exact date
            const matchesDate = !dateValue || log.created_at.startsWith(dateValue);

            return matchesSearch && matchesActionType && matchesDate;
        });

        currentPage = 1;
        renderLogs();
    }

    function renderLogs() {
        const tbody = document.getElementById('auditTableBody');
        const logCount = document.getElementById('logCount');
        
        // Update count
        logCount.textContent = filteredLogs.length;

        // Calculate pagination
        totalPages = Math.ceil(filteredLogs.length / logsPerPage);
        const startIndex = (currentPage - 1) * logsPerPage;
        const endIndex = startIndex + logsPerPage;
        const logsToShow = filteredLogs.slice(startIndex, endIndex);

        // Clear table
        tbody.innerHTML = '';

        if (logsToShow.length === 0) {
            const emptyMessage = document.getElementById('searchInput').value || 
                               document.getElementById('actionTypeFilter').value || 
                               document.getElementById('dateFilter').value
                ? 'No activity found matching your filters'
                : 'No activity recorded yet';
                
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="empty-title">No activity found</div>
                        <div class="empty-message">${emptyMessage}</div>
                    </td>
                </tr>
            `;
            document.getElementById('paginationWrapper').style.display = 'none';
            return;
        }

        // Render logs
        logsToShow.forEach(log => {
            const row = createLogRow(log);
            tbody.appendChild(row);
        });

        // Update pagination
        updatePagination();
    }

    function createLogRow(log) {
        const tr = document.createElement('tr');
        
        const initials = getInitials(log.full_name);
        const actionBadge = getActionBadge(log.action_type, log.action);
        const formattedDate = formatDateTime(log.created_at);
        const cleanDescription = getCleanDescription(log);
        
        tr.innerHTML = `
            <td>
                <div class="user-info">
                    <div class="user-avatar">${initials}</div>
                    <div class="user-details">
                        <div class="user-name">${escapeHtml(log.full_name)}</div>
                        <div class="user-role">${capitalizeFirst(log.role)}</div>
                    </div>
                </div>
            </td>
            <td>${actionBadge}</td>
            <td>
                <div style="max-width: 400px;" title="${escapeHtml(cleanDescription)}">
                    ${escapeHtml(cleanDescription)}
                </div>
            </td>
            <td style="white-space: nowrap;">${formattedDate}</td>
        `;
        
        return tr;
    }

    function getCleanDescription(log) {
        return log.description || log.action || 'No details';
    }

    function getActionBadge(actionType, action) {
        if (action && action.toLowerCase().includes('unenroll')) {
            return '<span class="badge badge-unenroll"><i class="fas fa-door-open"></i> Unenroll</span>';
        }
        
        const badges = {
            'login': '<span class="badge badge-login"><i class="fas fa-sign-in-alt"></i> Login</span>',
            'logout': '<span class="badge badge-logout"><i class="fas fa-sign-out-alt"></i> Logout</span>',
            'create': '<span class="badge badge-create"><i class="fas fa-plus"></i> Create</span>',
            'update': '<span class="badge badge-update"><i class="fas fa-edit"></i> Update</span>',
            'delete': '<span class="badge badge-delete"><i class="fas fa-trash"></i> Delete</span>',
            'view': '<span class="badge badge-view"><i class="fas fa-eye"></i> View</span>',
            'join': '<span class="badge badge-join"><i class="fas fa-user-plus"></i> Join</span>',
            'unenroll': '<span class="badge badge-unenroll"><i class="fas fa-door-open"></i> Unenroll</span>'
        };
        
        return badges[actionType] || `<span class="badge">${escapeHtml(actionType)}</span>`;
    }

    function updatePagination() {
        const paginationWrapper = document.getElementById('paginationWrapper');
        const pagination = document.getElementById('pagination');
        const paginationInfo = document.getElementById('paginationInfo');

        if (totalPages <= 1) {
            paginationWrapper.style.display = 'none';
            return;
        }

        paginationWrapper.style.display = 'flex';

        // Update info
        const startIndex = (currentPage - 1) * logsPerPage + 1;
        const endIndex = Math.min(currentPage * logsPerPage, filteredLogs.length);
        paginationInfo.textContent = `Showing ${startIndex}-${endIndex} of ${filteredLogs.length} entries`;

        // Clear pagination
        pagination.innerHTML = '';

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn';
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', () => changePage(currentPage - 1));
        pagination.appendChild(prevBtn);

        // Page numbers
        const maxPages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
        let endPage = Math.min(totalPages, startPage + maxPages - 1);

        if (endPage - startPage < maxPages - 1) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = 'page-btn' + (i === currentPage ? ' active' : '');
            pageBtn.textContent = i;
            pageBtn.addEventListener('click', () => changePage(i));
            pagination.appendChild(pageBtn);
        }

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn';
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.addEventListener('click', () => changePage(currentPage + 1));
        pagination.appendChild(nextBtn);
    }

    function changePage(page) {
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderLogs();
        
        // Scroll to top of table
        document.querySelector('.audit-table-wrapper').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }

    function showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        overlay.style.display = show ? 'flex' : 'none';
    }

    function showError(message) {
        const tbody = document.getElementById('auditTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="empty-state">
                    <div class="empty-icon" style="color: #ef4444;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="empty-title">Error</div>
                    <div class="empty-message">${escapeHtml(message)}</div>
                </td>
            </tr>
        `;
    }

    function getInitials(name) {
        if (!name) return '?';
        const parts = name.trim().split(' ');
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        };
        return date.toLocaleDateString('en-US', options);
    }

    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();