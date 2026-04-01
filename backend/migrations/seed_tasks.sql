USE taskmanager;

INSERT IGNORE INTO tasks (title, due_date, priority, status) VALUES
  ('Fix critical login bug',   '2026-06-01', 'high',   'pending'),
  ('Write unit tests',         '2026-06-05', 'medium', 'pending'),
  ('Update API documentation', '2026-06-10', 'low',    'pending'),
  ('Deploy to staging',        '2026-06-03', 'high',   'in_progress'),
  ('Code review PR #42',       '2026-05-30', 'medium', 'done');
