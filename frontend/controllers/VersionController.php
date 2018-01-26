<?php
namespace frontend\controllers;

use common\models\Version;

class VersionController extends PublicBaseController{
    /**
     * 获取版本信息
     * 安卓
     */
    public function actionGetVersion(){
        $version_info = Version::find()->where(['status'=>1,'system_type'=>0])
            ->select('id,version,url,info,update_mode,is_formal')->asArray()->one();
        if(!empty($version_info)){
            $version_info['url'] = $version_info['url'];
        }
        $this->_successData($version_info);
    }

    /**
     * 获取版本信息(新)
     * 安卓
     */
    public function actionGetAndroidVersion(){
        $version = isset($this->params['version']) ? $this->params['version'] : '';
        if(!$version){return $this->_errorData('404', '版本号错误');}
        //获取最新版本
        $version_info = Version::find()->where(['status'=>1,'system_type'=>0])
            ->select('id,version,url,info,update_mode,is_formal,show_video')->asArray()->one();
        if(!$version_info){
            $this->_errorData('404', '配置错误，请检查后台配置');
        }else{
            //查看 用户现用版本 信息 是否需要提示更新
            $user_version = Version::find()->where(['system_type'=>0,'version'=>$version])
                ->select('is_update,is_formal,update_mode,show_video')->orderBy("create_time desc")->asArray()->one();
            $user_arr = explode('.', $version);
            $new_arr = explode('.', $version_info['version']);
            $new_str = 0; // 当前用户为 最新版本
            if($new_arr[0] > $user_arr[0]){
                $new_str = 1; //当前用户为非 最新版本
            }else if($new_arr[0] == $user_arr[0]){
                if($new_arr[1] > $user_arr[1]){
                    $new_str = 1;

                }else if($new_arr[1] == $user_arr[1]){

                    if($new_arr[2] > $user_arr[2]){
                        $new_str = 1;
                    }
                }
            }

            // $new_str == 1  即  $version_info['version'] > $version
            if(($new_str == 1) && ($user_version['is_update'] == 1)){
                //返回 当前版本 是否必须更新 和 兑吧控制 状态值
                $version_info['update_mode'] = $user_version['update_mode'];
                $version_info['is_formal']   = $user_version['is_formal'];
                $version_info['show_video']  = $user_version['show_video'];
                $this->_successData($version_info);
            }else{
                $this->_successData(array('id'=>'-1', 'version'=>$version_info['version'],'is_formal'=>$user_version['is_formal'],'show_video'=>$user_version['show_video'],'url'=>$version_info['url'],'info'=>$version_info['info']));
            }
        }
    }

    /**
     * IOS获取版本信息
     * $return 版本信息
     */
    public function actionGetIosVersion(){
        $version = isset($this->params['version']) ? $this->params['version'] : '';
        if(!$version){return $this->_errorData('404', '版本号错误');}
        //获取最新版本
        $version_info = Version::find()->where(['status'=>1,'system_type'=>1])
                        ->select('id,version,url,info,update_mode,is_formal,show_video')->asArray()->one();
        if(!$version_info){
            $this->_errorData('404', '配置错误，请检查后台配置');
        }else{
            //查看 用户现用版本 信息 是否需要提示更新
            $user_version = Version::find()->where(['system_type'=>1,'version'=>$version])
                ->select('is_update,is_formal,update_mode,show_video')->orderBy("create_time desc")->asArray()->one();
            $user_arr = explode('.', $version);
            $new_arr = explode('.', $version_info['version']);
            $new_str = 0; // 当前用户为 最新版本
            if($new_arr[0] > $user_arr[0]){
                $new_str = 1; //当前用户为非 最新版本
            }else if($new_arr[0] == $user_arr[0]){
                if($new_arr[1] > $user_arr[1]){
                    $new_str = 1;

                }else if($new_arr[1] == $user_arr[1]){

                    if($new_arr[2] > $user_arr[2]){
                        $new_str = 1;
                    }
                }
            }

            // $new_str == 1  即  $version_info['version'] > $version
            if(($new_str == 1) && ($user_version['is_update'] == 1)){
                //返回 当前版本 是否必须更新 和 兑吧控制 状态值
                $version_info['update_mode'] = $user_version['update_mode'];
                $version_info['is_formal']   = $user_version['is_formal'];
                $version_info['show_video']   = $user_version['show_video'];
                $this->_successData($version_info);
            }else{
                $this->_successData(array('id'=>'-1', 'version'=>$version_info['version'],'is_formal'=>$user_version['is_formal'],'show_video'=>$user_version['show_video']));
            }
        }
    }

    //判断隐藏引导页、直播   0关闭 ，1 开启
    public function actionIsOpen(){
        $data = array();
        $data['is_open'] = 0;
        $this->_successData($data);
    }

    //判断隐藏引导页、直播   0关闭 ，1 开启
    public function actionIsOpenLive(){
        $data = array();
        $data['is_open'] = 0;
        $this->_successData($data);
    }

    function actionDownload(){
        $ad_version = Version::find()->where(['system_type'=>0, 'status'=>1])->orderBy('version desc')->asArray()
            ->one();
        if($ad_version){
            header("Location: ".$ad_version['url']);
            exit;
        }else{
            echo '敬请期待....';
        }
    }

}