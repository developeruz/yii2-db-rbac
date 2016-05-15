<?php
namespace twonottwo\db_rbac\views\access;

use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('db_rbac', 'Редактирование роли');
$this->params['breadcrumbs'][] = ['label' => Yii::t('db_rbac', 'Управление ролями'), 'url' => ['role']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="permit-update-role">
    <h3><?= Html::encode($this->title. ': ' . $role->name) ?></h3>

    <div class="row">
        <div class="col-lg-5">
            <?php ActiveForm::begin(['id' => 'update-role']); ?>

            <div class="form-group">
                <?= Html::label(Yii::t('db_rbac', 'Название роли')); ?>
                <?= Html::textInput('name', $role->name, ['class' => 'form-control']); ?>
            </div>

            <div class="form-group">
                <?= Html::label(Yii::t('db_rbac', 'Описание')); ?>
                <?= Html::textInput('description', $role->description, ['class' => 'form-control']); ?>
            </div>

            <div class="form-group">
                <?= Html::label(Yii::t('db_rbac', 'Есть доступ к')); ?>
                <?= Html::checkboxList('permissions', $role_permit, $permissions, ['separator' => '<br>']); ?>
            </div>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('db_rbac', 'Сохранить'), ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
