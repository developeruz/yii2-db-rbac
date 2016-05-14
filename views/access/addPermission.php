<?php
namespace twonottwo\db_rbac\views\access;

use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Links */
/* @var $form yii\widgets\ActiveForm */
$this->title = Yii::t('db_rbac', 'Новое разрешение');
$this->params['breadcrumbs'][] = ['label' => Yii::t('db_rbac', 'Разрешение на доступ'), 'url' => ['permission']];
$this->params['breadcrumbs'][] = $this->title;
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
            <?= Html::label(Yii::t('db_rbac', 'Описание')); ?>
            <?= Html::textInput('description'); ?>
        </div>

        <div class="form-group">
            <?= Html::label(Yii::t('db_rbac', 'Название разрешения')); ?>
            <?= Html::textInput('name'); ?>
            <?=Yii::t('db_rbac', '
            * Формат module/controller/action<br>
            site/article - доступ к странице site/article<br>
            site - доступ к любым action контроллера site');?>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('yii', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
