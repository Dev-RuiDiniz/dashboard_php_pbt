-- Sprint 37 - Principal entra nos indicadores da familia
-- Adiciona ocupacao/renda do responsavel principal e media de renda per capita.

ALTER TABLE families
    ADD COLUMN IF NOT EXISTS responsible_works TINYINT(1) NOT NULL DEFAULT 0 AFTER phone,
    ADD COLUMN IF NOT EXISTS responsible_income DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER responsible_works,
    ADD COLUMN IF NOT EXISTS family_income_average DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER family_income_total;

UPDATE families
SET responsible_income = COALESCE(responsible_income, 0),
    family_income_average = CASE
        WHEN (1 + COALESCE(children_count, 0) + GREATEST(COALESCE(adults_count, 0), 0)) > 0
            THEN ROUND(COALESCE(family_income_total, 0) / (1 + COALESCE(children_count, 0) + GREATEST(COALESCE(adults_count, 0), 0)), 2)
        ELSE 0
    END;
