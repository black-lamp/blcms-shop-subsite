# blcms-shop-subsite
```
php yii migrate --migrationPath=@vendor/black-lamp/blcms-shop-subsite/migrations
```

```
    'modules' => [
        'subsite' => [
            'class' => bl\cms\shop\subsite\Module::className()
        ],
    ]
```

```
    
    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
    ]
```