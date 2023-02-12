CREATE TABLE IF NOT EXISTS `tbl_node`
(
    ID        int NOT NULL AUTO_INCREMENT,
    parent_id int,
    name      varchar(255),
    alias     varchar(255),
    nature    VARCHAR(25),
    PRIMARY KEY (ID)
);