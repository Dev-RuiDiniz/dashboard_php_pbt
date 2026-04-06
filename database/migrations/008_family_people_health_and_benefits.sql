ALTER TABLE families
  ADD COLUMN chronic_disease VARCHAR(80) NULL AFTER is_active,
  ADD COLUMN has_physical_disability TINYINT(1) NOT NULL DEFAULT 0 AFTER chronic_disease,
  ADD COLUMN physical_disability_details VARCHAR(200) NULL AFTER has_physical_disability,
  ADD COLUMN uses_continuous_medication TINYINT(1) NOT NULL DEFAULT 0 AFTER physical_disability_details,
  ADD COLUMN continuous_medication_details VARCHAR(200) NULL AFTER uses_continuous_medication,
  ADD COLUMN social_benefit VARCHAR(80) NULL AFTER continuous_medication_details;

ALTER TABLE people
  ADD COLUMN phone VARCHAR(20) NULL AFTER stay_location,
  ADD COLUMN previous_address VARCHAR(200) NULL AFTER phone,
  ADD COLUMN chronic_disease VARCHAR(80) NULL AFTER work_interest_detail,
  ADD COLUMN has_physical_disability TINYINT(1) NOT NULL DEFAULT 0 AFTER chronic_disease,
  ADD COLUMN physical_disability_details VARCHAR(200) NULL AFTER has_physical_disability,
  ADD COLUMN uses_continuous_medication TINYINT(1) NOT NULL DEFAULT 0 AFTER physical_disability_details,
  ADD COLUMN continuous_medication_details VARCHAR(200) NULL AFTER uses_continuous_medication,
  ADD COLUMN social_benefit VARCHAR(80) NULL AFTER continuous_medication_details;
