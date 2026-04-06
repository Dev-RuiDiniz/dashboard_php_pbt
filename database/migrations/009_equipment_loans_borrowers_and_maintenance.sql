ALTER TABLE equipment
    ADD COLUMN maintenance_notes TEXT NULL AFTER notes,
    ADD COLUMN maintenance_completed_at DATETIME NULL AFTER maintenance_notes;

ALTER TABLE equipment_loans
    ADD COLUMN borrower_name VARCHAR(160) NULL AFTER person_id,
    ADD COLUMN borrower_phone VARCHAR(20) NULL AFTER borrower_name,
    ADD COLUMN borrower_cpf VARCHAR(14) NULL AFTER borrower_phone,
    ADD COLUMN borrower_address VARCHAR(200) NULL AFTER borrower_cpf,
    ADD COLUMN equipment_user_name VARCHAR(160) NULL AFTER borrower_address,
    ADD COLUMN maintenance_notes TEXT NULL AFTER notes;
