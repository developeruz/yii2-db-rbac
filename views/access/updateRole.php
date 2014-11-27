<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Links */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Редактирование роли: ' . ' ' . $role->name;
$this->params['breadcrumbs'][] = ['label' => 'Управление ролями', 'url' => ['role']];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="news-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="links-form">

        <?php
        if (!empty($error)) {
            ?>
            <div class="error-summary">
                <?php
                echo implode('<br>', $error);
                ?>
            </div>
        <?php
        }
        ?>

        <?php $form = ActiveForm::begin(); ?>

        <div class="form-group">
            <?= Html::label('Название роли'); ?>
            <?= Html::textInput('name', $role->name); ?>
        </div>

        <div class="form-group">
            <?= Html::label('Текстовое описание'); ?>
            <?= Html::textInput('description', $role->description); ?>
        </div>

        <div class="form-group">
            <?= Html::label('Разрешенные доступы'); ?>
            <?= Html::checkboxList('permissions', $role_permit, $permissions, ['separator' => '<br>']); ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
