-- $Horde: incubator/minerva/scripts/sql/minerva.sql,v 1.14 2008/07/10 08:06:33 duck Exp $

CREATE TABLE minerva_articles (
  invoice_id INT NOT NULL,
  article_id VARCHAR(255) NOT NULL,
  article_order INT NOT NULL DEFAULT 0,
  article_name VARCHAR(255) NOT NULL,
  article_price FLOAT NOT NULL,
  article_qt FLOAT NOT NULL,
  article_discount FLOAT NOT NULL,
  article_tax INT NOT NULL,
  article_total FLOAT NOT NULL
);

CREATE INDEX minerva_articles_invoice_idx ON minerva_articles (invoice_id);

CREATE TABLE minerva_banks (
  account VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  sort INT NOT NULL,
  updated INT NOT NULL,
  created INT NOT NULL,
--
  PRIMARY KEY  (account)
);

CREATE INDEX minerva_banks_sort_idx ON minerva_banks (sort);

CREATE TABLE minerva_clients (
  invoice_id INT NOT NULL,
  id VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  postal_address VARCHAR(255) NOT NULL,
  vat VARCHAR(255) NOT NULL,
  obligated INT NOT NULL,
--
  PRIMARY KEY  (invoice_id)
);

CREATE TABLE minerva_currencies (
  invoice_id INT NOT NULL,
  int_curr_symbol CHAR(3) NOT NULL,
  exchange_rate FLOAT NOT NULL,
  decimal_poINT CHAR(1) DEFAULT NULL,
  thousands_sep CHAR(1) DEFAULT NULL,
  currency_symbol VARCHAR(3),
  mon_decimal_poINT CHAR(1) DEFAULT NULL,
  mon_thousands_sep CHAR(1) DEFAULT NULL,
  positive_sign CHAR(1) DEFAULT NULL,
  negative_sign CHAR(1) DEFAULT NULL,
  int_frac_digits INT(3) DEFAULT '0' NOT NULL,
  frac_digits INT(3) DEFAULT '0' NOT NULL,
  p_cs_precedes INT DEFAULT '0' NOT NULL,
  p_sep_by_space INT DEFAULT '0' NOT NULL,
  n_cs_precedes INT DEFAULT '0' NOT NULL,
  n_sep_by_space INT DEFAULT '0' NOT NULL,
  p_sign_posn INT DEFAULT '0' NOT NULL,
  n_sign_posn INT DEFAULT '0' NOT NULL,
  updated INT,
--
  PRIMARY KEY  (int_curr_symbol, invoice_id)
);

CREATE INDEX minerva_currencies_rate_idx ON minerva_currencies (exchange_rate);

CREATE TABLE minerva_emails (
  id INT NOT NULL auto_increment,
  name VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  updated INT NOT NULL,
  subject VARCHAR(255) NOT NULL,
  created INT NOT NULL,
--
  PRIMARY KEY  (id)
);

CREATE TABLE minerva_tags (
  id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  created INT NOT NULL,
  updated INT NOT NULL,
--
  PRIMARY KEY  (id)
);

CREATE TABLE minerva_invoices (
  invoice_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  date DATE NOT NULL,
  expire INT(100) NOT NULL,
  type VARCHAR(32) NOT NULL,
  service VARCHAR(255) NOT NULL,
  total FLOAT NOT NULL,
  tax FLOAT NOT NULL,
  status VARCHAR(255)  DEFAULT 'pending' NOT NULL,
  place VARCHAR(255) NOT NULL,
  updated INT NOT NULL,
  comment VARCHAR(255) NOT NULL,
  tag INT NOT NULL DEFAULT 1,
--
  PRIMARY KEY  (invoice_id)
);

CREATE UNIQUE INDEX minerva_invoices_name_idx ON minerva_invoices (name, type);
CREATE INDEX minerva_invoices_status_idx ON minerva_invoices (status);
CREATE INDEX minerva_invoices_date_idx ON minerva_invoices (date);
CREATE INDEX minerva_invoices_type_idx ON minerva_invoices (type);

CREATE TABLE minerva_items (
  id int NOT NULL auto_increment,
  model varchar(255) NOT NULL,
  name varchar(255) NOT NULL,
  price float  NOT NULL,
  discount float NOT NULL,
  tax int NOT NULL,
--
  PRIMARY KEY (id)
);

CREATE TABLE minerva_log (
  log_id INT NOT NULL,
  invoice_id INT NOT NULL,
  horde_uid VARCHAR(32) NOT NULL,
  log_time INT NOT NULL,
  log_host VARCHAR(50) NOT NULL,
  log_type VARCHAR(32) NOT NULL,
  log_data text(32) NOT NULL,
--
  PRIMARY KEY  (log_id)
);

CREATE INDEX minerva_log_invoice_idx ON minerva_log (invoice_id);

CREATE TABLE minerva_outcome (
  id INT(5) NOT NULL auto_increment,
  datum_knjizbe DATE DEFAULT NULL,
  recived DATE NOT NULL,
  paid DATE DEFAULT '0000-00-00',
  due DATE DEFAULT NULL,
  client_name VARCHAR(255) NOT NULL,
  client_vat VARCHAR(255) NOT NULL,
  client_bank_account VARCHAR(255) NOT NULL,
  total FLOAT NOT NULL,
  oproscen_nabave FLOAT DEFAULT NULL,
  total_tax FLOAT DEFAULT NULL,
  ddv_ne_odbiti FLOAT DEFAULT NULL,
  ddv_8_doma FLOAT DEFAULT NULL,
  ddv_20_doma FLOAT DEFAULT NULL,
  osnova_tuje FLOAT DEFAULT NULL,
  ddv_8_tuje FLOAT DEFAULT NULL,
  ddv_20_tuje FLOAT DEFAULT NULL,
  bank INT DEFAULT '0',
  INTend VARCHAR(255) DEFAULT NULL,
  refference VARCHAR(255) NOT NULL,
  osnova_4 FLOAT DEFAULT NULL,
  znesek_4 FLOAT DEFAULT NULL,
  updated INT NOT NULL,
  created INT NOT NULL,
  currency CHAR(3) NOT NULL,
--
  PRIMARY KEY  (id)
);

CREATE TABLE minerva_recurrences (
  invoice_id INT NOT NULL,
  invoice_name VARCHAR(32) NOT NULL,
  horde_uid VARCHAR(32) NOT NULL,
  description VARCHAR(255) NOT NULL,
  created INT NOT NULL,
  articles INT NOT NULL,
  client INT NOT NULL,
  draft INT DEFAULT NULL,
  sendto VARCHAR(255) DEFAULT NULL,
  rstatus VARCHAR(32) DEFAULT NULL,
  rstart DATE DEFAULT NULL,
  rend INT DEFAULT NULL,
  rinterval INT DEFAULT NULL,
  roccurred INT DEFAULT NULL,
  rlast INT DEFAULT NULL,
--
  PRIMARY KEY  (invoice_id)
);

CREATE TABLE minerva_resellers (
  reseller_id VARCHAR(255) NOT NULL,
  client_id VARCHAR(255) NOT NULL,
  percentage FLOAT NOT NULL,
--
  PRIMARY KEY  (reseller_id, client_id)
);

CREATE TABLE minerva_statuses (
  id VARCHAR(32) NOT NULL,
  name VARCHAR(255) NOT NULL,
  sort INT DEFAULT '0' NOT NULL,
  updated INT NOT NULL,
  created INT NOT NULL,
--
  PRIMARY KEY  (id)
);

CREATE TABLE minerva_taxes (
  invoice_id INT NOT NULL,
  id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  value FLOAT NOT NULL,
--
  PRIMARY KEY  (invoice_id, id)
);

CREATE TABLE minerva_types (
  id VARCHAR(32) NOT NULL,
  name VARCHAR(255) NOT NULL,
  sort INT DEFAULT '0' NOT NULL,
  offset INT(3) NOT NULL,
  statuses VARCHAR(255) DEFAULT NULL,
  updated INT NOT NULL,
  created INT NOT NULL,
--
  PRIMARY KEY  (id)
);
