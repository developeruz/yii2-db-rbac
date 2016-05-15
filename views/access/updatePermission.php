<?php
namespace twonottwo\db_rbac\views\access;

use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('db_rbac', 'Редактирование разрешения');
$this->params['breadcrumbs'][] = ['label' => Yii::t('db_rbac', 'Разрешения на доступ'), 'url' => ['permission']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="news-index">

    <h3><?= Html::encode($this->title. ': '. $permit->description) ?></h3>

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
            <?= Html::label(Yii::t('db_rbac', 'Описание')); ?>
            <?= Html::textInput('description', $permit->description); ?>
        </div>

        <div class="form-group">
            <?= Html::label(Yii::t('db_rbac', 'Разрешение на доступ')); ?>
            <?= Html::textInput('name', $permit->name); ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('db_rbac', 'Сохранить'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>