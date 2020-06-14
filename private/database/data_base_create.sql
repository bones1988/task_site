create table tasks(
id bigint unsigned auto_increment primary key,
name varchar (256),
email varchar (256),
text varchar (256),
completed boolean,
redacted boolean
);
