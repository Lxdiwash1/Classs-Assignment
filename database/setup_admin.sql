USE student_course_hub;

-- Add IsPublished column to Programmes if not exists
ALTER TABLE Programmes ADD COLUMN IF NOT EXISTS IsPublished TINYINT(1) DEFAULT 1;

-- Create Admins table
CREATE TABLE IF NOT EXISTS Admins (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL
);

-- Insert default admin: email=admin@ltc.com, password=Admin1234
-- Password hash for 'Admin1234'
INSERT IGNORE INTO Admins (Email, Password)
VALUES ('admin@ltc.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
