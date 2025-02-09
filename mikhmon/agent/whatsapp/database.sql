CREATE TABLE IF NOT EXISTS agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(50) UNIQUE,
    name VARCHAR(100),
    balance DECIMAL(10,2) DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    password VARCHAR(64)
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT,
    type ENUM('deposit','purchase'),
    amount DECIMAL(10,2),
    buy_price DECIMAL(10,2),
    sell_price DECIMAL(10,2),
    voucher_code VARCHAR(50),
    profile VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES agents(id)
);

CREATE TABLE voucher_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile VARCHAR(50),
    buy_price DECIMAL(10,2),  -- Harga beli dari admin
    sell_price DECIMAL(10,2), -- Harga jual ke user
    agent_price DECIMAL(10,2), -- Harga khusus agen
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE transactions ADD INDEX (created_at);
ALTER TABLE transactions ADD INDEX (agent_id, type);

ALTER TABLE agents ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL; 