-- Sprint 36 - Documentos em family_members e children
-- Adiciona CPF/RG para membros/dependentes/criancas e indices de busca/unicidade.

ALTER TABLE family_members
    ADD COLUMN cpf VARCHAR(14) NULL AFTER relationship,
    ADD COLUMN rg VARCHAR(20) NULL AFTER cpf;

ALTER TABLE family_members
    ADD UNIQUE KEY uq_family_members_cpf (cpf),
    ADD INDEX idx_family_members_rg (rg);

ALTER TABLE children
    ADD COLUMN cpf VARCHAR(14) NULL AFTER name,
    ADD COLUMN rg VARCHAR(20) NULL AFTER cpf;

ALTER TABLE children
    ADD UNIQUE KEY uq_children_cpf (cpf),
    ADD INDEX idx_children_rg (rg);
