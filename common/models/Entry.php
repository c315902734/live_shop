<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\query;
use yii\behaviors\TimestampBehavior;
use common\models\News;
use common\models\AdminUser;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "entry".
 *
 * @property integer $id
 * @property integer $news_id
 * @property integer $entry_type_id
 * @property integer $operater_id
 * @property integer $is_sticky
 * @property integer $terminal_id
 * @property integer $weight
 * @property integer $create_time
 * @property integer $update_time
 */
class Entry extends ActiveRecord
{
    
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'entry';
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
            [['news_id', 'entry_type_id', 'operater_id'], 'required'],
            [['entry_type_id', 'operater_id', 'is_sticky', 'terminal_id', 'weight', 'create_time', 'update_time'], 'integer'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'news_id'       => 'News ID',
            'entry_type_id' => 'Entry Type ID',
            'operater_id'   => 'Operater ID',
            'is_sticky'     => 'Is Sticky',
            'terminal_id'   => 'Terminal ID',
            'weight'        => 'Weight',
            'create_time'   => 'Create Time',
            'update_time'   => 'Update Time',
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
    
    public static function EntryList($page = 5, $size = 0)
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
    
    public static function Create($column_type = 0, $column_id = 0)
    {
        
        $result = self::find()->with('news')->asArray()->all();
        $collect = ['column_id', 'area_id'];
        //$entry=
        
        return ['entry' => false, 'news' => '', 'fast_live_list' => '', '$new_ids' => []];
    }
    //判定入口是存在
    public static function ExistFastLiveEntry($column_type = 0, $column_id = 0)
    {
        
        $result = self::find()->with('news')->asArray()->all();
        $collect = ['column_id', 'area_id'];
        $lives = Live::fastliveList($page = 1, $size = 0, $user_id = 1, $is_pc = 0, $column_id, $column_type);
        foreach ($result as $key => $value) {
            if(isset($value['news'])){
                if ($value['news'][$collect[$column_type]] == $column_id) {
                    
                    $value['news']['fast_live_count'] = $lives['totalCount'];
                    $return = ['entry' => true, 'news' => $value['news'], 'entry_id' => $value['news']['news_id'], 'fast_live_count' => $lives['totalCount']];
        
                    return $return;
                }
    
            }
            
        }
     
        return ['entry' => false, 'news' => '', 'fast_live_count' => $lives['totalCount'], '$new_ids' => []];
    }
    
    
}