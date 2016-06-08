<?php
/**
 * @copyright Copyright (c) 2016 Kirill Ganenko
 */
use yii\db\Migration;
use yii\base\InvalidConfigException;
use yii\rbac\DbManager;


class m160608_064342_rbac_fill extends Migration
{
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }
        return $authManager;
    }

    public function up()
    {
        $authManager = $this->getAuthManager();
        $_at = new DateTime();
        $_at = $_at->getTimestamp();

        $this->execute("INSERT INTO {$authManager->itemTable} (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES ('root', '1', 'Суперпользователь', null, null, '$_at', '$_at'), ('permit', '2', 'Настройка прав доступа', null, null, $_at, $_at), ('adminpanel', '2', 'Админпанель', null, null, $_at, $_at)");
        $this->execute("INSERT INTO {$authManager->assignmentTable} (`item_name`, `user_id`, `created_at`) VALUES ('root', '1', $_at)");
        $this->execute("INSERT INTO {$authManager->itemChildTable} (`parent`, `child`) VALUES ('root', 'permit'), ('root', 'adminpanel')");
    }
}