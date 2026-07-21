-- =====================================================================
--  ⚠️  DEPRECATED — DO NOT RUN THIS FILE AGAINST THE MONOREPO
--
--  This file predates the monorepo refactor and uses the OLD, unprefixed
--  table names (rep_targets, alerts, user_settings, ...). The refactored
--  module's actual schema now lives in database/migrations/ as proper
--  Laravel migrations, with module-specific tables prefixed
--  sales_performance_reporting_* and shared/core tables (users, products,
--  regions, sales_reps, sales_orders) left unprefixed and flagged for
--  cross-team confirmation — see the migration file comments and the
--  refactor CHANGE LOG for details.
--
--  Run `php artisan migrate` instead. Kept here only for historical
--  reference to the pre-monorepo, single-team version of this module.
-- =====================================================================

-- =====================================================================
--  SalesIQ / ULTD — Sales Performance Reporting & Forecasting
--  MariaDB schema + seed data
--
--  Covers every screen currently built:
--    Dashboard | Generate Report | Revenue Forecast | Targets | Alerts
--    Account overlay | Settings overlay
--
--  NOT included yet: Sales Order / Customer Relation / After-Sales
--  Support — those sidebar links aren't wired to real pages yet, so
--  there's nothing concrete to model. Happy to add them once those
--  screens exist.
--
--  A note on the seed numbers: the original Figma mockups used
--  independent placeholder values per widget, so a few screens
--  disagreed with each other (e.g. Luzon showed "at risk" on the
--  Targets page but "exceeded" on Generate Report, and one row had
--  actual > target yet displayed under 100%). Real percentages should
--  be COMPUTED from actual/target, not hardcoded, so the seed data
--  below is normalized to be internally consistent. Swap in real
--  numbers whenever you're ready.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS alert_settings;
DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS forecast_assumptions;
DROP TABLE IF EXISTS monthly_revenue;
DROP TABLE IF EXISTS product_targets;
DROP TABLE IF EXISTS region_targets;
DROP TABLE IF EXISTS rep_targets;
DROP TABLE IF EXISTS sales_orders;
DROP TABLE IF EXISTS sales_reps;
DROP TABLE IF EXISTS user_settings;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS regions;

SET FOREIGN_KEY_CHECKS = 1;

CREATE DATABASE IF NOT EXISTS salesiq CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE salesiq;

-- =====================================================================
-- REFERENCE TABLES
-- =====================================================================

CREATE TABLE regions (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    sku         VARCHAR(30) UNIQUE,
    status      ENUM('active','discontinued') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- USERS  (login accounts — feeds the Account / Settings overlays)
-- =====================================================================

CREATE TABLE users (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(150) NOT NULL,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password            VARCHAR(255) NOT NULL,
    role                ENUM('admin','manager','rep') NOT NULL DEFAULT 'rep',
    region_id           TINYINT UNSIGNED NULL,
    avatar_initials     VARCHAR(4) NULL,
    employee_code       VARCHAR(20) NULL,
    department          VARCHAR(80) NULL,
    plan                VARCHAR(40) NULL DEFAULT 'Pro',
    email_verified_at   TIMESTAMP NULL,
    remember_token      VARCHAR(100) NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- One row per user — backs the Settings overlay toggles
CREATE TABLE user_settings (
    id                          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                     INT UNSIGNED NOT NULL UNIQUE,
    notifications_enabled       BOOLEAN NOT NULL DEFAULT 1,
    dark_mode_enabled           BOOLEAN NOT NULL DEFAULT 0,
    quota_reminders_enabled     BOOLEAN NOT NULL DEFAULT 1,
    updated_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- SALES REPS  (business entity — not every user is a rep, and a rep
-- doesn't strictly need a login yet, hence the nullable user_id)
-- =====================================================================

CREATE TABLE sales_reps (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL UNIQUE,
    name        VARCHAR(150) NOT NULL,
    region_id   TINYINT UNSIGNED NOT NULL,
    hire_date   DATE NULL,
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (region_id) REFERENCES regions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- SALES ORDERS  (the fact table — every dashboard KPI is really just
-- an aggregate query against this table)
-- =====================================================================

CREATE TABLE sales_orders (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_no    VARCHAR(20) NOT NULL UNIQUE,
    rep_id      SMALLINT UNSIGNED NOT NULL,
    product_id  SMALLINT UNSIGNED NOT NULL,
    quantity    INT UNSIGNED NOT NULL DEFAULT 1,
    amount      DECIMAL(14,2) NOT NULL,
    status      ENUM('closed_won','closed_lost','pending') NOT NULL DEFAULT 'closed_won',
    order_date  DATE NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rep_id) REFERENCES sales_reps(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_order_date (order_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- TARGETS / QUOTAS  (backs the Targets page — one table per dimension
-- so each panel is a plain SELECT, no polymorphic joins needed)
-- =====================================================================

CREATE TABLE rep_targets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rep_id          SMALLINT UNSIGNED NOT NULL,
    period          VARCHAR(10) NOT NULL COMMENT 'e.g. 2026-Q2',
    target_amount   DECIMAL(14,2) NOT NULL,
    actual_amount   DECIMAL(14,2) NOT NULL DEFAULT 0 COMMENT 'cached — refresh from sales_orders',
    UNIQUE KEY uq_rep_period (rep_id, period),
    FOREIGN KEY (rep_id) REFERENCES sales_reps(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE region_targets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    region_id       TINYINT UNSIGNED NOT NULL,
    period          VARCHAR(10) NOT NULL,
    target_amount   DECIMAL(14,2) NOT NULL,
    actual_amount   DECIMAL(14,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_region_period (region_id, period),
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_targets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      SMALLINT UNSIGNED NOT NULL,
    period          VARCHAR(10) NOT NULL,
    target_amount   DECIMAL(14,2) NOT NULL,
    actual_amount   DECIMAL(14,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_product_period (product_id, period),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- REVENUE VS FORECAST  (backs the Dashboard chart + Revenue Forecast page)
-- =====================================================================

CREATE TABLE monthly_revenue (
    id                  SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    period_month        DATE NOT NULL UNIQUE COMMENT 'first day of month',
    actual_amount       DECIMAL(14,2) NULL COMMENT 'null once the month has not closed yet',
    forecast_amount     DECIMAL(14,2) NOT NULL,
    is_projected        BOOLEAN NOT NULL DEFAULT 0 COMMENT '1 = still a projection, not yet actualized'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The 3 Revenue Forecast sliders (growth rate / deal close rate /
-- seasonality) save here so the assumptions persist per quarter.
CREATE TABLE forecast_assumptions (
    id                          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    period                      VARCHAR(10) NOT NULL UNIQUE,
    growth_rate_pct             DECIMAL(5,2) NOT NULL DEFAULT 5.00,
    deal_close_rate_pct         DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    seasonality_factor_pct      DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    updated_at                  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- ALERTS
-- =====================================================================

CREATE TABLE alerts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category        ENUM('critical','warning','positive','info') NOT NULL,
    title           VARCHAR(150) NOT NULL,
    description     TEXT NOT NULL,
    link_label      VARCHAR(100) NULL,
    link_url        VARCHAR(255) NULL,
    related_type    ENUM('region','rep','product','forecast','report','model') NULL,
    related_id      INT UNSIGNED NULL COMMENT 'id within related_type — intentionally no FK (polymorphic)',
    is_read         BOOLEAN NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Single-row, org-wide config for the "Alert settings" panel
CREATE TABLE alert_settings (
    id                              TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    target_breach_threshold_pct     TINYINT UNSIGNED NOT NULL DEFAULT 70,
    inventory_trigger_enabled       BOOLEAN NOT NULL DEFAULT 1,
    inventory_trigger_growth_pct    TINYINT UNSIGNED NOT NULL DEFAULT 15,
    inventory_trigger_months        TINYINT UNSIGNED NOT NULL DEFAULT 2,
    forecast_deviation_enabled      BOOLEAN NOT NULL DEFAULT 1,
    forecast_deviation_pct          TINYINT UNSIGNED NOT NULL DEFAULT 10,
    updated_at                      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================================
-- SEED DATA
-- =====================================================================

INSERT INTO regions (id, name) VALUES
    (1, 'NCR'), (2, 'Luzon'), (3, 'Visayas'), (4, 'Mindanao');

INSERT INTO products (id, name, sku) VALUES
    (1, 'EcoLine X2',    'ECO-X2'),
    (2, 'ProSeries 500', 'PRO-500'),
    (3, 'BasicKit',      'BSC-KIT'),
    (4, 'SmartPack Pro', 'SMP-PRO'),
    (5, 'LiteBundle',    'LTE-BND');

-- password hashes below are bcrypt('password') placeholders — replace before go-live
INSERT INTO users (id, name, email, password, role, region_id, avatar_initials, employee_code, department, plan) VALUES
    (1, 'Jordan Reyes', 'jordan.reyes@ultd.com', '$2y$10$examplebcrypthashexamplebcrypthash', 'manager', NULL, 'JR', 'ULTD-0001', 'Sales Operations', 'Pro'),
    (2, 'Charles',       'charles@ultd.com',      '$2y$10$examplebcrypthashexamplebcrypthash', 'rep', 1, 'CH', 'ULTD-0102', 'Sales', 'Standard'),
    (3, 'Bryan',         'bryan@ultd.com',        '$2y$10$examplebcrypthashexamplebcrypthash', 'rep', 3, 'BR', 'ULTD-0103', 'Sales', 'Standard'),
    (4, 'Jade',          'jade@ultd.com',         '$2y$10$examplebcrypthashexamplebcrypthash', 'rep', 4, 'JD', 'ULTD-0104', 'Sales', 'Standard'),
    (5, 'Harvey',        'harvey@ultd.com',       '$2y$10$examplebcrypthashexamplebcrypthash', 'rep', 2, 'HV', 'ULTD-0105', 'Sales', 'Standard'),
    (6, 'Pat',           'pat@ultd.com',          '$2y$10$examplebcrypthashexamplebcrypthash', 'rep', 1, 'PT', 'ULTD-0106', 'Sales', 'Standard');

INSERT INTO user_settings (user_id, notifications_enabled, dark_mode_enabled, quota_reminders_enabled) VALUES
    (1, 1, 0, 1),
    (2, 1, 0, 1),
    (3, 1, 0, 1),
    (4, 1, 0, 1),
    (5, 1, 0, 1),
    (6, 1, 0, 1);

INSERT INTO sales_reps (id, user_id, name, region_id, hire_date, status) VALUES
    (1, 2, 'Charles', 1, '2023-02-01', 'active'),
    (2, 3, 'Bryan',   3, '2023-06-15', 'active'),
    (3, 4, 'Jade',    4, '2022-11-10', 'active'),
    (4, 5, 'Harvey',  2, '2021-09-01', 'active'),
    (5, 6, 'Pat',     1, '2024-01-20', 'active');

-- A representative sample of closed deals (not all 218 — the dashboard's
-- "Closed Deals" count is meant to be COUNT(*) over a full quarter's worth)
INSERT INTO sales_orders (order_no, rep_id, product_id, quantity, amount, status, order_date) VALUES
    ('SO-20260403-01', 1, 1, 12, 84000.00, 'closed_won', '2026-04-03'),
    ('SO-20260408-02', 1, 3, 5,  36000.00, 'closed_won', '2026-04-08'),
    ('SO-20260415-03', 2, 2, 8,  52000.00, 'closed_won', '2026-04-15'),
    ('SO-20260420-04', 3, 4, 10, 64000.00, 'closed_won', '2026-04-20'),
    ('SO-20260502-05', 4, 5, 6,  38000.00, 'closed_won', '2026-05-02'),
    ('SO-20260509-06', 4, 1, 15, 96000.00, 'closed_won', '2026-05-09'),
    ('SO-20260514-07', 5, 3, 4,  27000.00, 'closed_won', '2026-05-14'),
    ('SO-20260521-08', 1, 4, 9,  58000.00, 'closed_won', '2026-05-21'),
    ('SO-20260602-09', 2, 5, 7,  41000.00, 'pending',    '2026-06-02'),
    ('SO-20260607-10', 3, 2, 3,  19000.00, 'closed_lost','2026-06-07');

-- Targets — period 2026-Q2, normalized so actual/target == the displayed %
INSERT INTO rep_targets (rep_id, period, target_amount, actual_amount) VALUES
    (1, '2026-Q2', 1000000.00, 920000.00),  -- Charles  92% on-track
    (2, '2026-Q2', 800000.00,  488000.00),  -- Bryan    61% at-risk
    (3, '2026-Q2', 750000.00,  712500.00),  -- Jade     95% on-track
    (4, '2026-Q2', 600000.00,  648000.00),  -- Harvey  108% exceeded
    (5, '2026-Q2', 700000.00,  560000.00);  -- Pat      80% on-track

INSERT INTO region_targets (region_id, period, target_amount, actual_amount) VALUES
    (1, '2026-Q2', 1700000.00, 1564000.00), -- NCR       92% on-track
    (2, '2026-Q2', 600000.00,  366000.00),  -- Luzon     61% at-risk
    (3, '2026-Q2', 750000.00,  712500.00),  -- Visayas   95% on-track
    (4, '2026-Q2', 700000.00,  756000.00);  -- Mindanao 108% exceeded

INSERT INTO product_targets (product_id, period, target_amount, actual_amount) VALUES
    (1, '2026-Q2', 1100000.00, 1012000.00), -- EcoLine X2     92% on-track
    (2, '2026-Q2', 1000000.00, 610000.00),  -- ProSeries 500  61% at-risk
    (3, '2026-Q2', 800000.00,  760000.00),  -- BasicKit       95% on-track
    (4, '2026-Q2', 600000.00,  648000.00),  -- SmartPack Pro 108% exceeded
    (5, '2026-Q2', 720000.00,  576000.00);  -- LiteBundle     80% on-track

INSERT INTO forecast_assumptions (period, growth_rate_pct, deal_close_rate_pct, seasonality_factor_pct) VALUES
    ('2026-Q2', 5.00, 50.00, 50.00);

-- Revenue vs Forecast chart, Jan–Aug 2026 (Jun = "today" marker on the Forecast page)
INSERT INTO monthly_revenue (period_month, actual_amount, forecast_amount, is_projected) VALUES
    ('2026-01-01', 4000.00,  7500.00,  0),
    ('2026-02-01', 6500.00,  8600.00,  0),
    ('2026-03-01', 5200.00,  8200.00,  0),
    ('2026-04-01', 7000.00,  8000.00,  0),
    ('2026-05-01', 8600.00,  9200.00,  0),
    ('2026-06-01', 8000.00,  12000.00, 0),
    ('2026-07-01', NULL,     17500.00, 1),
    ('2026-08-01', NULL,     19500.00, 1);

INSERT INTO alert_settings (target_breach_threshold_pct, inventory_trigger_enabled, inventory_trigger_growth_pct, inventory_trigger_months, forecast_deviation_enabled, forecast_deviation_pct) VALUES
    (70, 1, 15, 2, 1, 10);

INSERT INTO alerts (category, title, description, link_label, link_url, related_type, related_id, is_read, created_at) VALUES
    ('critical', 'Visayas region below target',   'Attainment at 61% with 12 days left in Q2. Recommend pipeline review and possible budget reallocation.', 'View region report', '/regions/3', 'region',  3, 0, NOW() - INTERVAL 2 HOUR),
    ('critical', 'Rep M. Santos at risk',          'Current attainment 61% with ₱312,000 gap to quota. Last 3 deals stalled at proposal stage.',              'View rep details',   '/reps/2',    'rep',     2, 0, NOW() - INTERVAL 3 HOUR),
    ('warning',  'LiteBundle quota shortfall',     'Product at 89% of quota — ₱82,000 gap. Low close rate this quarter compared to Q1.',                     'View product report','/products/5','product', 5, 1, NOW() - INTERVAL 1 DAY),
    ('warning',  'Q2 close in 12 days',            '₱550,000 of pipeline still unconfirmed. Prioritise high-probability deals to meet EOQ target.',          'View open pipeline', '/pipeline',  NULL,      NULL, 0, NOW() - INTERVAL 1 DAY),
    ('warning',  'BasicKit declining',             'Revenue down 2% vs last quarter. Inventory excess building — consider markdown or bundle promotion.',    'View product report','/products/3','product', 3, 1, NOW() - INTERVAL 2 DAY),
    ('positive', 'EcoLine X2 outperforming',       'Up 22% MoM for 2 consecutive months. Forecast suggests continued growth — recommend stock increase of ~20%.', 'View inventory plan','/products/1/inventory','product', 1, 0, NOW() - INTERVAL 3 DAY),
    ('positive', 'Luzon exceeding quota',          'P. Garcia at 108% attainment. High close rate signals strong regional demand — consider expanding headcount.', 'View region report','/regions/2', 'region',  2, 1, NOW() - INTERVAL 3 DAY),
    ('positive', 'Forecast upgraded',              'EOQ forecast revised to ₱4.6M based on May actuals — 9.5% above Q1 actuals.',                              'View forecast',      '/forecast',  'forecast',NULL, 1, NOW() - INTERVAL 4 DAY),
    ('info',     'Q2 mid-quarter report ready',    'Automated report generated for all regions and reps as of Jun 18, 2026.',                                 'Download report',    '/reports/latest','report', NULL, 1, NOW() - INTERVAL 5 DAY),
    ('info',     'Forecast model updated',         'Historical data refreshed with May close data. Model accuracy improved to 94%.',                          'View model details', '/forecast/model','model',  NULL, 1, NOW() - INTERVAL 6 DAY);


-- =====================================================================
-- EXAMPLE QUERIES — how each screen pulls its data
-- =====================================================================

-- Dashboard: $ Total Revenue (current quarter, closed deals only)
-- SELECT SUM(amount) AS total_revenue
-- FROM sales_orders
-- WHERE status = 'closed_won' AND order_date BETWEEN '2026-04-01' AND '2026-06-30';

-- Dashboard: Closed Deals count
-- SELECT COUNT(*) AS closed_deals
-- FROM sales_orders
-- WHERE status = 'closed_won' AND order_date BETWEEN '2026-04-01' AND '2026-06-30';

-- Dashboard: Target Attainment % (sum of all rep targets for the quarter)
-- SELECT ROUND(SUM(actual_amount) / SUM(target_amount) * 100, 0) AS attainment_pct
-- FROM rep_targets WHERE period = '2026-Q2';

-- Dashboard / Revenue Forecast: Revenue vs Forecast chart
-- SELECT DATE_FORMAT(period_month, '%b') AS month_label, actual_amount, forecast_amount
-- FROM monthly_revenue ORDER BY period_month;

-- Dashboard table (By Representative / By Region / By Product toggle)
-- SELECT sr.name AS rep, r.name AS region, rt.actual_amount, rt.target_amount,
--        ROUND(rt.actual_amount / rt.target_amount * 100) AS pct
-- FROM rep_targets rt
-- JOIN sales_reps sr ON sr.id = rt.rep_id
-- JOIN regions r ON r.id = sr.region_id
-- WHERE rt.period = '2026-Q2';

-- Generate Report: Sales by Rep, filtered by region/rep, compared to forecast
-- SELECT sr.name, r.name AS region, SUM(so.amount) AS actual
-- FROM sales_orders so
-- JOIN sales_reps sr ON sr.id = so.rep_id
-- JOIN regions r ON r.id = sr.region_id
-- WHERE so.status = 'closed_won'
--   AND (:region_filter = 'all' OR r.name = :region_filter)
--   AND (:rep_filter = 'all' OR sr.name = :rep_filter)
-- GROUP BY sr.id;

-- Targets page: Rep / Region / Product Attainment panels
-- SELECT p.name AS product, pt.actual_amount, pt.target_amount,
--        ROUND(pt.actual_amount / pt.target_amount * 100) AS pct
-- FROM product_targets pt JOIN products p ON p.id = pt.product_id
-- WHERE pt.period = '2026-Q2';

-- Targets page: "Reps on Track" KPI (>= 80% attainment)
-- SELECT
--   SUM(actual_amount / target_amount >= 0.8) AS reps_on_track,
--   COUNT(*) AS total_reps
-- FROM rep_targets WHERE period = '2026-Q2';

-- Alerts page: summary counts
-- SELECT category, COUNT(*) AS total, SUM(is_read = 0) AS unread
-- FROM alerts GROUP BY category;

-- Alerts page: sidebar badge (unread count)
-- SELECT COUNT(*) AS unread FROM alerts WHERE is_read = 0;

-- Alerts page: filtered list with human-readable "time ago" (app-layer format, MariaDB gives raw timestamp)
-- SELECT category, title, description, link_label, link_url, is_read, created_at
-- FROM alerts
-- WHERE (:filter = 'all' OR category = :filter)
-- ORDER BY created_at DESC;

-- Revenue Forecast sliders: load saved assumptions, then recompute client-side
-- SELECT growth_rate_pct, deal_close_rate_pct, seasonality_factor_pct
-- FROM forecast_assumptions WHERE period = '2026-Q2';

-- Account overlay
-- SELECT u.name, u.email, u.role, u.avatar_initials, u.employee_code, u.department, u.plan, r.name AS region
-- FROM users u LEFT JOIN regions r ON r.id = u.region_id
-- WHERE u.id = :current_user_id;

-- Settings overlay
-- SELECT notifications_enabled, dark_mode_enabled, quota_reminders_enabled
-- FROM user_settings WHERE user_id = :current_user_id;
