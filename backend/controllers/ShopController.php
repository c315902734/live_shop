<?php
namespace backend\controllers;

use app\models\AdminUser;
use backend\models\SysMenuAdmin;
use common\models\ApiResponse;
use common\models\CompanyBanner;
use common\models\CompanyInfo;
use common\models\News;
use common\models\ShopOrder;
use common\models\SpecialColumnType;
use OAuth2\Request;
use Yii;
use yii\base\Exception;
use yii\db\Query;
use yii\rest\Controller;
use common\models\Category;
use common\models\Company;
use common\models\Brand;
use common\models\Goods;
use common\models\Garbage;
use common\models\GoodsAttribute;
use common\models\VirtualGoodsInfo;
use common\models\GoodsAttributeValues;


/**
 * 新闻相关接口
 */
class ShopController extends PublicBaseController
{

    /**
     *  后台用户登录
     *
     *
     */
    public function actionLogin(){
        if(yii::$app->request->isPost){
            $username = isset($this->params['username']) ? $this->params['username'] : '';
            $password = isset($this->params['password']) ? $this->params['password'] : '';

            if(!$username || !$password) $this->_errorData('1301', '参数错误');

            $user_info = AdminUser::find()->where(['username'=>$username, 'status'=>1])->asArray()->one();

            $authcode = 'mMuv1JM8xVBLmdCyKH';
            $password = "###".md5(md5($authcode.$password));

            if(!$user_info) $this->_errorData('1302', '用户名或密码错误');

            //登录成功
            if($user_info && $password == $user_info['admin_pwd']){
                if($user_info['username'] != 'admin'){
                    $company_admin_info = SysMenuAdmin::find()
                        ->where("admin_id={$user_info['admin_id']} AND company_id <> 0")
                        ->asArray()
                        ->one();
                    if(!$company_admin_info) $this->_errorData('1304','此账号无商城权限');

                    $company_info = Company::find()->where(['company_id'=>$company_admin_info['company_id']])->asArray()->one();
                    if(!$company_info) $this->_errorData('1305', '此账号无商城权限');
                    if($company_info['status'] != 1) $this->_errorData('1306', '该地区已经暂时关闭');
                }else{
                    $company_admin_info['company_id'] = 1;
                }

                $return_data = [
                    'username'  => $user_info['username'],
                    'type'      => $user_info['type'],
                    'user_type' => $user_info['user_type'],
                    'company_id'=> $company_admin_info['company_id'],
                ];
                $this->_successData($return_data);
            }
            $this->_errorData('1303', '帐号或密码错误，请核对后在进行登录');
        }
        $this->_errorData('1300', '请求错误');
    }

	
	/**
	 * 分公司列表
	 * @by  cong.zhao  2017-05-15
	 * @return array
	 */
	function actionCompanyList(){
//		$company_list = Company::GetList();
//		$this->_successData($company_list);
		$company_list = Company::find()
            ->where([
                'status' => 1
            ])
            ->asArray()
            ->all();

		$this->_successData($company_list);
	}
	
	
	/**
     * 属性列表
     * @by cong.zhao 2017-05-17
     * @param goods
     */
    function actionAttributeList(){
        $company_id = yii::$app->request->post('company_id', 0);
        $page = yii::$app->request->post('page', 1);
        $size = yii::$app->request->post('size', 10);
        if(!$company_id) $this->_errorData('11000', '参数错误');

        $attribute_list = GoodsAttribute::GetList($company_id, $page, $size);
        $this->_successData($attribute_list);
    }

    /**
     * 商品属性列表
     */
    public function actionGoodsAttrList(){
        $goods_id = yii::$app->request->post('goods_id', 0);
        $page = yii::$app->request->post('page', 0);
        $size = yii::$app->request->post('size', 10);
        if (!$goods_id) $this->_errorData('11001', '参数错误');

        $goods_attr_list = GoodsAttributeValues::GetList($goods_id, $page, $size);
        $this->_successData($goods_attr_list);
    }


	/**
	 * 分类列表
	 * @by  cong.zhao  2017-05-15
	 * @param company_id            分公司ID(不传为总公司)
	 * @param examine_status       审核状态(0:未审核 1:审核中 2:审核通过 3:审核不通过)
	 * @param category_name       分类名称
	 * @param page       					当前页码
	 * @param pageS
     * @size       			每页数量
	 * @return array
	 */
    function actionCategoryList(){
        $page       = isset($this->params['page']) ? $this->params['page'] : 1;
        $page_size  = isset($this->params['page_size']) ? $this->params['page_size'] : 10;
        $company_id = isset($this->params['company_id']) ? $this->params['company_id'] : '';
        $parent_id  = isset($this->params['parent_id']) ? intval($this->params['parent_id']) : 0;
        $category_name    = isset($this->params['category_name']) ? $this->params['category_name'] : '';
        $review_status    = isset($this->params['review_status']) ? $this->params['review_status']: 2;    //审核状态
        $include_children = isset($this->params['include_children']) ? $this->params['include_children']: 0;    //审核状态

        if(!$company_id){
        	$this->_errorData('0001','参数错误');
        }
        
        $category_list = Category::GetList($company_id, $parent_id, $category_name, $review_status, $include_children, $page, $page_size);

        $this->_successData($category_list);
    }

    /**
     * 添加分类
     * @by  cong.zhao  2017-05-15
     * @param company_id            		分公司ID
     * @param parent_id       				父级id
     * @param category_name       	分类名称
     * @param is_show       					是否显示 (0:不显示  1:显示)
     * @param sort       						排序
     * @param category_describe  	描述
     * @return float
     */
    function actionAddCategory(){
        $c_id = isset($this->params['category_id'])?$this->params['category_id']:'0';                //分类ID
    	$company_id  = isset($this->params['company_id'])?$this->params['company_id']:'0';
    	$parent_id   = isset($this->params['parent_id'])?$this->params['parent_id']: 0;
    	$category_name  = isset($this->params['category_name'])?$this->params['category_name']:'';
    	$is_show  = isset($this->params['is_show'])?$this->params['is_show']:'0';
    	$sort  = isset($this->params['sort'])?$this->params['sort']:'0';
    	$category_describe  = isset($this->params['category_describe'])?$this->params['category_describe']:'';

    	if(!$company_id || !$category_name){
    		$this->_errorData('0001','参数错误');
    	}
    	
    	$category_id = Category::AddCategory($company_id, $c_id, $parent_id, $category_name, $is_show, $sort, $category_describe);
    
    	if($category_id){
    		$this->_successData(array('category_id'=>$category_id), "编辑成功");
    	}else{
    		$this->_errorData('0002','编辑失败');
    	}
    }

    /**
     * 分类详情
     */
    public function actionCategoryInfo(){
        $company_id   = yii::$app->request->post('company_id', 0);
        $category_id  = yii::$app->request->post('category_id', 0);

        if(!$category_id){
            $this->_errorData('0001','参数错误');
        }

        $category_info = Category::find()
            ->alias('c')
            ->select(['c.*', 'c1.category_name as parent_name'])
            ->leftJoin('vrshop.category c1', 'c.parent_id = c1.category_id')
            ->where(['c.category_id'=>$category_id])
            ->asArray()
            ->one();

        $this->_successData($category_info);
    }


    /**
     * 删除分类
     */
    public function actionDelCategory(){
        $category_id = yii::$app->request->post('category_id', 0); //分类ID
        if(!$category_id) $this->_errorData('3000', '参数错误');

        $del_category_ret = Category::updateAll(['is_del'=>1], "category_id = {$category_id} OR parent_id = {$category_id}");

        if($del_category_ret) $this->_successData('删除成功');
        $this->_errorData('3001', '删除失败');
    }
    
    /**
     * 品牌列表
     * @by cong.zhao 2017-05-15
     * @param company_id            		分公司ID
     * @param brand_name   				品牌名称
     * @param page       						当前页码
	 * @param pageSize       				每页数量
	 * @return array
     */
    function actionBrandList(){
    	$company_id = isset($this->params['company_id'])?$this->params['company_id']:'0';
    	$brand_name = isset($this->params['brand_name'])?$this->params['brand_name']:'';
    	$is_show    = isset($this->params['is_show']) ? $this->params['is_show'] : false;
    	$page      = isset($this->params['page'])?$this->params['page']:'1';
    	$pageSize     = isset($this->params['pageSize'])?$this->params['pageSize']:10;
    	$pageStart = ($page - 1) * $pageSize;
    	$pageEnd = $page * $pageSize;
    	
    	if(!$company_id){
    		$this->_errorData('0001','参数错误');
    	}
    	
    	$brand_list = Brand::GetList($company_id, $brand_name, $is_show, $pageStart, $pageEnd);
    	
    	$this->_successData($brand_list);
    }
    

    /**
     * 添加品牌
     * @by cong.zhao 2017-05-15
     * @param company_id            		分公司ID
     * @param brand_name            	品牌名称
     * @param logo                             品牌logo
     * @param is_show            	        是否显示(0不显示 1显示)
     * @param brand_url                    品牌官网url
     * @param brand_introduce        品牌介绍
     * @return float
     */
    public function actionAddBrand(){
    	$company_id = isset($this->params['company_id'])?$this->params['company_id']:'0';
    	$brand_id   = isset($this->params['brand_id']) ? $this->params['brand_id'] : 0;
    	$brand_name = isset($this->params['brand_name'])?$this->params['brand_name']:'';
    	$logo = isset($this->params['logo'])?$this->params['logo']:'';
    	$is_show = isset($this->params['is_show'])?$this->params['is_show']:'0';
    	$brand_url = isset($this->params['brand_url'])?$this->params['brand_url']:'';
    	$brand_introduce = isset($this->params['brand_introduce'])?$this->params['brand_introduce']:'';

    	if(!$company_id || !$brand_name){
    		$this->_errorData('0001','参数错误');
    	}
    	
    	$brand_id = Brand::AddBrand($brand_id, $company_id, $brand_name, $logo, $is_show, $brand_url, $brand_introduce);
    	
    	if($brand_id){
    		$this->_successData(array('brand_id'=>$brand_id), "成功");
    	}else{
    		$this->_errorData('0002','失败');
    	}
    }

    /**
     * 删除品牌
     */
    public function actionDelBrand(){
        $brand_id = yii::$app->request->post('brand_id', 0);
        if(!$brand_id) $this->_errorData('0001', '参数错误');

        $up_brand_ret = Brand::updateAll(['is_del'=>1], ['brand_id'=>$brand_id]);
        if(!$up_brand_ret) $this->_errorData('00011', '删除失败');

        $this->_successData('删除成功');
    }

    /**
     * 后台 添加属性
     */
    public function actionAddAttribute(){
        $company_id = yii::$app->request->post('company_id', 0);
        $attr_name  = yii::$app->request->post('attr_name', '');
        if(!$company_id || !$attr_name) $this->_errorData('10000', '参数错误');

        $ret = GoodsAttribute::addAttr($company_id, $attr_name);
        if($ret) $this->_successData($ret);
        $this->_errorData('10001', 'error');
    }

    /**
     * 后台 商品详情
     */
    public function actionGoodsInfo(){
        $goods_id = yii::$app->request->post('goods_id');
        if(!$goods_id) $this->_errorData('8000', '参数错误');

        $info = Goods::getGoodsInfo($goods_id);
        $this->_successData($info);
    }

    /**
     * 直播中关联商品
     * 选择商品列表
     */
    public function actionLiveGoodsList(){
        $company_id = isset($this->params['company_id']) ? $this->params['company_id'] : 0;
        $section_id = isset($this->params['section_id']) ? $this->params['section_id'] : 0;
        $goods_name = isset($this->params['goods_name']) ? $this->params['goods_name'] : '';
        $brand_name = isset($this->params['brand_name']) ? $this->params['brand_name'] : '0';
        $freight_type = isset($this->params['freight_type']) ? $this->params['freight_type'] : '0';
        $goods_tag = isset($this->params['goods_tag']) ? $this->params['goods_tag'] : '';
        $add_status = isset($this->params['add_status']) ? $this->params['add_status'] : 0;
        $page       = isset($this->params['page']) ? $this->params['page'] : '1';
        $size       = isset($this->params['size']) ? $this->params['size'] : 10;

        $list = Goods::liveGoodsList($company_id, $section_id, $goods_name, $brand_name, $freight_type, $goods_tag, $add_status, $page, $size);
        $this->_successData($list);
    }

    /**
     * 商品列表
     * @by cong.zhao 2017-05-16
     * @param company_id            		分公司ID
     * @param brand_id            		    品牌ID
     * @param is_shelves                    上架状态(0:下架  1:上架)
     * @param recommend_status     推荐审核状态(0:未审核 1:审核中 2:审核通过 3:审核不通过)
     * @param goods_name     			商品名称
     * @param goods_type               	 商品类型
     * @param sale_type               	 销售类型 0 全部  1 商城  2 直播
     * @param live_goods_verify_state    直播商品审核  0 未通过  1已通过
     * @return array
     */
    public function actionGoodsList(){
    	$company_id = isset($this->params['company_id'])?$this->params['company_id']:'0';
    	$brand_id = isset($this->params['brand_id'])?$this->params['brand_id']:'0';
    	$is_shelves = yii::$app->request->post('is_shelves', null);
    	$recommend_status = isset($this->params['recommend_status'])?$this->params['recommend_status']:'';
    	$goods_name = isset($this->params['goods_name'])?$this->params['goods_name']:'';
    	$goods_type = isset($this->params['goods_type'])?$this->params['goods_type']:'0';
    	$sale_type  = isset($this->params['sale_type'])?$this->params['sale_type']:'0';
        $live_goods_verify_state  = isset($this->params['live_goods_verify_state'])?$this->params['live_goods_verify_state']:false;
    	$stock_warning = isset($this->params['stock_warning']) ? $this->params['stock_warning'] : 0;
    	$page       = isset($this->params['page'])?$this->params['page']:'1';
    	$pageSize   = isset($this->params['pageSize'])?$this->params['pageSize']:10;

//    	if(!$company_id){
//    		$this->_errorData('0001','参数错误');
//    	}

    	$goods_list = Goods::GetList($company_id, $brand_id, $is_shelves, $recommend_status, $goods_name, $goods_type, $stock_warning, $sale_type, $live_goods_verify_state, '',$page, $pageSize);
    	$this->_successData($goods_list);
    }

    /**
     * 添加商品
     * @by cong.zhao 2017-05-17
     * @param company_id            		分公司ID
     * @param goods_name               商品名称
     * @param tags               				标签
     * @param art_no               			货号
     * @param huiwenbi               		汇闻币
     * @param market_price               市场价
     * @param brand_id               		品牌id
     * @param banner_image            商品封面图
     * @param category_id               	分类id
     * @param attribute_list               	属性数组
     * @param goods_stock               	总库存
     * @param goods_stock_warning        库存预警数
     * @param freight                          运费
     * @param delivery_area_id          发货地id
     * @param goods_type               	 商品类型
     * @param card_type               	 	 虚拟商品添加方式(1：单张卡 2：批量卡)
     * @param virtual_goods_info      虚拟商品列表（暂时还没想到怎么用）
     * @param goods_introduce         商品介绍
     * @param is_shelves                     0:下架  1:上架
     * @param sale_type                     销售类型 0 全部  1 商城  2 直播
     * @return integer
     */
    public function actionAddGoods(){
    	$goods_id = isset($this->params['goods_id'])?$this->params['goods_id']:'0';
    	$company_id = isset($this->params['company_id'])?$this->params['company_id']:'0';
    	$goods_name = isset($this->params['goods_name'])?$this->params['goods_name']:'';
    	$tags = isset($this->params['tags'])?$this->params['tags']:'';
    	$abstract = isset($this->params['abstract'])?$this->params['abstract'] : '';
    	$art_no = isset($this->params['art_no'])?$this->params['art_no']:'';
        $video_url = isset($this->params['video_url']) ? $this->params['video_url'] : '';
    	$pay_type  = isset($this->params['pay_type']) ? $this->params['pay_type'] : false;   //支付方式  0 ：汇闻币  1：汇闻币 + 第三方支付  2：第三方支付
    	$huiwenbi  = yii::$app->request->post('huiwenbi', 0);
    	$rmb_price = isset($this->params['rmb_price']) ? $this->params['rmb_price'] : 0;
    	$market_price = isset($this->params['market_price'])?$this->params['market_price']:'0';
    	$brand_id = isset($this->params['brand_id'])?$this->params['brand_id']:'0';
    	$brand_name = isset($this->params['brand_name'])?$this->params['brand_name']:'';
    	$banner_image = isset($this->params['banner_image'])?$this->params['banner_image']:'';
    	$category_id = isset($this->params['category_id'])?$this->params['category_id']:'0';
    	$attribute_list = isset($this->params['attribute_list']) ? json_decode($this->params['attribute_list'], true) : array();
    	$goods_stock = isset($this->params['goods_stock'])?$this->params['goods_stock']:'0';
    	$goods_stock_warning = isset($this->params['goods_stock_warning'])?$this->params['goods_stock_warning']:'0';
    	$freight_type = isset($this->params['freight_type'])?$this->params['freight_type']:'0';
    	$freight = isset($this->params['freight'])?$this->params['freight']:'0';
    	$delivery_area = isset($this->params['delivery_area'])?$this->params['delivery_area']:'';
    	$goods_type = isset($this->params['goods_type'])?$this->params['goods_type']:'1';
    	$card_type = isset($this->params['card_type'])?$this->params['card_type']:'1';
    	$virtual_goods_info = isset($this->params['virtual_goods_info']) ? json_decode($this->params['virtual_goods_info'], true) : array();
    	$goods_introduce = isset($this->params['goods_introduce'])?$this->params['goods_introduce']:'';
    	$is_shelves = isset($this->params['is_shelves'])?$this->params['is_shelves']:false;

    	/*
    	 * 直播关联商品
    	 * 增加售卖类型
    	 */
    	$sale_type = yii::$app->request->post('sale_type', 0);  // 0:全部 1：商城 2：直播间

    	if(!$company_id || !$goods_name || $pay_type === false || !$brand_id || !$banner_image || !$category_id  || !$goods_stock || !$goods_stock_warning || !$goods_type || !$goods_introduce || $is_shelves == ''){
            $this->_errorData('0001','参数错误');
        }

        if($pay_type == 1 && !$rmb_price) $this->_errorData('请填写人民币价格');

    	if($goods_type == '2' && $card_type == '1' && isset($virtual_goods_info['single']['serial_number'])){
    		$this->_errorData('0002','卡号序列号不能为空');
    	}

    	$goods_id = Goods::AddGoods(
    			$goods_id,
    			$company_id,
    			$goods_name,
    			$tags,
                $abstract,
    			$art_no,
                $video_url,
                $pay_type,
    			$huiwenbi,
    			$rmb_price,
    			$market_price,
    			$brand_id,
    			$brand_name,
    			$banner_image,
    			$category_id,
    			$attribute_list,
    			$goods_stock,
    			$goods_stock_warning,
    			$freight,
    			$delivery_area,
    			$goods_type,
    			$card_type,
    			$virtual_goods_info,
    			$goods_introduce,
    			$is_shelves,
                $freight_type,
                $sale_type
    	);
    	
    	if($goods_id){
    		$this->_successData(array('goods_id'=>$goods_id), "添加成功");
    	}else{
    		$this->_errorData('0002','添加失败');
    	}
    }

    /**
     * 商品上下架
     */
    public function actionEditGoodsShelvesStatus(){
        $goods_id = yii::$app->request->post('goods_id', 0);
        $shelves_status = yii::$app->request->post('shelves_status', 0);
        if(!$goods_id || !in_array($shelves_status, array(0, 1))) $this->_errorData('40000', '参数错误');

        $goods_id_arr = json_decode($goods_id, true);
        if(!$goods_id_arr) $this->_errorData('40000', '商品ID错误');

        $up_ret = Goods::updateAll(['is_shelves'=>$shelves_status], ['goods_id'=>$goods_id_arr]);
        if($up_ret) $this->_successData('修改成功');
        $this->_errorData('40002', '修改失败');
    }

    /**
     * 分公司 提交商品推荐
     * @param goods_id  					商品id
     * @return integer
     */
    public function actionCommitGoodsRecommend(){
        $goods_id = isset($this->params['goods_id'])?$this->params['goods_id']:'0';

        if(!$goods_id){
            $this->_errorData('0001','参数错误');
        }

        $goods_info = Goods::findOne($goods_id);
        if($goods_info->recommend_status > 0) $this->_errorData('00023', '此商品已经提交推荐申请或已通过审核');

        $goods_info->recommend_status = 1;
        $goods_info->save();
        $goods_id = $goods_info->goods_id;

        if($goods_id){
            $this->_successData(array('goods_id'=>$goods_id), "设置成功");
        }else{
            $this->_errorData('0002','设置失败');
        }
    }

    /**
     * 商品推荐审核(总公司审核分公司提交的推荐审核
     * @by cong.zhao 2017-05-17
     * @param goods_id  					商品id
     * @param recommend_status     推荐审核状态
     * @return integer
     */
    public function actionSetRecommendStatus(){
    	$goods_id = isset($this->params['goods_id'])?$this->params['goods_id']:'0';
    	$recommend_status = isset($this->params['recommend_status'])?$this->params['recommend_status']:'0';
    	
    	if(!$goods_id || !$recommend_status){
    		$this->_errorData('0001','参数错误');
    	}
    	if(!in_array($recommend_status, array(2,3))) $this->_errorData('00022', '审核状态码错误');

    	$goods_info = Goods::findOne($goods_id);
    	if($goods_info->recommend_status == 0) $this->_errorData('00021', '此商品未提交推荐审核');

    	$goods_id = Goods::SetRecommendStatus($goods_id, $recommend_status);
    	
    	if($goods_id){
    		$this->_successData(array('goods_id'=>$goods_id), "设置成功");
    	}else{
    		$this->_errorData('0002','设置失败');
    	}
    } 
    
    
    
    /**
     * 商品下架
     * @by cong.zhao 2017-05-18
     * @param goods_ids_arr  	商品id集合
     * @return integer
     */
    public function actionShelvesGoods(){
    	$goods_ids_arr = isset($this->params['goods_ids_arr'])?$this->params['goods_ids_arr']:array();
    	 
    	if(empty(goods_ids_arr)){
    		$this->_errorData('0001','参数错误');
    	}
    	 
    	$return_status = Goods::ShelvesGoods($goods_ids_arr);
    	 
    	if($return_status){
    		$this->_successData('1', "设置成功");
    	}else{
    		$this->_errorData('0002','设置失败');
    	}
    }
    
    
    /**
     * 商品批量回收
     * @by cong.zhao 2017-05-17
     * @param goods_ids_arr  	商品id集合
     * @return integer
     */
    public function actionRecoveryGoods(){
        $company_id    = yii::$app->request->post('company_id', 0);
        $goods_ids_arr = isset($this->params['goods_ids_arr']) ? json_decode($this->params['goods_ids_arr'], true) : array();
        if(!$company_id || empty($goods_ids_arr)){
            $this->_errorData('0001','参数错误');
        }

        $return_status = Garbage::AddGarbage($company_id, $goods_ids_arr);

         if($return_status){
             $this->_successData("设置成功");
         }else{
             $this->_errorData('0002','设置失败');
         }
    }
    
    
    
    /*--------------------------------------------------------回收站（开始）--------------------------------------------------*/
    /**
     * 商品回收站列表
     * @by cong.zhao 2017-05-18
     * @param company_id  	分公司id
     * @param keyword  	        关键字(搜索商品名或货号)
     * @return array
     */
    public function actionGarbageList(){
    	$company_id = isset($this->params['company_id'])?$this->params['company_id']:'0';
    	$keyword = isset($this->params['keyword'])?$this->params['keyword']:'';
    	$search_start_time = isset($this->params['search_start_time']) ?$this->params['search_start_time']:'';
    	$search_end_time = isset($this->params['search_end_time'])?$this->params['search_end_time']:'';
    	$page      = isset($this->params['page'])?$this->params['page']:'1';
    	$pageSize     = isset($this->params['pageSize'])?$this->params['pageSize']:10;
    	$pageStart = ($page - 1) * $pageSize;
    	$pageEnd = $page * $pageSize;
    	 
    	if(!$company_id){
    		$this->_errorData('0001','参数错误');
    	}

    	$garbage_list = Garbage::GetList($company_id, $keyword, $search_start_time, $search_end_time, $pageStart, $pageEnd);

    	if($garbage_list){
    		$this->_successData($garbage_list);
    	}else{
    		$this->_errorData('0002','设置失败');
    	}
    }
    
    
    /**
     * 回收站批量恢复/删除
     * @by cong.zhao 2017-05-19
     * @param garbage_ids_arr  	回收站id集
     * @param type  	                    1:恢复  2:删除
     * @return array
     */
    public function actionSetGarbages(){
    	$garbage_ids_arr = isset($this->params['garbage_ids_arr']) ? json_decode($this->params['garbage_ids_arr'], true) : array();
    	$type = isset($this->params['type'])?$this->params['type']:'1';

    	if(empty($garbage_ids_arr)){
    		$this->_errorData('0001','参数错误');
    	}
    
    	$return_status = Garbage::SetGarbages($garbage_ids_arr, $type);
    
    	if($return_status){
    		$this->_successData('1', "设置成功");
    	}else{
    		$this->_errorData('0002','设置失败');
    	}
    }
    /*--------------------------------------------------------回收站（结束）--------------------------------------------------*/
    
    
    
    /*--------------------------------------------------------虚拟商品（开始）--------------------------------------------------*/
    /**
     * 虚拟商品列表
     * @by cong.zhao 2017-05-17
     * @param goods_id   商品id
     */
    public function actionVirtualGoodsList(){
    	$goods_id = isset($this->params['goods_id'])?$this->params['goods_id']:'0';
    	$is_sold  = isset($this->params['is_sold'])?$this->params['is_sold']:false;
        $is_activity = isset($this->params['is_activity'])?$this->params['is_activity']:'0';
        $stock_warning = isset($this->params['stock_warning'])?$this->params['stock_warning']:0;
    	$keyword     = isset($this->params['keyword'])?$this->params['keyword']:'';
    	$page        = isset($this->params['page'])?$this->params['page']:'1';
    	$pageSize    = isset($this->params['pageSize'])?$this->params['pageSize']:10;
    	$pageStart   = ($page - 1) * $pageSize;
    	$pageEnd     = $page * $pageSize;

    	if(!$goods_id){
    		$this->_errorData('0001','参数错误');
    	}
    	
    	$virtual_goods_list = VirtualGoodsInfo::GetList($goods_id, $keyword, $is_sold, $is_activity, $stock_warning, $pageStart, $pageEnd);
    	
    	$this->_successData($virtual_goods_list);
    }
    
    
    /**
     * 虚拟商品补货/编辑
     * @by cong.zhao 2017-05-18
     * @param goods_id   商品id
     * @param details_id  虚拟商品id
     */
    public function actionAddVirtualGoods(){
    	$goods_id = isset($this->params['goods_id'])?$this->params['goods_id']:'0';
    	$details_id = isset($this->params['details_id'])?$this->params['details_id']:'0';
    	$card_type = isset($this->params['card_type'])?$this->params['card_type']:'1';
    	$virtual_goods_info = isset($this->params['virtual_goods_info']) ? json_decode($this->params['virtual_goods_info'], true) : array();

    	if(!$goods_id){
    		$this->_errorData('0001','参数错误');
    	}

    	if($card_type == '1' && !$virtual_goods_info['single']['serial_number']){
    		$this->_errorData('0001','卡号序列号不能为空');
    	}

    	$goods_id = VirtualGoodsInfo::AddVirtualGoods($goods_id, $details_id, $card_type, $virtual_goods_info);

    	if($goods_id){
    		$this->_successData('1', "成功");
    	}else{
    		$this->_errorData('0002','失败');
    	}
    }
    
    
    /**
     * 虚拟商品批量删除
     * @by cong.zhao 2017-05-18
     * @param goods_id   商品id
     * @param details_id  虚拟商品id
     */
    public function actionDeleteVirtualGoods(){
    	$details_ids_arr = isset($this->params['details_ids_arr'])?$this->params['details_ids_arr']:array();
    
    	if(empty($details_ids_arr)){
    		$this->_errorData('0001','参数错误');
    	}
    	 
    
    	$return_status = VirtualGoodsInfo::DeleteVirtualGoods($details_ids_arr);
    
    	if($return_status){
    		$this->_successData('1', "删除成功");
    	}else{
    		$this->_errorData('0002','删除失败');
    	}
    }
    /*--------------------------------------------------------虚拟商品（结束）--------------------------------------------------*/
    
    
    
    /**
     * 商品属性列表(暂时不用)
     * @by cong.zhao 2017-05-17
     * @param goods_id   商品id
     */
    public function actionGoodsAttributeValuesList(){
    	$goods_id = isset($this->params['goods_id'])?$this->params['goods_id']:'0';
    	$page      = isset($this->params['page'])?$this->params['page']:'1';
    	$pageSize     = isset($this->params['pageSize'])?$this->params['pageSize']:10;
    	$pageStart = ($page - 1) * $pageSize;
    	$pageEnd = $page * $pageSize;

    	if(!$goods_id){
    		$this->_errorData('0001','参数错误');
    	}
    	 
    	$virtual_goods_list = GoodsAttributeValues::GetList($goods_id, $pageStart, $pageEnd);
    	 
    	$this->_successData($virtual_goods_list);
    }


    /**
     * 配置商家信息
     * @param company_id  分公司ID
     * @param company_name  分公司名称
     * @param company_contact_person  分公司联系人
     * @param company_phone  分公司联系电话
     * @param company_email  分公司邮箱
     */
    public function actionSetCompanyInfo(){
        $company_id   = isset($this->params['company_id'])?$this->params['company_id']:'0';
        $company_name = isset($this->params['name'])?$this->params['name']:'0';
        $company_contact_person = isset($this->params['contact_person'])?$this->params['contact_person']:'0';
        $company_phone = isset($this->params['phone'])?$this->params['phone']:'0';
        $company_email = isset($this->params['email'])?$this->params['email']:'0';

        if(!$company_id || !$company_name || !$company_contact_person){
            $this->_errorData('0001','参数错误');
        }

        $company_info_model = new CompanyInfo();

        //先删除
        $company_info = $company_info_model::deleteAll(['company_id'=>$company_id]);

        //再添加
        $company_info_model->company_id = $company_id;
        $company_info_model->company_name = $company_name;
        $company_info_model->company_contact_person = $company_contact_person;
        $company_info_model->company_phone = $company_phone;
        $company_info_model->company_email = $company_email;
        $company_info_model->create_time = time();

        $ret = $company_info_model->save();

        if($ret){
            $this->_successData('成功');
        }
        $this->_successData('0070', '失败');
    }

    /**
     * 获取商家信息
     * @param company_id  分公司ID
     */
    public function actionGetCompanyInfo(){
        $company_id   = isset($this->params['company_id'])?$this->params['company_id']:'0';

        if(!$company_id){
            $this->_errorData('0001','参数错误');
        }

        $company_info_model = new CompanyInfo();

        $ret = $company_info_model::find()->where(['company_id'=>$company_id])->asArray()->one();

        if($ret){
            $this->_successData($ret);
        }
        $this->_successData('0070', '失败');
    }

    /**
     * 添加banner
     * @param company_id  分公司ID
     * @param banner_desc  banner描述
     * @param banner_link  banner地址
     * @param banner_img_url  banner图片地址
     */
    public function actionAddBanner(){
        $company_id   = isset($this->params['company_id'])?$this->params['company_id']:'0';
        $banner_desc  = isset($this->params['banner_desc'])?$this->params['banner_desc']:'0';
        $banner_link  = isset($this->params['banner_link'])?$this->params['banner_link']:'0';
        $banner_img_url = isset($this->params['banner_img_url'])?$this->params['banner_img_url']:'0';
        $is_global    = isset($this->params['is_global']) ? $this->params['is_global'] : '0';

        if(!$company_id || !$banner_img_url){
            $this->_errorData('0001','参数错误');
        }

        if($is_global == 1 && $company_id != 1) $this->_errorData('00810', '参数错误');

        $company_banner_model = new CompanyBanner();

        //每个公司下不能超过六个
        $banner_list = $company_banner_model::find()->where(['company_id'=>$company_id])->orderBy('banner_sort desc')->asArray()->all();
        if(count($banner_list) >= 6) $this->_errorData('0081', '最多添加六个轮播图');

        if(!$banner_list){
            $sort = 1;
        }else{
            $sort = $banner_list[0]['banner_sort'] + 1;
        }

        $company_banner_model->company_id = $company_id;
        $company_banner_model->banner_desc = $banner_desc;
        $company_banner_model->banner_link = $banner_link;
        $company_banner_model->banner_img_url = $banner_img_url;
        $company_banner_model->banner_sort = $sort;
        $company_banner_model->is_global   = $is_global;
        $company_banner_model->create_time = time();

        $ret = $company_banner_model->save();

        if($ret){
            $this->_successData('添加成功');
        }
        $this->_errorData('0080', '添加失败');
    }

    /**
     * 编辑banner
     * @param company_id  分公司ID
     * @param banner_id  banner id
     * @param banner_desc  banner描述
     * @param banner_link  bannerlink
     * @param banner_img_url  banner 图片地址
     */
    public function actionEditBanner(){
        $company_id = isset($this->params['company_id'])?$this->params['company_id']:'0';
        $banner_id  = isset($this->params['banner_id'])?$this->params['banner_id']:'0';
        $banner_desc  = isset($this->params['banner_desc'])?$this->params['banner_desc']:'0';
        $banner_link  = isset($this->params['banner_link'])?$this->params['banner_link']:'0';
        $banner_img_url = isset($this->params['banner_img_url'])?$this->params['banner_img_url']:'0';
        $is_global    = isset($this->params['is_global']) ? $this->params['is_global'] : '0';

        if(!$company_id || !$banner_id){
            $this->_errorData('0001','参数错误');
        }

        if($is_global == 1 && $company_id != 1) $this->_errorData('00811', '参数错误');

        $banner_model = new CompanyBanner();
        $banner_info = $banner_model::find()->where(['company_id'=>$company_id, 'banner_id'=>$banner_id])->one();

        if(!$banner_info) $this->_errorData('0082', '找不到这条轮播图信息');

        $ret = $banner_model::updateAll(['banner_desc'=>$banner_desc, 'banner_link'=>$banner_link, 'banner_img_url'=>$banner_img_url, 'is_global'=>$is_global], ['banner_id'=>$banner_id, 'company_id'=>$company_id]);

        if($ret !== false){
            $this->_successData('编辑成功');
        }
        $this->_errorData('0080', '添加失败');
    }

    /**
     * banner list
     * @param company_id  分公司ID
     */
    public function actionBannerList(){
        $company_id = isset($this->params['company_id'])?$this->params['company_id']:'0';

        if(!$company_id){
            $this->_errorData('0001','参数错误');
        }

        $banner_model = new CompanyBanner();
        $banner_list = $banner_model::find()->where(['company_id'=>$company_id])->orderBy('banner_sort asc')->asArray()->all();

        if(!$banner_list) $this->_errorData('0082', '找不到轮播图信息');

        if($banner_list){
            $this->_successData($banner_list);
        }
        $this->_errorData('0080', '添加失败');
    }

    /**
     * banner删除
     * @param banner id
     */
    public function actionDelBanner(){
        $banner_id = isset($this->params['banner_id'])?$this->params['banner_id']:'0';

        if(!$banner_id){
            $this->_errorData('0001','参数错误');
        }

        $banner_model = new CompanyBanner();
        $del_ret = $banner_model::deleteAll(['banner_id'=>$banner_id]);

        if($del_ret){
            $this->_successData('删除成功');
        }
        $this->_errorData('0080', '删除失败');
    }

    /**
     * banner排序
     * @param banner id
     */
    public function actionSortBanner(){
        $company_id = isset($this->params['company_id']) ? $this->params['company_id'] : '0';
        $banner_id  = isset($this->params['banner_id']) ? $this->params['banner_id'] : '0';
        $type  = isset($this->params['type']) ? $this->params['type'] : '0';                    #0 + / 1 -

        if(!$company_id || !$banner_id || !$banner_id){
            $this->_errorData('0001','参数错误');
        }

        //现在的位置
        $banner_info = CompanyBanner::find()->where(['banner_id'=>$banner_id, 'company_id'=>$company_id])->one();
        if(!$banner_info) $this->_errorData('00011', '轮播图信息错误');

        //交换位置
        $temp = $banner_info->banner_sort;

        $transaction = Yii::$app->db->beginTransaction();
        if($type == 0){
            //如果已经是第一的位置
            if($banner_info->banner_sort == 1){
                $this->_errorData('0091', '已经是第一个了');
            }
            if($banner_info->banner_sort == 6){
                $this->_errorData('0092', '已经是最后了');
            }
            //sort 升序
            try{
                //修改上一条sort + 1
                $last_banner_data = CompanyBanner::find()->where(['company_id'=>$company_id, 'banner_sort'=>$temp - 1])->one();
                if(!$last_banner_data) throw new Exception('数据错误，请删除所有banner后再进行操作');

                $last_banner_data->banner_sort = $temp;

                $up_res = $last_banner_data->save();
                if(!$up_res) throw new Exception('修改失败');

                //上一条修改成功后修改现在的这条 - 1
                $current_banner_data = CompanyBanner::find()->where(['company_id'=>$company_id, 'banner_id'=>$banner_id])->one();
                $current_banner_data->banner_sort = $temp - 1;
                $res = $current_banner_data->save();

                if(!$res) throw new Exception('修改失败');

                $transaction->commit();

                $this->_successData('ok');
            }catch(\Exception $e){
                $transaction->rollBack();
                $this->_errorData('0093', $e->getMessage());
            }
            $this->_errorData('0094', '操作失败');
        }else if($type == 1){
            //sort 降序
            if($banner_info->banner_sort == 6){
                $this->_errorData('0099', '已经是最后了，不能再往下降了');
            }
            try{
                //修改上一条sort + 1
                $last_banner_data = CompanyBanner::find()->where(['company_id'=>$company_id, 'banner_sort'=>$temp + 1])->one();
                if(!$last_banner_data) throw new Exception('数据错误，请删除所有banner后再进行操作');

                $last_banner_data->banner_sort = $temp;

                $up_res = $last_banner_data->save();
                if(!$up_res) throw new Exception('修改失败');

                //上一条修改成功后修改现在的这条 - 1
                $current_banner_data = CompanyBanner::find()->where(['company_id'=>$company_id, 'banner_id'=>$banner_id])->one();
                $current_banner_data->banner_sort = $temp + 1;
                $res = $current_banner_data->save();

                if(!$res) throw new Exception('修改失败');

                $transaction->commit();

                $this->_successData('ok');
            }catch(\Exception $e){
                $transaction->rollBack();
                $this->_errorData('0111', $e->getMessage());
            }
            $this->_errorData('0098', '操作失败');
        }

        $this->_errorData('0095', '失败');
    }
}