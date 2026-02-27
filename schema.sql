CREATE TABLE IF NOT EXISTS users (
    id          SERIAL PRIMARY KEY,
    username    VARCHAR(50)    NOT NULL UNIQUE,
    password    VARCHAR(255)   NOT NULL,
    full_name   VARCHAR(100)   NOT NULL DEFAULT '',
    email       VARCHAR(100)   NOT NULL DEFAULT '',
    balance     NUMERIC(15,2)  NOT NULL DEFAULT 1000.00,
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id              SERIAL PRIMARY KEY,
    from_user_id    INT            NOT NULL REFERENCES users(id),
    to_user_id      INT            NOT NULL REFERENCES users(id),
    amount          NUMERIC(15,2)  NOT NULL,
    description     VARCHAR(255)   DEFAULT '',
    created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, full_name, email, balance) VALUES
('admin',   'admin123',   'Alice Administrator', 'admin@vulnerablebank.com',  50000.00),
('johndoe', 'password',   'John Doe',            'john@vulnerablebank.com',   1500.00),
('janedoe', 'jane2024',   'Jane Doe',            'jane@vulnerablebank.com',   3200.00);
