<?php

function getClassInfo($class_id, $teacher_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                c.class_id,
                c.class_name,
                c.subject,
                c.section,
                c.class_code,
                c.created_at,
                COUNT(DISTINCT e.student_id) as student_count
            FROM classes c
            LEFT JOIN enrollments e ON c.class_id = e.class_id AND e.status = 'active'
            WHERE c.class_id = :class_id AND c.teacher_id = :teacher_id AND c.status = 'active'
            GROUP BY c.class_id
        ");
        
        $stmt->execute([
            ':class_id' => $class_id,
            ':teacher_id' => $teacher_id
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getClassInfo error: " . $e->getMessage());
        return null;
    }
}

function getEnrolledStudents($class_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                u.user_id,
                u.full_name,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.email,
                u.program,
                u.year_section,
                u.profile_picture,
                e.enrolled_at,
                e.enrollment_id
            FROM enrollments e
            JOIN users u ON e.student_id = u.user_id
            WHERE e.class_id = :class_id AND e.status = 'active'
            ORDER BY u.last_name ASC, u.first_name ASC
        ");
        
        $stmt->execute([':class_id' => $class_id]);
        
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($students as &$student) {
            $first_name = trim($student['first_name'] ?? '');
            $middle_name = trim($student['middle_name'] ?? '');
            $last_name = trim($student['last_name'] ?? '');
            
            if (empty($first_name) && empty($last_name) && !empty($student['full_name'])) {
                $full_name = trim($student['full_name']);
                $name_parts = explode(' ', $full_name);
                
                if (count($name_parts) == 1) {
                    $first_name = $name_parts[0];
                } elseif (count($name_parts) == 2) {
                    $first_name = $name_parts[0];
                    $last_name = $name_parts[1];
                } else {
                    $first_name = array_shift($name_parts);
                    $last_name = array_pop($name_parts);
                    $middle_name = implode(' ', $name_parts);
                }
            }
            
            $middleInitial = '';
            if (!empty($middle_name)) {
                $middleInitial = ' ' . strtoupper(substr($middle_name, 0, 1)) . '.';
            }
            
            $student['formatted_name'] = strtoupper($last_name) . ', ' . $first_name . $middleInitial;
            
            if (!empty($student['profile_picture'])) {
                $student['profile_picture'] = '../../uploads/profiles/' . $student['profile_picture'];
            } else {
                $student['profile_picture'] = '../../assets/images/default-avatar.jpg';
            }
        }
        
        return $students;
    } catch (PDOException $e) {
        error_log("getEnrolledStudents error: " . $e->getMessage());
        return [];
    }
}

function initializeDefaultGrades($student_id, $class_id, $grading_period, $teacher_id) {
    global $conn;
    
    $examName = ($grading_period === 'finals') ? 'Final Exam' : 'Midterm Exam';
    
    $defaultColumns = [
        ['name' => 'Recitation', 'type' => 'recitation', 'max' => 0],
        ['name' => 'Quiz 1', 'type' => 'quiz', 'max' => 0],
        ['name' => 'Activity 1', 'type' => 'activity', 'max' => 0],
        ['name' => 'Project 1', 'type' => 'project', 'max' => 0],
        ['name' => $examName, 'type' => 'exam', 'max' => 0]
    ];
        
    try {
        foreach ($defaultColumns as $col) {
            $checkStmt = $conn->prepare("
                SELECT grade_id FROM grades 
                WHERE student_id = :student_id 
                AND class_id = :class_id 
                AND activity_name = :activity_name
                AND grading_period = :grading_period
            ");
            $checkStmt->execute([
                ':student_id' => $student_id,
                ':class_id' => $class_id,
                ':activity_name' => $col['name'],
                ':grading_period' => $grading_period
            ]);
            
            if ($checkStmt->rowCount() === 0) {
                $stmt = $conn->prepare("
                    INSERT INTO grades (student_id, class_id, activity_name, activity_type, score, max_score, grading_period, recorded_by)
                    VALUES (:student_id, :class_id, :activity_name, :activity_type, 0, :max_score, :grading_period, :recorded_by)
                ");
                
                $stmt->execute([
                    ':student_id' => $student_id,
                    ':class_id' => $class_id,
                    ':activity_name' => $col['name'],
                    ':activity_type' => $col['type'],
                    ':max_score' => $col['max'],
                    ':grading_period' => $grading_period,
                    ':recorded_by' => $teacher_id
                ]);
            }
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("initializeDefaultGrades error: " . $e->getMessage());
        return false;
    }
}

function getClassGrades($class_id, $grading_period = 'midterm') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                g.grade_id,
                g.student_id,
                g.activity_name,
                g.activity_type,
                g.score,
                g.max_score,
                g.percentage,
                g.remarks,
                g.grading_period,
                g.created_at,
                g.updated_at
            FROM grades g
            WHERE g.class_id = :class_id AND g.grading_period = :grading_period
            ORDER BY g.created_at
        ");
        
        $stmt->execute([
            ':class_id' => $class_id,
            ':grading_period' => $grading_period
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getClassGrades error: " . $e->getMessage());
        return [];
    }
}

function getClassAttendance($class_id, $grading_period = 'midterm') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                a.attendance_id,
                a.student_id,
                a.attendance_date,
                a.status,
                a.remarks,
                a.grading_period,
                a.created_at
            FROM attendance a
            WHERE a.class_id = :class_id 
            AND a.grading_period = :grading_period
            ORDER BY a.attendance_date DESC, a.student_id
        ");
        
        $stmt->execute([
            ':class_id' => $class_id,
            ':grading_period' => $grading_period
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getClassAttendance error: " . $e->getMessage());
        return [];
    }
}

function getAttendanceDates($class_id, $grading_period = 'midterm') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT DISTINCT attendance_date
            FROM attendance
            WHERE class_id = :class_id
            AND grading_period = :grading_period
            ORDER BY attendance_date ASC
        ");
        
        $stmt->execute([
            ':class_id' => $class_id,
            ':grading_period' => $grading_period
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("getAttendanceDates error: " . $e->getMessage());
        return [];
    }
}

function getActivityColumns($class_id, $grading_period = 'midterm') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT DISTINCT 
                activity_name, 
                activity_type, 
                MAX(max_score) as max_score,
                MIN(created_at) as first_created
            FROM grades
            WHERE class_id = :class_id AND grading_period = :grading_period
            GROUP BY activity_name, activity_type
        ");
        
        $stmt->execute([
            ':class_id' => $class_id,
            ':grading_period' => $grading_period
        ]);
        
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        usort($activities, function($a, $b) {
            $typePriority = [
                'recitation' => 1,
                'quiz' => 2,
                'activity' => 3,
                'assignment' => 3,
                'project' => 4,
                'exam' => 5
            ];
            
            $priorityA = $typePriority[$a['activity_type']] ?? 99;
            $priorityB = $typePriority[$b['activity_type']] ?? 99;
            
            if ($priorityA === $priorityB) {
                preg_match('/(\d+)/', $a['activity_name'], $matchA);
                preg_match('/(\d+)/', $b['activity_name'], $matchB);
                
                $numA = isset($matchA[1]) ? intval($matchA[1]) : 0;
                $numB = isset($matchB[1]) ? intval($matchB[1]) : 0;
                
                if ($numA !== $numB) {
                    return $numA - $numB;
                }
                
                return strcmp($a['activity_name'], $b['activity_name']);
            }
            
            return $priorityA - $priorityB;
        });
        
        return $activities;
    } catch (PDOException $e) {
        error_log("getActivityColumns error: " . $e->getMessage());
        return [];
    }
}

function addAttendanceDate($class_id, $attendance_date, $grading_period, $teacher_id) {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count
            FROM attendance
            WHERE class_id = :class_id 
            AND attendance_date = :attendance_date
            AND grading_period = :grading_period
        ");
        $stmt->execute([
            ':class_id' => $class_id,
            ':attendance_date' => $attendance_date,
            ':grading_period' => $grading_period
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            $conn->rollBack();
            return ['success' => false, 'message' => 'Attendance for this date already exists in this grading period'];
        }
        
        $students = getEnrolledStudents($class_id);
        
        if (empty($students)) {
            $conn->rollBack();
            return ['success' => false, 'message' => 'No students enrolled in this class'];
        }
        
        $stmt = $conn->prepare("
            INSERT INTO attendance (student_id, class_id, attendance_date, status, grading_period, recorded_by)
            VALUES (:student_id, :class_id, :attendance_date, NULL, :grading_period, :recorded_by)
        ");
        
        foreach ($students as $student) {
            $success = $stmt->execute([
                ':student_id' => $student['user_id'],
                ':class_id' => $class_id,
                ':attendance_date' => $attendance_date,
                ':grading_period' => $grading_period,
                ':recorded_by' => $teacher_id
            ]);
            
            if (!$success) {
                $conn->rollBack();
                return ['success' => false, 'message' => 'Failed to insert attendance record'];
            }
        }
        
        logAudit(
            'Added attendance column',
            'CREATE',
            'attendance',
            $class_id,
            "Added attendance date: " . date('M d, Y', strtotime($attendance_date)) . " for " . $grading_period,
            $teacher_id
        );
        
        $conn->commit();
        return ['success' => true, 'message' => 'Attendance date added successfully'];
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("addAttendanceDate error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function addGradeColumn($class_id, $activity_type, $grading_period, $teacher_id) {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        if ($activity_type === 'assignment') {
            $activity_type = 'activity';
        }
        
        $typeLabels = [
            'quiz' => 'Quiz',
            'activity' => 'Activity',
            'assignment' => 'Activity',
            'project' => 'Project',
            'exam' => 'Exam',
            'recitation' => 'Recitation'
        ];
        
        $typeLabel = $typeLabels[$activity_type] ?? ucfirst($activity_type);
        
        $stmt = $conn->prepare("
            SELECT DISTINCT activity_name
            FROM grades
            WHERE class_id = :class_id 
            AND grading_period = :grading_period
            AND activity_name LIKE :pattern
            ORDER BY activity_name
        ");
        
        $pattern = $typeLabel . ' %';
        
        $stmt->execute([
            ':class_id' => $class_id,
            ':grading_period' => $grading_period,
            ':pattern' => $pattern
        ]);
        
        $existingActivities = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $nextNumber = 1;
        $existingNumbers = [];
        
        foreach ($existingActivities as $activityName) {
            if (preg_match('/' . preg_quote($typeLabel) . '\s+(\d+)/', $activityName, $matches)) {
                $existingNumbers[] = intval($matches[1]);
            }
        }
        
        if (!empty($existingNumbers)) {
            sort($existingNumbers);
            $nextNumber = max($existingNumbers) + 1;
        }
        
        $activity_name = $typeLabel . ' ' . $nextNumber;
        
        $checkStmt = $conn->prepare("
            SELECT COUNT(*) as exists_count,
                   GROUP_CONCAT(DISTINCT activity_type) as existing_types
            FROM grades
            WHERE class_id = :class_id 
            AND activity_name = :activity_name
            AND grading_period = :grading_period
        ");
        
        $checkStmt->execute([
            ':class_id' => $class_id,
            ':activity_name' => $activity_name,
            ':grading_period' => $grading_period
        ]);
        
        $existsResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existsResult['exists_count'] > 0) {
            $conn->rollBack();
            
            $errorMsg = "Activity '{$activity_name}' already exists";
            if ($existsResult['existing_types']) {
                $errorMsg .= " (type: {$existsResult['existing_types']})";
            }
            $errorMsg .= ". Please refresh the page to see current columns.";
            
            return [
                'success' => false, 
                'message' => $errorMsg,
                'existing_activity' => $activity_name
            ];
        }
        
        $students = getEnrolledStudents($class_id);
        
        if (empty($students)) {
            $conn->rollBack();
            return ['success' => false, 'message' => 'No students enrolled in this class'];
        }
        
        $stmt = $conn->prepare("
            INSERT INTO grades (student_id, class_id, activity_name, activity_type, score, max_score, grading_period, recorded_by)
            VALUES (:student_id, :class_id, :activity_name, :activity_type, 0, 0, :grading_period, :recorded_by)
        ");
        
        $insertedCount = 0;
        
        foreach ($students as $student) {
            $studentCheckStmt = $conn->prepare("
                SELECT grade_id FROM grades
                WHERE student_id = :student_id
                AND class_id = :class_id
                AND activity_name = :activity_name
                AND grading_period = :grading_period
            ");
            
            $studentCheckStmt->execute([
                ':student_id' => $student['user_id'],
                ':class_id' => $class_id,
                ':activity_name' => $activity_name,
                ':grading_period' => $grading_period
            ]);
            
            if ($studentCheckStmt->rowCount() === 0) {
                $success = $stmt->execute([
                    ':student_id' => $student['user_id'],
                    ':class_id' => $class_id,
                    ':activity_name' => $activity_name,
                    ':activity_type' => $activity_type,
                    ':grading_period' => $grading_period,
                    ':recorded_by' => $teacher_id
                ]);
                
                if ($success) {
                    $insertedCount++;
                }
            }
        }
        
        if ($insertedCount === 0) {
            $conn->rollBack();
            return [
                'success' => false, 
                'message' => "Failed to create '{$activity_name}'. All students already have this activity. Please refresh the page."
            ];
        }
        
        logAudit(
            'Added grade column',
            'CREATE',
            'grades',
            $class_id,
            "Added {$activity_name} (initial total: 0) for {$grading_period}",
            $teacher_id
        );
        
        $conn->commit();
        
        return [
            'success' => true, 
            'message' => "Grade column '{$activity_name}' added successfully", 
            'activity_name' => $activity_name,
            'students_added' => $insertedCount
        ];
        
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("addGradeColumn error: " . $e->getMessage());
        return [
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

function setColumnTotal($class_id, $activity_name, $max_score, $grading_period, $teacher_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_id = :class_id AND teacher_id = :teacher_id");
        $stmt->execute([':class_id' => $class_id, ':teacher_id' => $teacher_id]);
        
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $stmt = $conn->prepare("
            UPDATE grades 
            SET max_score = :max_score,
                updated_at = CURRENT_TIMESTAMP
            WHERE class_id = :class_id 
            AND activity_name = :activity_name
            AND grading_period = :grading_period
        ");
        
        $stmt->execute([
            ':max_score' => $max_score,
            ':class_id' => $class_id,
            ':activity_name' => $activity_name,
            ':grading_period' => $grading_period
        ]);
        
        if ($stmt->rowCount() > 0) {
            logAudit(
                'Set column total',
                'UPDATE',
                'grades',
                $class_id,
                "Set total score for {$activity_name}: {$max_score} ({$grading_period})",
                $teacher_id
            );
            
            return ['success' => true, 'message' => 'Column total set successfully'];
        } else {
            return ['success' => false, 'message' => 'No records updated'];
        }
    } catch (PDOException $e) {
        error_log("setColumnTotal error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to set column total'];
    }
}

function deleteAttendanceDate($class_id, $attendance_date, $grading_period, $teacher_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT class_id FROM classes 
            WHERE class_id = :class_id AND teacher_id = :teacher_id
        ");
        $stmt->execute([
            ':class_id' => $class_id,
            ':teacher_id' => $teacher_id
        ]);
        
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $stmt = $conn->prepare("
            DELETE FROM attendance
            WHERE class_id = :class_id 
            AND attendance_date = :attendance_date
            AND grading_period = :grading_period
        ");
        
        $stmt->execute([
            ':class_id' => $class_id,
            ':attendance_date' => $attendance_date,
            ':grading_period' => $grading_period
        ]);
        
        logAudit(
            'Deleted attendance column',
            'DELETE',
            'attendance',
            $class_id,
            "Deleted attendance for " . date('M d, Y', strtotime($attendance_date)) . " ({$grading_period})",
            $teacher_id
        );
        
        return ['success' => true, 'message' => 'Attendance column deleted successfully'];
    } catch (PDOException $e) {
        error_log("deleteAttendanceDate error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete attendance column'];
    }
}

function deleteGradeColumn($class_id, $activity_name, $teacher_id) {
    global $conn;
    
    $defaultColumns = ['Recitation', 'Midterm Exam', 'Final Exam'];
    if (in_array($activity_name, $defaultColumns)) {
        return ['success' => false, 'message' => 'Cannot delete default columns'];
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT class_id FROM classes 
            WHERE class_id = :class_id AND teacher_id = :teacher_id
        ");
        $stmt->execute([
            ':class_id' => $class_id,
            ':teacher_id' => $teacher_id
        ]);
        
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $stmt = $conn->prepare("
            DELETE FROM grades
            WHERE class_id = :class_id AND activity_name = :activity_name
        ");
        
        $stmt->execute([
            ':class_id' => $class_id,
            ':activity_name' => $activity_name
        ]);
        
        logAudit(
            'Deleted grade column',
            'DELETE',
            'grades',
            $class_id,
            "Deleted grade column: {$activity_name}",
            $teacher_id
        );
        
        return ['success' => true, 'message' => 'Grade column deleted successfully'];
    } catch (PDOException $e) {
        error_log("deleteGradeColumn error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete grade column'];
    }
}

function saveGrade($data) {
    global $conn;
    
    $required = ['student_id', 'class_id', 'activity_name', 'score', 'grading_period'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            return ['success' => false, 'message' => "Missing required field: $field"];
        }
    }
    
    try {
        $studentStmt = $conn->prepare("
            SELECT full_name, first_name, middle_name, last_name 
            FROM users 
            WHERE user_id = :student_id
        ");
        $studentStmt->execute([':student_id' => $data['student_id']]);
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $conn->prepare("
            UPDATE grades 
            SET score = :score,
                updated_at = CURRENT_TIMESTAMP
            WHERE student_id = :student_id 
            AND class_id = :class_id 
            AND activity_name = :activity_name
            AND grading_period = :grading_period
        ");
        
        $stmt->execute([
            ':score' => $data['score'],
            ':student_id' => $data['student_id'],
            ':class_id' => $data['class_id'],
            ':activity_name' => $data['activity_name'],
            ':grading_period' => $data['grading_period']
        ]);
        
        if ($stmt->rowCount() > 0) {
            $first_name = trim($student['first_name'] ?? '');
            $middle_name = trim($student['middle_name'] ?? '');
            $last_name = trim($student['last_name'] ?? '');
            
            if (empty($first_name) && empty($last_name) && !empty($student['full_name'])) {
                $full_name = trim($student['full_name']);
                $name_parts = explode(' ', $full_name);
                
                if (count($name_parts) == 1) {
                    $first_name = $name_parts[0];
                } elseif (count($name_parts) == 2) {
                    $first_name = $name_parts[0];
                    $last_name = $name_parts[1];
                } else {
                    $first_name = array_shift($name_parts);
                    $last_name = array_pop($name_parts);
                    $middle_name = implode(' ', $name_parts);
                }
            }
            
            $studentName = $first_name;
            if (!empty($middle_name)) {
                $studentName .= ' ' . $middle_name;
            }
            $studentName .= ' ' . $last_name;
            
            $auditMessage = "Updated {$data['activity_name']} for {$studentName}: {$data['score']} ({$data['grading_period']})";
            
            logAudit(
                'Updated grade',
                'UPDATE',
                'grades',
                $data['student_id'],
                $auditMessage,
                $_SESSION['user_id']
            );
            
            return ['success' => true, 'message' => 'Grade updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Grade record not found'];
        }
    } catch (PDOException $e) {
        error_log("saveGrade error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save grade'];
    }
}

function saveAttendance($data) {
    global $conn;
    
    $required = ['student_id', 'class_id', 'attendance_date', 'grading_period'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            return ['success' => false, 'message' => "Missing required field: $field"];
        }
    }
    
    if (!isset($data['status']) || $data['status'] === '') {
        $data['status'] = null;
    }
    
    $valid_statuses = ['present', 'absent', 'late', 'excused', null];
    if (!in_array($data['status'], $valid_statuses, true)) {
        return ['success' => false, 'message' => 'Invalid attendance status'];
    }
    
    try {
        $studentStmt = $conn->prepare("
            SELECT full_name, first_name, middle_name, last_name 
            FROM users 
            WHERE user_id = :student_id
        ");
        $studentStmt->execute([':student_id' => $data['student_id']]);
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
        
        $checkStmt = $conn->prepare("
            SELECT attendance_id FROM attendance
            WHERE student_id = :student_id 
            AND class_id = :class_id 
            AND attendance_date = :attendance_date
            AND grading_period = :grading_period
        ");
        
        $checkStmt->execute([
            ':student_id' => $data['student_id'],
            ':class_id' => $data['class_id'],
            ':attendance_date' => $data['attendance_date'],
            ':grading_period' => $data['grading_period']
        ]);
        
        if ($checkStmt->rowCount() > 0) {
            $stmt = $conn->prepare("
                UPDATE attendance 
                SET status = :status,
                    remarks = :remarks,
                    updated_at = CURRENT_TIMESTAMP
                WHERE student_id = :student_id 
                AND class_id = :class_id 
                AND attendance_date = :attendance_date
                AND grading_period = :grading_period
            ");
            
            $stmt->execute([
                ':status' => $data['status'],
                ':remarks' => $data['remarks'] ?? null,
                ':student_id' => $data['student_id'],
                ':class_id' => $data['class_id'],
                ':attendance_date' => $data['attendance_date'],
                ':grading_period' => $data['grading_period']
            ]);
            
            $first_name = trim($student['first_name'] ?? '');
            $middle_name = trim($student['middle_name'] ?? '');
            $last_name = trim($student['last_name'] ?? '');
            
            if (empty($first_name) && empty($last_name) && !empty($student['full_name'])) {
                $full_name = trim($student['full_name']);
                $name_parts = explode(' ', $full_name);
                
                if (count($name_parts) == 1) {
                    $first_name = $name_parts[0];
                } elseif (count($name_parts) == 2) {
                    $first_name = $name_parts[0];
                    $last_name = $name_parts[1];
                } else {
                    $first_name = array_shift($name_parts);
                    $last_name = array_pop($name_parts);
                    $middle_name = implode(' ', $name_parts);
                }
            }
            
            $studentName = $first_name;
            if (!empty($middle_name)) {
                $studentName .= ' ' . $middle_name;
            }
            $studentName .= ' ' . $last_name;
            
            $statusText = $data['status'] ? ucfirst($data['status']) : 'Not marked';
            logAudit(
                'Marked attendance',
                'UPDATE',
                'attendance',
                $data['student_id'],
                "Marked {$studentName} as {$statusText} on " . date('M d, Y', strtotime($data['attendance_date'])) . " ({$data['grading_period']})",
                $_SESSION['user_id']
            );
            
            return ['success' => true, 'message' => 'Attendance updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Attendance record not found'];
        }
    } catch (PDOException $e) {
        error_log("saveAttendance error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save attendance'];
    }
}

if (basename($_SERVER['PHP_SELF']) === 'view-grades-handler.php') {
    require_once '../../includes/config.php';
    require_once '../../includes/session.php';
    require_once '../../includes/functions.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $teacher_id = $_SESSION['user_id'];
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add_attendance_date':
            case 'add_attendance_column':
                $class_id = $_POST['class_id'] ?? null;
                $attendance_date = $_POST['attendance_date'] ?? null;
                $grading_period = $_POST['grading_period'] ?? 'midterm';
                
                if (!$class_id || !$attendance_date) {
                    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                    exit;
                }
                
                $verifyStmt = $conn->prepare("SELECT class_id FROM classes WHERE class_id = :class_id AND teacher_id = :teacher_id");
                $verifyStmt->execute([':class_id' => $class_id, ':teacher_id' => $teacher_id]);
                if (!$verifyStmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
                    exit;
                }
                
                $result = addAttendanceDate($class_id, $attendance_date, $grading_period, $teacher_id);
                echo json_encode($result);
                break;
                
            case 'add_grade_column':
                $class_id = $_POST['class_id'] ?? null;
                $activity_type = $_POST['activity_type'] ?? null;
                $grading_period = $_POST['grading_period'] ?? 'midterm';
                
                if (!$class_id || !$activity_type) {
                    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                    exit;
                }
                
                $result = addGradeColumn($class_id, $activity_type, $grading_period, $teacher_id);
                echo json_encode($result);
                break;
            
            case 'set_column_total':
                $class_id = $_POST['class_id'] ?? null;
                $activity_name = $_POST['activity_name'] ?? null;
                $max_score = $_POST['max_score'] ?? null;
                $grading_period = $_POST['grading_period'] ?? 'midterm';
                
                if (!$class_id || !$activity_name || !$max_score) {
                    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                    exit;
                }
                
                $result = setColumnTotal($class_id, $activity_name, $max_score, $grading_period, $teacher_id);
                echo json_encode($result);
                break;
            
            case 'delete_attendance_date':
            case 'delete_attendance_column':
                $grading_period = $_POST['grading_period'] ?? 'midterm';
                $result = deleteAttendanceDate($_POST['class_id'], $_POST['attendance_date'], $grading_period, $teacher_id);
                echo json_encode($result);
                break;
                
            case 'delete_grade_column':
                $result = deleteGradeColumn($_POST['class_id'], $_POST['activity_name'], $teacher_id);
                echo json_encode($result);
                break;
                
            case 'save_grade':
                $data = [
                    'student_id' => $_POST['student_id'] ?? null,
                    'class_id' => $_POST['class_id'] ?? null,
                    'activity_name' => $_POST['activity_name'] ?? null,
                    'score' => $_POST['score'] ?? null,
                    'grading_period' => $_POST['grading_period'] ?? 'midterm'
                ];
                
                $result = saveGrade($data);
                echo json_encode($result);
                break;
            
            case 'save_attendance':
                $data = [
                    'student_id' => $_POST['student_id'] ?? null,
                    'class_id' => $_POST['class_id'] ?? null,
                    'attendance_date' => $_POST['attendance_date'] ?? null,
                    'status' => $_POST['status'] ?? null,
                    'remarks' => $_POST['remarks'] ?? null,
                    'grading_period' => $_POST['grading_period'] ?? 'midterm'
                ];
                
                $result = saveAttendance($data);
                echo json_encode($result);
                break;
                
            case 'initialize_student':
                $student_id = $_POST['student_id'] ?? null;
                $class_id = $_POST['class_id'] ?? null;
                $grading_period = $_POST['grading_period'] ?? 'midterm';
                
                if (!$student_id || !$class_id) {
                    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                    exit;
                }
                
                $success = initializeDefaultGrades($student_id, $class_id, $grading_period, $teacher_id);
                echo json_encode(['success' => $success]);
                break;
            
            case 'get_data':
                $class_id = $_POST['class_id'] ?? null;
                $grading_period = $_POST['grading_period'] ?? 'midterm';
                $initialize_defaults = $_POST['initialize_defaults'] ?? 'no'; 
    
                if (!$class_id) {
                    echo json_encode(['success' => false, 'message' => 'Missing class ID']);
                    exit;
                }
                
                $students = getEnrolledStudents($class_id);
                
                if ($initialize_defaults === 'yes') {
                    foreach ($students as $student) {
                        initializeDefaultGrades($student['user_id'], $class_id, $grading_period, $teacher_id);
                    }
                }
    
                $grades = getClassGrades($class_id, $grading_period);
                $attendance = getClassAttendance($class_id, $grading_period);
                $attendanceDates = getAttendanceDates($class_id, $grading_period);
                $activityColumns = getActivityColumns($class_id, $grading_period);
                
                echo json_encode([
                    'success' => true,
                    'students' => $students,
                    'grades' => $grades,
                    'attendance' => $attendance,
                    'attendanceDates' => $attendanceDates,
                    'activityColumns' => $activityColumns,
                    'grading_period' => $grading_period
                ]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
        exit;
    }
}
?>