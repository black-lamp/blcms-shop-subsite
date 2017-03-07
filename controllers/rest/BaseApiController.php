<?php
namespace bl\cms\shop\subsite\controllers\rest;
use yii\filters\Cors;
use yii\rest\Controller;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
abstract class BaseApiController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => $this->module->queenDomainNames
            ]
        ];
        return $behaviors;
    }
}