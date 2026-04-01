<?php




/*
═══════════════════════════════════════════════════════════════════
  SQL — SCHEMA COMPLETO
  Executar uma vez para criar todas as tabelas:
  mysql -u root petropub < config.php  (ou via phpMyAdmin)
═══════════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS petropub
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE petropub;

-- ── SECÇÕES DO PORTAL ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sections (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(80)  NOT NULL UNIQUE,
    name        VARCHAR(120) NOT NULL,
    description TEXT,
    icon        VARCHAR(10)  NOT NULL DEFAULT '📂',
    color       VARCHAR(40)  DEFAULT '#6B1020',
    sort_order  SMALLINT     DEFAULT 0,
    is_active   TINYINT(1)   DEFAULT 1,
    show_home   TINYINT(1)   DEFAULT 1,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO sections (slug, name, icon, color, sort_order) VALUES
  ('oportunidades',  'Oportunidades & Recursos',  '⛽', '#6B1020', 1),
  ('destaques',      'Conteúdo em Destaque',       '🔥', '#1A5C8A', 2),
  ('categorias',     'Categorias',                 '📂', '#2D7A4F', 3),
  ('avisos',         'Avisos & Novidades',         '📢', '#C47A1A', 4),
  ('tendencias',     'Tendências',                 '📈', '#5A3A8A', 5);

-- ── OPORTUNIDADES (Oil & Gas) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS opportunities (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type        ENUM('Curso','Equipamento','Evento','Vaga') NOT NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    source      VARCHAR(120),
    icon        VARCHAR(10)  DEFAULT '📌',
    link_url    VARCHAR(500),
    image_path  VARCHAR(500),
    grad_start  VARCHAR(20)  DEFAULT '#4A0B16',
    grad_end    VARCHAR(20)  DEFAULT '#8C1A2E',
    location    VARCHAR(120),
    event_date  DATE,
    is_active   TINYINT(1)   DEFAULT 1,
    is_featured TINYINT(1)   DEFAULT 0,
    sort_order  SMALLINT     DEFAULT 0,
    views       INT UNSIGNED DEFAULT 0,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO opportunities (type,title,description,source,icon,grad_start,grad_end,location) VALUES
  ('Curso',       'Engenharia de Petróleo — Curso Online',         'Formação completa em engenharia de reservatórios com certificado reconhecido.',         'Sonangol EP',      '🎓','#1A3860','#1A5C8A',NULL),
  ('Equipamento', 'Catálogo de Equipamentos de Perfuração',        'Lista actualizada de fornecedores de equipamentos para perfuração offshore em Angola.', 'Angola LNG',       '⚙️','#4A0B16','#8C1A2E',NULL),
  ('Evento',      'Conferência de Energia — Luanda 2025',          'O maior evento do sector energético angolano. Junho 2025.',                             'ANPG',             '🗓️','#1A4A2E','#2D7A4F','Luanda'),
  ('Vaga',        'Técnico de Manutenção — Offshore',              'Oportunidade para técnicos sénior em plataformas offshore. Experiência mínima 3 anos.', 'TotalEnergies AO', '💼','#5A3A00','#C47A1A',NULL),
  ('Curso',       'Segurança Industrial em Instalações Petrolíferas','Formação certificada NEBOSH. Inscrições abertas para Março e Abril.',                 'IFP Training',     '📚','#2C1A4A','#5A3A8A',NULL),
  ('Evento',      'Workshop: Transição Energética em África',      'Debate sobre energias renováveis no contexto africano.',                                'SADC Energy',      '🔬','#1A3820','#2D7A4F','Luanda');

-- ── AVISOS ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notices (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    icon        VARCHAR(10)  DEFAULT '📢',
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    link_url    VARCHAR(500),
    type        ENUM('info','success','warning','update') DEFAULT 'info',
    is_active   TINYINT(1)   DEFAULT 1,
    sort_order  SMALLINT     DEFAULT 0,
    published_at DATETIME    DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO notices (icon,title,description,type) VALUES
  ('🆕','Mais de 120 novos documentos esta semana','TCCs do ISPTEC e dissertações da UAN foram recentemente adicionados ao acervo.','success'),
  ('⚙️','Melhorias na pesquisa avançada','O motor de pesquisa foi actualizado com filtros por área e ano de publicação.','update'),
  ('🎓','Parceria com a Universidade Agostinho Neto','Novo acordo para disponibilização de mais 400 documentos académicos da UAN.','info'),
  ('💎','Programa de Pontos & Recompensas activo','Ganhe pontos por cada download, avaliação ou publicação.','info');

-- ── SECÇÃO DESTAQUES ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS featured_docs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tab         ENUM('popular','recent','recommended') DEFAULT 'popular',
    doc_id      INT UNSIGNED,
    title       VARCHAR(200) NOT NULL,
    author      VARCHAR(120),
    institution VARCHAR(80),
    type        VARCHAR(40),
    icon        VARCHAR(10)  DEFAULT '📄',
    bg_color    VARCHAR(40)  DEFAULT 'hsl(200,55%,92%)',
    rating      DECIMAL(3,1) DEFAULT 4.0,
    price       INT UNSIGNED DEFAULT 0,
    is_active   TINYINT(1)   DEFAULT 1,
    sort_order  SMALLINT     DEFAULT 0,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── AUDIT LOG ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_log (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT UNSIGNED,
    action      VARCHAR(80)  NOT NULL,
    table_name  VARCHAR(60),
    record_id   INT UNSIGNED,
    old_data    JSON,
    new_data    JSON,
    ip          VARCHAR(45),
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

*/
?>
