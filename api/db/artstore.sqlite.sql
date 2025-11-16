BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "artworks" (
	"id"	INTEGER,
	"title"	TEXT,
	"description"	TEXT,
	"price"	REAL,
	"image"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "invoice_logs" (
	"id"	INTEGER,
	"invoice_id"	INTEGER,
	"sent_at"	TEXT,
	"status"	TEXT,
	"response"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT),
	FOREIGN KEY("invoice_id") REFERENCES "invoices"("id")
);
CREATE TABLE IF NOT EXISTS "invoices" (
	"id"	INTEGER,
	"order_id"	INTEGER UNIQUE,
	"invoice_number"	TEXT UNIQUE,
	"issued_at"	TEXT DEFAULT CURRENT_TIMESTAMP,
	"customer_name"	TEXT,
	"customer_email"	TEXT,
	"item_title"	TEXT,
	"item_price"	REAL,
	"total"	REAL,
	"valid"	INTEGER DEFAULT 1,
	"provider_response"	TEXT,
	"provider_invoice_id"	TEXT,
	"status"	TEXT DEFAULT 'pending',
	"timestamp_sent"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT),
	FOREIGN KEY("order_id") REFERENCES "orders"("id")
);
CREATE TABLE IF NOT EXISTS "order_items" (
	"id"	INTEGER,
	"order_id"	INTEGER,
	"artwork_id"	INTEGER,
	"quantity"	INTEGER DEFAULT 1,
	"price"	REAL,
	PRIMARY KEY("id" AUTOINCREMENT),
	FOREIGN KEY("artwork_id") REFERENCES "artworks"("id"),
	FOREIGN KEY("order_id") REFERENCES "orders"("id")
);
CREATE TABLE IF NOT EXISTS "orders" (
	"id"	INTEGER,
	"customer_name"	TEXT,
	"customer_email"	TEXT,
	"items"	TEXT,
	"total"	REAL,
	"created_at"	DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("id" AUTOINCREMENT)
);
INSERT INTO "artworks" VALUES (1,'Landscape1','pen in paper',120.0,'face.jpg');
INSERT INTO "artworks" VALUES (2,'Portrait1','pen in paper',112.0,'firebird.jpg');
INSERT INTO "artworks" VALUES (3,'Portrait2','pencil in paper',112.0,'HamletWithSkull.jpg');
INSERT INTO "artworks" VALUES (4,'Abstract1','pen,posca and pencil in paper',110.0,'mermaids.jpg');
INSERT INTO "artworks" VALUES (5,'Axis Mundi','pencil on paper',100.0,'monster.jpg');
INSERT INTO "artworks" VALUES (6,'Room in the evenung','marker and pencil in paper',120.0,'poseidon.jpg');
INSERT INTO "invoice_logs" VALUES (1,1,'2025-11-02 12:01:44','created','Invoice generated successfully');
INSERT INTO "invoice_logs" VALUES (2,2,'2025-11-02 13:34:07','created','Invoice generated successfully');
INSERT INTO "invoice_logs" VALUES (3,3,'2025-11-02 13:34:22','created','Invoice generated successfully');
INSERT INTO "invoice_logs" VALUES (4,4,'2025-11-02 13:51:10','created','Invoice generated successfully');
INSERT INTO "invoice_logs" VALUES (5,5,'2025-11-02 13:53:42','created','Invoice generated successfully');
INSERT INTO "invoices" VALUES (1,5,'INV-2025-000005','2025-11-02 11:01:43','Amanda Alexopoulou','am.alexopoulou@gmail.com',NULL,NULL,220.0,1,NULL,NULL,'pending',NULL);
INSERT INTO "invoices" VALUES (2,6,'INV-2025-000006','2025-11-02 12:34:06','Amanda Alexopoulou','am.alexopoulou@gmail.com',NULL,NULL,330.0,1,NULL,NULL,'pending',NULL);
INSERT INTO "invoices" VALUES (3,7,'INV-2025-000007','2025-11-02 12:34:21','Amanda Alexopoulou','am.alexopoulou@gmail.com',NULL,NULL,220.0,1,NULL,NULL,'pending',NULL);
INSERT INTO "invoices" VALUES (4,8,'INV-2025-000008','2025-11-02 12:51:08','Amanda Alexopoulou','am.alexopoulou@gmail.com',NULL,NULL,220.0,1,NULL,NULL,'pending',NULL);
INSERT INTO "invoices" VALUES (5,9,'INV-2025-000009','2025-11-02 12:53:42','Amanda Alexopoulou','am.alexopoulou@gmail.com',NULL,NULL,220.0,1,NULL,NULL,'pending',NULL);
INSERT INTO "order_items" VALUES (1,5,5,1,100.0);
INSERT INTO "order_items" VALUES (2,5,6,1,120.0);
INSERT INTO "order_items" VALUES (3,6,6,1,120.0);
INSERT INTO "order_items" VALUES (4,6,5,1,100.0);
INSERT INTO "order_items" VALUES (5,6,4,1,110.0);
INSERT INTO "order_items" VALUES (6,7,6,1,120.0);
INSERT INTO "order_items" VALUES (7,7,5,1,100.0);
INSERT INTO "order_items" VALUES (8,8,6,1,120.0);
INSERT INTO "order_items" VALUES (9,8,5,1,100.0);
INSERT INTO "order_items" VALUES (10,9,6,1,120.0);
INSERT INTO "order_items" VALUES (11,9,5,1,100.0);
INSERT INTO "orders" VALUES (1,'Maria Pappa','m.pappa@gmail.com','1',120.0,'2025-10-08 16:29:42');
INSERT INTO "orders" VALUES (2,'Nikos Kollias','nkl@gmail.com','2',112.0,'2025-10-08 16:32:07');
INSERT INTO "orders" VALUES (3,'Amanda Alexopoulou','am.alexopoulou@gmail.com','1',112.0,'2025-10-08 16:33:44');
INSERT INTO "orders" VALUES (4,'Katerina Alexopoulou','kat.alex@gmail.com','1',120.0,'2025-10-08 16:34:35');
INSERT INTO "orders" VALUES (5,'Amanda Alexopoulou','am.alexopoulou@gmail.com','[{"id":5,"title":"Axis Mundi","price":100,"quantity":1},{"id":6,"title":"Room in the evenung","price":120,"quantity":1}]',220.0,'2025-11-02 11:01:43');
INSERT INTO "orders" VALUES (6,'Amanda Alexopoulou','am.alexopoulou@gmail.com','[{"id":6,"title":"Room in the evenung","price":120,"quantity":1},{"id":5,"title":"Axis Mundi","price":100,"quantity":1},{"id":4,"title":"Abstract1","price":110,"quantity":1}]',330.0,'2025-11-02 12:34:06');
INSERT INTO "orders" VALUES (7,'Amanda Alexopoulou','am.alexopoulou@gmail.com','[{"id":6,"title":"Room in the evenung","price":120,"quantity":1},{"id":5,"title":"Axis Mundi","price":100,"quantity":1}]',220.0,'2025-11-02 12:34:21');
INSERT INTO "orders" VALUES (8,'Amanda Alexopoulou','am.alexopoulou@gmail.com','[{"id":6,"title":"Room in the evenung","price":120,"quantity":1},{"id":5,"title":"Axis Mundi","price":100,"quantity":1}]',220.0,'2025-11-02 12:51:08');
INSERT INTO "orders" VALUES (9,'Amanda Alexopoulou','am.alexopoulou@gmail.com','[{"id":6,"title":"Room in the evenung","price":120,"quantity":1},{"id":5,"title":"Axis Mundi","price":100,"quantity":1}]',220.0,'2025-11-02 12:53:42');
CREATE VIEW invoices_summary AS
SELECT 
    COUNT(*) AS total_invoices,
    SUM(total) AS total_sales,
    SUM(CASE WHEN status='sent' THEN 1 ELSE 0 END) AS sent_count,
    SUM(CASE WHEN status!='sent' THEN 1 ELSE 0 END) AS pending_count
FROM invoices;
COMMIT;
