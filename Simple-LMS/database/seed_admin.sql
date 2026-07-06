-- database/seed_admin.sql

INSERT INTO users (full_name, email, password, role) 
VALUES (
    'Admin User',
    'admin@example.com',
    '$2y$12$cCUpYhpkZkBl.1WZ7pEB5enOqG7G5698wBFyZlRTlrRwNdboK8lEW', -- Admin@123
    'admin'
);