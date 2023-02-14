<?php

namespace bhenk\corewa\dao\user;

use bhenk\corewa\dao\abc\AbstractDao;

class UserDao extends AbstractDao {

    public function getDataObjectName(): string {
        return UserDo::class;
    }

    public function getTableName(): string {
        return "tbl_users";
    }
}