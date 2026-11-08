-- SQL Server schema for diagram only. No data inserted.
-- MySQL unsigned types mapped to SQL Server integer types.
-- All FK actions use NO ACTION to avoid multiple cascade paths.
-- MySQL ON UPDATE timestamps are omitted (diagram only).
USE [master];
GO
IF DB_ID(N'olist_db_diagram') IS NULL
    EXEC(N'CREATE DATABASE [olist_db_diagram]');
GO
USE [olist_db_diagram];
GO

CREATE TABLE [dbo].[association_rules] (
    [rule_id] int IDENTITY(1,1) NOT NULL,
    [user_id] int NOT NULL,
    [antecedent_category_name] varchar(100) NOT NULL,
    [consequent_category_name] varchar(100) NOT NULL,
    [support] decimal(20,12) DEFAULT NULL,
    [confidence] decimal(20,12) DEFAULT NULL,
    [lift] decimal(30,12) DEFAULT NULL,
    [created_at] datetime2 NULL DEFAULT CURRENT_TIMESTAMP,
    [dataset_id] bigint NOT NULL,
    [pair_count] bigint DEFAULT NULL,
    PRIMARY KEY ([rule_id])
);
GO

CREATE INDEX [fk_rules_user_ref] ON [dbo].[association_rules] ([user_id]);

CREATE INDEX [idx_rules_dataset_antecedent] ON [dbo].[association_rules] ([dataset_id],[antecedent_category_name]);

CREATE INDEX [idx_rules_dataset_consequent] ON [dbo].[association_rules] ([dataset_id],[consequent_category_name]);

CREATE TABLE [dbo].[bundle_recommendations] (
    [bundle_id] int IDENTITY(1,1) NOT NULL,
    [user_id] int NOT NULL,
    [main_product_id] varchar(50) DEFAULT NULL,
    [related_product_id] varchar(50) DEFAULT NULL,
    [confidence] decimal(20,12) DEFAULT NULL,
    [lift] decimal(30,12) DEFAULT NULL,
    [discount_rate] decimal(5,2) DEFAULT NULL,
    [expected_revenue] decimal(12,2) DEFAULT NULL,
    [status] varchar(30) DEFAULT 'active' CHECK ([status] IN ('active','inactive')),
    [created_at] datetime2 NULL DEFAULT CURRENT_TIMESTAMP,
    [dataset_id] bigint NOT NULL,
    [main_category_name] varchar(100) DEFAULT NULL,
    [related_category_name] varchar(100) DEFAULT NULL,
    [support] decimal(20,12) DEFAULT NULL,
    [pair_count] bigint DEFAULT NULL,
    [rank] tinyint DEFAULT NULL,
    PRIMARY KEY ([bundle_id])
);
GO

CREATE INDEX [fk_bundles_user_ref] ON [dbo].[bundle_recommendations] ([user_id]);

CREATE INDEX [fk_bundles_main_ref] ON [dbo].[bundle_recommendations] ([main_product_id]);

CREATE INDEX [fk_bundles_related_ref] ON [dbo].[bundle_recommendations] ([related_product_id]);

CREATE INDEX [fk_bundles_main_ref_dataset] ON [dbo].[bundle_recommendations] ([dataset_id],[main_product_id]);

CREATE INDEX [fk_bundles_related_ref_dataset] ON [dbo].[bundle_recommendations] ([dataset_id],[related_product_id]);

CREATE TABLE [dbo].[customers] (
    [customer_id] varchar(50) NOT NULL,
    [customer_unique_id] varchar(50) NOT NULL,
    [customer_zip_code_prefix] int DEFAULT NULL,
    [customer_city] varchar(100) DEFAULT NULL,
    [customer_state] char(2) DEFAULT NULL,
    [dataset_id] bigint NOT NULL,
    PRIMARY KEY ([dataset_id],[customer_id])
);
GO

CREATE TABLE [dbo].[customer_segments] (
    [dataset_id] bigint NOT NULL,
    [analysis_date] date NOT NULL,
    [customer_unique_id] varchar(50) NOT NULL,
    [last_purchase] date NOT NULL,
    [recency] int NOT NULL,
    [frequency] bigint NOT NULL,
    [monetary] decimal(20,2) NOT NULL,
    [r_score] tinyint NOT NULL,
    [f_score] tinyint NOT NULL,
    [m_score] tinyint NOT NULL,
    [segment] varchar(20) NOT NULL,
    [analyzed_at] datetime2 NOT NULL,
    PRIMARY KEY ([dataset_id],[analysis_date],[customer_unique_id])
);
GO

CREATE INDEX [customer_segments_group_index] ON [dbo].[customer_segments] ([dataset_id],[analysis_date],[segment]);

CREATE TABLE [dbo].[datasets] (
    [dataset_id] bigint IDENTITY(1,1) NOT NULL,
    [user_id] int DEFAULT NULL,
    [name] varchar(150) NOT NULL,
    [description] nvarchar(max),
    [created_at] datetime2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
    [updated_at] datetime2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
    [rules_version] bigint NOT NULL DEFAULT '0',
    [rules_filters] nvarchar(max) DEFAULT NULL,
    [recommendation_context] nvarchar(max) DEFAULT NULL,
    PRIMARY KEY ([dataset_id])
);
GO

CREATE INDEX [fk_datasets_user] ON [dbo].[datasets] ([user_id]);

CREATE TABLE [dbo].[import_logs] (
    [import_id] bigint IDENTITY(1,1) NOT NULL,
    [dataset_id] bigint NOT NULL,
    [uploaded_by] int NOT NULL,
    [data_type] varchar(30) NOT NULL CHECK ([data_type] IN ('products','customers','orders','order_items','order_payments','order_reviews')),
    [original_filename] varchar(255) NOT NULL,
    [stored_path] varchar(500) NOT NULL,
    [status] varchar(30) NOT NULL DEFAULT 'uploaded' CHECK ([status] IN ('uploaded','validating','importing','completed','failed')),
    [total_rows] int NOT NULL DEFAULT '0',
    [success_rows] int NOT NULL DEFAULT '0',
    [failed_rows] int NOT NULL DEFAULT '0',
    [skipped_rows] int NOT NULL DEFAULT '0',
    [error_message] nvarchar(max),
    [error_file_path] varchar(500) DEFAULT NULL,
    [started_at] datetime2 DEFAULT NULL,
    [finished_at] datetime2 DEFAULT NULL,
    [created_at] datetime2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
    [updated_at] datetime2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ([import_id])
);
GO

CREATE INDEX [idx_import_dataset_created] ON [dbo].[import_logs] ([dataset_id],[created_at]);

CREATE INDEX [fk_import_uploader] ON [dbo].[import_logs] ([uploaded_by]);

CREATE TABLE [dbo].[migrations] (
    [id] int IDENTITY(1,1) NOT NULL,
    [migration] varchar(255) NOT NULL,
    [batch] int NOT NULL,
    PRIMARY KEY ([id])
);
GO

CREATE TABLE [dbo].[orders] (
    [order_id] varchar(50) NOT NULL,
    [customer_id] varchar(50) NOT NULL,
    [order_status] varchar(30) DEFAULT NULL,
    [order_approved_at] datetime2 DEFAULT NULL,
    [order_delivered_carrier_date] datetime2 DEFAULT NULL,
    [order_delivered_customer_date] datetime2 DEFAULT NULL,
    [order_estimated_delivery_date] datetime2 DEFAULT NULL,
    [order_purchase_timestamp] datetime2 DEFAULT NULL,
    [dataset_id] bigint NOT NULL,
    PRIMARY KEY ([dataset_id],[order_id])
);
GO

CREATE INDEX [fk_orders_customer_ref] ON [dbo].[orders] ([customer_id]);

CREATE INDEX [fk_orders_customer_ref_dataset] ON [dbo].[orders] ([dataset_id],[customer_id]);

CREATE TABLE [dbo].[order_items] (
    [order_id] varchar(50) NOT NULL,
    [order_item_id] int NOT NULL,
    [product_id] varchar(50) DEFAULT NULL,
    [shipping_limit_date] datetime2 DEFAULT NULL,
    [price] decimal(10,2) DEFAULT NULL,
    [freight_value] decimal(10,2) DEFAULT NULL,
    [dataset_id] bigint NOT NULL,
    PRIMARY KEY ([dataset_id],[order_id],[order_item_id])
);
GO

CREATE INDEX [fk_items_product_ref] ON [dbo].[order_items] ([product_id]);

CREATE INDEX [fk_items_product_ref_dataset] ON [dbo].[order_items] ([dataset_id],[product_id]);

CREATE TABLE [dbo].[order_payments] (
    [dataset_id] bigint NOT NULL,
    [order_id] varchar(50) NOT NULL,
    [payment_sequential] int NOT NULL,
    [payment_type] varchar(30) DEFAULT NULL,
    [payment_installments] int DEFAULT NULL,
    [payment_value] decimal(10,2) DEFAULT NULL,
    PRIMARY KEY ([dataset_id],[order_id],[payment_sequential])
);
GO

CREATE TABLE [dbo].[products] (
    [product_id] varchar(50) NOT NULL,
    [product_category_name] varchar(100) DEFAULT NULL,
    [product_name_length] int DEFAULT NULL,
    [product_description_length] int DEFAULT NULL,
    [product_photos_qty] int DEFAULT NULL,
    [product_weight_g] decimal(10,2) DEFAULT NULL,
    [product_length_cm] decimal(10,2) DEFAULT NULL,
    [product_height_cm] decimal(10,2) DEFAULT NULL,
    [product_width_cm] decimal(10,2) DEFAULT NULL,
    [dataset_id] bigint NOT NULL,
    PRIMARY KEY ([dataset_id],[product_id])
);
GO

CREATE INDEX [fk_product_category] ON [dbo].[products] ([product_category_name]);

CREATE TABLE [dbo].[sessions] (
    [id] varchar(191) NOT NULL,
    [user_id] int DEFAULT NULL,
    [ip_address] varchar(45) DEFAULT NULL,
    [user_agent] nvarchar(max),
    [payload] nvarchar(max) NOT NULL,
    [last_activity] int NOT NULL,
    PRIMARY KEY ([id])
);
GO

CREATE INDEX [sessions_user_id_index] ON [dbo].[sessions] ([user_id]);

CREATE INDEX [sessions_last_activity_index] ON [dbo].[sessions] ([last_activity]);

CREATE TABLE [dbo].[users] (
    [user_id] int IDENTITY(1,1) NOT NULL,
    [shop_name] varchar(150) NOT NULL,
    [owner_name] varchar(100) DEFAULT NULL,
    [email] varchar(120) NOT NULL,
    [password_hash] varchar(255) NOT NULL,
    [created_at] datetime2 NULL DEFAULT CURRENT_TIMESTAMP,
    [remember_token] varchar(100) DEFAULT NULL,
    PRIMARY KEY ([user_id]),
    CONSTRAINT [email] UNIQUE ([email])
);
GO

ALTER TABLE [dbo].[association_rules] ADD CONSTRAINT [association_rules_dataset_id_foreign] FOREIGN KEY ([dataset_id]) REFERENCES [datasets] ([dataset_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[association_rules] ADD CONSTRAINT [fk_rules_user_ref] FOREIGN KEY ([user_id]) REFERENCES [users] ([user_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[bundle_recommendations] ADD CONSTRAINT [bundle_recommendations_dataset_id_foreign] FOREIGN KEY ([dataset_id]) REFERENCES [datasets] ([dataset_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[bundle_recommendations] ADD CONSTRAINT [fk_bundles_main_ref_dataset] FOREIGN KEY ([dataset_id],[main_product_id]) REFERENCES [products] ([dataset_id], [product_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[bundle_recommendations] ADD CONSTRAINT [fk_bundles_related_ref_dataset] FOREIGN KEY ([dataset_id],[related_product_id]) REFERENCES [products] ([dataset_id], [product_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[bundle_recommendations] ADD CONSTRAINT [fk_bundles_user_ref] FOREIGN KEY ([user_id]) REFERENCES [users] ([user_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[customers] ADD CONSTRAINT [customers_dataset_id_foreign] FOREIGN KEY ([dataset_id]) REFERENCES [datasets] ([dataset_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[customer_segments] ADD CONSTRAINT [customer_segments_dataset_foreign] FOREIGN KEY ([dataset_id]) REFERENCES [datasets] ([dataset_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[datasets] ADD CONSTRAINT [fk_datasets_user] FOREIGN KEY ([user_id]) REFERENCES [users] ([user_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[import_logs] ADD CONSTRAINT [fk_import_dataset] FOREIGN KEY ([dataset_id]) REFERENCES [datasets] ([dataset_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[import_logs] ADD CONSTRAINT [fk_import_uploader] FOREIGN KEY ([uploaded_by]) REFERENCES [users] ([user_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[orders] ADD CONSTRAINT [fk_orders_customer_ref_dataset] FOREIGN KEY ([dataset_id],[customer_id]) REFERENCES [customers] ([dataset_id], [customer_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[orders] ADD CONSTRAINT [orders_dataset_id_foreign] FOREIGN KEY ([dataset_id]) REFERENCES [datasets] ([dataset_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[order_items] ADD CONSTRAINT [fk_items_order_ref_dataset] FOREIGN KEY ([dataset_id],[order_id]) REFERENCES [orders] ([dataset_id], [order_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[order_items] ADD CONSTRAINT [fk_items_product_ref_dataset] FOREIGN KEY ([dataset_id],[product_id]) REFERENCES [products] ([dataset_id], [product_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[order_items] ADD CONSTRAINT [order_items_dataset_id_foreign] FOREIGN KEY ([dataset_id]) REFERENCES [datasets] ([dataset_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[order_payments] ADD CONSTRAINT [order_payments_dataset_id_foreign] FOREIGN KEY ([dataset_id]) REFERENCES [datasets] ([dataset_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[order_payments] ADD CONSTRAINT [order_payments_dataset_id_order_id_foreign] FOREIGN KEY ([dataset_id],[order_id]) REFERENCES [orders] ([dataset_id], [order_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE [dbo].[products] ADD CONSTRAINT [products_dataset_id_foreign] FOREIGN KEY ([dataset_id]) REFERENCES [datasets] ([dataset_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;
GO

ALTER TABLE [dbo].[sessions] ADD CONSTRAINT [fk_sessions_user] FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([user_id]) ON DELETE NO ACTION ON UPDATE NO ACTION;
GO
