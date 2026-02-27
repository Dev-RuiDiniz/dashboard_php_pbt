-- Sprint 19 - Auditoria, seguranca e hardening

ALTER TABLE users
  ADD COLUMN failed_login_attempts INT NOT NULL DEFAULT 0 AFTER is_active,
  ADD COLUMN locked_until DATETIME NULL AFTER failed_login_attempts,
  ADD COLUMN password_reset_token_hash VARCHAR(64) NULL AFTER locked_until,
  ADD COLUMN password_reset_expires_at DATETIME NULL AFTER password_reset_token_hash,
  ADD COLUMN password_reset_requested_at DATETIME NULL AFTER password_reset_expires_at;

ALTER TABLE users
  ADD INDEX idx_users_locked_until (locked_until),
  ADD INDEX idx_users_password_reset_expires_at (password_reset_expires_at);

ALTER TABLE audit_logs DROP FOREIGN KEY fk_audit_logs_user;

ALTER TABLE audit_logs
  MODIFY user_id INT NULL;

ALTER TABLE audit_logs
  ADD CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

