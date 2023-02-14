WITH RECURSIVE generation AS (SELECT ID, 0 AS generation_number
                              FROM tbl_node
                              WHERE parent_id IS NULL
                              UNION ALL
                              SELECT child.id, generation_number + 1 AS generation_number
                              FROM tbl_node child
                                       JOIN generation g
                                            ON g.id = child.parent_id)
SELECT ID, generation_number
FROM generation;