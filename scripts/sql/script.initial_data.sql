--
-- $Horde: incubator/minerva/scripts/sql/script.initial_data.sql,v 1.1 2007/07/31 23:18:20 chuck Exp $
-- --------------------------------------------------------

--
-- Initial data for minerva
--

INSERT INTO minerva_statuses VALUES ('pending', 'Pending', 0, 1156850133, 1156522350);
INSERT INTO minerva_statuses VALUES ('paid', 'Paid', 1, 1156522327, 1156522327);

INSERT INTO minerva_types VALUES ('proforma', 'Proforma', 0, 1156522364, 1156522364);
INSERT INTO minerva_types VALUES ('invoice', 'Invoice', 1, 1156495059, 1156495059);

INSERT INTO horde_company (
id, short_name, long_name, address, city, postal, url, email, phone, fax,
country, logo, crn, vat, taxable, capital_amount, capital_currency,
registration_unit, contact_person, contact_function, contact_email,
contact_phone, created, updated
) VALUES (
'1', 'My company', 'My copany tld', 'My street', 'My city', '13454',
'http://www.comany.mine', 'info@copany.mine', '010 2020 2020', '010 3030 3030',
'SI', '', '987654', '3456789', '', '210987', 'EUR', 'My registration unit',
'My first and second name', 'CEO', 'ceo@compay.mine', '039386 7373 88',
'1156522350', '1156522350'
);