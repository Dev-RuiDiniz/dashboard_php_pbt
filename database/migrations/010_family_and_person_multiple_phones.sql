CREATE TABLE IF NOT EXISTS family_phones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  family_id INT NOT NULL,
  number VARCHAR(20) NOT NULL,
  label VARCHAR(120) NULL,
  sort_order INT NOT NULL DEFAULT 1,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_family_phones_family FOREIGN KEY (family_id) REFERENCES families(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_family_phones_family_id (family_id),
  INDEX idx_family_phones_primary (family_id, is_primary, sort_order)
);

CREATE TABLE IF NOT EXISTS person_phones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  person_id INT NOT NULL,
  number VARCHAR(20) NOT NULL,
  label VARCHAR(120) NULL,
  sort_order INT NOT NULL DEFAULT 1,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_person_phones_person FOREIGN KEY (person_id) REFERENCES people(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_person_phones_person_id (person_id),
  INDEX idx_person_phones_primary (person_id, is_primary, sort_order)
);
