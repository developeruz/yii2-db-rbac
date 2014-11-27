<?php

namespace common\module;

use Yii;

class Yii2DbRbac extends \yii\base\Module
{
    public $controllerNamespace = 'developeruz\db_rbac\controllers';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/db_rbac/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'ru-Ru',
            'basePath' => '@developeruz/db_rbac/messages',
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/db_rbac/' . $category, $message, $params, $language);
    }
}
