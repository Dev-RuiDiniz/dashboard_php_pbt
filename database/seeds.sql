-- Sprint 2 - seed admin inicial
-- Credenciais iniciais (alterar apos primeiro login):
-- email: admin@igrejasocial.local
-- senha: admin123

INSERT INTO users (name, email, password_hash, role, is_active)
VALUES (
  'Administrador',
  'admin@igrejasocial.local',
  '$2y$10$Q0RHPjvNDZnlOjDYNMR3cO3Hx9ZHH8g26O1BHNE..f5DxMX9BOCcC',
  'admin',
  1
)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  password_hash = VALUES(password_hash),
  role = VALUES(role),
  is_active = VALUES(is_active);
