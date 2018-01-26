<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use common\models\News;
use common\models\AdminUser;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "basket".
 *
 * @property integer $id
 * @property string  $title
 * @property string  $description
 * @property integer $column_id
 * @property integer $column_type
 * @property string  $news_id
 * @property integer $basket_type_id
 * @property integer $operater_id
 * @property integer $is_active
 * @property integer $terminal_id
 * @property integer $weight
 * @property integer $create_time
 * @property integer $update_time
 */
class Basket extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'basket';
    }
    
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('vrnews1');
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['column_id', 'operater_id'], 'required'],
            [['column_id', 'column_type', 'news_id', 'basket_type_id', 'operater_id', 'is_active', 'terminal_id', 'weight', 'create_time', 'update_time'], 'integer'],
            [['title', 'description'], 'string', 'max' => 255],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'title'          => 'Title',
            'description'    => 'Description',
            'column_id'      => 'Column ID',
            'column_type'    => 'Column Type',
            'news_id'        => 'News ID',
            'basket_type_id' => 'Basket Type ID',
            'operater_id'    => 'Operater ID',
            'is_active'      => 'Is Active',
            'terminal_id'    => 'Terminal ID',
            'weight'         => 'Weight',
            'create_time'    => 'Create Time',
            'update_time'    => 'Update Time',
        ];
    }
    
    public function behaviors()
    {
        return [
            [
                'class'      => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['create_time', 'update_time'],
                    ActiveRecord::EVENT_AFTER_UPDATE  => ['update_time']
                ]
            ]
        ];
    }
    
    public function getNews()
    {
        return $this->hasOne(News::className(), ['news_id' => 'news_id']);
        
    }
    
    public function getAdmin()
    {
        return $this->hasOne(AdminUser::className(), ['admin_id' => 'operater_id']);
        
    }
    
    
    /**
     *
     *  创建篮子（集）
     * 同时创建一条对应的新闻
     * @return Response
     */
    public function CreateBasket($column_id, $column_type)
    {
    
    
    }
    
    /**
     *编辑篮子（集）
     *同时更新对应新闻的相关字段
     * @return Response
     */
    public function EditBasket()
    {
    
    }
    
    /**
     *删除篮子（集）
     *同时删除对应新闻纪录（软删除）
     * @return Response
     */
    public function RemoveBasket()
    {
    
    }
    
    /**
     * @param int $page
     * @param int $size
     *
     * 查看篮子内的列表
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function BasketList($page = 5, $size = 0)
    {
        
        $result = self::find()->with(['news'  => function ($query) {
            $query->select('news_id,title,weight');
        },
                                      'admin' => function ($query) {
                                          $query->select('admin_id,real_name');
                                      },])
            ->offset($size * $page)->limit($page)->asArray()->all();
        foreach ($result as $key => $value) {
            $result[$key]['real_name'] = $value['admin']['real_name'];
            unset($result[$key]['admin']);
            $result[$key]['weight'] = $value['news']['weight'];
            $result[$key]['title'] = $value['news']['title'];
            unset($result[$key]['news']);
            
        }
        
        return $result;
        
    }
    
    
    /**
     *
     * 切换开启和关闭篮子
     * @return Response
     */
    public function BasketSetActive()
    {
    
    }
    
    
    /**
     *
     * 添加移除篮子里子栏目
     * @return Response
     */
    public function BasketAddItems()
    {
    
    }
    
    /*
     * 快直播篮子（集）开启后，新闻列表中不显示快直播，只显示集本身对应的News
     */
    public static function BasketLiveIds($column_type = 0, $column_id = 0, $entry = 0)
    {
        
        
        $collect = ['column_id', 'area_id'];
        $lives = Live::find()->alias('li')
            ->leftJoin('vrnews1.news n', 'n.news_id =li.news_id')->select('n.news_id')
            ->where(['li.is_fast' => 1])
            //->andWhere(['!=', 'li.status', 0])
            ->andWhere(['n.' . $collect[$column_type] => $column_id])
            ->asArray()->all();
        $news_ids = ArrayHelper::getColumn($lives, 'news_id');
        
        //return $lives;
        return $news_ids;
        
        return ['entry' => false, 'news' => '', 'fast_live_list' => '', '$new_ids' => []];
    }
    
    /*
    * 快直播篮子（集）存在和开启与否判断
    */
    public static function BasketCheck($column_type = 0, $column_id = 0, $is_active = 0)
    {
        $baskets_count = self::find()
            ->where(['column_id' => $column_id, 'column_type' => $column_type, 'is_active' => $is_active])
            ->count();
        
        return $baskets_count;
    }
    
}