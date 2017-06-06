create table documents (
	id number(10),
	typedoc varchar(50),
	title varchar(200),
	content clob,
	views number(10)
);

CREATE SEQUENCE documents_id_seq
START WITH 1
INCREMENT BY 1
MAXVALUE 99999999
MINVALUE 1
NOCACHE
NOORDER
NOCYCLE;


 UPDATE tableName SET columnName=seq_id.NEXTVAL;
