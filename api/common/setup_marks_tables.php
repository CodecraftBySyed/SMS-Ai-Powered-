<?php
require_once 'db.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS subjects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(20) NOT NULL,
            dept_id INT NOT NULL,
            year INT NOT NULL,
            FOREIGN KEY (dept_id) REFERENCES departments(id)
        );

        CREATE TABLE IF NOT EXISTS marks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            subject_id INT NOT NULL,
            marks_obtained INT NOT NULL,
            total_marks INT NOT NULL DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id),
            FOREIGN KEY (subject_id) REFERENCES subjects(id),
            UNIQUE KEY unique_mark (student_id, subject_id)
        );
        
        -- Insert some dummy subjects if empty
        -- For all departments, add Year 1 subjects
        INSERT INTO subjects (name, code, dept_id, year) 
        SELECT 'Mathematics I', 'MAT101', id, 1 FROM departments 
        WHERE NOT EXISTS (SELECT 1 FROM subjects WHERE code = 'MAT101' AND dept_id = departments.id);

        INSERT INTO subjects (name, code, dept_id, year) 
        SELECT 'Physics', 'PHY101', id, 1 FROM departments 
        WHERE NOT EXISTS (SELECT 1 FROM subjects WHERE code = 'PHY101' AND dept_id = departments.id);

        -- Add specific subjects
        INSERT INTO subjects (name, code, dept_id, year) 
        SELECT 'Data Structures', 'CS201', 1, 2 
        WHERE 1 IN (SELECT id FROM departments WHERE id=1)
        AND NOT EXISTS (SELECT 1 FROM subjects WHERE code = 'CS201');

        INSERT INTO subjects (name, code, dept_id, year) 
        SELECT 'Circuits', 'EE201', 2, 2 
        WHERE 2 IN (SELECT id FROM departments WHERE id=2)
        AND NOT EXISTS (SELECT 1 FROM subjects WHERE code = 'EE201');
    ");
    echo "Tables created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>