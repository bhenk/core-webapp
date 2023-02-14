CREATE TABLE IF NOT EXISTS `tbl_node`
(
    ID        INT NOT NULL AUTO_INCREMENT,
    parent_id INT,
    name      VARCHAR(255),
    alias     VARCHAR(255),
    nature    VARCHAR(25),
    public    BOOLEAN,
    estimate  FLOAT,
    PRIMARY KEY (ID)
);