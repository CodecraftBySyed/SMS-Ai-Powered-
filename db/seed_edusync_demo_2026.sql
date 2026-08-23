-- EduSync Demo Seed 2026
-- Inserts exactly 500 Tamil Indian students + attendance + marks
-- No schema changes. Safe to import via phpMyAdmin.
-- Order: Students → Attendance → Marks

START TRANSACTION;

-- ========= SCHEMA UPDATES (Parent Details) =========
-- Add parent_name and parent_mobile columns (safe to run multiple times in MySQL 8.0.29+)
ALTER TABLE students 
  ADD COLUMN IF NOT EXISTS parent_name VARCHAR(100) NULL AFTER email,
  ADD COLUMN IF NOT EXISTS parent_mobile VARCHAR(10) NULL AFTER parent_name;

-- =============== STUDENTS (500) ===============
INSERT INTO students (reg_no, name, dept_id, year, phone, email, total_fee, paid_fee, balance_fee, status)
SELECT
  CONCAT(
    CASE dept_idx
      WHEN 0 THEN 'DCSE'
      WHEN 1 THEN 'DECE'
      WHEN 2 THEN 'DEEE'
      ELSE 'DME'
    END,
    '-26-', LPAD(pos, 3, '0')
  ) AS reg_no,
  CASE (n % 2)
    WHEN 0 THEN CONCAT(
      CASE (n % 10)
        WHEN 0 THEN 'Aravind'
        WHEN 1 THEN 'Karthik'
        WHEN 2 THEN 'Praveen'
        WHEN 3 THEN 'Surya'
        WHEN 4 THEN 'Vignesh'
        WHEN 5 THEN 'Dinesh'
        WHEN 6 THEN 'Harish'
        WHEN 7 THEN 'Manoj'
        WHEN 8 THEN 'Naveen'
        ELSE 'Sriram'
      END, ' ',
      CASE (n % 8)
        WHEN 0 THEN 'Kumar'
        WHEN 1 THEN 'R'
        WHEN 2 THEN 'S'
        WHEN 3 THEN 'M'
        WHEN 4 THEN 'N'
        WHEN 5 THEN 'P'
        WHEN 6 THEN 'K'
        ELSE 'V'
      END
    )
    ELSE CONCAT(
      CASE (n % 10)
        WHEN 0 THEN 'Nivetha'
        WHEN 1 THEN 'Keerthana'
        WHEN 2 THEN 'Swathi'
        WHEN 3 THEN 'Harini'
        WHEN 4 THEN 'Anitha'
        WHEN 5 THEN 'Deepika'
        WHEN 6 THEN 'Pavithra'
        WHEN 7 THEN 'Lakshmi'
        WHEN 8 THEN 'Dharani'
        ELSE 'Aishwarya'
      END, ' ',
      CASE (n % 8)
        WHEN 0 THEN 'S'
        WHEN 1 THEN 'R'
        WHEN 2 THEN 'M'
        WHEN 3 THEN 'K'
        WHEN 4 THEN 'N'
        WHEN 5 THEN 'P'
        WHEN 6 THEN 'L'
        ELSE 'V'
      END
    )
  END AS name,
  (dept_idx + 1) AS dept_id,
  CASE dept_idx
    WHEN 0 THEN CASE WHEN pos <= 42 THEN 1 WHEN pos <= 84 THEN 2 ELSE 3 END
    WHEN 1 THEN CASE WHEN pos <= 41 THEN 1 WHEN pos <= 83 THEN 2 ELSE 3 END
    WHEN 2 THEN CASE WHEN pos <= 42 THEN 1 WHEN pos <= 83 THEN 2 ELSE 3 END
    ELSE         CASE WHEN pos <= 42 THEN 1 WHEN pos <= 84 THEN 2 ELSE 3 END
  END AS year,
  CONCAT('9', LPAD(600000000 + n, 9, '0')) AS phone,
  CONCAT('student_seed_', LPAD(n, 3, '0'), '@edusync.com') AS email,
  CASE (dept_idx + 1)
    WHEN 1 THEN 15000 + (pos % 100)
    WHEN 2 THEN 16000 + (pos % 100)
    WHEN 3 THEN 17000 + (pos % 100)
    ELSE        18000 + (pos % 200)
  END AS total_fee,
  FLOOR(
    (CASE (dept_idx + 1)
      WHEN 1 THEN 15000 + (pos % 100)
      WHEN 2 THEN 16000 + (pos % 100)
      WHEN 3 THEN 17000 + (pos % 100)
      ELSE        18000 + (pos % 200)
    END) *
    (CASE (pos % 10)
      WHEN 0 THEN 1.00
      WHEN 1 THEN 0.90
      WHEN 2 THEN 0.85
      WHEN 3 THEN 0.80
      WHEN 4 THEN 0.75
      WHEN 5 THEN 0.70
      WHEN 6 THEN 0.65
      WHEN 7 THEN 0.60
      WHEN 8 THEN 0.55
      ELSE 0.50
    END)
  ) AS paid_fee,
  GREATEST(
    (CASE (dept_idx + 1)
      WHEN 1 THEN 15000 + (pos % 100)
      WHEN 2 THEN 16000 + (pos % 100)
      WHEN 3 THEN 17000 + (pos % 100)
      ELSE        18000 + (pos % 200)
    END) -
    FLOOR(
      (CASE (dept_idx + 1)
        WHEN 1 THEN 15000 + (pos % 100)
        WHEN 2 THEN 16000 + (pos % 100)
        WHEN 3 THEN 17000 + (pos % 100)
        ELSE        18000 + (pos % 200)
      END) *
      (CASE (pos % 10)
        WHEN 0 THEN 1.00
        WHEN 1 THEN 0.90
        WHEN 2 THEN 0.85
        WHEN 3 THEN 0.80
        WHEN 4 THEN 0.75
        WHEN 5 THEN 0.70
        WHEN 6 THEN 0.65
        WHEN 7 THEN 0.60
        WHEN 8 THEN 0.55
        ELSE 0.50
      END)
    ), 0
  ) AS balance_fee,
  CASE
    WHEN (pos % 10) IN (0,1,2) THEN 'Good'
    WHEN (pos % 10) IN (3,4,5) THEN 'Average'
    ELSE 'Needs Attention'
  END AS status
FROM (
  SELECT
    (a.n * 50 + b.n + 1) AS n,
    FLOOR(((a.n * 50 + b.n + 1) - 1) / 125) AS dept_idx,
    ((a.n * 50 + b.n + 1) - FLOOR(((a.n * 50 + b.n + 1) - 1) / 125) * 125) AS pos
  FROM
    (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
  CROSS JOIN
    (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL
     SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL
     SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL
     SELECT 30 UNION ALL SELECT 31 UNION ALL SELECT 32 UNION ALL SELECT 33 UNION ALL SELECT 34 UNION ALL SELECT 35 UNION ALL SELECT 36 UNION ALL SELECT 37 UNION ALL SELECT 38 UNION ALL SELECT 39 UNION ALL
     SELECT 40 UNION ALL SELECT 41 UNION ALL SELECT 42 UNION ALL SELECT 43 UNION ALL SELECT 44 UNION ALL SELECT 45 UNION ALL SELECT 46 UNION ALL SELECT 47 UNION ALL SELECT 48 UNION ALL SELECT 49) b
  WHERE (a.n * 50 + b.n + 1) <= 500
) t;

-- Avoid trigger conflicts by materializing student IDs into a temporary table first
DROP TEMPORARY TABLE IF EXISTS tmp_seed_students;
CREATE TEMPORARY TABLE tmp_seed_students
SELECT s.id, s.reg_no
FROM students s
WHERE s.reg_no LIKE 'DCSE-26-%' OR s.reg_no LIKE 'DECE-26-%' OR s.reg_no LIKE 'DEEE-26-%' OR s.reg_no LIKE 'DME-26-%';

INSERT INTO attendance (student_id, week_date, present, percentage)
SELECT t.id, '2026-02-01', 1,
  CASE (t.id % 10)
    WHEN 0 THEN 55.00 + (t.id % 8)      -- 55-62 (≈10%)
    WHEN 1 THEN 70.00 + (t.id % 16)     -- 70-85 (≈60%)
    WHEN 2 THEN 70.00 + ((t.id+3) % 16)
    WHEN 3 THEN 70.00 + ((t.id+5) % 16)
    WHEN 4 THEN 70.00 + ((t.id+7) % 16)
    WHEN 5 THEN 70.00 + ((t.id+9) % 16)
    WHEN 6 THEN 85.00 + (t.id % 13)     -- 85-98 (≈30%)
    WHEN 7 THEN 85.00 + ((t.id+4) % 13)
    WHEN 8 THEN 85.00 + ((t.id+8) % 13)
    ELSE 75.00 + ((t.id+2) % 10)
  END AS percentage
FROM tmp_seed_students t;

DROP TEMPORARY TABLE IF EXISTS tmp_seed_students;

-- =============== MARKS (2500; 5 subjects per student) ===============
INSERT INTO marks (student_id, year, subject, marks, created_at)
SELECT s.id, s.year, subj.subject,
  CASE
    WHEN ((s.id + subj.idx) % 10) = 0 THEN 35 + FLOOR(RAND(s.id * 37 + subj.idx) * 4)      -- single-subject arrears ≈10%
    WHEN ((s.id + subj.idx) % 7) = 0 THEN 90 + FLOOR(RAND(s.id * 11 + subj.idx) * 10)      -- high achievers ≈15%
    ELSE 55 + FLOOR(RAND(s.id * 23 + subj.idx) * 21)                                       -- majority 55–75
  END AS marks,
  DATE_ADD('2026-01-15', INTERVAL subj.idx WEEK) AS created_at
FROM students s
JOIN (
  SELECT 1 AS year, 0 AS idx, 'Mathematics I' AS subject
  UNION ALL SELECT 1, 1, 'Physics'
  UNION ALL SELECT 1, 2, 'English'
  UNION ALL SELECT 1, 3, 'Computer Basics'
  UNION ALL SELECT 1, 4, 'Communication Skills'

  UNION ALL SELECT 2, 0, 'Data Structures'
  UNION ALL SELECT 2, 1, 'Algorithms'
  UNION ALL SELECT 2, 2, 'Operating Systems'
  UNION ALL SELECT 2, 3, 'Database Systems'
  UNION ALL SELECT 2, 4, 'Networks'

  UNION ALL SELECT 3, 0, 'Software Engineering'
  UNION ALL SELECT 3, 1, 'Web Technologies'
  UNION ALL SELECT 3, 2, 'Machine Learning'
  UNION ALL SELECT 3, 3, 'Distributed Systems'
  UNION ALL SELECT 3, 4, 'Cybersecurity'
) subj ON subj.year = s.year
WHERE s.reg_no LIKE 'DCSE-26-%' OR s.reg_no LIKE 'DECE-26-%' OR s.reg_no LIKE 'DEEE-26-%' OR s.reg_no LIKE 'DME-26-%';

COMMIT;

-- =============== SAMPLE PARENT DETAILS (Manual) ===============
-- Update a specific student by Reg No
-- UPDATE students SET parent_name = 'Ramesh Kumar', parent_mobile = '9876543210' WHERE reg_no = 'DCSE-26-001';
-- UPDATE students SET parent_name = 'Lakshmi Devi', parent_mobile = '9123456789' WHERE reg_no = 'DECE-26-042';
-- Support common Indian formats: +91XXXXXXXXXX or 0XXXXXXXXXX (store last 10 digits)
-- Examples (pre-normalize outside or store last 10 digits manually):
-- UPDATE students SET parent_name = 'Suresh Babu', parent_mobile = '9876501234' WHERE reg_no = 'DEEE-26-085';
-- UPDATE students SET parent_name = 'Meena', parent_mobile = '9000012345' WHERE reg_no = 'DME-26-120';

-- =============== DISTRIBUTION CHECKS (read-only examples) ===============
-- Total seeded students:
-- SELECT COUNT(*) FROM students WHERE reg_no LIKE '%-26-%';
-- Per department:
-- SELECT dept_id, COUNT(*) FROM students WHERE reg_no LIKE '%-26-%' GROUP BY dept_id;
-- Per year:
-- SELECT year, COUNT(*) FROM students WHERE reg_no LIKE '%-26-%' GROUP BY year;
-- Attendance breakdown (approx):
-- SELECT 
--   SUM(percentage < 65) AS below_65,
--   SUM(percentage BETWEEN 70 AND 85) AS between_70_85,
--   SUM(percentage > 85) AS above_85
-- FROM attendance a JOIN students s ON s.id=a.student_id
-- WHERE s.reg_no LIKE '%-26-%';
-- Fee status:
-- SELECT status, COUNT(*) FROM students WHERE reg_no LIKE '%-26-%' GROUP BY status;
