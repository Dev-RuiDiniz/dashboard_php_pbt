-- Sprint 5 - Seeds iniciais (MVP)
-- Execute apos aplicar database/schema.sql
-- Senha padrao dos usuarios abaixo: admin123 (alterar no primeiro acesso)

INSERT INTO users (name, email, password_hash, role, is_active)
VALUES
  ('Administrador', 'admin@igrejasocial.local', '$2y$10$Q0RHPjvNDZnlOjDYNMR3cO3Hx9ZHH8g26O1BHNE..f5DxMX9BOCcC', 'admin', 1),
  ('Voluntario Base', 'voluntario@igrejasocial.local', '$2y$10$Q0RHPjvNDZnlOjDYNMR3cO3Hx9ZHH8g26O1BHNE..f5DxMX9BOCcC', 'voluntario', 1),
  ('Pastoral Base', 'pastoral@igrejasocial.local', '$2y$10$Q0RHPjvNDZnlOjDYNMR3cO3Hx9ZHH8g26O1BHNE..f5DxMX9BOCcC', 'pastoral', 1),
  ('Viewer Base', 'viewer@igrejasocial.local', '$2y$10$Q0RHPjvNDZnlOjDYNMR3cO3Hx9ZHH8g26O1BHNE..f5DxMX9BOCcC', 'viewer', 1)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  password_hash = VALUES(password_hash),
  role = VALUES(role),
  is_active = VALUES(is_active);

-- Dados de exemplo (opcional para desenvolvimento local)
INSERT INTO families (
  responsible_name, cpf_responsible, phone, neighborhood, city, state,
  documentation_status, needs_visit, is_active
)
VALUES
  ('Maria de Souza', '123.456.789-00', '(11) 99999-0001', 'Centro', 'Sao Paulo', 'SP', 'pendente', 1, 1),
  ('Jose Almeida', '987.654.321-00', '(11) 99999-0002', 'Jardim Esperanca', 'Sao Paulo', 'SP', 'ok', 0, 1)
ON DUPLICATE KEY UPDATE
  responsible_name = VALUES(responsible_name),
  phone = VALUES(phone),
  neighborhood = VALUES(neighborhood),
  city = VALUES(city),
  state = VALUES(state),
  documentation_status = VALUES(documentation_status),
  needs_visit = VALUES(needs_visit),
  is_active = VALUES(is_active);

INSERT INTO people (
  full_name, social_name, cpf, gender, is_homeless, has_family_in_region, work_interest
)
VALUES
  ('Carlos Pereira', NULL, '111.222.333-44', 'masculino', 0, 1, 1),
  ('Ana (social)', 'Ana', NULL, 'feminino', 1, 0, 0)
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  social_name = VALUES(social_name),
  gender = VALUES(gender),
  is_homeless = VALUES(is_homeless),
  has_family_in_region = VALUES(has_family_in_region),
  work_interest = VALUES(work_interest);

INSERT INTO equipment (code, type, condition_state, status, notes)
VALUES
  ('CAD-001', 'cadeira de rodas', 'bom', 'disponivel', 'Seed inicial'),
  ('MUL-001', 'muleta', 'bom', 'disponivel', 'Seed inicial')
ON DUPLICATE KEY UPDATE
  type = VALUES(type),
  condition_state = VALUES(condition_state),
  status = VALUES(status),
  notes = VALUES(notes);

