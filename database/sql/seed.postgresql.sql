INSERT INTO users (id, student_number, email, password_hash, role, first_name, last_name, is_active) VALUES
  (1, NULL, 'registrar@bcc.edu.ph', '$2b$10$tySQhYFvm9eh5UV84Dl4mOn2IcBFfujJwejeNlVLx.z52jC1EvZm2', 'registrar', 'Elena', 'Santos', TRUE),
  (2, '2026-0001', 'student@bcc.edu.ph', '$2b$10$xsvkuhX5w3O0S.Ow0R.jkeTq1SeMpwtuNYc8f9cAfsk3AKRxHFE/G', 'student', 'Mika', 'Reyes', TRUE)
ON CONFLICT (email) DO UPDATE SET
  student_number = EXCLUDED.student_number,
  password_hash = EXCLUDED.password_hash,
  role = EXCLUDED.role,
  first_name = EXCLUDED.first_name,
  last_name = EXCLUDED.last_name,
  is_active = TRUE;

INSERT INTO programs (id, source_id, code, name, department, duration_years, is_active) VALUES
  (1, 4, 'BSIT', 'Bachelor of Science in Information Technology', 'Buenavista Community College', 4, TRUE),
  (2, 5, 'BSHM', 'Bachelor of Science in Hospitality Management', 'Buenavista Community College', 4, TRUE),
  (3, 1, 'BEED', 'Bachelor of Elementary Education', 'Buenavista Community College', 4, TRUE),
  (6, 2, 'BSED', 'Bachelor of Secondary Education', 'Buenavista Community College', 4, TRUE),
  (7, 3, 'BSCRIM', 'Bachelor of Science in Criminology', 'Buenavista Community College', 4, TRUE),
  (12, 9, 'BSTM', 'Bachelor of Science in Tourism Management', 'Buenavista Community College', 4, TRUE)
ON CONFLICT (id) DO UPDATE SET
  source_id = EXCLUDED.source_id,
  code = EXCLUDED.code,
  name = EXCLUDED.name,
  department = EXCLUDED.department,
  duration_years = EXCLUDED.duration_years,
  is_active = TRUE;

INSERT INTO subjects (id, program_id, code, title, units, year_level, semester, is_active) VALUES
  (1, 1, 'IT101', 'Introduction to Computing', 3.0, 1, '1st', FALSE),
  (2, 1, 'IT102', 'Computer Programming 1', 3.0, 1, '1st', FALSE),
  (3, 1, 'GEC101', 'Understanding the Self', 3.0, 1, '1st', FALSE),
  (4, 2, 'HM101', 'Introduction to Hospitality', 3.0, 1, '1st', FALSE),
  (5, 2, 'HM102', 'Kitchen Essentials', 3.0, 1, '1st', FALSE),
  (6, 3, 'ED101', 'The Child and Adolescent Learners', 3.0, 1, '1st', FALSE),
  (7, 3, 'GEC102', 'Mathematics in the Modern World', 3.0, 1, '1st', FALSE)
ON CONFLICT (id) DO UPDATE SET
  program_id = EXCLUDED.program_id,
  code = EXCLUDED.code,
  title = EXCLUDED.title,
  units = EXCLUDED.units,
  year_level = EXCLUDED.year_level,
  semester = EXCLUDED.semester,
  is_active = FALSE;

INSERT INTO enrollments (id, student_id, program_id, school_year, semester, year_level, student_type, status)
VALUES (1, 2, 1, '2026-2027', '1st', 1, 'Continuing', 'approved')
ON CONFLICT (id) DO UPDATE SET status = EXCLUDED.status;

INSERT INTO enrollment_subjects (enrollment_id, subject_id) VALUES
  (1, 1), (1, 2), (1, 3)
ON CONFLICT DO NOTHING;

INSERT INTO class_schedules
  (id, program_id, subject_id, school_year, semester, year_level, section, instructor_name, days, start_time, end_time, room, capacity, is_active)
VALUES
  (1, 1, 1, '2026-2027', '1st', 1, 'A', 'Engr. Paolo D. Cruz', 'Mon / Wed', '08:00:00', '09:30:00', 'Computer Lab 1', 40, TRUE),
  (2, 1, 2, '2026-2027', '1st', 1, 'A', 'Ms. Rina M. Flores', 'Tue / Thu', '10:00:00', '11:30:00', 'Computer Lab 2', 40, TRUE),
  (3, 1, 3, '2026-2027', '1st', 1, 'A', 'Mr. Noel B. Garcia', 'Fri', '13:00:00', '16:00:00', 'Room 204', 45, TRUE)
ON CONFLICT (id) DO UPDATE SET
  instructor_name = EXCLUDED.instructor_name,
  room = EXCLUDED.room,
  capacity = EXCLUDED.capacity,
  is_active = TRUE;

INSERT INTO grades (enrollment_id, subject_id, preliminary, midterm, final_term, final_grade, remarks, encoded_by) VALUES
  (1, 1, 1.75, 1.75, NULL, NULL, 'In progress', 1),
  (1, 2, 2.00, 1.75, NULL, NULL, 'In progress', 1),
  (1, 3, 1.50, 1.75, NULL, NULL, 'In progress', 1)
ON CONFLICT (enrollment_id, subject_id) DO UPDATE SET
  preliminary = EXCLUDED.preliminary,
  midterm = EXCLUDED.midterm,
  final_term = EXCLUDED.final_term,
  final_grade = EXCLUDED.final_grade,
  remarks = EXCLUDED.remarks,
  encoded_by = EXCLUDED.encoded_by;

SELECT setval(pg_get_serial_sequence('users', 'id'), GREATEST(COALESCE((SELECT MAX(id) FROM users), 1), 1), TRUE);
SELECT setval(pg_get_serial_sequence('programs', 'id'), GREATEST(COALESCE((SELECT MAX(id) FROM programs), 1), 1), TRUE);
SELECT setval(pg_get_serial_sequence('subjects', 'id'), GREATEST(COALESCE((SELECT MAX(id) FROM subjects), 1), 1), TRUE);
SELECT setval(pg_get_serial_sequence('enrollments', 'id'), GREATEST(COALESCE((SELECT MAX(id) FROM enrollments), 1), 1), TRUE);
SELECT setval(pg_get_serial_sequence('class_schedules', 'id'), GREATEST(COALESCE((SELECT MAX(id) FROM class_schedules), 1), 1), TRUE);
