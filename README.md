Динамическая настройка прав доступа для Yii2
============

Модуль для создания ролей и прав доступа через веб-интерфейс.
Поведение для приложения, проверяющее право доступа к action по внесенным в модуле правилам.

###Установка:###
```bash
$ php composer.phar require developeruz/yii2-db-rbac "*"
```

Для корректной работы модуля необходимо настроить authManager в конфиге приложения (common/config/main.php для advanced или config/web.php и config/console  для basic приложения)
```php
    'components' => [
       'authManager' => [
          'class' => 'yii\rbac\DbManager',
        ],
    ...
    ]
```

И выполнить миграции, создающие таблицы для DbManager (подразумевается, что коннект к БД для приложения уже настроен)
```bash
$ yii migrate --migrationPath=@yii/rbac/migrations/
```

##Подключение модуля##
В конфиге приложения (backend/config/main.php для advanced или config/web.php для basic приложения) прописываем модуль
```php
  'modules' => [
        'permit' => [
            'class' => 'developeruz\db_rbac\Yii2DbRbac',
        ],
    ],
```
Если нужно передать layout это можно сделать так:
```php
  'modules' => [
        'permit' => [
            'class' => 'developeruz\db_rbac\Yii2DbRbac',
            'layout' => '//admin'
        ],
    ],
```

Добавляем ссылки в меню
/permit/access/role - управление ролями
/permit/access/permission - управление правами доступа

###Назначение ролей пользователям###
Модуль управления пользователями не входит в функционал данного модуля.
Ниже приведены подсказки по использованию DbManager для назначения ролей пользователям

- Присвоить роль пользователю
```php
$userRole = Yii::$app->authManager->getRole('name_of_role');
Yii::$app->authManager->assign($userRole, $user->getId());
```
Присваивать роль можно при создании пользователя, при редактировании пользователя из админки или при авторизации.
Допустимо множественое присвоение ролей (у 1 пользователя может быть N ролей)

- Получить массив всех ролей
```php
ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'description');
```

- Получить массив присвоенных юзеру ролей
```php
array_keys(Yii::$app->authManager->getRolesByUser($user->getId()));
```

- Удалить все ранее привязанные роли пользователя
```php
Yii::$app->authManager->revokeAll($user->getId())
```

- Проверить, имеет ли пользователь право на действие
```php
Yii::$app->user->can($permissionName);
```
$permissionName - может быть как ролью так и правом

##Поведение, динамически проверяющее наличие прав##

Данное поведение позволяет не писать Yii::$app->user->can($permissionName); в каждом action, а проверять права доступа на лету.
Это удобно для гибкой настройки прав при использовании сторонних модулей.

###Подключение поведения###
В конфиге того приложения, доступ к которому следует проверять на лету, необходимо подключить поведение
```php
use developeruz\db_rbac\behaviors\AccessBehavior;

 'as AccessBehavior' => [
        'class' => AccessBehavior::className(),
 ]
```
С этого момента, после обработки запроса (событие EVENT_AFTER_REQUEST) проверяются права текущего пользователя (Yii::$app->user) на выполнение запрашиваемого действия (Yii::$app->user->can())
Действие считается разрешенным, если:
 - пользователю разрешен доступ к конкретному action (правило записано как: module/controller/action)
 - пользователю разрешен доступ к любым action данного контроллера (правило записано как: module/controller)
 - пользователю разрешен доступ к любым action данного модуля (правило записано как: module)

###Настройка прав доступа по умолчанию###
После подключения поведения, доступ становится возможен только авторизованному пользователю, имеющему некие права.
Для исключений из этого правила можно прописать доступы по умолчанию в том же формате AccessControl, что и в контроллере:
```php
    'as AccessBehavior' => [
        'class' => AccessBehavior::className(),
        'rules' =>
            ['site' =>
                [
                    [
                        'actions' => ['login', 'index'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['about'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ]
            ]
    ]
```
В приведенном выше примере разрешен доступ любому пользователю к site/login и site/index и доступ пользователя с ролью admin к site/about
Правила прописанные в конфиге имеют приоритет над динамически настраиваемыми правилами.