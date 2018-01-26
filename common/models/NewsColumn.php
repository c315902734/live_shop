<?php

namespace common\models;

use frontend\controllers\NewslinkController;
use frontend\controllers\PublicBaseController;
use Yii;

/**
 * This is the model class for table "news_column".
 *
 * @property integer $column_id
 * @property string $name
 * @property integer $type
 * @property integer $weight
 * @property string $create_time
 * @property integer $creator_id
 * @property string $update_time
 * @property integer $status
 */
class NewsColumn extends \yii\db\ActiveRecord
{
    public static $area = array(
    		'保定',
    		'石家庄',
    		'北京',
    		'郑州',
    		'延吉',
    		'重庆',
    		'温州',
    		'济南',
    		'杭州',
    		'临沧',
    		'阜阳',
    		'安庆',
    		'郴州',
    		'长沙',
    		'呼和浩特',
    		'济宁',
    		'聊城',
    		'枣庄',
    		'西安',
    		'延安',
    	    '商丘',
    		'太原',
    		'徐州',
    		'广州',
    		'连云港',
    		'忻州',
            '贵港',
    		'朔州',
    		'大同',
    		'合肥',
    		'庆阳',
    		'上饶',
    		'延边',
    		'天水',
    		'咸阳',
    		'南阳',
    		'洛阳',
    		'周口',
    		'平顶山',
    		'武汉',

    		'汕头',
    		'湖州',
    		'中山'

    );
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news_column';
    }

    public static function getDb()
    {
        return Yii::$app->vrnews1;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'weight', 'creator_id', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'column_id' => 'Column ID',
            'name' => 'Name',
            'type' => 'Type',
            'weight' => 'Weight',
            'create_time' => 'Create Time',
            'creator_id' => 'Creator ID',
            'update_time' => 'Update Time',
            'status' => 'Status',
        ];
    }

    public static function getColumnType($area, $is_pc='', $show_type='', $py_area=''){
        $live = array(
            'column_id' => '99',
            'name' => '直播',
            'type' => '99',
            'columntype' => array(
                array(
                    'column_id' => '98',
                    'name' => '直播',
                ),
                array(
                    'column_id' => '97',
                    'name' => 'VR'
                )
            )
        );
//        if(!in_array($area, static::$area)){
//            $area = '北京';
//        }
        if(!empty($py_area)){
            $area_info = Area::find()->where(['pinyin'=>$py_area])->asArray()->one();
            $area = $area_info['name'];
        }
        $where = " 1=1";
        if($is_pc){
            $where .= " and show_type in (1,3)";
        }else{
            $where .= " and show_type in (1,2) and column_id != 9";
        }
        if($area){
            //查看 栏目内是否 开通 本地
            $is_area = static::find()->where(['type'=>2, 'name'=>'本地','status'=>1])->andWhere(['>=', 'weight','70'])->asArray()->one();
            if(!$is_area){
                $list = static::find()->where($where)->andWhere(['status'=>1,'type'=>1])->andWhere(['>=','weight','70'])->select('column_id, name, type, alias')
                        ->orderBy('weight desc')->asArray()->all();
                //查看 对应的 二级栏目
                $list = NewsColumn::getColumns($list, $show_type);
                if($is_pc == 1){
                    $list1 = array_splice($list, 0,2);
                    $list2 = array_splice($list, 0);
                    array_push($list1, $live);
                    $list = array_merge($list1, $list2);
                }
                return $list;
            }else{
                //查看 本地 对应地区
                $area_sel = new Area();
                $area_one = $area_sel->find()->where(['name'=>$area, 'establish_status'=>1,'disable_status'=>0])
                            ->select('area_id as column_id,name')
                            ->asArray()->one();
                if($area_one){
                    $list = static::find()->where($where)->andWhere(['status'=>1])->andWhere(['>=', 'weight', '70'])->select('column_id, name, type, alias')
                            ->orderBy('weight desc')->asArray()->all();
                    foreach($list as $key=>$val){
                        if($val['type'] == 2){
                            //将本地 栏目信息加入
                            $list[$key]['column_id'] = $area_one['column_id'];
                            $list[$key]['name']      = $area_one['name'];
                        }
                    }
                    //查看 对应的 二级栏目
                    $list = NewsColumn::getColumns($list, $show_type);
                    if($is_pc == 1){
                        $list1 = array_splice($list, 0,2);
                        $list2 = array_splice($list, 0);
                        array_push($list1, $live);
                        $list = array_merge($list1, $list2);
                    }
                    return $list;
                }else{
                    //如果不存在
                    return static::getColumnType('北京', $is_pc);
                    $list = static::find()->where($where)->andWhere(['status'=>1,'type'=>1])->andWhere(['>=','weight','70'])
                            ->select('column_id, name, type, alias')->orderBy('weight desc')->asArray()->all();
                    //查看 对应的 二级栏目
                    $list = NewsColumn::getColumns($list, $show_type);
                    if($is_pc == 1){
                        $list1 = array_splice($list, 0,2);
                        $list2 = array_splice($list, 0);
                        array_push($list1, $live);
                        $list = array_merge($list1, $list2);
                    }
                    return $list;
                }
            }
        }else{
            $list = static::find()->where($where)->andWhere(['status'=>1,'type'=>1])->andWhere(['>=','weight','70'])
                ->select('column_id, name, type, alias')->orderBy('weight desc')->asArray()->all();
            //查看 对应的 二级栏目
            $list = NewsColumn::getColumns($list, $show_type);
            if($is_pc == 1){
                $list1 = array_splice($list, 0,2);
                $list2 = array_splice($list, 0);
                array_push($list1, $live);
                $list = array_merge($list1, $list2);
            }
            return $list;
        }
    }

    //获取 二级栏目
    public static function getColumns($list, $show_type=''){
        foreach ($list as $key=>$val){
            $column_list = array();
            $column_list = NewsColumnType::find()->where(['column_id'=>$val['column_id'],'status'=>1])->andWhere(['>=','weight','70'])->select('type_id as column_id,name,alias')
                ->orderBy('weight desc')->asArray()->all();
            if(!empty($column_list) && $val['column_id'] != 9){
                $list[$key]['columntype'] = $column_list;
                
            }else{
                $list[$key]['columntype'] = array();
            }
            if($val['type'] == 2){
                $column_list = Area::getArena();
                if($show_type == 1){
                    $city_list = array_column($column_list , 'name');
                    $light_id = Cities::find()->select("provinceid")->where(['city'=>$val['name']])->asArray()->one();
                    $province_ids = Cities::find()->select("provinceid")->where(['in','city', $city_list])->asArray()->all();
                    $ids = array_column($province_ids, 'provinceid');
                    $province_list = Provinces::find()->where(['in', 'provinceid', $ids])->asArray()->all();
                    $news_list = array();
                    foreach ($province_list as $k=>$v){
//                        if($val['name'] == $v['province']){
//                            continue;
//                        }
                        if($v['provinceid'] == $light_id['provinceid']){
                            $news_list[$k]['is_light'] = '1';
                        }else{
                            $news_list[$k]['is_light'] = '0';
                        }
                        $news_list[$k]['column_id'] = $v['provinceid'];
                        $news_list[$k]['name'] = $v['province'];
                        $news_list[$k]['type'] = 2;
                        foreach ($column_list as $c_k=>$c_v){
                            if($c_v['name'] == $v['province']){
                                $news_list[$k]['ename'] = $c_v['pinyin'];
                            }
                        }
                    }
                }else{
                    $news_list = array();
                    foreach ($column_list as $k=>$v){
                        if($val['name'] == $v['name']){
                            continue;
                        }
                        $news_list[$k]['column_id'] = $v['area_id'];
                        $news_list[$k]['name'] = $v['name'];
                        //$news_list[$k]['enname'] = $v['enname'];
                        $news_list[$k]['type'] = 2;
                    }
                }
                $list[$key]['columntype'] = array_values($news_list);
            }
        }
        return $list;
    }

    /**
     * 获取视频栏目id
     */
    public static function getColumnId($name){
        $column_id = '';
        $column_info = NewsColumn::find()->where(['name'=>'视频'])->select("column_id")->asArray()->one();
        if(!empty($column_info)){
            $column_id = $column_info['column_id'];
        }
        return $column_id;
    }
    
    /*
     * 获取 栏目对应的 相关新闻
     *
     * */
    public static function getSameList($column_type,$column_id, $pageStart, $pageEnd){
        $model = new News();
        $news_infos = array();
        if($column_id){
            if($column_type == 1){
                $area_model = new Area();
                $ret = $area_model::find()->where(['area_id'=>$column_id])->asArray()->one();
                $andwhere = 'news.area_id = '.$column_id;
            }else{
                $ret = NewsColumn::find()->where(['column_id'=>$column_id])->asArray()->one();
                $andwhere = 'news.column_id = '.$column_id;
            }

            if($ret){
                $trans_field = ' ,vrnews1.news_video.video_url as video_url,vrnews1.news_video.video_url1,vrnews1.news_video.video_url2,vrnews1.news_video.width,vrnews1.news_video.width1,vrnews1.news_video.width2,vrnews1.news_video.height,vrnews1.news_video.height1,vrnews1.news_video.height2,vrnews1.news_video.size,vrnews1.news_video.size1,vrnews1.news_video.size2 ,vrnews1.news_video.`file_id` as file_id';
                $news_infos = $model::find()
                    ->join('LEFT JOIN', 'news_video', 'news.news_id = news_video.news_id')
                    ->where("news.weight >= 70 and news.type not in (2,9,10,11,12,13,14,15,16,17) and news.top_status = 0  and news.special_news_id = 0 and news.status=0 and ".$andwhere)

                    ->select([
                        "news.news_id",
                        "abstract as news_abstract",
                        "title",
                        "subtitle",
                        "content",
                        "cover_image",
                        "vote_id",
                        "reference_type",
                        "reference_id",
                        "type",
                        "column_id",
                        "area_id",
                        "DATE_FORMAT(create_time,'%Y/%m/%d %H:%i:%s') as create_time",
                        "type_id",
                        "special_news_id",
                        "top_status",
                        "full_status",
                        "full_title",
                        "full_subtitle",
                        "full_cover_image",
                        "source_id",
                        "source_name",
                        "special_id",
                        "special_type",
                        "special_title",
                        "special_abstract",
                        "special_image",
                        "thumbnail_url",
                        "duration",
                        "play_count",
                        "category",
                        "outer_url_ishot",
                        "outer_url",
                        "external_link",
                        "year(create_time) as year1",
                        "month(create_time) as month1",
                        "day(create_time) as day1",
                        "year(from_unixtime(refresh_time)) as year",
                        "month(from_unixtime(refresh_time)) as month",
                        "day(from_unixtime(refresh_time)) as day",
                        "from_unixtime(refresh_time) as refresh_time".$trans_field
                    ])

                    ->orderBy([
                            'case when `year` is null then `year1` else `year` end' => SORT_DESC,
                            'case when `month` is null then `month1` else `month` end'    => SORT_DESC,
                            'case when `day` is null then `day1` else `day` end' 			=> SORT_DESC,
                            'top_status' => SORT_DESC,
                            'weight'=>SORT_DESC,
                            'refresh_time' => SORT_DESC,
                            'create_time' => SORT_DESC,
                        ]
                    )
                    ->offset($pageStart)
                    ->limit($pageEnd-$pageStart)
                    ->asArray()
                    ->all();

                foreach ($news_infos as $key=>$val){
                    //查看是否有 引用新闻
                    if(!empty($val['reference_type']) && $val['reference_type'] == 1 && !empty($val['reference_id'])){
                        //查看 引用视频信息
                        $ref_news = NewsVideo::find()->where(" news_id = ".$val['reference_id'])->asArray()->one();
                        $news_infos[$key]["thumbnail_url"]   = $ref_news['thumbnail_url'];
                        $news_infos[$key]["duration"]        = $ref_news['duration'];
                        $news_infos[$key]["play_count"]      = $ref_news['play_count'];
                        $news_infos[$key]["category"]        = $ref_news['category'];
                    }

                    if($val['type'] == 5){
                        $news_infos[$key]['content'] = json_decode($val['content']);
                    }else{
                        $news_infos[$key]['content'] = [];
                    }

                    if($val['vote_id']){
                        $news_infos[$key]['vote_url'] = yii::$app->params['vote_url'];
                    }

                    if($val['title']){
                        $news_infos[$key]['title'] = html_entity_decode($val['title'], ENT_QUOTES);
                    }
                    $news_info[$key] = NewsColumn::Processdata($news_infos[$key]);
                }

            }else{
                return false;
            }
        }

        return $news_infos;
    }


    public static function Processdata($news_info){
        //处理  返回值
        if($news_info['video_url']){
            unset($news_info['video_url1']);
            unset($news_info['video_url2']);
        }else if($news_info['video_url1']){
            $news_info['video_url'] = $news_info['video_url1'];
            unset($news_info['video_url1']);
            unset($news_info['video_url2']);
        }else if($news_info['video_url2']){
            $news_info['video_url'] = $news_info['video_url2'];
            unset($news_info['video_url1']);
            unset($news_info['video_url2']);
        }else{
            unset($news_info['video_url1']);
            unset($news_info['video_url2']);
        }
        if($news_info['height']){
            unset($news_info['height1']);
            unset($news_info['height2']);
        }else if($news_info['height1']){
            $news_info['height'] = $news_info['height1'];
            unset($news_info['height1']);
            unset($news_info['height2']);
        }else if($news_info['height2']){
            $news_info['height'] = $news_info['height2'];
            unset($news_info['height1']);
            unset($news_info['height2']);
        }else{
            unset($news_info['height1']);
            unset($news_info['height2']);
        }
        if($news_info['width']){
            unset($news_info['width1']);
            unset($news_info['width2']);
        }else if($news_info['width1']){
            $news_info['width'] = $news_info['width1'];
            unset($news_info['width1']);
            unset($news_info['width2']);
        }else if($news_info['width2']){
            $news_info['width'] = $news_info['width2'];
            unset($news_info['width1']);
            unset($news_info['width2']);
        }else{
            unset($news_info['width1']);
            unset($news_info['width2']);
        }
        if($news_info['size']){
            unset($news_info['size1']);
            unset($news_info['size2']);
        }else if($news_info['size1']){
            $news_info['size'] = $news_info['size1'];
            unset($news_info['size1']);
            unset($news_info['size2']);
        }else if($news_info['size2']){
            $news_info['size'] = $news_info['size2'];
            unset($news_info['size1']);
            unset($news_info['size2']);
        }else{
            unset($news_info['size1']);
            unset($news_info['size2']);
        }

        return $news_info;
    }
    
    
}
