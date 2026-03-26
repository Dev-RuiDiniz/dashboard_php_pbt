ALTER TABLE family_members
  ADD COLUMN studies TINYINT(1) NOT NULL DEFAULT 0 AFTER birth_date;

ALTER TABLE children
  ADD COLUMN studies TINYINT(1) NOT NULL DEFAULT 0 AFTER relationship;
