DELETE FROM minerva_currencies WHERE  invoice_id IN (
SELECT minerva_clients.invoice_id
FROM minerva_clients
LEFT JOIN minerva_invoices ON ( minerva_invoices.invoice_id = minerva_clients.invoice_id )
WHERE minerva_invoices.invoice_id IS NULL
)

DELETE FROM minerva_taxes WHERE invoice_id IN (
SELECT minerva_clients.invoice_id
FROM minerva_clients
LEFT JOIN minerva_invoices ON ( minerva_invoices.invoice_id = minerva_clients.invoice_id )
WHERE minerva_invoices.invoice_id IS NULL
)

DELETE FROM minerva_articles WHERE invoice_id IN (
SELECT minerva_clients.invoice_id
FROM minerva_clients
LEFT JOIN minerva_invoices ON ( minerva_invoices.invoice_id = minerva_clients.invoice_id )
WHERE minerva_invoices.invoice_id IS NULL
)

DELETE FROM minerva_clients WHERE invoice_id IN (
SELECT minerva_clients_bak.invoice_id
FROM minerva_clients_bak
LEFT JOIN minerva_invoices ON ( minerva_invoices.invoice_id = minerva_clients_bak.invoice_id )
WHERE minerva_invoices.invoice_id IS NULL
)

DROP TABLE `minerva_clients_bak`