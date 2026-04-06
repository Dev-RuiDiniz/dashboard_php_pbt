ALTER TABLE family_members
  ADD COLUMN receives_social_benefit TINYINT(1) NOT NULL DEFAULT 0 AFTER works,
  ADD COLUMN purpose VARCHAR(200) NULL AFTER receives_social_benefit;
