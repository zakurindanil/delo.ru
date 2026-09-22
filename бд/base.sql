CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(20) NOT NULL,
    fio VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(30),
    password_hash VARCHAR(150) NOT NULL,
    avatar VARCHAR(150),
    created_account DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE case_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL
);

CREATE TABLE case_subtypes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_id INT NOT NULL,
    name VARCHAR(150) NOT NULL
);

CREATE TABLE case_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    color VARCHAR(10) NOT NULL
);

CREATE TABLE cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(30) UNIQUE NOT NULL,
    applicant_id INT NOT NULL,
    judge_id INT,
    type_id INT NOT NULL,
    subtype_id INT,
    status_id INT NOT NULL,
    comment TEXT,
    admin_comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_id) REFERENCES users(id),
    FOREIGN KEY (judge_id)     REFERENCES users(id),
    FOREIGN KEY (type_id)      REFERENCES case_types(id),
    FOREIGN KEY (subtype_id)   REFERENCES case_subtypes(id),
    FOREIGN KEY (status_id)    REFERENCES case_statuses(id)
);

CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT,
    uploaded_by INT,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id)     REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE hearings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    judge_id INT NOT NULL,
    hearing_date DATE NOT NULL,
    hearing_time TIME NOT NULL,
    status VARCHAR(30) DEFAULT 'scheduled',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id)  REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (judge_id) REFERENCES users(id)
);

CREATE TABLE decisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    admin_id INT NOT NULL,
    decision_type VARCHAR(20) NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id)  REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    text TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
);