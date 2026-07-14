-- =========================================================
-- Base de datos: Taller Jesús Gardea
-- Basado en el Diagrama ER y Diagrama de Clases del proyecto
-- =========================================================

CREATE DATABASE IF NOT EXISTS taller_jesus_gardea
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE taller_jesus_gardea;

-- ---------------------------------------------------------
-- EMPLEADO (superclase) -> MECANICO / ADMINISTRATIVO (subclases)
-- ---------------------------------------------------------
CREATE TABLE empleado (
    id_empleado     INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(50)  NOT NULL,
    apellido_pat    VARCHAR(50)  NOT NULL,
    apellido_mat    VARCHAR(50),
    telefono        VARCHAR(15),
    direccion       VARCHAR(150),
    turno           VARCHAR(50),
    activo          BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mecanico (
    id_empleado     INT PRIMARY KEY,
    especialidad    VARCHAR(100),
    CONSTRAINT fk_mecanico_empleado
        FOREIGN KEY (id_empleado) REFERENCES empleado(id_empleado)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE administrativo (
    id_empleado     INT PRIMARY KEY,
    area            VARCHAR(100),
    CONSTRAINT fk_administrativo_empleado
        FOREIGN KEY (id_empleado) REFERENCES empleado(id_empleado)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- USUARIO (credenciales de acceso de un empleado - login)
-- ---------------------------------------------------------
CREATE TABLE usuario (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    correo          VARCHAR(100) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    id_empleado     INT NOT NULL UNIQUE,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_empleado
        FOREIGN KEY (id_empleado) REFERENCES empleado(id_empleado)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- CLIENTE
-- ---------------------------------------------------------
CREATE TABLE cliente (
    id_cliente      INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(50)  NOT NULL,
    apellido_pat    VARCHAR(50)  NOT NULL,
    apellido_mat    VARCHAR(50),
    telefono        VARCHAR(15),
    correo          VARCHAR(100),
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- AUTO (pertenece a un cliente)
-- ---------------------------------------------------------
CREATE TABLE auto (
    id_auto         INT AUTO_INCREMENT PRIMARY KEY,
    tipo            VARCHAR(50),
    marca           VARCHAR(50),
    modelo          VARCHAR(50),
    color           VARCHAR(30),
    anio            SMALLINT,
    id_cliente      INT NOT NULL,
    placa           VARCHAR(10) UNIQUE,
    CONSTRAINT fk_auto_cliente
        FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- CITA (cliente agenda, mecánico la realiza)
-- ---------------------------------------------------------
CREATE TABLE cita (
    id_cita         INT AUTO_INCREMENT PRIMARY KEY,
    fecha           DATE NOT NULL,
    hora            TIME NOT NULL,
    motivo          VARCHAR(200),
    id_cliente      INT NOT NULL,
    id_auto         INT NOT NULL,
    id_mecanico     INT,
    estado          ENUM('Programada','Confirmada','Cancelada') DEFAULT 'Programada',
    CONSTRAINT fk_cita_cliente
        FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
    CONSTRAINT fk_cita_auto
        FOREIGN KEY (id_auto) REFERENCES auto(id_auto),
    CONSTRAINT fk_cita_mecanico
        FOREIGN KEY (id_mecanico) REFERENCES mecanico(id_empleado)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- DIAGNOSTICO (lo elabora un mecánico a partir de una cita)
-- ---------------------------------------------------------
CREATE TABLE diagnostico (
    id_diagnostico  INT AUTO_INCREMENT PRIMARY KEY,
    descripcion     TEXT,
    presupuesto     DECIMAL(10,2),
    id_cita         INT NOT NULL,
    id_mecanico     INT NOT NULL,
    decision_cliente ENUM('Pendiente','Aceptado','Rechazado') DEFAULT 'Pendiente',
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_diagnostico_cita
        FOREIGN KEY (id_cita) REFERENCES cita(id_cita),
    CONSTRAINT fk_diagnostico_mecanico
        FOREIGN KEY (id_mecanico) REFERENCES mecanico(id_empleado)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- SERVICIO (orden de servicio generada por el administrativo)
-- ---------------------------------------------------------
CREATE TABLE servicio (
    id_servicio     INT AUTO_INCREMENT PRIMARY KEY,
    costo           DECIMAL(10,2),
    descripcion     TEXT,
    tipo_servicio   VARCHAR(100),
    tiempo_estimado VARCHAR(50),
    estado          ENUM('Pendiente','En proceso','Finalizado','Cancelado') DEFAULT 'Pendiente',
    id_diagnostico  INT NOT NULL,
    id_administrativo INT NOT NULL,
    id_mecanico     INT NOT NULL,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_servicio_diagnostico
        FOREIGN KEY (id_diagnostico) REFERENCES diagnostico(id_diagnostico),
    CONSTRAINT fk_servicio_administrativo
        FOREIGN KEY (id_administrativo) REFERENCES administrativo(id_empleado),
    CONSTRAINT fk_servicio_mecanico
        FOREIGN KEY (id_mecanico) REFERENCES mecanico(id_empleado)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- HISTORIAL_ESTADO (RF11 - bitácora de cambios de estado del servicio)
-- ---------------------------------------------------------
CREATE TABLE historial_estado (
    id_historial    INT AUTO_INCREMENT PRIMARY KEY,
    id_servicio     INT NOT NULL,
    estado_anterior VARCHAR(20),
    estado_nuevo    VARCHAR(20) NOT NULL,
    cambiado_por    INT NOT NULL,  -- id_empleado (mecánico) que hizo el cambio
    fecha_cambio    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_servicio
        FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio),
    CONSTRAINT fk_historial_empleado
        FOREIGN KEY (cambiado_por) REFERENCES empleado(id_empleado)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- PROVEEDOR
-- ---------------------------------------------------------
CREATE TABLE proveedor (
    id_proveedor    INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(50) NOT NULL,
    apellido_pat    VARCHAR(50),
    apellido_mat    VARCHAR(50),
    telefono        VARCHAR(15),
    correo          VARCHAR(100)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- REFACCION (surtida por un proveedor)
-- ---------------------------------------------------------
CREATE TABLE refaccion (
    id_pieza        INT AUTO_INCREMENT PRIMARY KEY,
    nombre_pieza    VARCHAR(100) NOT NULL,
    marca           VARCHAR(50),
    cantidad        INT NOT NULL DEFAULT 0,
    stock_minimo    INT NOT NULL DEFAULT 0,
    precio          DECIMAL(10,2),
    id_proveedor    INT NOT NULL,
    CONSTRAINT fk_refaccion_proveedor
        FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- SERVICIO_REFACCION (tabla intermedia N:M - relación "Utiliza")
-- ---------------------------------------------------------
CREATE TABLE servicio_refaccion (
    id_servicio     INT NOT NULL,
    id_pieza        INT NOT NULL,
    cantidad_usada  INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id_servicio, id_pieza),
    CONSTRAINT fk_sr_servicio
        FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio),
    CONSTRAINT fk_sr_refaccion
        FOREIGN KEY (id_pieza) REFERENCES refaccion(id_pieza)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- SOLICITUD_REFACCION (RF18 - un mecánico pide una refacción para un
-- servicio; el administrativo la atiende decidiendo a qué proveedor
-- pedirla, o la rechaza)
-- ---------------------------------------------------------
CREATE TABLE solicitud_refaccion (
    id_solicitud    INT AUTO_INCREMENT PRIMARY KEY,
    id_mecanico     INT NOT NULL,
    id_servicio     INT,
    nombre_pieza    VARCHAR(100) NOT NULL,
    cantidad        INT NOT NULL DEFAULT 1,
    estado          ENUM('Pendiente','Atendida','Rechazada') DEFAULT 'Pendiente',
    id_proveedor    INT,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitud_mecanico
        FOREIGN KEY (id_mecanico) REFERENCES mecanico(id_empleado),
    CONSTRAINT fk_solicitud_servicio
        FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio),
    CONSTRAINT fk_solicitud_proveedor
        FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TICKET (generado al finalizar el servicio y cobrar)
-- ---------------------------------------------------------
CREATE TABLE ticket (
    id_ticket       INT AUTO_INCREMENT PRIMARY KEY,
    fecha_ingreso   DATE NOT NULL,
    fecha_entrega   DATE,
    monto           DECIMAL(10,2) NOT NULL,
    metodo_pago     ENUM('Efectivo','Tarjeta','Transferencia') NOT NULL,
    id_servicio     INT NOT NULL,
    id_administrativo INT NOT NULL,
    CONSTRAINT fk_ticket_servicio
        FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio),
    CONSTRAINT fk_ticket_administrativo
        FOREIGN KEY (id_administrativo) REFERENCES administrativo(id_empleado)
) ENGINE=InnoDB;

-- =========================================================
-- Trigger: actualizar stock de refacciones automáticamente (RF9)
-- =========================================================
DELIMITER $$

CREATE TRIGGER trg_descontar_stock
AFTER INSERT ON servicio_refaccion
FOR EACH ROW
BEGIN
    UPDATE refaccion
    SET cantidad = cantidad - NEW.cantidad_usada
    WHERE id_pieza = NEW.id_pieza;
END$$

DELIMITER ;

-- =========================================================
-- Trigger: registrar historial de estado automáticamente (RF11)
-- =========================================================
DELIMITER $$

CREATE TRIGGER trg_historial_estado
AFTER UPDATE ON servicio
FOR EACH ROW
BEGIN
    IF OLD.estado <> NEW.estado THEN
        INSERT INTO historial_estado (id_servicio, estado_anterior, estado_nuevo, cambiado_por)
        VALUES (NEW.id_servicio, OLD.estado, NEW.estado, NEW.id_mecanico);
    END IF;
END$$

DELIMITER ;
