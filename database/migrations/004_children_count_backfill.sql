-- Sprint 33 - Backfill de children_count por contagem real da tabela children
-- Mantem families.children_count sincronizado com os registros vinculados.

UPDATE families f
SET f.children_count = (
    SELECT COUNT(*)
    FROM children c
    WHERE c.family_id = f.id
);
