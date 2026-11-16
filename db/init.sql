CREATE TABLE IF NOT EXISTS tasks (
    id          SERIAL PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    completed   BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMP    NOT NULL DEFAULT NOW()
);

INSERT INTO tasks (title, completed) VALUES
('Set up the database', TRUE),
('Build the PHP API', TRUE),
('Create the React frontend', FALSE);