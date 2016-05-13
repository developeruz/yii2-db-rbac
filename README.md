Модуль настройки прав доступа через web-интерфейс
============

Модуль для создания прав доступа, ролей, назначения ролей пользователям через web-интерфейс.

###Установка###

Получаем модуль из репозитория
```bash
$ php composer.phar require twonottwo/yii2-db-rbac "dev-master"
```
либо
```bash
$ composer require twonottwo/yii2-db-rbac "dev-master"
```
Имя ветки может быть отличным от dev-master

Настраиваем authManager приложения (`common/config/main.php` для advanced приложения, `config/web.php` и `config/console` для basic приложения)
```php
'components' => [
    'authManager' => [
      'class' => 'yii\rbac\DbManager',
    ],
]
```

Выполняем миграцию создания таблиц для DbManager
```bash
$ yii migrate --migrationPath=@yii/rbac/migrations/
```

##Подключение модуля к приложению##
В настройках приложения (`backend/config/main.php` и `frontend/config/main.php` для advanced и `config/web.php` для basic приложения) прописываем модуль

Для basic приложения
```php
'modules' => [
    'permit' => [
        'class' => 'app\modules\db_rbac\Yii2DbRbac',
        'params' => [
            'userClass' => 'app\models\User'
        ]
    ],
],
```

Для advanced приложения
```php
'modules' => [
    'permit' => [
        'class' => 'app\modules\db_rbac\Yii2DbRbac',
        'params' => [
            'userClass' => 'common\models\User'
        ]
    ],
],
```

Для передачи layout дописать:
```php
'modules' => [
    'permit' => [
        'layout' => '//admin'
    ],
],
```

Дописываем в класс User (`common/models/User.php` для advanced приложения и `models/User.php` для basic приложения) интерфейс `UserRbacInterface` и функцию `getUserName()`
```php

use twonottwo\db_rbac\interfaces\UserRbacInterface;
```

```php
class User extends ActiveRecord implements IdentityInterface, UserRbacInterface {
    public function getUserName()
    {
       return $this->username;
    }
}
```



Если вы используете ЧПУ, то убедитесь что у вас прописаны правила роутинга для модулей
```php
'<module:\w+>/<controller:\w+>/<action:(\w|-)+>' => '<module>/<controller>/<action>',
'<module:\w+>/<controller:\w+>/<action:(\w|-)+>/<id:\d+>' => '<module>/<controller>/<action>',
```

**Доступ к настройке разрешений, ролей, назначение ролей пользователю**
- /permit/access/permission - права доступа
- /permit/access/role - роли
- /permit/user/view?id=N - назначение ролей пользователю, где N - id записи пользователя


Пример кода Grid со списком пользователей и кнопкой для перехода к назначению ролей
```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        'id',
        'username',
        'email:email',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}&nbsp;&nbsp;{update}&nbsp;&nbsp;{permit}&nbsp;&nbsp;{delete}',
            'buttons' => [
                'permit' => function ($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-wrench"></span>', Url::to(['/permit/user/view', 'id' => $model->id]), [
                        'title' => Yii::t('yii', 'Change user role')
                    ]);
                },
            ]
        ],
    ],
]);
```

Присвоить роль пользователю можно в коде, например при создании нового пользователя
```php
$userRole = Yii::$app->authManager->getRole('name_of_role');
Yii::$app->authManager->assign($userRole, $user->getId());
```

Проверить, имеет ли пользователь право на действие можно через метод `can()` компонента User
```php
Yii::$app->user->can($permissionName);
```
$permissionName - может быть как ролью так и правом

##Проверка прав доступа на лету##

Данное поведение позволяет не писать `Yii::$app->user->can($permissionName);` в каждом action, а проверять права доступа на лету

###Подключение поведения###
В настройках того приложения, доступ к которому следует проверять на лету, подключаем поведение
```php
use twonottwo\db_rbac\behaviors\AccessBehavior;
```

```php
return [
    'as AccessBehavior' => [
        'class' => AccessBehavior::className(),
    ]
```

С этого момента, после обработки запроса (событие EVENT_AFTER_REQUEST) проверяются права текущего пользователя `(Yii::$app->user)` на выполнение запрашиваемого действия (`Yii::$app->user->can()`).

Действие считается разрешенным, если:
 - пользователю разрешен доступ к конкретному action (правило записано как: module/controller/action)
 - пользователю разрешен доступ к любым action данного контроллера (правило записано как: module/controller)
 - пользователю разрешен доступ к любым action данного модуля (правило записано как: module)

###Настройка прав доступа по умолчанию###

После подключения поведения, доступ становится возможен только авторизованному пользователю, имеющему некие права.
Для исключений из этого правила можно прописать доступы по умолчанию в том же формате AccessControl, что и в контроллере.

При данной настройке ввод логина и пароля обязателен
```php
    'as AccessBehavior' => [
        'class' => AccessBehavior::className(),
        'rules' => [
            'site' => [
                [
                    'actions' => ['error'],
                    'allow' => true,
                ],
                [
                    'actions' => ['login'],
                    'allow' => true,
                    'roles' => ['?'],
                ],
                [
                    'actions' => ['logout' ],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ]
        ]
    ],
```

А при такой гости смогут увидеть главную страницу
```php
    'as AccessBehavior' => [
        'class' => AccessBehavior::className(),
        'rules' =>[
            'site' => [
                [
                    'actions' => ['error'],
                    'allow' => true,
                ],
                [
                    'actions' => ['login', 'index'],
                    'allow' => true,
                    'roles' => ['?']
                ],
                [
                    'actions' => ['logout'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ]
        ]
    ],
```

Правила прописанные в настройках приложения имеют приоритет над динамически настраиваемыми правилами.