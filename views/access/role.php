<?php
/**
 * author: TwoNotTwo
 * email: 2not2.github@gmail.com
 *
 * ver: 0.0.1
 * date: 2016.05.15 10:30
 */
namespace twonottwo\db_rbac\views\access;

use Yii;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\grid\ActionColumn;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\BaseHtml;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('db_rbac', 'Роли');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="permit-role">
    <h3><?= Html::encode($this->title) ?></h3>
    <p><?= Html::a(Yii::t('db_rbac', 'Добавить новую роль'), ['add-role'], ['class' => 'btn btn-success']) ?></p>

<?php
    $dataProvider = new ArrayDataProvider([
          'allModels' => Yii::$app->authManager->getRoles(),
          'sort' => [
              'attributes' => ['name', 'description'],
          ],
          'pagination' => [
              'pageSize' => 10,
          ],
    ]);
?>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => SerialColumn::className()],
            "name:text:" . Yii::t('db_rbac', 'Название роли'),
            "description:text:" . Yii::t('db_rbac', 'Описание'),
            [
                'label'     => Yii::t('db_rbac', 'Есть доступ к'),
                'format'    => ['html'],
                'value'     => function($data) { return implode('<br>',array_keys(ArrayHelper::map(Yii::$app->authManager->getPermissionsByRole($data->name), 'description', 'description')));}
            ],
            ['class' => ActionColumn::className(),
                'template' => '{update} {delete}',
                'buttons' =>
                    [
                        'update' => function ($url, $model) {
                                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Url::toRoute(['update-role', 'name' => $model->name]), [
                                            'title' => Yii::t('yii', 'Update'),
                                            'data-pjax' => '0',
                                        ]); },
                        'delete' => function ($url, $model) {
                                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::toRoute(['delete-role','name' => $model->name]), [
                                            'title' => Yii::t('yii', 'Delete'),
                                            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                            'data-method' => 'post',
                                            'data-pjax' => '0',
                                        ]);
                            }
                    ]
            ],
        ]
    ]);
?>
</div>