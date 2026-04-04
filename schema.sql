CREATE DATABASE IF NOT EXISTS qr_asistencia
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE qr_asistencia;

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE admin_users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('superadmin', 'rrhh', 'supervisor', 'operator') NOT NULL DEFAULT 'rrhh',
  active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(150) NOT NULL,
  description VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (role_id, permission_id),
  KEY idx_role_permissions_permission (permission_id),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_user_roles (
  admin_user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (admin_user_id),
  KEY idx_admin_user_roles_role (role_id),
  CONSTRAINT fk_admin_user_roles_user FOREIGN KEY (admin_user_id) REFERENCES admin_users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_admin_user_roles_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employee_groups (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_employee_groups_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE work_schedules (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  tolerance_before_minutes INT NOT NULL DEFAULT 0,
  tolerance_after_minutes INT NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE group_schedule_assignments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  group_id BIGINT UNSIGNED NOT NULL,
  schedule_id BIGINT UNSIGNED NOT NULL,
  day_of_week TINYINT UNSIGNED NULL,
  valid_from DATE NULL,
  valid_to DATE NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_group_schedule_assignments_group_day (group_id, day_of_week, active),
  CONSTRAINT fk_group_schedule_assignments_group FOREIGN KEY (group_id) REFERENCES employee_groups (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_group_schedule_assignments_schedule FOREIGN KEY (schedule_id) REFERENCES work_schedules (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employees (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  group_id BIGINT UNSIGNED NULL,
  cedula VARCHAR(20) NOT NULL,
  full_name VARCHAR(180) NOT NULL,
  email VARCHAR(190) NULL,
  pin_hash VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_employees_cedula (cedula),
  KEY idx_employees_group_active (group_id, active),
  CONSTRAINT fk_employees_group FOREIGN KEY (group_id) REFERENCES employee_groups (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE qr_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  token_hash CHAR(64) NOT NULL,
  window_start DATETIME NOT NULL,
  window_end DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  revoked_at DATETIME NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_qr_sessions_token_hash (token_hash),
  KEY idx_qr_sessions_window (window_start, window_end, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance_records (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  employee_id BIGINT UNSIGNED NOT NULL,
  schedule_id BIGINT UNSIGNED NULL,
  qr_session_id BIGINT UNSIGNED NULL,
  mark_type ENUM('entry', 'exit') NOT NULL,
  schedule_state ENUM('on_time', 'late', 'early', 'outside_window', 'unscheduled') NOT NULL DEFAULT 'unscheduled',
  attendance_date DATE NOT NULL,
  attendance_time TIME NOT NULL,
  marked_at DATETIME NOT NULL,
  attempt_bucket INT UNSIGNED NOT NULL,
  source ENUM('qr_global', 'admin_manual') NOT NULL DEFAULT 'qr_global',
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  notes VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_attendance_employee_bucket (employee_id, attempt_bucket),
  KEY idx_attendance_employee_date (employee_id, attendance_date, marked_at),
  KEY idx_attendance_schedule_date (schedule_id, attendance_date),
  CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id) REFERENCES employees (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_attendance_schedule FOREIGN KEY (schedule_id) REFERENCES work_schedules (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_attendance_qr_session FOREIGN KEY (qr_session_id) REFERENCES qr_sessions (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  actor_type ENUM('admin', 'employee', 'system') NOT NULL DEFAULT 'system',
  actor_id BIGINT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  entity VARCHAR(120) NULL,
  entity_id BIGINT UNSIGNED NULL,
  payload JSON NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_logs_actor (actor_type, actor_id),
  KEY idx_audit_logs_action_created (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO employee_groups (name, slug, description, active) VALUES
  ('Administrativo', 'administrativo', 'Personal administrativo', 1),
  ('Operativo', 'operativo', 'Personal operativo', 1);

INSERT INTO roles (name, slug, description, active) VALUES
  ('Superadmin', 'superadmin', 'Acceso total al sistema', 1),
  ('RRHH', 'rrhh', 'Gestiona personal y horarios', 1),
  ('Supervisor', 'supervisor', 'Supervision operativa y reportes', 1),
  ('Operador', 'operator', 'Acceso operativo basico', 1);

INSERT INTO permissions (name, slug, description) VALUES
  ('Ver dashboard', 'dashboard.view', 'Acceso al panel principal'),
  ('Gestionar grupos', 'groups.manage', 'Crear y editar grupos'),
  ('Ver empleados', 'employees.view', 'Consultar empleados'),
  ('Gestionar empleados', 'employees.manage', 'Crear, editar o eliminar empleados'),
  ('Gestionar horarios', 'schedules.manage', 'Crear, editar o eliminar horarios'),
  ('Ver reportes', 'reports.view', 'Acceder a reportes'),
  ('Ver QR', 'qr.view', 'Acceder a QR global'),
  ('Ver auditoria', 'audit.view', 'Acceder a auditoria'),
  ('Gestionar roles', 'roles.manage', 'Crear y editar roles'),
  ('Gestionar usuarios', 'users.manage', 'Crear y editar usuarios administradores');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'groups.manage',
  'employees.view',
  'employees.manage',
  'schedules.manage',
  'reports.view',
  'qr.view',
  'audit.view',
  'roles.manage',
  'users.manage'
)
WHERE r.slug = 'superadmin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'groups.manage',
  'employees.view',
  'employees.manage',
  'schedules.manage',
  'reports.view',
  'qr.view'
)
WHERE r.slug = 'rrhh';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'employees.view',
  'reports.view',
  'qr.view'
)
WHERE r.slug = 'supervisor';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN (
  'dashboard.view',
  'qr.view'
)
WHERE r.slug = 'operator';