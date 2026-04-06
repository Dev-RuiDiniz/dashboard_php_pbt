ALTER TABLE deliveries
  ADD COLUMN monthly_block_exception TINYINT(1) NOT NULL DEFAULT 0 AFTER signature_name,
  ADD COLUMN monthly_block_exception_reason VARCHAR(255) NULL AFTER monthly_block_exception,
  ADD COLUMN monthly_block_exception_authorized_by INT NULL AFTER monthly_block_exception_reason,
  ADD CONSTRAINT fk_deliveries_monthly_block_exception_authorized_by
    FOREIGN KEY (monthly_block_exception_authorized_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  ADD INDEX idx_deliveries_monthly_block_exception (monthly_block_exception);
