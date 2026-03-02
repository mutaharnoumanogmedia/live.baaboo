
INSERT INTO `live_shows` (`id`, `title`, `description`, `scheduled_at`, `stream_link`, `status`, `created_by`, `host_name`, `prize_amount`, `currency`, `thumbnail`, `banner`, `created_at`, `updated_at`) 
VALUES (NULL, CONCAT('baaboo General Knowledge Special ', NOW()), 
'Interesting General Knowledge Quiz', 
'2025-09-10 19:30:00', 
'https://youtube.com/embed/LxTvqLlEMm4?si=test123', 
'live', 
'1', 
'Kathleen Rohan', 
'500.00', 
'EUR', 
'https://img.youtube.com/vi/LxTvqLlEMm4/hqdefault.jpg', 
'https://fastly.picsum.photos/id/1015/1200/600.jpg', 
NOW(), NOW());

SET @live_show_id = LAST_INSERT_ID();


-- Q1: Geography
INSERT INTO live_show_quizzes (live_show_id, question, created_at, updated_at) 
VALUES (@live_show_id, 
'Which country spans both Europe and Asia and has Istanbul as one of its major cities?', 
NOW(), NOW());
SET @q1_id = LAST_INSERT_ID();

INSERT INTO quiz_options (quiz_id, option_text, is_correct, created_at, updated_at) VALUES
(@q1_id, 'Greece', 0, NOW(), NOW()),
(@q1_id, 'Turkey', 1, NOW(), NOW()),
(@q1_id, 'Russia', 0, NOW(), NOW()),
(@q1_id, 'Romania', 0, NOW(), NOW());


-- Q2: Science
INSERT INTO live_show_quizzes VALUES 
(NULL, @live_show_id, 
'What element has the chemical symbol "Au"?', 
NOW(), NOW());
SET @q2_id = LAST_INSERT_ID();

INSERT INTO quiz_options VALUES
(NULL, @q2_id, 'Silver', 0, NOW(), NOW()),
(NULL, @q2_id, 'Gold', 1, NOW(), NOW()),
(NULL, @q2_id, 'Argon', 0, NOW(), NOW()),
(NULL, @q2_id, 'Aluminum', 0, NOW(), NOW());


-- Q3: History
INSERT INTO live_show_quizzes VALUES 
(NULL, @live_show_id, 
'The Great Wall of China was primarily built to protect against which group?', 
NOW(), NOW());
SET @q3_id = LAST_INSERT_ID();

INSERT INTO quiz_options VALUES
(NULL, @q3_id, 'Romans', 0, NOW(), NOW()),
(NULL, @q3_id, 'Mongols', 1, NOW(), NOW()),
(NULL, @q3_id, 'Vikings', 0, NOW(), NOW()),
(NULL, @q3_id, 'Ottomans', 0, NOW(), NOW());


-- Q4: Literature
INSERT INTO live_show_quizzes VALUES 
(NULL, @live_show_id, 
'Who wrote the dystopian novel "1984"?', 
NOW(), NOW());
SET @q4_id = LAST_INSERT_ID();

INSERT INTO quiz_options VALUES
(NULL, @q4_id, 'Aldous Huxley', 0, NOW(), NOW()),
(NULL, @q4_id, 'George Orwell', 1, NOW(), NOW()),
(NULL, @q4_id, 'Ray Bradbury', 0, NOW(), NOW()),
(NULL, @q4_id, 'J.R.R. Tolkien', 0, NOW(), NOW());


-- Q5: Space
INSERT INTO live_show_quizzes VALUES 
(NULL, @live_show_id, 
'What is the only planet in our solar system that rotates clockwise on its axis?', 
NOW(), NOW());
SET @q5_id = LAST_INSERT_ID();

INSERT INTO quiz_options VALUES
(NULL, @q5_id, 'Mars', 0, NOW(), NOW()),
(NULL, @q5_id, 'Venus', 1, NOW(), NOW()),
(NULL, @q5_id, 'Saturn', 0, NOW(), NOW()),
(NULL, @q5_id, 'Neptune', 0, NOW(), NOW());


-- Q6: Technology
INSERT INTO live_show_quizzes VALUES 
(NULL, @live_show_id, 
'What does the "WWW" stand for in a website address?', 
NOW(), NOW());
SET @q6_id = LAST_INSERT_ID();

INSERT INTO quiz_options VALUES
(NULL, @q6_id, 'World Web Wide', 0, NOW(), NOW()),
(NULL, @q6_id, 'Wide World Web', 0, NOW(), NOW()),
(NULL, @q6_id, 'World Wide Web', 1, NOW(), NOW()),
(NULL, @q6_id, 'Web World Wide', 0, NOW(), NOW());


-- Q7: Economics
INSERT INTO live_show_quizzes VALUES 
(NULL, @live_show_id, 
'Which country uses the yen as its official currency?', 
NOW(), NOW());
SET @q7_id = LAST_INSERT_ID();

INSERT INTO quiz_options VALUES
(NULL, @q7_id, 'South Korea', 0, NOW(), NOW()),
(NULL, @q7_id, 'China', 0, NOW(), NOW()),
(NULL, @q7_id, 'Japan', 1, NOW(), NOW()),
(NULL, @q7_id, 'Thailand', 0, NOW(), NOW());


-- Q8: Nature
INSERT INTO live_show_quizzes VALUES 
(NULL, @live_show_id, 
'What is the largest ocean on Earth?', 
NOW(), NOW());
SET @q8_id = LAST_INSERT_ID();

INSERT INTO quiz_options VALUES
(NULL, @q8_id, 'Indian Ocean', 0, NOW(), NOW()),
(NULL, @q8_id, 'Pacific Ocean', 1, NOW(), NOW()),
(NULL, @q8_id, 'Atlantic Ocean', 0, NOW(), NOW()),
(NULL, @q8_id, 'Arctic Ocean', 0, NOW(), NOW());


-- Q9: Culture
INSERT INTO live_show_quizzes VALUES 
(NULL, @live_show_id, 
'Which country is home to the ancient city of Petra?', 
NOW(), NOW());
SET @q9_id = LAST_INSERT_ID();

INSERT INTO quiz_options VALUES
(NULL, @q9_id, 'Egypt', 0, NOW(), NOW()),
(NULL, @q9_id, 'Jordan', 1, NOW(), NOW()),
(NULL, @q9_id, 'Morocco', 0, NOW(), NOW()),
(NULL, @q9_id, 'Turkey', 0, NOW(), NOW());


-- Q10: Physics
INSERT INTO live_show_quizzes VALUES 
(NULL, @live_show_id, 
'What is the speed of light in vacuum (approximately)?', 
NOW(), NOW());
SET @q10_id = LAST_INSERT_ID();

INSERT INTO quiz_options VALUES
(NULL, @q10_id, '300,000 km/s', 1, NOW(), NOW()),
(NULL, @q10_id, '150,000 km/s', 0, NOW(), NOW()),
(NULL, @q10_id, '30,000 km/s', 0, NOW(), NOW()),
(NULL, @q10_id, '3,000 km/s', 0, NOW(), NOW());