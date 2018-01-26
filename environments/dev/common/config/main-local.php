<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=nma-tools',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',

            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=nma-tools'],
            ],
        ],
        'tools' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=nma-tools',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=nma-tools'],
            ],
        ],
        'vradmin' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=vradmin',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=vradmin'],
            ],
        ],
        'vradmin1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=vradmin1',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=vradmin1'],
            ],
        ],
        'vrlive' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=vrlive',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=vrlive'],
            ],
        ],
        'vrnews' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=vrnews',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=vrnews'],
            ],
        ],
        'vrnews1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=vrnews1',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=vrnews1'],
            ],
        ],
        'vruser' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=vruser',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=vruser'],
            ],
        ],
        'vruser1' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=vruser1',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=vruser1'],
            ],
        ],
        'vrvideo' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=vrvideo',
            'username' => 'root',
            'password' => 'eVic^?L2CA7Wo6mK',
            'charset' => 'utf8',
            'slaveConfig'=>[
                'username' => 'root',
                'password' => 'eVic^?L2CA7Wo6mK',
                'attributes'=>[
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
                'charset' => 'utf8',
            ],
            'slaves'=>[
                ['dsn'=>'mysql:host=localhost;dbname=vrvideo'],
            ],
        ],
    	'vrvote' => [
    		'class' => 'yii\db\Connection',
    		'dsn' => 'mysql:host=localhost;dbname=vrvote',
    		'username' => 'root',
    		'password' => 'eVic^?L2CA7Wo6mK',
    		'charset' => 'utf8',
    		'slaveConfig'=>[
    				'username' => 'root',
    				'password' => 'eVic^?L2CA7Wo6mK',
    				'attributes'=>[
    					// use a smaller connection timeout
    					PDO::ATTR_TIMEOUT => 10,
    				],
    				'charset' => 'utf8',
    			],
    			'slaves'=>[
    					['dsn'=>'mysql:host=localhost;dbname=vrvote'],
    			],
    	],   
    	'vrshop' => [
    			'class' => 'yii\db\Connection',
    			'dsn' => 'mysql:host=localhost;dbname=vrshop',
    			'username' => 'root',
    			'password' => 'eVic^?L2CA7Wo6mK',
    			'charset' => 'utf8',
    			'slaveConfig'=>[
    					'username' => 'root',
    					'password' => 'eVic^?L2CA7Wo6mK',
    					'attributes'=>[
    					// use a smaller connection timeout
    		    			PDO::ATTR_TIMEOUT => 10,
    					],
    					'charset' => 'utf8',
    				],
    				'slaves'=>[
    						['dsn'=>'mysql:host=localhost;dbname=vrshop'],
    			],
    	],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
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
