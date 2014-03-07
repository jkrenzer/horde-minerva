-- You can simply execute this file in your database.
--
-- For MySQL run:
--
-- $ mysql --user=root --password=<MySQL-root-password> <db name> < 2006-05-29_rename.sql
--
-- Or, for PostgreSQL:
--
-- $ psql <db name> -f 2006-05-29_rename.sql

ALTER TABLE proforma_articles RENAME minerva_articles;
ALTER TABLE proforma_clients RENAME minerva_clients;
ALTER TABLE proforma_currencies RENAME minerva_currencies;
ALTER TABLE proforma_emails RENAME minerva_emails;
ALTER TABLE proforma_invoices RENAME minerva_invoices;
ALTER TABLE proforma_invoices_seq RENAME minerva_invoices_seq;
ALTER TABLE proforma_locked RENAME minerva_locked;
ALTER TABLE proforma_resellers RENAME minerva_resellers;
ALTER TABLE proforma_statuses RENAME minerva_statuses;
ALTER TABLE proforma_taxes RENAME minerva_taxes;
ALTER TABLE proforma_types RENAME minerva_types;
ALTER TABLE proforma_versions RENAME minerva_versions;
ALTER TABLE proforma_klishe RENAME minerva_klishe;

