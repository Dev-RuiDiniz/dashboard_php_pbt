-- Sprint 5 - Schema MVP (MySQL/MariaDB)
-- Sistema Igreja Social - Dashboard PHP PBT
-- Recomendado: InnoDB + utf8mb4

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','voluntario','pastoral','viewer') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS families (
  id INT AUTO_INCREMENT PRIMARY KEY,
  responsible_name VARCHAR(160) NOT NULL,
  cpf_responsible VARCHAR(14) NULL UNIQUE,
  rg_responsible VARCHAR(20) NULL,
  birth_date DATE NULL,
  phone VARCHAR(20) NULL,
  marital_status VARCHAR(30) NULL,
  education_level VARCHAR(40) NULL,
  professional_status VARCHAR(40) NULL,
  profession_detail VARCHAR(120) NULL,
  cep VARCHAR(10) NULL,
  address VARCHAR(200) NULL,
  address_number VARCHAR(20) NULL,
  address_complement VARCHAR(120) NULL,
  neighborhood VARCHAR(80) NULL,
  city VARCHAR(80) NULL,
  state CHAR(2) NULL,
  location_reference VARCHAR(200) NULL,
  housing_type VARCHAR(60) NULL,
  adults_count INT NOT NULL DEFAULT 0,
  workers_count INT NOT NULL DEFAULT 0,
  family_income_total DECIMAL(10,2) NOT NULL DEFAULT 0,
  children_count INT NOT NULL DEFAULT 0,
  documentation_status VARCHAR(30) NOT NULL DEFAULT 'ok',
  documentation_notes TEXT NULL,
  needs_visit TINYINT(1) NOT NULL DEFAULT 0,
  general_notes TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_families_responsible_name (responsible_name),
  INDEX idx_families_city_neighborhood (city, neighborhood),
  INDEX idx_families_documentation_status (documentation_status),
  INDEX idx_families_needs_visit (needs_visit),
  INDEX idx_families_is_active (is_active),
  INDEX idx_families_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS family_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  family_id INT NOT NULL,
  name VARCHAR(160) NOT NULL,
  relationship VARCHAR(40) NULL,
  birth_date DATE NULL,
  works TINYINT(1) NOT NULL DEFAULT 0,
  income DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_family_members_family FOREIGN KEY (family_id) REFERENCES families(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_family_members_family_id (family_id),
  INDEX idx_family_members_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS children (
  id INT AUTO_INCREMENT PRIMARY KEY,
  family_id INT NOT NULL,
  name VARCHAR(160) NOT NULL,
  birth_date DATE NULL,
  age_years INT NULL,
  relationship VARCHAR(40) NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_children_family FOREIGN KEY (family_id) REFERENCES families(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_children_family_id (family_id),
  INDEX idx_children_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS people (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(160) NULL,
  social_name VARCHAR(160) NULL,
  cpf VARCHAR(14) NULL UNIQUE,
  rg VARCHAR(20) NULL,
  birth_date DATE NULL,
  approx_age INT NULL,
  gender VARCHAR(20) NULL,
  is_homeless TINYINT(1) NOT NULL DEFAULT 0,
  homeless_time VARCHAR(20) NULL,
  stay_location VARCHAR(200) NULL,
  has_family_in_region TINYINT(1) NOT NULL DEFAULT 0,
  family_contact VARCHAR(200) NULL,
  education_level VARCHAR(40) NULL,
  profession_skills VARCHAR(200) NULL,
  formal_work_history TINYINT(1) NOT NULL DEFAULT 0,
  work_interest TINYINT(1) NOT NULL DEFAULT 0,
  work_interest_detail VARCHAR(200) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_people_full_name (full_name),
  INDEX idx_people_social_name (social_name),
  INDEX idx_people_is_homeless (is_homeless),
  INDEX idx_people_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS social_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  person_id INT NOT NULL,
  family_id INT NULL,
  chronic_diseases TEXT NULL,
  continuous_medication TEXT NULL,
  substance_use TEXT NULL,
  disability VARCHAR(80) NULL,
  immediate_needs TEXT NULL,
  spiritual_wants_prayer TINYINT(1) NOT NULL DEFAULT 0,
  spiritual_accepts_visit TINYINT(1) NOT NULL DEFAULT 0,
  church_name VARCHAR(160) NULL,
  spiritual_decision VARCHAR(80) NULL,
  notes TEXT NULL,
  consent_text_version VARCHAR(40) NOT NULL,
  consent_name VARCHAR(160) NOT NULL,
  consent_at DATETIME NOT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_social_records_person FOREIGN KEY (person_id) REFERENCES people(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_social_records_family FOREIGN KEY (family_id) REFERENCES families(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_social_records_user FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_social_records_person_id (person_id),
  INDEX idx_social_records_family_id (family_id),
  INDEX idx_social_records_created_by (created_by),
  INDEX idx_social_records_consent_at (consent_at),
  INDEX idx_social_records_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS referrals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  social_record_id INT NOT NULL,
  referral_type VARCHAR(60) NOT NULL,
  referral_date DATE NOT NULL,
  responsible_user_id INT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'encaminhado',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_referrals_record FOREIGN KEY (social_record_id) REFERENCES social_records(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_referrals_user FOREIGN KEY (responsible_user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_referrals_social_record_id (social_record_id),
  INDEX idx_referrals_type (referral_type),
  INDEX idx_referrals_status (status),
  INDEX idx_referrals_referral_date (referral_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS spiritual_followups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  person_id INT NOT NULL,
  followup_date DATE NOT NULL,
  action VARCHAR(80) NULL,
  notes TEXT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_spiritual_followups_person FOREIGN KEY (person_id) REFERENCES people(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_spiritual_followups_user FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_spiritual_followups_person_id (person_id),
  INDEX idx_spiritual_followups_followup_date (followup_date),
  INDEX idx_spiritual_followups_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS delivery_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  event_date DATE NOT NULL,
  block_multiple_same_month TINYINT(1) NOT NULL DEFAULT 1,
  max_baskets INT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'aberto',
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_delivery_events_user FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_delivery_events_event_date (event_date),
  INDEX idx_delivery_events_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS deliveries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  family_id INT NULL,
  person_id INT NULL,
  ticket_number INT NOT NULL,
  document_id VARCHAR(20) NULL,
  observations VARCHAR(200) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'nao_veio',
  quantity INT NOT NULL DEFAULT 1,
  delivered_at DATETIME NULL,
  delivered_by INT NULL,
  signature_name VARCHAR(160) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_deliveries_event FOREIGN KEY (event_id) REFERENCES delivery_events(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_deliveries_family FOREIGN KEY (family_id) REFERENCES families(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_deliveries_person FOREIGN KEY (person_id) REFERENCES people(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_deliveries_user FOREIGN KEY (delivered_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  UNIQUE KEY uq_deliveries_event_ticket (event_id, ticket_number),
  INDEX idx_deliveries_status (status),
  INDEX idx_deliveries_family_id (family_id),
  INDEX idx_deliveries_person_id (person_id),
  INDEX idx_deliveries_delivered_at (delivered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS equipment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  type VARCHAR(60) NOT NULL,
  condition_state VARCHAR(20) NOT NULL DEFAULT 'bom',
  status VARCHAR(20) NOT NULL DEFAULT 'disponivel',
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_equipment_type (type),
  INDEX idx_equipment_status (status),
  INDEX idx_equipment_condition_state (condition_state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS equipment_loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  equipment_id INT NOT NULL,
  family_id INT NULL,
  person_id INT NULL,
  loan_date DATE NOT NULL,
  due_date DATE NOT NULL,
  return_date DATE NULL,
  return_condition VARCHAR(20) NULL,
  notes TEXT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_equipment_loans_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_equipment_loans_family FOREIGN KEY (family_id) REFERENCES families(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_equipment_loans_person FOREIGN KEY (person_id) REFERENCES people(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_equipment_loans_user FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_equipment_loans_equipment_id (equipment_id),
  INDEX idx_equipment_loans_family_id (family_id),
  INDEX idx_equipment_loans_person_id (person_id),
  INDEX idx_equipment_loans_due_date (due_date),
  INDEX idx_equipment_loans_return_date (return_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  family_id INT NULL,
  person_id INT NULL,
  requested_by INT NOT NULL,
  requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  scheduled_date DATE NULL,
  completed_by INT NULL,
  completed_at DATETIME NULL,
  notes TEXT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pendente',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_visits_family FOREIGN KEY (family_id) REFERENCES families(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_visits_person FOREIGN KEY (person_id) REFERENCES people(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_visits_requested_by FOREIGN KEY (requested_by) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_visits_completed_by FOREIGN KEY (completed_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_visits_family_id (family_id),
  INDEX idx_visits_person_id (person_id),
  INDEX idx_visits_status (status),
  INDEX idx_visits_scheduled_date (scheduled_date),
  INDEX idx_visits_completed_at (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  action VARCHAR(80) NOT NULL,
  entity VARCHAR(80) NOT NULL,
  entity_id INT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  details_json JSON NULL,
  CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_audit_logs_user_id (user_id),
  INDEX idx_audit_logs_action (action),
  INDEX idx_audit_logs_entity (entity),
  INDEX idx_audit_logs_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

