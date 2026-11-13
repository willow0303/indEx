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

        /* Modal Overlay */
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
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-close {
            position: absolute;
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

        /* Table Styles */
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
        
        /* FLIP CARD MECHANISM - VERTICAL */
        .flip-container {
            width: 95%; 
            max-width: 900px;
            height: fit-content; 
            margin: 0 auto;
            perspective: 2000px; 
            position: relative; 
            min-height: 550px; 
        }

        .flipper {
            transition: transform 0.8s;
            transform-style: preserve-3d;
            position: absolute; 
            width: 100%;
            height: 100%; 
        }

        .flip-container.flipped .flipper {
            transform: rotateX(180deg);
        }

        .front, .back {
            position: absolute;
            width: 100%;
            height: 100%; 
            backface-visibility: hidden;
            background-color: var(--cream); 
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2); 
            border: 2px solid var(--gray-dark); 
            border-radius: 5px; 
            overflow: hidden; 
        }

        .back {
            transform: rotateX(180deg);
        }

        /* GRADE SHEET LAYOUT STYLES */
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

        /* Interactive Header Styles */
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
            }

            .photo-cell {
                width: 100px;
            }

            .modal-close {
                top: 10px;
                right: 10px;
            }
        }
    </style>
</head>
<body>

    <!-- Modal -->
    <div class="modal-overlay" id="gradeCardModal">
        <button class="modal-close" onclick="closeGradeCardModal()">×</button>
        
        <div class="flip-container" id="cardFlipContainer">
            <div class="flipper">
                <!-- FRONT - MIDTERM -->
                <div class="front">
                    <div class="card-content">
                        <div class="header-section">
                            <table class="info-table">
                                <tbody>
                                    <tr><th style="border-top: 1px solid var(--gray-dark);">NAME:</th><td></td></tr>
                                    <tr><th>COURSE, YEAR & SECTION:</th><td></td></tr>
                                    <tr><th>COURSE TITLE:</th><td></td></tr>
                                    <tr><th>COURSE CODE:</th><td></td></tr>
                                </tbody>
                            </table>
                            <table style="width: 150px;">
                                <tr>
                                    <td class="photo-cell photo-row-height" style="height: 100%;">
                                        <div class="image-container">
                                            <img src="" alt="1x1 Photo" title="Insert 1x1 Photo Here">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <table>
                            <tbody>
                                <tr class="grade-row">
                                    <th colspan="2" style="background-color: var(--gray-light);">Midterm Grade:</th>
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
                            <tbody>
                                <tr><td rowspan="10" class="term-group"></td><td class="score-label">MQ1</td><td class="score-input"></td><td class="score-label">MACT1</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">MQ2</td><td class="score-input"></td><td class="score-label">MACT2</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">MQ3</td><td class="score-input"></td><td class="score-label">MACT3</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">MQ4</td><td class="score-input"></td><td class="score-label">MACT4</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">MQ5</td><td class="score-input"></td><td class="score-label">MACT5</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">MQ6</td><td class="score-input"></td><td class="score-label">MACT6</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">MQ7</td><td class="score-input"></td><td class="score-label">MACT7</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">MQ8</td><td class="score-input"></td><td class="score-label">MACT8</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">MQ9</td><td class="score-input"></td><td class="score-label">MACT9</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">MQ10</td><td class="score-input"></td><td class="score-label">MACT10</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>

                                <tr class="total-row">
                                    <td class="combined-total-label">TOTAL / AVERAGE</td> 
                                    <td colspan="1" class="total-label">Quiz Total:</td><td colspan="1" class="total-input"></td> 
                                    <td colspan="1" class="total-label">Activity Total:</td><td colspan="1" class="total-input"></td> 
                                    <td class="special-score total-input"></td>
                                    <td class="special-score total-input"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- BACK - FINALS -->
                <div class="back">
                    <div class="card-content">
                        <div class="header-section">
                            <table class="info-table">
                                <tbody>
                                    <tr><th style="border-top: 1px solid var(--gray-dark);">NAME:</th><td></td></tr>
                                    <tr><th>COURSE, YEAR & SECTION:</th><td></td></tr>
                                    <tr><th>COURSE TITLE:</th><td></td></tr>
                                    <tr><th>COURSE CODE:</th><td></td></tr>
                                </tbody>
                            </table>
                            <table style="width: 150px;">
                                <tr>
                                    <td class="photo-cell photo-row-height" style="height: 100%;">
                                        <div class="image-container">
                                            <img src="" alt="1x1 Photo" title="Insert 1x1 Photo Here">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <table>
                            <tbody>
                                <tr class="grade-row">
                                    <th colspan="2" class="interactive-header" onclick="flipCardBack()">Midterm Grade: (Click to flip back)</th> 
                                    <th colspan="2" style="background-color: var(--gray-light);">Final Term Grade:</th>
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
                            <tbody>
                                <tr><td rowspan="10" class="term-group"></td><td class="score-label">FQ1</td><td class="score-input"></td><td class="score-label">FACT1</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">FQ2</td><td class="score-input"></td><td class="score-label">FACT2</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">FQ3</td><td class="score-input"></td><td class="score-label">FACT3</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">FQ4</td><td class="score-input"></td><td class="score-label">FACT4</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">FQ5</td><td class="score-input"></td><td class="score-label">FACT5</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">FQ6</td><td class="score-input"></td><td class="score-label">FACT6</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">FQ7</td><td class="score-input"></td><td class="score-label">FACT7</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">FQ8</td><td class="score-input"></td><td class="score-label">FACT8</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">FQ9</td><td class="score-input"></td><td class="score-label">FACT9</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                <tr><td class="score-label">FQ10</td><td class="score-input"></td><td class="score-label">FACT10</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>

                                <tr class="total-row">
                                    <td class="combined-total-label">TOTAL / AVERAGE</td> 
                                    <td colspan="1" class="total-label">Quiz Total:</td><td colspan="1" class="total-input"></td> 
                                    <td colspan="1" class="total-label">Activity Total:</td><td colspan="1" class="total-input"></td> 
                                    <td class="special-score total-input"></td>
                                    <td class="special-score total-input"></td>
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
        
        function openGradeCardModal() {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeGradeCardModal() {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
            // Reset card to front when closing
            setTimeout(() => {
                card.classList.remove('flipped');
            }, 300);
        }
        
        function flipCardToFinal() {
            // Flip downward to upward (Midterm to Finals)
            card.classList.add('flipped');
        }
        
        function flipCardBack() {
            // Flip upward to downward (Finals to Midterm)
            card.classList.remove('flipped');
        }

        // Close modal when clicking outside the card
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeGradeCardModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeGradeCardModal();
            }
        });

        // Expose function to parent window
        if (window.parent) {
            window.parent.openGradeCardModal = openGradeCardModal;
        }
    </script>

</body>
</html>