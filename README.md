Динамическая настройка прав доступа для Yii2
============

Модуль для создания ролей и прав доступа через веб-интерфейс, так же имеющий веб интерфейс для назначения ролей пользователям
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

Если вы используете ЧПУ, то убедитесь что у вас прописаны правила роутинга для модулей
```php
'<module:\w+>/<controller:\w+>/<action:(\w|-)+>' => '<module>/<controller>/<action>',
'<module:\w+>/<controller:\w+>/<action:(\w|-)+>/<id:\d+>' => '<module>/<controller>/<action>',
```

**Добавляем ссылки в меню**

**/permit/access/role - управление ролями**

**/permit/access/permission - управление правами доступа**

###Назначение ролей пользователям###
По многочисленным просьбам в модуль добавлен интерфейс для назначения ролей пользователям. 

Для корректной работы модуля нужно указать в параметрах модуля класс `User`.
Когда через модуль будут созданы роли, то в настройках модуля можно будет указать, с какими ролями пользователь имеет доступ к модулю
```php
'modules' => [
        'permit' => [
            'class' => 'app\modules\db_rbac\Yii2DbRbac',
            'params' => [
                'userClass' => 'app\models\User',                
                'accessRoles'=>['admin']
            ]
        ],
    ],
```

Класс User должен реализовывать интерфейс `developeruz\db_rbac\interfaces\UserRbacInterface`. 
В большинстве случаев придется дописать в нем 1 функцию `getUserName()` которая будет возвращать отображаемое имя пользователя.
```php
use developeruz\db_rbac\interfaces\UserRbacInterface;

class User extends ActiveRecord implements IdentityInterface, UserRbacInterface
{
...
    public function getUserName()
    {
       return $this->username;
    }
}
```

**Управление ролью пользователя происходит на странице `/permit/user/view/1` для пользователя с id=1.**
Удобнее всего дописать кнопку на эту страницу в Grid со списком пользователей.
```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        'id',
        'username',
        'email:email',

        ['class' => 'yii\grid\ActionColumn',
         'template' => '{view}&nbsp;&nbsp;{update}&nbsp;&nbsp;{permit}&nbsp;&nbsp;{delete}',
         'buttons' =>
             [
                 'permit' => function ($url, $model) {
                     return Html::a('<span class="glyphicon glyphicon-wrench"></span>', Url::to(['/permit/user/view', 'id' => $model->id]), [
                         'title' => Yii::t('yii', 'Change user role')
                     ]); },
             ]
        ],
    ],
]);
```

Присвоить роль пользователю можно и в коде, например при создании нового пользователя. 
```php
$userRole = Yii::$app->authManager->getRole('name_of_role');
Yii::$app->authManager->assign($userRole, $user->getId());
```

Проверить, имеет ли пользователь право на действие можно через метод `can()` компонента User
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