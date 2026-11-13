<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Card</title>
    <style>
        :root {
    --cream: #FFF8E7;
    --gray-dark: #333;
    --gray-medium: #666;
    --gray-light: #E8E8E8;
    --maroon-light: #8B0000;
    --maroon-dark: #660000;
    --white: #FFFFFF;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: transparent;
}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.3s;
    overflow-y: auto;
}

.modal-overlay.active {
    display: flex;
    opacity: 1;
}

.modal-close {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    background-color: var(--maroon-light);
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    z-index: 1002;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.modal-close:hover {
    background-color: var(--maroon-dark);
    transform: rotate(90deg);
}

table { 
    width: 100%; 
    border-collapse: collapse; 
    text-align: left; 
    font-size: 0.85em; 
}

th, td { 
    padding: 5px; 
    border: 1px solid var(--gray-dark);
    height: 15px; 
    vertical-align: middle; 
}

.flip-container {
    width: 95%; 
    max-width: 900px;
    height: auto;
    min-height: 550px;
    margin: auto;
    perspective: 2000px; 
    position: relative;
    padding: 60px 0;
}

.flipper {
    transition: transform 0.8s;
    transform-style: preserve-3d;
    position: relative;
    width: 100%;
    min-height: 100%;
}

.flip-container.flipped .flipper {
    transform: rotateX(180deg);
}

.front, .back {
    width: 100%;
    min-height: 100%;
    backface-visibility: hidden;
    background-color: var(--cream); 
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2); 
    border: 2px solid var(--gray-dark); 
    border-radius: 5px; 
    overflow: visible;
}

.back {
    transform: rotateX(180deg);
    position: absolute;
    top: 0;
    left: 0;
}

.front {
    position: relative;
}

.card-content {
    width: 100%;
    padding-bottom: 20px;
}

.header-section { 
    display: flex; 
    width: 100%; 
    border-bottom: 2px solid var(--gray-dark); 
}

.info-table { 
    flex-grow: 1; 
    border: none; 
}

.info-table th { 
    width: 30%; 
    font-weight: bold; 
    background-color: var(--cream); 
    border-top: none; 
    border-left: none; 
    border-right: 1px solid var(--gray-dark); 
}

.info-table td { 
    background-color: var(--cream); 
}

.photo-cell { 
    width: 150px; 
    text-align: center; 
    border-left: 2px solid var(--gray-dark); 
    border-top: none; 
    padding: 10px;
    background-color: var(--cream); 
}

.image-container {
    width: 100%; 
    height: 100%;
    display: flex; 
    align-items: center; 
    justify-content: center;
    border: 1px solid var(--gray-medium);
    background-color: var(--gray-light); 
    overflow: hidden;
}

.image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-row-height { 
    height: 120px; 
}

.grade-row th, .main-sections th, .term-group { 
    text-align: center; 
    font-weight: bold; 
    background-color: var(--gray-light); 
    padding: 10px 5px; 
}

.term-group { 
    width: 10%; 
}

.special-header { 
    width: 15%; 
    text-align: center; 
}

.score-label, .score-input, .special-score { 
    background-color: var(--cream); 
}

.score-label { 
    width: 5%; 
    text-align: center; 
    font-weight: normal; 
    background-color: var(--cream); 
}

.score-input { 
    width: 5%; 
    text-align: center; 
    font-size: 0.8em; 
    background-color: var(--cream); 
}

.special-score { 
    width: 15%; 
    text-align: center; 
    background-color: var(--cream); 
}

.total-row td { 
    font-weight: bold; 
    background-color: var(--gray-light); 
}

.total-row .total-label { 
    text-align: right; 
    padding-right: 10px; 
    background-color: var(--gray-light); 
}

.total-row .total-input { 
    background-color: var(--cream);
    font-size: 0.85em; 
}

.combined-total-label { 
    text-align: center; 
}

.interactive-header {
    cursor: pointer; 
    user-select: none;
    background-color: var(--maroon-light);
    color: var(--white);
    transition: background-color 0.3s;
    position: relative;
}

.interactive-header:hover {
    background-color: var(--maroon-dark);
}

.interactive-header::after {
    content: '▼';
    position: absolute;
    right: 10px;
    font-size: 12px;
}

.flip-container.flipped .front .interactive-header::after {
    content: '▲';
}

@media (max-width: 768px) {
    .flip-container {
        width: 100%;
        font-size: 0.9em;
        padding: 80px 10px 20px 10px;
    }

    .photo-cell {
        width: 100px;
    }

    .modal-close {
        top: 10px;
        right: 10px;
    }
    
    .modal-overlay {
        padding: 10px;
    }
}

@media (max-height: 700px) {
    .flip-container {
        min-height: auto;
    }
}
    </style>
</head>
<body>

    <div class="modal-overlay" id="gradeCardModal">
        <button class="modal-close" onclick="closeGradeCardModal()">×</button>
        
        <div class="flip-container" id="cardFlipContainer">
            <div class="flipper">
                <div class="front">
                    <div class="card-content">
                        <div class="header-section">
                            <table class="info-table">
                                <tbody>
                                    <tr><th style="border-top: 1px solid var(--gray-dark);">NAME:</th><td id="studentName"></td></tr>
                                    <tr><th>COURSE, YEAR & SECTION:</th><td id="studentYearSection"></td></tr>
                                    <tr><th>COURSE TITLE:</th><td id="courseTitle"></td></tr>
                                    <tr><th>COURSE CODE:</th><td id="courseCode"></td></tr>
                                </tbody>
                            </table>
                            <table style="width: 150px;">
                                <tr>
                                    <td class="photo-cell photo-row-height" style="height: 100%;">
                                        <div class="image-container">
                                            <img id="studentPhoto" src="" alt="1x1 Photo" title="Student Photo">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <table>
                            <tbody>
                                <tr class="grade-row">
                                    <th colspan="2" style="background-color: var(--gray-light);">Midterm Grade: <span id="midtermAverage">0.00</span>%</th>
                                    <th colspan="2" class="interactive-header" onclick="flipCardToFinal()">Final Term Grade: (Click to flip)</th> 
                                </tr>
                            </tbody>
                        </table>

                        <table>
                            <thead>
                                <tr class="main-sections">
                                    <th rowspan="2" class="term-group">MIDTERMS</th>
                                    <th colspan="2">QUIZZES</th>
                                    <th colspan="2">ACTIVITIES</th>
                                    <th rowspan="2" class="special-header">PROJECT</th>
                                    <th rowspan="2" class="special-header">MIDTERM EXAM</th>
                                </tr>
                                <tr class="main-sections">
                                    <th class="score-label">Label</th><th class="score-input">Score</th>
                                    <th class="score-label">Label</th><th class="score-input">Score</th>
                                </tr>
                            </thead>
                            <tbody id="midtermTableBody">
                                <tr><td rowspan="10" class="term-group"></td><td class="score-label">Quiz 1</td><td class="score-input">-</td><td class="score-label">Activity 1</td><td class="score-input">-</td><td class="special-score">-</td><td class="special-score">-</td></tr>
                                <tr><td class="score-label">Quiz 2</td><td class="score-input">-</td><td class="score-label">Activity 2</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 3</td><td class="score-input">-</td><td class="score-label">Activity 3</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 4</td><td class="score-input">-</td><td class="score-label">Activity 4</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 5</td><td class="score-input">-</td><td class="score-label">Activity 5</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 6</td><td class="score-input">-</td><td class="score-label">Activity 6</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 7</td><td class="score-input">-</td><td class="score-label">Activity 7</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 8</td><td class="score-input">-</td><td class="score-label">Activity 8</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 9</td><td class="score-input">-</td><td class="score-label">Activity 9</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 10</td><td class="score-input">-</td><td class="score-label">Activity 10</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>

                                <tr class="total-row">
                                    <td class="combined-total-label">TOTAL / AVERAGE</td> 
                                    <td colspan="1" class="total-label">Total:</td><td colspan="1" class="total-input" id="midtermQuizTotal">-</td> 
                                    <td colspan="1" class="total-label">Total:</td><td colspan="1" class="total-input" id="midtermActivityTotal">-</td> 
                                    <td class="special-score total-input" id="midtermProjectTotal">-</td>
                                    <td class="special-score total-input" id="midtermExamTotal">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="back">
                    <div class="card-content">
                        <div class="header-section">
                            <table class="info-table">
                                <tbody>
                                    <tr><th style="border-top: 1px solid var(--gray-dark);">NAME:</th><td id="studentNameBack"></td></tr>
                                    <tr><th>COURSE, YEAR & SECTION:</th><td id="studentYearSectionBack"></td></tr>
                                    <tr><th>COURSE TITLE:</th><td id="courseTitleBack"></td></tr>
                                    <tr><th>COURSE CODE:</th><td id="courseCodeBack"></td></tr>
                                </tbody>
                            </table>
                            <table style="width: 150px;">
                                <tr>
                                    <td class="photo-cell photo-row-height" style="height: 100%;">
                                        <div class="image-container">
                                            <img id="studentPhotoBack" src="" alt="1x1 Photo" title="Student Photo">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <table>
                            <tbody>
                                <tr class="grade-row">
                                    <th colspan="2" class="interactive-header" onclick="flipCardBack()">Midterm Grade: (Click to flip back)</th> 
                                    <th colspan="2" style="background-color: var(--gray-light);">Final Term Grade: <span id="finalsAverage">0.00</span>%</th>
                                </tr>
                            </tbody>
                        </table>
                        
                        <table>
                            <thead>
                                <tr class="main-sections">
                                    <th rowspan="2" class="term-group">FINALS</th> 
                                    <th colspan="2">QUIZZES</th>
                                    <th colspan="2">ACTIVITIES</th>
                                    <th rowspan="2" class="special-header">PROJECT</th>
                                    <th rowspan="2" class="special-header">FINAL EXAM</th> 
                                </tr>
                                <tr class="main-sections">
                                    <th class="score-label">Label</th><th class="score-input">Score</th>
                                    <th class="score-label">Label</th><th class="score-input">Score</th>
                                </tr>
                            </thead>
                            <tbody id="finalsTableBody">
                                <tr><td rowspan="10" class="term-group"></td><td class="score-label">Quiz 1</td><td class="score-input">-</td><td class="score-label">Activity 1</td><td class="score-input">-</td><td class="special-score">-</td><td class="special-score">-</td></tr>
                                <tr><td class="score-label">Quiz 2</td><td class="score-input">-</td><td class="score-label">Activity 2</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 3</td><td class="score-input">-</td><td class="score-label">Activity 3</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 4</td><td class="score-input">-</td><td class="score-label">Activity 4</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 5</td><td class="score-input">-</td><td class="score-label">Activity 5</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 6</td><td class="score-input">-</td><td class="score-label">Activity 6</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 7</td><td class="score-input">-</td><td class="score-label">Activity 7</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 8</td><td class="score-input">-</td><td class="score-label">Activity 8</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 9</td><td class="score-input">-</td><td class="score-label">Activity 9</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">Quiz 10</td><td class="score-input">-</td><td class="score-label">Activity 10</td><td class="score-input">-</td><td class="special-score"></td><td class="special-score"></td></tr>

                                <tr class="total-row">
                                    <td class="combined-total-label">TOTAL / AVERAGE</td> 
                                    <td colspan="1" class="total-label">Total:</td><td colspan="1" class="total-input" id="finalsQuizTotal">-</td> 
                                    <td colspan="1" class="total-label">Total:</td><td colspan="1" class="total-input" id="finalsActivityTotal">-</td> 
                                    <td class="special-score total-input" id="finalsProjectTotal">-</td>
                                    <td class="special-score total-input" id="finalsExamTotal">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('gradeCardModal');
        const card = document.getElementById('cardFlipContainer');
        let currentClassId = null;
        
        function openGradeCardModal() {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeGradeCardModal() {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                card.classList.remove('flipped');
            }, 300);
            
            if (window.parent) {
                window.parent.postMessage('closeGradeCardModal', '*');
            }
        }
        
        function flipCardToFinal() {
            card.classList.add('flipped');
        }
        
        function flipCardBack() {
            card.classList.remove('flipped');
        }

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeGradeCardModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeGradeCardModal();
            }
        });

        window.addEventListener('message', function(event) {
            if (event.data.action === 'openGradeCard') {
                currentClassId = event.data.classId;
                fetchGradeData(currentClassId);
                openGradeCardModal();
            }
        });

        function fetchGradeData(classId) {
            fetch('../api/student/get-student-grades.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'class_id=' + classId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadGradeData(data);
                } else {
                    console.error('Failed to load grade data:', data.message);
                    alert('Failed to load grades: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching grades:', error);
                alert('Error loading grades. Please try again.');
            });
        }

        function loadGradeData(data) {
    console.log('Loading grade data:', data);
    
    const student = data.student;
    const midterm = data.midterm;
    const finals = data.finals;

    document.getElementById('studentName').textContent = student.full_name || '';
    document.getElementById('studentNameBack').textContent = student.full_name || '';
    
    document.getElementById('studentYearSection').textContent = student.year_section || '';
    document.getElementById('studentYearSectionBack').textContent = student.year_section || '';
    
    document.getElementById('courseTitle').textContent = student.class_name || '';
    document.getElementById('courseTitleBack').textContent = student.class_name || '';
    
    document.getElementById('courseCode').textContent = student.subject || '';
    document.getElementById('courseCodeBack').textContent = student.subject || '';

    const photoFront = document.getElementById('studentPhoto');
    const photoBack = document.getElementById('studentPhotoBack');
    const defaultImage = '../assets/images/default-avatar.jpg';
    
    console.log('Profile picture URL from API:', student.profile_picture);
    
    photoFront.src = defaultImage;
    photoBack.src = defaultImage;
    
    if (student.profile_picture && student.profile_picture.trim() !== '') {
        const tempImg = new Image();
        
        tempImg.onload = function() {
            console.log('Profile picture loaded successfully');
            photoFront.src = student.profile_picture;
            photoBack.src = student.profile_picture;
        };
        
        tempImg.onerror = function() {
            console.error('Failed to load profile picture:', student.profile_picture);
            const altPath = student.profile_picture.replace('../', '');
            console.log('Trying alternative path:', altPath);
            
            const tempImg2 = new Image();
            tempImg2.onload = function() {
                photoFront.src = altPath;
                photoBack.src = altPath;
            };
            tempImg2.onerror = function() {
                console.log('Alternative path failed, keeping default picture');
            };
            tempImg2.src = altPath;
        };
        
        tempImg.src = student.profile_picture;
    } else {
        console.log('No profile picture provided, using default');
    }

    document.getElementById('midtermAverage').textContent = midterm.average || '0.00';
    document.getElementById('finalsAverage').textContent = finals.average || '0.00';

    populateGrades('midterm', midterm.grades);
    populateGrades('finals', finals.grades);
}


        function populateGrades(term, grades) {
            console.log(`Populating ${term} grades:`, grades);
            
            const tbody = document.getElementById(term + 'TableBody');
            const rows = tbody.querySelectorAll('tr:not(.total-row)');

            const quizzes = grades.quizzes || [];
            const activities = grades.activities || [];
            
            console.log(`${term} - Quizzes:`, quizzes);
            console.log(`${term} - Activities:`, activities);
            
            for (let i = 0; i < 10; i++) {
                const row = rows[i];
                if (row) {
                    let quizScoreIndex, activityScoreIndex, projectIndex, examIndex;
                    
                    if (i === 0) {
                        quizScoreIndex = 2;
                        activityScoreIndex = 4;
                        projectIndex = 5;
                        examIndex = 6;
                    } else {
                        quizScoreIndex = 1;
                        activityScoreIndex = 3;
                        projectIndex = 4;
                        examIndex = 5;
                    }
                    
                    const quizCell = row.cells[quizScoreIndex];
                    if (quizzes[i] && quizzes[i].max_score > 0) {
                        quizCell.textContent = `${quizzes[i].score}/${quizzes[i].max_score}`;
                        console.log(`${term} Quiz ${i+1}:`, quizzes[i]);
                    } else {
                        quizCell.textContent = '-';
                    }
                    
                    const activityCell = row.cells[activityScoreIndex];
                    if (activities[i] && activities[i].max_score > 0) {
                        activityCell.textContent = `${activities[i].score}/${activities[i].max_score}`;
                        console.log(`${term} Activity ${i+1}:`, activities[i]);
                    } else {
                        activityCell.textContent = '-';
                    }
                    
                    if (i === 0) {
                        const projectCell = row.cells[projectIndex];
                        const examCell = row.cells[examIndex];
                        
                        const project = grades.project;
                        if (project && project.max_score > 0) {
                            projectCell.textContent = `${project.score}/${project.max_score}`;
                        } else {
                            projectCell.textContent = '-';
                        }
                        
                        const exam = grades.exam;
                        if (exam && exam.max_score > 0) {
                            examCell.textContent = `${exam.score}/${exam.max_score}`;
                        } else {
                            examCell.textContent = '-';
                        }
                    }
                }
            }

            let quizTotal = 0, quizMax = 0;
            let activityTotal = 0, activityMax = 0;

            quizzes.forEach(q => {
                if (q && q.max_score > 0) {
                    quizTotal += parseFloat(q.score) || 0;
                    quizMax += parseFloat(q.max_score) || 0;
                }
            });

            activities.forEach(a => {
                if (a && a.max_score > 0) {
                    activityTotal += parseFloat(a.score) || 0;
                    activityMax += parseFloat(a.max_score) || 0;
                }
            });

            document.getElementById(term + 'QuizTotal').textContent = quizMax > 0 ? `${quizTotal}/${quizMax}` : '-';
            document.getElementById(term + 'ActivityTotal').textContent = activityMax > 0 ? `${activityTotal}/${activityMax}` : '-';
            
            const project = grades.project;
            if (project && project.max_score > 0) {
                document.getElementById(term + 'ProjectTotal').textContent = `${project.score}/${project.max_score}`;
            } else {
                document.getElementById(term + 'ProjectTotal').textContent = '-';
            }
            
            const exam = grades.exam;
            if (exam && exam.max_score > 0) {
                document.getElementById(term + 'ExamTotal').textContent = `${exam.score}/${exam.max_score}`;
            } else {
                document.getElementById(term + 'ExamTotal').textContent = '-';
            }
            
            console.log(`${term} Totals - Quiz: ${quizTotal}/${quizMax}, Activity: ${activityTotal}/${activityMax}`);
        }

        if (window.parent) {
            window.parent.openGradeCardModal = openGradeCardModal;
        }
    </script>

</body>
</html>