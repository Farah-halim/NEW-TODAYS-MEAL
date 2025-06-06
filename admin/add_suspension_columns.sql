-- Add suspension columns to cloud_kitchen_owner table
ALTER TABLE cloud_kitchen_owner 
ADD COLUMN suspension_reason TEXT NULL DEFAULT NULL,
ADD COLUMN suspended_by INT(11) NULL DEFAULT NULL,
ADD COLUMN suspension_date TIMESTAMP NULL DEFAULT NULL;

-- Add foreign key constraint for suspended_by (references admin user_id)
ALTER TABLE cloud_kitchen_owner 
ADD CONSTRAINT fk_suspended_by 
FOREIGN KEY (suspended_by) REFERENCES users(user_id) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Show the updated table structure
DESCRIBE cloud_kitchen_owner;