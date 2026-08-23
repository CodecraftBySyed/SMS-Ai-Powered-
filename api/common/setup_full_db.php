<?php
require_once 'constants.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dbname = DB_NAME;
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");

    // 1. Departments
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(10) NOT NULL
    )");
    
    // Seed Departments if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM departments");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO departments (name) VALUES ('DCSE'), ('DECE'), ('DEEE'), ('DME')");
    }

    // 2. Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher') NOT NULL,
        dept_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (dept_id) REFERENCES departments(id)
    )");

    // 3. Students
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reg_no VARCHAR(20) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        dept_id INT NOT NULL,
        year INT NOT NULL COMMENT '1/2/3/4',
        phone VARCHAR(20),
        email VARCHAR(100),
        parent_name VARCHAR(100) NULL,
        parent_mobile VARCHAR(10) NULL,
        total_fee DECIMAL(10, 2) NOT NULL DEFAULT 50000.00,
        paid_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        balance_fee DECIMAL(10, 2) NOT NULL DEFAULT 50000.00,
        status VARCHAR(50) DEFAULT 'Average',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (dept_id) REFERENCES departments(id),
        INDEX (reg_no)
    )");
    
    // Ensure parent fields exist for older databases
    try {
        $colCheck = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$dbname}' AND TABLE_NAME = 'students'")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('parent_name', $colCheck, true)) {
            $pdo->exec("ALTER TABLE students ADD COLUMN parent_name VARCHAR(100) NULL AFTER email");
        }
        if (!in_array('parent_mobile', $colCheck, true)) {
            $pdo->exec("ALTER TABLE students ADD COLUMN parent_mobile VARCHAR(10) NULL AFTER parent_name");
        }
    } catch (PDOException $e) {
        // Non-fatal: skip if permissions or version issues
    }

    // 4. Attendance
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        week_date DATE NOT NULL,
        present TINYINT(1) NOT NULL DEFAULT 0,
        percentage DECIMAL(5, 2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id),
        UNIQUE KEY unique_attendance (student_id, week_date)
    )");

    // 5. Subjects
    $pdo->exec("CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        code VARCHAR(20) NOT NULL,
        dept_id INT NOT NULL,
        year INT NOT NULL,
        FOREIGN KEY (dept_id) REFERENCES departments(id)
    )");

    // 6. Marks
    $pdo->exec("CREATE TABLE IF NOT EXISTS marks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        subject_id INT NOT NULL,
        marks_obtained INT NOT NULL,
        total_marks INT NOT NULL DEFAULT 100,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (subject_id) REFERENCES subjects(id),
        UNIQUE KEY unique_mark (student_id, subject_id)
    )");

    $deptStmt = $pdo->query("SELECT id, name FROM departments");
    $departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);
    $deptMap = [];
    foreach ($departments as $d) {
        $deptMap[$d['name']] = (int)$d['id'];
    }
    foreach ($departments as $d) {
        $did = (int)$d['id'];
        $check = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE dept_id = ? AND year = 1");
        $check->execute([$did]);
        if ((int)$check->fetchColumn() === 0) {
            $ins = $pdo->prepare("INSERT INTO subjects (name, code, dept_id, year) VALUES (?, ?, ?, ?)");
            $ins->execute(['English Communication', 'COM101', $did, 1]);
            $ins->execute(['Engineering Mathematics I', 'COM102', $did, 1]);
            $ins->execute(['Physics Fundamentals', 'COM103', $did, 1]);
        }
    }
    if (isset($deptMap['DECE'])) {
        $dece = $deptMap['DECE'];
        $pairs2 = [
            ['Circuit Theory', 'ECE201'],
            ['Electronic Devices', 'ECE202'],
            ['Signals and Systems', 'ECE203'],
            ['Network Analysis', 'ECE204'],
            ['Digital Electronics', 'ECE205'],
        ];
        foreach ($pairs2 as $p) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE code = ? AND dept_id = ? AND year = 2");
            $c->execute([$p[1], $dece]);
            if ((int)$c->fetchColumn() === 0) {
                $i = $pdo->prepare("INSERT INTO subjects (name, code, dept_id, year) VALUES (?, ?, ?, 2)");
                $i->execute([$p[0], $p[1], $dece]);
            }
        }
        $pairs3 = [
            ['Microprocessors', 'ECE301'],
            ['Communication Systems', 'ECE302'],
            ['Control Systems', 'ECE303'],
            ['VLSI Design', 'ECE304'],
            ['Embedded Systems', 'ECE305'],
        ];
        foreach ($pairs3 as $p) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE code = ? AND dept_id = ? AND year = 3");
            $c->execute([$p[1], $dece]);
            if ((int)$c->fetchColumn() === 0) {
                $i = $pdo->prepare("INSERT INTO subjects (name, code, dept_id, year) VALUES (?, ?, ?, 3)");
                $i->execute([$p[0], $p[1], $dece]);
            }
        }
        $students = [
            ['REG2025DECE001', 'N Syed Asrar', 2],
            ['REG2025DECE002', 'N Syed Maaz', 2],
            ['REG2025DECE003', 'M Ragul', 3],
            ['REG2025DECE004', 'K Serush', 3],
        ];
        foreach ($students as $s) {
            $exists = $pdo->prepare("SELECT COUNT(*) FROM students WHERE reg_no = ?");
            $exists->execute([$s[0]]);
            if ((int)$exists->fetchColumn() === 0) {
                $ins = $pdo->prepare("INSERT INTO students (reg_no, name, dept_id, year, status) VALUES (?, ?, ?, ?, 'Average')");
                $ins->execute([$s[0], $s[1], $dece, $s[2]]);
            }
        }
    }
    if (isset($deptMap['DCSE'])) {
        $dcse = $deptMap['DCSE'];
        $cs_year2 = [
            ['Data Structures', 'CS201'],
            ['Algorithms', 'CS202'],
            ['Database Systems', 'CS203'],
            ['Operating Systems', 'CS204'],
            ['Computer Networks', 'CS205'],
        ];
        foreach ($cs_year2 as $p) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE code = ? AND dept_id = ? AND year = 2");
            $c->execute([$p[1], $dcse]);
            if ((int)$c->fetchColumn() === 0) {
                $i = $pdo->prepare("INSERT INTO subjects (name, code, dept_id, year) VALUES (?, ?, ?, 2)");
                $i->execute([$p[0], $p[1], $dcse]);
            }
        }
        $cs_year3 = [
            ['Software Engineering', 'CS301'],
            ['Web Technologies', 'CS302'],
            ['Machine Learning', 'CS303'],
            ['Distributed Systems', 'CS304'],
            ['Cybersecurity', 'CS305'],
        ];
        foreach ($cs_year3 as $p) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE code = ? AND dept_id = ? AND year = 3");
            $c->execute([$p[1], $dcse]);
            if ((int)$c->fetchColumn() === 0) {
                $i = $pdo->prepare("INSERT INTO subjects (name, code, dept_id, year) VALUES (?, ?, ?, 3)");
                $i->execute([$p[0], $p[1], $dcse]);
            }
        }
    }
    if (isset($deptMap['DEEE'])) {
        $deee = $deptMap['DEEE'];
        $eee_year2 = [
            ['Circuit Theory', 'EE201'],
            ['Electrical Machines', 'EE202'],
            ['Power Systems I', 'EE203'],
            ['Control Systems', 'EE204'],
            ['Measurements and Instrumentation', 'EE205'],
        ];
        foreach ($eee_year2 as $p) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE code = ? AND dept_id = ? AND year = 2");
            $c->execute([$p[1], $deee]);
            if ((int)$c->fetchColumn() === 0) {
                $i = $pdo->prepare("INSERT INTO subjects (name, code, dept_id, year) VALUES (?, ?, ?, 2)");
                $i->execute([$p[0], $p[1], $deee]);
            }
        }
        $eee_year3 = [
            ['Power Electronics', 'EE301'],
            ['High Voltage Engineering', 'EE302'],
            ['Renewable Energy Systems', 'EE303'],
            ['Power System Protection', 'EE304'],
            ['Electrical Drives', 'EE305'],
        ];
        foreach ($eee_year3 as $p) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE code = ? AND dept_id = ? AND year = 3");
            $c->execute([$p[1], $deee]);
            if ((int)$c->fetchColumn() === 0) {
                $i = $pdo->prepare("INSERT INTO subjects (name, code, dept_id, year) VALUES (?, ?, ?, 3)");
                $i->execute([$p[0], $p[1], $deee]);
            }
        }
    }
    if (isset($deptMap['DME'])) {
        $dme = $deptMap['DME'];
        $me_year2 = [
            ['Engineering Mechanics', 'ME201'],
            ['Thermodynamics', 'ME202'],
            ['Strength of Materials', 'ME203'],
            ['Manufacturing Processes', 'ME204'],
            ['Fluid Mechanics', 'ME205'],
        ];
        foreach ($me_year2 as $p) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE code = ? AND dept_id = ? AND year = 2");
            $c->execute([$p[1], $dme]);
            if ((int)$c->fetchColumn() === 0) {
                $i = $pdo->prepare("INSERT INTO subjects (name, code, dept_id, year) VALUES (?, ?, ?, 2)");
                $i->execute([$p[0], $p[1], $dme]);
            }
        }
        $me_year3 = [
            ['Heat Transfer', 'ME301'],
            ['Machine Design', 'ME302'],
            ['CAD/CAM', 'ME303'],
            ['Dynamics of Machinery', 'ME304'],
            ['Industrial Engineering', 'ME305'],
        ];
        foreach ($me_year3 as $p) {
            $c = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE code = ? AND dept_id = ? AND year = 3");
            $c->execute([$p[1], $dme]);
            if ((int)$c->fetchColumn() === 0) {
                $i = $pdo->prepare("INSERT INTO subjects (name, code, dept_id, year) VALUES (?, ?, ?, 3)");
                $i->execute([$p[0], $p[1], $dme]);
            }
        }
    }

    // 7. Subject Catalog (regulation-based, common + department-specific)
    $pdo->exec("CREATE TABLE IF NOT EXISTS subject_catalog (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_name VARCHAR(100) NOT NULL,
        department INT NULL,
        year INT NOT NULL,
        is_common TINYINT(1) NOT NULL DEFAULT 0,
        regulation_year INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department) REFERENCES departments(id)
    )");

    // Seed common first-year subjects once
    $checkCommon = $pdo->query("SELECT COUNT(*) FROM subject_catalog WHERE is_common = 1 AND year = 1");
    if ((int)$checkCommon->fetchColumn() === 0) {
        $ins = $pdo->prepare("INSERT INTO subject_catalog (subject_name, department, year, is_common, regulation_year) VALUES (?, NULL, 1, 1, ?)");
        $regYear = (int)date('Y');
        $ins->execute(['Mathematics', NULL, 1, 1, $regYear]);
        $ins->execute(['Physics', NULL, 1, 1, $regYear]);
        $ins->execute(['Chemistry', NULL, 1, 1, $regYear]);
        $ins->execute(['English', NULL, 1, 1, $regYear]);
        $ins->execute(['Tamil', NULL, 1, 1, $regYear]);
    }

    // Seed department-specific first-year core subjects
    $coreMap = [
        'DCSE' => 'Basics of Computer',
        'DECE' => 'Basics of Electronics',
        'DEEE' => 'Electrical Fundamentals',
        'DME'  => 'Engineering Mechanics'
    ];
    foreach ($coreMap as $deptCode => $coreName) {
        if (isset($deptMap[$deptCode])) {
            $did = (int)$deptMap[$deptCode];
            $check = $pdo->prepare("SELECT COUNT(*) FROM subject_catalog WHERE department = ? AND year = 1 AND subject_name = ?");
            $check->execute([$did, $coreName]);
            if ((int)$check->fetchColumn() === 0) {
                $ins = $pdo->prepare("INSERT INTO subject_catalog (subject_name, department, year, is_common, regulation_year) VALUES (?, ?, 1, 0, ?)");
                $ins->execute([$coreName, $did, (int)date('Y')]);
            }
        }
    }

    // 8. Student Marks (normalized to subject_catalog)
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_marks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        subject_id INT NOT NULL,
        marks INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (subject_id) REFERENCES subject_catalog(id),
        UNIQUE KEY uniq_student_subject (student_id, subject_id)
    )");

    echo "Database setup completed successfully.";

} catch (PDOException $e) {
    die("DB Setup Error: " . $e->getMessage());
}
?>
