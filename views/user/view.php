<?php
namespace twonottwo\db_rbac\views\user;

use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('db_rbac', 'Назначение ролей');
$this->params['breadcrumbs'][] = $this->title;
?>
<h3><?= $this->title .': '.$user->getUserName(); ?></h3>
<?php $form = ActiveForm::begin(['action' => ["/{$moduleName}/user/update", 'id' => $user->getId()]]); ?>

<?= Html::checkboxList('roles', $user_permit, $roles, ['separator' => '<br>']); ?>

<div class="form-group">
    <?= Html::submitButton(Yii::t('yii', 'Save'), ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

