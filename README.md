# DBdoc

Organize, search your documents with easy.


# Instructions

## Database

### Create user

```
SQL> create tablespace dbdoc_tabspc datafile '...' size 100M autoextend on;
SQL> create user dbdoc identified by "passdbdoc" default tablespace dbdoc_tabspc;
SQL> alter user dbdoc quota unlimited on dbdoc_tabspc;
SQL> grant connect to dbdoc;

```

## PHP


