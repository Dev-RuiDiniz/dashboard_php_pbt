ALTER TABLE families
  MODIFY COLUMN chronic_disease TEXT NULL,
  ADD COLUMN chronic_disease_other_details VARCHAR(200) NULL AFTER chronic_disease,
  ADD COLUMN has_addiction TINYINT(1) NOT NULL DEFAULT 0 AFTER continuous_medication_details,
  ADD COLUMN addiction_details VARCHAR(200) NULL AFTER has_addiction,
  ADD COLUMN housing_type_other_details VARCHAR(200) NULL AFTER housing_type;

ALTER TABLE people
  MODIFY COLUMN chronic_disease TEXT NULL,
  ADD COLUMN chronic_disease_other_details VARCHAR(200) NULL AFTER chronic_disease,
  ADD COLUMN has_addiction TINYINT(1) NOT NULL DEFAULT 0 AFTER continuous_medication_details,
  ADD COLUMN addiction_details VARCHAR(200) NULL AFTER has_addiction;
