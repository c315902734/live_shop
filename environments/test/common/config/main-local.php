<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=nma-tools',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=nma-tools'],
            ],
        ],
        'tools' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=nma-tools',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=nma-tools'],
            ],
        ],
        'vradmin' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=vradmin',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=vradmin'],
            ],
        ],
        'vradmin1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=vradmin1',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=vradmin1'],
            ],
        ],
        'vrlive' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=vrlive',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=vrlive'],
            ],
        ],
        'vrnews' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=vrnews',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=vrnews'],
            ],
        ],
        'vrnews1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=vrnews1',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=vrnews1'],
            ],
        ],
        'vruser' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=vruser',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=vruser'],
            ],
        ],
        'vruser1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=vruser1',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=vruser1'],
            ],
        ],
        'vrvideo' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=123.206.24.18;dbname=vrvideo',
            'username' => 'webuser',
            'password' => '123456',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'webuser',
                'password' => '123456',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=123.206.24.18;dbname=vrvideo'],
            ],
        ],
    	'vrvote' => [
    		'class' => 'yii\db\Connection',
    		'dsn' => 'mysql:host=123.206.24.18;dbname=vrvote',
    		'username' => 'webuser',
    		'password' => '123456',
    		'charset' => 'utf8',
    		'slaveConfig'=>[
    				'username' => 'webuser',
    				'password' => '123456',
    				'attributes'=>[
    				// use a smaller connection timeout
    				PDO::ATTR_TIMEOUT => 10,
    			],
    			'charset' => 'utf8',
    		],
    		'slaves'=>[
    				['dsn'=>'mysql:host=123.206.24.18;dbname=vrvote'],
    		],
    	],
    	'vrshop' => [
    			'class' => 'yii\db\Connection',
    			'dsn' => 'mysql:host=123.206.24.18;dbname=vrshop',
    			'username' => 'webuser',
    			'password' => '123456',
    			'charset' => 'utf8',
    			'slaveConfig'=>[
    					'username' => 'webuser',
    					'password' => '123456',
    					'attributes'=>[
    					// use a smaller connection timeout
    		    					PDO::ATTR_TIMEOUT => 10,
    					],
    					'charset' => 'utf8',
    			],
    			'slaves'=>[
    				['dsn'=>'mysql:host=123.206.24.18;dbname=vrshop'],
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
