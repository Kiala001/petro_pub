-- Petrochamp - Sistema de Base de Dados Académica
-- Script de criação do banco de dados

CREATE DATABASE IF NOT EXISTS petrochamp_db;
USE petrochamp_db;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('ADMIN', 'PROFESSOR', 'COORDINATOR', 'COMMON_USER') NOT NULL DEFAULT 'COMMON_USER',
    balance DECIMAL(15, 2) DEFAULT 0.00,
    total_points INT DEFAULT 0,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(32),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_type (type)
);

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS categories (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(20) NOT NULL,
    allowed_file_types JSON NOT NULL,
    base_price_kz DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    requires_review BOOLEAN DEFAULT FALSE,
    upload_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    revenue_kz DECIMAL(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_name (name),
    INDEX idx_name (name)
);

-- Tabela de Documentos
CREATE TABLE IF NOT EXISTS documents (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    category_id VARCHAR(36) NOT NULL,
    title VARCHAR(200) NOT NULL,
    authors JSON NOT NULL,
    institution VARCHAR(150),
    course VARCHAR(100),
    summary TEXT NOT NULL,
    keywords JSON NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    file_type VARCHAR(10),
    price_kz DECIMAL(15, 2) DEFAULT 0.00,
    is_paid BOOLEAN DEFAULT TRUE,
    status ENUM('PENDING', 'APPROVED', 'REJECTED', 'ARCHIVED') DEFAULT 'PENDING',
    version INT DEFAULT 1,
    download_link VARCHAR(255),
    expires_at DATETIME,
    plagiarism_score DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    FULLTEXT INDEX ft_title (title),
    FULLTEXT INDEX ft_summary (summary)
);

-- Tabela de Pagamentos
CREATE TABLE IF NOT EXISTS payments (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    document_id VARCHAR(36) NOT NULL,
    amount_kz DECIMAL(15, 2) NOT NULL,
    method ENUM('TRANSFER', 'CARD', 'DIGITAL_APP') NOT NULL,
    status ENUM('PENDING', 'VERIFIED', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    reference_number VARCHAR(50) NOT NULL,
    proof_file_path VARCHAR(255),
    proof_approved_at DATETIME,
    approved_by VARCHAR(36),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_document_id (document_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_reference (reference_number)
);

-- Tabela de Histórico de Downloads
CREATE TABLE IF NOT EXISTS download_history (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    document_id VARCHAR(36) NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_document_id (document_id),
    INDEX idx_downloaded_at (downloaded_at)
);

-- Tabela de Avaliações
CREATE TABLE IF NOT EXISTS ratings (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    document_id VARCHAR(36) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_document_rating (user_id, document_id),
    INDEX idx_document_id (document_id)
);

-- Tabela de Assinaturas
CREATE TABLE IF NOT EXISTS subscriptions (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    plan_type ENUM('FREE', 'BASIC', 'PREMIUM', 'ENTERPRISE') DEFAULT 'FREE',
    monthly_limit_downloads INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_active_subscription (user_id),
    INDEX idx_plan_type (plan_type),
    INDEX idx_is_active (is_active)
);

-- Tabela de Favoritos
CREATE TABLE IF NOT EXISTS favorites (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    document_id VARCHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_document_favorite (user_id, document_id),
    INDEX idx_user_id (user_id)
);

-- Tabela de Comentários
CREATE TABLE IF NOT EXISTS comments (
    id VARCHAR(36) PRIMARY KEY,
    document_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    parent_comment_id VARCHAR(36),
    content TEXT NOT NULL,
    status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'APPROVED',
    is_helpful_count INT DEFAULT 0,
    is_not_helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_document_id (document_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_comment_id (parent_comment_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Tabela de Reações em Comentários (curtir/não curtir)
CREATE TABLE IF NOT EXISTS comment_reactions (
    id VARCHAR(36) PRIMARY KEY,
    comment_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    reaction_type ENUM('HELPFUL', 'NOT_HELPFUL') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_comment_reaction (user_id, comment_id),
    INDEX idx_comment_id (comment_id),
    INDEX idx_user_id (user_id)
);

-- Tabela de Revisões de Documentos
CREATE TABLE IF NOT EXISTS document_review (
    id VARCHAR(36) PRIMARY KEY,
    document_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    suggest TEXT,
    decision ENUM('approve', 'reject', 'revision') NOT NULL DEFAULT 'revision',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_document_id (document_id),
    INDEX idx_user_id (user_id),
    INDEX idx_decision (decision)
);

-- Tabela de Histórico de Pontos
CREATE TABLE IF NOT EXISTS user_points_history (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    points INT NOT NULL,
    operation ENUM('gain', 'loss') NOT NULL,
    reference_id VARCHAR(36),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at)
);

-- Tabela de Notificações
CREATE TABLE IF NOT EXISTS notifications (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    icon VARCHAR(50),
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    visibility ENUM('private', 'public') DEFAULT 'private',
    role_target VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_type (type)
);

-- Inserir Categorias Padrão
INSERT INTO categories (id, name, description, icon, allowed_file_types, base_price_kz, requires_review) VALUES
('cat-001', 'Artigo Científico', 'Artigos de pesquisa e publicações científicas', '📄', '["pdf", "doc", "docx"]', 2500.00, TRUE),
('cat-002', 'Trabalho de Conclusão de Curso', 'TCC de graduação e pós-graduação', '🎓', '["pdf", "doc", "docx"]', 5000.00, TRUE),
('cat-003', 'Relatório Técnico', 'Relatórios técnicos e análises', '🧪', '["pdf", "doc", "docx", "xls", "xlsx"]', 3000.00, TRUE),
('cat-004', 'Monografia', 'Monografias e estudos aprofundados', '📘', '["pdf", "doc", "docx"]', 4000.00, TRUE),
('cat-005', 'Dissertação', 'Dissertações de mestrado e doutorado', '📕', '["pdf", "doc", "docx"]', 6000.00, TRUE),
('cat-006', 'Apresentação Acadêmica', 'Slides e apresentações de pesquisa', '📊', '["pdf", "pptx", "odp"]', 1500.00, FALSE),
('cat-007', 'Dataset Científico', 'Conjuntos de dados para pesquisa', '📁', '["csv", "xlsx", "json", "xml"]', 7500.00, TRUE),
('cat-008', 'Outros Documentos', 'Outros documentos académicos', '📚', '["pdf", "doc", "docx", "txt"]', 2000.00, FALSE);

-- Criar usuário admin padrão (senha: admin123)
INSERT INTO users (id, email, password_hash, name, type, balance) VALUES
('user-admin-001', 'admin@petrochamp.ao', '$2y$12$abc123', 'Administrador Sistema', 'ADMIN', 999999.99);
