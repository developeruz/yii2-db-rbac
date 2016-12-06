<?php
namespace developeruz\db_rbac\interfaces;

interface UserRbacInterface
{
    public function getId();

    public function getUserName();

    public static function findIdentity($id);
}
