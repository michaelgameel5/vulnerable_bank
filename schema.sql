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

CREATE TABLE IF NOT EXISTS virtualCards (
    id          SERIAL PRIMARY KEY,
    userId      INTEGER REFERENCES users(id) ON DELETE CASCADE,
    cardNumber  TEXT NOT NULL UNIQUE,
    cvv         TEXT NOT NULL,
    expiryDate  TEXT NOT NULL,
    cardLimit   DECIMAL(15, 2) DEFAULT 10000.0,
    balance     DECIMAL(15, 2) DEFAULT 0.0,
    createdAt   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lastUsed    TIMESTAMP,
    cardType    TEXT DEFAULT 'standard'
);

CREATE TABLE IF NOT EXISTS billers (
    id          SERIAL PRIMARY KEY,
    name        TEXT NOT NULL,
    isActive BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS billPayments (
    id          SERIAL PRIMARY KEY,
    userId      INTEGER REFERENCES users(id) ON DELETE CASCADE,
    billerId    INTEGER REFERENCES billers(id),
    amount      DECIMAL(15, 2) NOT NULL,
    paymentMethod TEXT NOT NULL,  -- 'balance' or 'card'
    cardId      INTEGER REFERENCES virtualCards(id),  -- NULL if paid with balance
    referenceNumber TEXT,
    createdAt   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processedAt TIMESTAMP,
    description TEXT
);

INSERT INTO users (username, password, full_name, email, balance) VALUES
('admin',   'admin123',   'Alice Administrator', 'admin@vulnerablebank.com',  50000.00),
('johndoe', 'password',   'John Doe',            'john@vulnerablebank.com',   1500.00),
('janedoe', 'jane2024',   'Jane Doe',            'jane@vulnerablebank.com',   3200.00);

INSERT INTO billers (name, isActive) VALUES 
('ElectricCo', TRUE),
('FastNet Internet', TRUE),
('City Water Dept', TRUE),
('StreamingServices Inc.', TRUE),
('MobilePhone Corp', TRUE)
('GamePass Online', TRUE),
('MusicFlow Premium', TRUE);
