<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=nma-tools',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=nma-tools'],
            ],
        ],
        'tools' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=nma-tools',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=nma-tools'],
            ],
        ],
        'vradmin' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=vradmin',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=vradmin'],
            ],
        ],
        'vradmin1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=vradmin1',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=vradmin1'],
            ],
        ],
        'vrlive' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=vrlive',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=vrlive'],
            ],
        ],
        'vrnews' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=vrnews',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=vrnews'],
            ],
        ],
        'vrnews1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=vrnews1',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=vrnews1'],
            ],
        ],
        'vruser' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=vruser',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=vruser'],
            ],
        ],
        'vruser1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=vruser1',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=vruser1'],
            ],
        ],
        'vrvideo' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.66.154.179;dbname=vrvideo',
            'username' => 'root',
            'password' => 'k489CQHdWUKQBJ',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'k489CQHdWUKQBJ',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=10.66.154.179;dbname=vrvideo'],
            ],
        ],
    	'vrvote' => [
    		'class' => 'yii\db\Connection',
    		'dsn' => 'mysql:host=10.66.154.179;dbname=vrvote',
    		'username' => 'root',
    		'password' => 'k489CQHdWUKQBJ',
    		'charset' => 'utf8',
    		'slaveConfig'=>[
    				'username' => 'root',
    				'password' => 'k489CQHdWUKQBJ',
    				'attributes'=>[
    				// use a smaller connection timeout
    				PDO::ATTR_TIMEOUT => 10,
    			],
    			'charset' => 'utf8',
    		],
    		'slaves'=>[
    				['dsn'=>'mysql:host=10.66.154.179;dbname=vrvote'],
    		],
    	],
    	'vrshop' => [
    			'class' => 'yii\db\Connection',
    			'dsn' => 'mysql:host=10.66.154.179;dbname=vrshop',
    			'username' => 'root',
    			'password' => 'k489CQHdWUKQBJ',
    			'charset' => 'utf8',
    			'slaveConfig'=>[
    					'username' => 'root',
    					'password' => 'k489CQHdWUKQBJ',
    					'attributes'=>[
    					// use a smaller connection timeout
    		    			PDO::ATTR_TIMEOUT => 10,
    					],
    			'charset' => 'utf8',
    			],
    			'slaves'=>[
    					['dsn'=>'mysql:host=10.66.154.179;dbname=vrshop'],
    			],
    	],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
    	'cache' => [
    			// 'class' => 'yii\caching\FileCache',
    			'class' => 'yii\redis\Cache',
    	],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '10.66.157.45',
            'port' => 6379,
            'database' => 0,
            'password' => 'crs-guq6g1ma:2K9Fr4BXzc2C%',
        ],
    ],
];
