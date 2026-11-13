let students = [];
let grades = [];
let attendance = [];
let attendanceDates = [];
let activityColumns = [];
let isLoading = false;
let currentGradingPeriod = 'midterm';

document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('attendanceDate');
    if (dateInput) {
        dateInput.valueAsDate = new Date();
    }
    
    const btnMidterm = document.getElementById('btnMidterm');
    if (btnMidterm) {
        btnMidterm.classList.add('active');
    }
    
    if (window.classData) {
        loadData();
    }
    
    initializeEventListeners();
    addModalStyles();
    addAdditionalStyles();
});

function addModalStyles() {
    const style = document.createElement('style');
    style.textContent = `
        #studentProfileModal {
            animation: fadeIn 0.3s ease;
        }
        #studentProfileModal.closing {
            animation: fadeOut 0.3s ease;
        }
        #studentProfileModal iframe {
            animation: slideUp 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
}

function addAdditionalStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .readonly-input {
            background-color: #f3f4f6 !important;
            cursor: not-allowed !important;
            color: #6b7280 !important;
        }
        
        .field-note {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .setup-notice {
            background-color: #dbeafe;
            border-left: 3px solid #3b82f6;
            padding: 0.75rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #1e40af;
            font-size: 0.875rem;
        }
    `;
    document.head.appendChild(style);
}

function openStudentProfile(studentId, classId) {
    const modalOverlay = document.createElement('div');
    modalOverlay.id = 'studentProfileModal';
    modalOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    `;

    const iframe = document.createElement('iframe');
    iframe.src = `view-student-profile.php?student_id=${studentId}&class_id=${classId}`;
    iframe.style.cssText = `
        width: 90%;
        max-width: 1000px;
        height: 90vh;
        border: none;
        border-radius: 24px;
        background: white;
        box-shadow: 0 24px 80px rgba(0, 0, 0, 0.3);
    `;

    modalOverlay.appendChild(iframe);
    document.body.appendChild(modalOverlay);
    document.body.style.overflow = 'hidden';

    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) {
            closeStudentProfileModal();
        }
    });

    const escapeHandler = (e) => {
        if (e.key === 'Escape') {
            closeStudentProfileModal();
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    document.addEventListener('keydown', escapeHandler);

    window.addEventListener('message', handleModalMessage);
}

function handleModalMessage(e) {
    if (e.data === 'closeStudentProfile') {
        closeStudentProfileModal();
    }
}

function closeStudentProfileModal() {
    const modal = document.getElementById('studentProfileModal');
    if (modal) {
        modal.classList.add('closing');
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = '';
            window.removeEventListener('message', handleModalMessage);
        }, 300);
    }
}

function loadData() {
    showLoading('Loading student data...');
    
    const formData = new FormData();
    formData.append('action', 'get_data');
    formData.append('class_id', window.classData.class_id);
    formData.append('grading_period', currentGradingPeriod);
    formData.append('initialize_defaults', 'yes');
    
    fetch('../api/teacher/view-grades-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        if (result.success) {
            students = result.students || [];
            grades = result.grades || [];
            attendance = result.attendance || [];
            attendanceDates = result.attendanceDates || [];
            activityColumns = result.activityColumns || [];
            
            renderHeaders();
            renderTable();
            updateInfo();
            updateStudentCount();
        } else {
            showToast('Failed to load data: ' + result.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error loading data:', error);
        showToast('Network error: Failed to load data', 'error');
    });
}

function formatDate(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function showLoading(message = 'Processing...') {
    if (document.getElementById('loadingOverlay')) return;
    
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>${message}</p>
        </div>
    `;
    document.body.appendChild(overlay);
    isLoading = true;
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
    isLoading = false;
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    const container = document.querySelector('.content-wrapper');
    if (container) {
        container.insertBefore(toast, container.firstChild);
        
        setTimeout(() => {
            toast.style.animation = 'slideUp 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

function updateStudentCount() {
    const countElement = document.getElementById('studentCount');
    if (countElement) {
        countElement.textContent = students.length;
    }
}

function sortActivityColumns() {
    const typeOrder = {
        'recitation': 1,
        'assignment': 2,
        'quiz': 3,
        'project': 4,
        'exam': 5
    };
    
    return [...activityColumns].sort((a, b) => {
        let typeA = (a.activity_type || '').toLowerCase();
        let typeB = (b.activity_type || '').toLowerCase();
        
        const nameA = (a.activity_name || '').toLowerCase();
        const nameB = (b.activity_name || '').toLowerCase();
        
        if (!typeA && nameA.includes('activity')) {
            typeA = 'assignment';
        }
        if (!typeB && nameB.includes('activity')) {
            typeB = 'assignment';
        }
        
        const orderA = typeOrder[typeA] || 999;
        const orderB = typeOrder[typeB] || 999;
        
        if (orderA !== orderB) {
            return orderA - orderB;
        }
        
        return a.activity_name.localeCompare(b.activity_name);
    });
}

function renderHeaders() {
    const header = document.getElementById('tableHeader');
    if (!header) return;
    
    const sortedColumns = sortActivityColumns();
    
    let headerHTML = '<tr>';
    headerHTML += '<th class="col-number">#</th>';
    headerHTML += '<th class="col-student-name">Student Name<span class="date-label">Surname, Firstname</span></th>';
    
    attendanceDates.forEach(date => {
        headerHTML += `<th class="col-attendance" data-date="${date}">
            Attendance
            <span class="date-label">${formatDate(date)}</span>
            <button class="btn-delete-column" onclick="deleteAttendanceColumn('${date}')" title="Delete column">
                <i class="fas fa-times"></i>
            </button>
        </th>`;
    });
    
    sortedColumns.forEach(col => {
        const isDefault = ['Recitation', 'Midterm Exam', 'Final Exam'].includes(col.activity_name);
        const maxScore = parseFloat(col.max_score);
        const totalDisplay = maxScore === 0 ? '0' : maxScore.toFixed(0);
        
        headerHTML += `<th class="col-grade" data-activity="${col.activity_name}">
            ${col.activity_name}
            <span class="date-label">Total: ${totalDisplay}</span>
            ${!isDefault ? `<button class="btn-delete-column" onclick="deleteGradeColumn('${col.activity_name}')" title="Delete column">
                <i class="fas fa-times"></i>
            </button>` : ''}
        </th>`;
    });
    
    headerHTML += '<th class="col-average">Average</th>';
    headerHTML += '</tr>';
    
    header.innerHTML = headerHTML;
}

function showSetTotalModal(activityName) {
    const existingModal = document.getElementById('gradeModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement('div');
    modal.id = 'gradeModal';
    modal.className = 'grade-modal-overlay';
    modal.innerHTML = `
        <div class="grade-modal">
            <div class="grade-modal-header">
                <h3>Set Total Score</h3>
                <button class="modal-close" onclick="closeGradeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grade-modal-body">
                <div class="modal-info">
                    <div class="info-item">
                        <span class="info-label">Column:</span>
                        <span class="info-value">${escapeHtml(activityName)}</span>
                    </div>
                </div>
                
                <div class="modal-form">
                    <div class="setup-notice">
                        <i class="fas fa-info-circle"></i>
                        <span>Set the total score for this column. This will apply to all students. After setting the total, you can enter individual grades directly in the table.</span>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="modalMaxScore">Total Score for ${escapeHtml(activityName)}</label>
                        <input type="number" id="modalMaxScore" class="modal-input" 
                               value="" 
                               min="1" step="1" 
                               placeholder="Enter total score (e.g., 50, 100)"
                               autofocus>
                        <span class="field-note">This total will be applied to all students in this column</span>
                    </div>
                </div>
            </div>
            <div class="grade-modal-footer">
                <button class="btn-modal btn-cancel" onclick="closeGradeModal()">Cancel</button>
                <button class="btn-modal btn-save" onclick="saveTotalFromModal('${escapeHtml(activityName)}')">
                    <i class="fas fa-save"></i> Set Total Score
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    setTimeout(() => {
        const maxInput = document.getElementById('modalMaxScore');
        if (maxInput) {
            maxInput.focus();
            maxInput.select();
        }
    }, 100);
    
    const maxInput = document.getElementById('modalMaxScore');
    
    maxInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            saveTotalFromModal(activityName);
        }
    });
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeGradeModal();
        }
    });
}

function closeGradeModal() {
    const modal = document.getElementById('gradeModal');
    if (modal) {
        modal.classList.add('closing');
        setTimeout(() => modal.remove(), 200);
    }
}

function saveTotalFromModal(activityName) {
    const maxInput = document.getElementById('modalMaxScore');
    
    let newMaxScore = parseFloat(maxInput.value);
    
    if (isNaN(newMaxScore) || newMaxScore <= 0) {
        showToast('Please enter a valid total score (must be greater than 0)', 'error');
        maxInput.focus();
        return;
    }
    
    newMaxScore = Math.round(newMaxScore);
    
    closeGradeModal();
    showLoading('Setting total score...');
    
    const formData = new FormData();
    formData.append('action', 'set_column_total');
    formData.append('class_id', window.classData.class_id);
    formData.append('activity_name', activityName);
    formData.append('max_score', newMaxScore);
    formData.append('grading_period', currentGradingPeriod);
    
    fetch('../api/teacher/view-grades-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        if (result.success) {
            showToast('Total score set successfully', 'success');
            
            activityColumns.forEach(col => {
                if (col.activity_name === activityName) {
                    col.max_score = newMaxScore;
                }
            });
            
            grades.forEach(g => {
                if (g.activity_name === activityName) {
                    g.max_score = newMaxScore;
                }
            });
            
            renderHeaders();
            renderTable();
        } else {
            showToast('Failed to set total: ' + result.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('Network error: Failed to set total', 'error');
    });
}

window.closeGradeModal = closeGradeModal;
window.saveTotalFromModal = saveTotalFromModal;

function getGrade(studentId, activityName) {
    const grade = grades.find(g => 
        g.student_id == studentId && g.activity_name === activityName
    );
    return grade ? parseFloat(grade.score) || 0 : 0;
}

function getMaxScore(activityName) {
    const activity = activityColumns.find(col => col.activity_name === activityName);
    return activity ? parseFloat(activity.max_score) || 0 : 0;
}

function getAttendanceStatus(studentId, date) {
    const att = attendance.find(a => 
        a.student_id == studentId && a.attendance_date === date
    );
    
    if (!att) {
        return '';
    }
    
    const status = att.status || '';
    return status;
}

function calculateAverage(studentId) {
    if (activityColumns.length === 0) return '0.00';
    
    let totalPercentage = 0;
    let validColumns = 0;
    
    activityColumns.forEach(col => {
        const maxScore = parseFloat(col.max_score) || 0;
        if (maxScore > 0) {
            const grade = getGrade(studentId, col.activity_name);
            const percentage = (grade / maxScore) * 100;
            totalPercentage += percentage;
            validColumns++;
        }
    });
    
    return validColumns > 0 ? (totalPercentage / validColumns).toFixed(2) : '0.00';
}

function renderTable() {
    const tbody = document.getElementById('tableBody');
    if (!tbody) return;
    
    const sortedColumns = sortActivityColumns();
    const totalColumns = 2 + attendanceDates.length + activityColumns.length + 1;
    
    if (students.length === 0) {
        tbody.innerHTML = `
            <tr class="no-data-row">
                <td colspan="${totalColumns}" style="text-align: center; padding: 2rem; color: #999;">
                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    Waiting for students to enroll...
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    
    students.forEach((student, index) => {
        const average = calculateAverage(student.user_id);
        
        html += `<tr data-student-id="${student.user_id}">
            <td>${index + 1}</td>
            <td class="student-name-cell" data-student-id="${student.user_id}" style="color: #800020; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                ${escapeHtml(student.formatted_name)}
            </td>`;
        
        attendanceDates.forEach(date => {
            const status = getAttendanceStatus(student.user_id, date);
            const statusClass = status !== '' ? status : '';
            const selectedEmpty = status === '' ? 'selected' : '';
            const selectedPresent = status === 'present' ? 'selected' : '';
            const selectedAbsent = status === 'absent' ? 'selected' : '';
            const selectedLate = status === 'late' ? 'selected' : '';
            const selectedExcused = status === 'excused' ? 'selected' : '';
            
            html += `<td class="attendance-cell">
                <select class="attendance-select ${statusClass}" 
                        data-student-id="${student.user_id}" 
                        data-date="${date}">
                    <option value="" ${selectedEmpty}>-- Mark --</option>
                    <option value="present" ${selectedPresent}>Present</option>
                    <option value="absent" ${selectedAbsent}>Absent</option>
                    <option value="late" ${selectedLate}>Late</option>
                    <option value="excused" ${selectedExcused}>Excused</option>
                </select>
            </td>`;
        });
        
        sortedColumns.forEach(col => {
            const grade = getGrade(student.user_id, col.activity_name);
            const maxScore = parseFloat(col.max_score) || 0;
            const displayGrade = maxScore === 0 ? 'Ã¢â‚¬â€' : grade;
            const titleText = maxScore === 0 ? 'Click to set total score for this column' : `Click to enter grade (Max: ${maxScore})`;
            
            html += `<td class="editable" 
                        data-student-id="${student.user_id}" 
                        data-activity="${escapeHtml(col.activity_name)}"
                        data-max="${col.max_score}"
                        title="${titleText}">
                ${displayGrade}
            </td>`;
        });
        
        html += `<td class="total-cell" title="Average Percentage">${average}%</td>`;
        html += `</tr>`;
    });
    
    tbody.innerHTML = html;
    attachEventListeners();
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function updateInfo() {
    const attendanceInfo = document.getElementById('attendanceInfo');
    const gradeInfo = document.getElementById('gradeInfo');
    
    if (!attendanceInfo || !gradeInfo) return;
    
    if (attendanceDates.length === 0) {
        attendanceInfo.textContent = 'No attendance sessions added yet';
    } else {
        const dates = attendanceDates.slice(0, 3).map(d => formatDate(d)).join(', ');
        const more = attendanceDates.length > 3 ? ` and ${attendanceDates.length - 3} more` : '';
        attendanceInfo.textContent = `${attendanceDates.length} session(s): ${dates}${more}`;
    }
    
    if (activityColumns.length === 0) {
        gradeInfo.textContent = 'Default columns loaded (Total: 0 for each)';
    } else {
        const summary = {};
        activityColumns.forEach(col => {
            const type = col.activity_type.charAt(0).toUpperCase() + col.activity_type.slice(1);
            summary[type] = (summary[type] || 0) + 1;
        });
        const parts = Object.entries(summary).map(([type, count]) => 
            `${count} ${type}${count > 1 ? 's' : ''}`
        );
        gradeInfo.textContent = parts.join(', ');
    }
}

function attachEventListeners() {
    document.querySelectorAll('.student-name-cell').forEach(cell => {
        cell.addEventListener('click', function(e) {
            if (isLoading) return;
            
            const studentId = this.dataset.studentId;
            const classId = window.classData.class_id;
            
            openStudentProfile(studentId, classId);
        });
        
        cell.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(128, 0, 32, 0.05)';
            this.style.textDecoration = 'underline';
        });
        
        cell.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.textDecoration = 'none';
        });
    });
    
    document.querySelectorAll('.editable').forEach(cell => {
        cell.addEventListener('click', function(e) {
            if (isLoading) return;
            if (this.querySelector('input')) return;
            
            const currentMaxScore = parseFloat(this.dataset.max);
            const studentId = this.dataset.studentId;
            const activityName = this.dataset.activity;
            
            if (currentMaxScore === 0) {
                showSetTotalModal(activityName);
            } else {
                makeInlineEditable(this, studentId, activityName, currentMaxScore);
            }
        });
    });
    
    document.querySelectorAll('.attendance-select').forEach(select => {
        select.addEventListener('change', function() {
            if (isLoading) return;
            
            const studentId = this.dataset.studentId;
            const date = this.dataset.date;
            const status = this.value;
            
            if (status === '') {
                this.className = 'attendance-select';
                saveAttendanceToServer(studentId, date, status);
                return;
            }
            
            this.className = `attendance-select ${status}`;
            saveAttendanceToServer(studentId, date, status);
            
            const attIndex = attendance.findIndex(a => 
                a.student_id == studentId && a.attendance_date === date
            );
            if (attIndex !== -1) {
                attendance[attIndex].status = status;
            } else {
                attendance.push({
                    student_id: studentId,
                    attendance_date: date,
                    status: status
                });
            }
        });
    });
}

function makeInlineEditable(cell, studentId, activityName, maxScore) {
    const currentValue = cell.textContent.trim();
    const numValue = (currentValue === 'Ã¢â‚¬â€') ? 0 : (parseFloat(currentValue) || 0);
    
    const input = document.createElement('input');
    input.type = 'number';
    input.value = numValue;
    input.min = 0;
    input.max = maxScore;
    input.step = 0.5;
    input.style.cssText = `
        width: 100%;
        padding: 0.5rem;
        border: 2px solid #800020;
        border-radius: 4px;
        font-size: 0.9rem;
        text-align: center;
        background: #fff9f0;
    `;
    
    cell.innerHTML = '';
    cell.appendChild(input);
    input.focus();
    input.select();
    
    const saveInlineGrade = () => {
        let newScore = parseFloat(input.value);
        
        if (isNaN(newScore)) newScore = 0;
        if (newScore < 0) newScore = 0;
        if (newScore > maxScore) newScore = maxScore;
        
        newScore = Math.round(newScore * 100) / 100;
        
        cell.textContent = newScore;
        
        saveGradeToServer(studentId, activityName, newScore);
        
        const gradeIndex = grades.findIndex(g => 
            g.student_id == studentId && g.activity_name === activityName
        );
        if (gradeIndex !== -1) {
            grades[gradeIndex].score = newScore;
        } else {
            grades.push({
                student_id: studentId,
                activity_name: activityName,
                score: newScore,
                max_score: maxScore
            });
        }
        
        const row = cell.closest('tr');
        if (row) {
            const avgCell = row.querySelector('.total-cell');
            if (avgCell) {
                const newAvg = calculateAverage(studentId);
                avgCell.textContent = newAvg + '%';
            }
        }
    };
    
    input.addEventListener('blur', saveInlineGrade);
    
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            input.blur();
        }
    });
    
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cell.textContent = currentValue;
        }
    });
}

function saveGradeToServer(studentId, activityName, score) {
    const formData = new FormData();
    formData.append('action', 'save_grade');
    formData.append('student_id', studentId);
    formData.append('class_id', window.classData.class_id);
    formData.append('activity_name', activityName);
    formData.append('score', score);
    formData.append('grading_period', currentGradingPeriod);
    
    fetch('../api/teacher/view-grades-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('Grade saved', 'success');
        } else {
            console.error('Error saving grade:', result.message);
            showToast('Failed to save grade: ' + result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error: Failed to save grade', 'error');
    });
}

function saveAttendanceToServer(studentId, date, status) {
    const formData = new FormData();
    formData.append('action', 'save_attendance');
    formData.append('student_id', studentId);
    formData.append('class_id', window.classData.class_id);
    formData.append('attendance_date', date);
    formData.append('status', status);
    formData.append('grading_period', currentGradingPeriod);
    
    fetch('../api/teacher/view-grades-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const statusText = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Not marked';
            showToast(`Attendance marked as ${statusText}`, 'success');
        } else {
            console.error('Error saving attendance:', result.message);
            showToast('Failed to save attendance', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error: Failed to save attendance', 'error');
    });
}

function initializeEventListeners() {
    const addAttendanceBtn = document.getElementById('addAttendanceBtn');
    if (addAttendanceBtn) {
        addAttendanceBtn.addEventListener('click', addAttendanceColumn);
    }
    
    const addColumnBtns = document.querySelectorAll('.btn-add');
    addColumnBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            addGradeColumn(type);
        });
    });
    
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortStudents(this.value);
        });
    }
    
    const periodBtns = document.querySelectorAll('.btn-period');
    periodBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            switchGradingPeriod(period);
        });
    });
    
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            loadData();
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
            }, 1000);
        });
    }
}

function addAttendanceColumn() {
    if (isLoading) return;
    
    const dateInput = document.getElementById('attendanceDate');
    if (!dateInput || !dateInput.value) {
        showToast('Please select a date', 'error');
        return;
    }
    
    const selectedDate = dateInput.value;
    
    if (attendanceDates.includes(selectedDate)) {
        showToast('Attendance for this date already exists', 'error');
        return;
    }
    
    showLoading('Adding attendance column...');
    
    const formData = new FormData();
    formData.append('action', 'add_attendance_column');
    formData.append('class_id', window.classData.class_id);
    formData.append('attendance_date', selectedDate);
    formData.append('grading_period', currentGradingPeriod);
    
    fetch('../api/teacher/view-grades-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        if (result.success) {
            showToast('Attendance column added successfully', 'success');
            loadData();
        } else {
            showToast('Failed to add attendance column: ' + result.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('Network error: Failed to add attendance column', 'error');
    });
}

function addGradeColumn(type) {
    if (isLoading) return;
    
    const typeNames = {
        'quiz': 'Quiz',
        'assignment': 'Activity',
        'project': 'Project'
    };
    
    const typeName = typeNames[type] || type;
    
    showLoading(`Adding ${typeName} column...`);
    
    const formData = new FormData();
    formData.append('action', 'add_grade_column');
    formData.append('class_id', window.classData.class_id);
    formData.append('activity_type', type);
    formData.append('grading_period', currentGradingPeriod);
    
    fetch('../api/teacher/view-grades-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        if (result.success) {
            showToast(`${typeName} column added successfully (Total: 0)`, 'success');
            loadData();
        } else {
            showToast(`Failed to add ${typeName} column: ` + result.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast(`Network error: Failed to add ${typeName} column`, 'error');
    });
}

function deleteAttendanceColumn(date) {
    if (isLoading) return;
    
    if (!confirm(`Are you sure you want to delete the attendance column for ${formatDate(date)}? This will remove all attendance records for this date.`)) {
        return;
    }
    
    showLoading('Deleting attendance column...');
    
    const formData = new FormData();
    formData.append('action', 'delete_attendance_column');
    formData.append('class_id', window.classData.class_id);
    formData.append('attendance_date', date);
    formData.append('grading_period', currentGradingPeriod);
    
    fetch('../api/teacher/view-grades-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        if (result.success) {
            showToast('Attendance column deleted successfully', 'success');
            loadData();
        } else {
            showToast('Failed to delete attendance column: ' + result.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('Network error: Failed to delete attendance column', 'error');
    });
}

window.deleteAttendanceColumn = deleteAttendanceColumn;

function deleteGradeColumn(activityName) {
    if (isLoading) return;
    
    if (!confirm(`Are you sure you want to delete the column "${activityName}"? This will remove all grades for this activity.`)) {
        return;
    }
    
    showLoading('Deleting grade column...');
    
    const formData = new FormData();
    formData.append('action', 'delete_grade_column');
    formData.append('class_id', window.classData.class_id);
    formData.append('activity_name', activityName);
    formData.append('grading_period', currentGradingPeriod);
    
    fetch('../api/teacher/view-grades-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        if (result.success) {
            showToast('Grade column deleted successfully', 'success');
            loadData();
        } else {
            showToast('Failed to delete grade column: ' + result.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('Network error: Failed to delete grade column', 'error');
    });
}

window.deleteGradeColumn = deleteGradeColumn;

function sortStudents(sortType) {
    if (!students || students.length === 0) return;
    
    const sortedStudents = [...students];
    
    switch(sortType) {
        case 'name-az':
            sortedStudents.sort((a, b) => a.formatted_name.localeCompare(b.formatted_name));
            break;
        case 'name-za':
            sortedStudents.sort((a, b) => b.formatted_name.localeCompare(a.formatted_name));
            break;
        case 'grade-high':
            sortedStudents.sort((a, b) => {
                const avgA = parseFloat(calculateAverage(a.user_id));
                const avgB = parseFloat(calculateAverage(b.user_id));
                return avgB - avgA;
            });
            break;
        case 'grade-low':
            sortedStudents.sort((a, b) => {
                const avgA = parseFloat(calculateAverage(a.user_id));
                const avgB = parseFloat(calculateAverage(b.user_id));
                return avgA - avgB;
            });
            break;
    }
    
    students = sortedStudents;
    renderTable();
}

function switchGradingPeriod(period) {
    if (isLoading || period === currentGradingPeriod) return;
    
    currentGradingPeriod = period;
    
    document.querySelectorAll('.btn-period').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.period === period) {
            btn.classList.add('active');
        }
    });
    
    loadData();
}