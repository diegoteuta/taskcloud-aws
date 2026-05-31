-- Esquema para TaskCloud
-- Ejecutar como usuario admin de MySQL:
--   mysql -h <host> -u <user> -p < schema.sql

CREATE DATABASE IF NOT EXISTS tasksdb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tasksdb;

CREATE TABLE IF NOT EXISTS tasks (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200)         NOT NULL,
    description TEXT                 NULL,
    priority    ENUM('baja','media','alta') NOT NULL DEFAULT 'media',
    status      ENUM('pendiente','en_progreso','completada') NOT NULL DEFAULT 'pendiente',
    due_date    DATE                 NULL,
    created_at  TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status   (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo
INSERT INTO tasks (title, description, priority, status, due_date) VALUES
('Configurar VPC en AWS',           'Crear nube privada virtual con 2 subredes públicas y 2 privadas en 2 AZ.', 'alta',  'completada', '2026-05-28'),
('Lanzar instancia EC2 con LAMP',   'Provisionar t2.micro con Apache + PHP + MySQL.',                            'alta',  'completada', '2026-05-28'),
('Crear base de datos en RDS',      'Migrar la base local a Amazon RDS MySQL db.t3.micro.',                       'alta',  'en_progreso','2026-05-29'),
('Configurar Application LB',       'ALB en 2 AZ distribuyendo tráfico entre instancias del ASG.',                'media', 'pendiente',  '2026-05-29'),
('Implementar Auto Scaling',        'Launch Template + ASG con políticas de escalado por CPU.',                   'media', 'pendiente',  '2026-05-30'),
('Pruebas de carga',                'Ejecutar loadtest para validar que el ASG escale correctamente.',            'baja',  'pendiente',  '2026-05-30'),
('Endurecer seguridad',             'Restringir SG, mover credenciales a Secrets Manager.',                       'alta',  'pendiente',  '2026-05-30'),
('Redactar documento final',        'Documento Word con diagrama, evidencias y WAF.',                             'alta',  'pendiente',  '2026-05-31');
