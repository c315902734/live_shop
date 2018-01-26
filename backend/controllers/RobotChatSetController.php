<?php
namespace backend\controllers;

use common\models\LiveRobotChatSetup;
use common\models\LiveRobotChat;
use common\models\LiveRobotChatAnswer;
use common\models\LiveRobotChatUser;

class RobotChatSetController extends PublicBaseController
{
    /**
     * 增加聊天室设置
     */
    public function actionAdd()
    {
        $live_id           = isset($_REQUEST['live_id']) ? $_REQUEST['live_id'] : '';
        $preheat_starttime = isset($_REQUEST['preheat_starttime']) ? $_REQUEST['preheat_starttime'] : '';
        $start_preheat_rate= isset($_REQUEST['start_preheat_rate']) ? $_REQUEST['start_preheat_rate'] : '';
        $end_preheat_rate  = isset($_REQUEST['end_preheat_rate']) ? $_REQUEST['end_preheat_rate'] : '';
        $start_chat_rate   = isset($_REQUEST['start_chat_rate']) ? $_REQUEST['start_chat_rate'] : '';
        $end_chat_rate     = isset($_REQUEST['end_chat_rate']) ? $_REQUEST['end_chat_rate'] : '';
        $is_open           = isset($_REQUEST['is_open']) ? $_REQUEST['is_open'] : '1';
        $start_endchat_rate= isset($_REQUEST['start_endchat_rate']) ? $_REQUEST['start_endchat_rate'] : '';
        $end_endchat_rate  = isset($_REQUEST['end_endchat_rate']) ? $_REQUEST['end_endchat_rate'] : '';
        $files             = isset($_REQUEST['xls']) ? $_REQUEST['xls'] : '';
        $file_name         = isset($_REQUEST['file_name']) ? $_REQUEST['file_name'] : '';
        if(!$live_id || !$file_name || !$files)
        {
            $this->_errorData(0001, '参数错误');
        }
        if ($files) {
            $files = base64_decode($files);
            $ext  = pathinfo($file_name, PATHINFO_EXTENSION);
            $path = \Yii::$app->basePath.'/web/localFile/';
            $uniName = time() . "." . $ext;
            $destination = $path . $uniName;
            if (file_put_contents($destination, $files)) {
                if ($ext =='xlsx') {
                    $objReader = new \PHPExcel_Reader_Excel2007();
                } elseif ($ext =='xls') {
                    $objReader   = new \PHPExcel_Reader_Excel5();
                } elseif ($ext=='csv') {
                    $objReader = new \PHPExcel_Reader_CSV();
                }
                $objPHPExcel = $objReader->load($destination);
                $objPHPExcel->setActiveSheetIndex(0);
                $sheet0   = $objPHPExcel->getSheet(0);
                $rowCount = $sheet0->getHighestRow();//excel行数
                $data = [];
                $answer_data = [];
                $robot_user = new LiveRobotChatUser();
                $robot_user_list = $robot_user::find()->where("avatar is not null and status = 1")->asArray()->all();
                if(!$robot_user_list)
                {
                    exit();
                }
                for ($i = 2; $i <= $rowCount; $i++) {
                    $robot_chat = new LiveRobotChat();
                    $item['chat_type'] = $objPHPExcel->getActiveSheet()->getCell("L" . $i)->getValue();
                    if (empty($item['chat_type'])) {
                        continue;
                    }
                    $item['type'] = $objPHPExcel->getActiveSheet()->getCell("C" . $i)->getValue();
                    if($item['type'] != 3)
                    {
                        $item['target']      = $objPHPExcel->getActiveSheet()->getCell("D" . $i)->getValue();
                        $item['content']     = $objPHPExcel->getActiveSheet()->getCell("E" . $i)->getValue();
                        $item['num']         = $objPHPExcel->getActiveSheet()->getCell("F" . $i)->getValue();
                        $item['create_time'] = date('Y-m-d H:i:s', time());
                        $item['live_id']     = $live_id;
                        $rand = array_rand($robot_user_list, 1);
                        $item['name']        = $robot_user_list[$rand]['username'];
                        $item['photo']       = $robot_user_list[$rand]['avatar'];
                    }else
                    {
                        $rand_answer         = array_rand($robot_user_list, 1);
                        $item['target']      = $objPHPExcel->getActiveSheet()->getCell("D" . $i)->getValue();
                        $item['num']         = $objPHPExcel->getActiveSheet()->getCell("F" . $i)->getValue();
                        $item['create_time'] = date('Y-m-d H:i:s', time());
                        $item['live_id']     = $live_id;
                        $rand = array_rand($robot_user_list, 1);
                        $item['name']        = $robot_user_list[$rand]['username'];
                        $item['photo']       = $robot_user_list[$rand]['avatar'];
                        $item['content'] = '欢迎'.$robot_user_list[$rand_answer]['username'];
                    }
                    $robot_chat->setAttributes($item);
                    $robot_chat->save();
                    $data[] = $item;

                    $content = $objPHPExcel->getActiveSheet()->getCell("J" . $i)->getValue();
                    if(!empty($content))
                    {
                        if($item['type'] != 3)
                        {
                            $content_arr = explode(';', $content);
                            $count = count($content_arr);
                            for ($j=0; $j<$count; $j++)
                            {
                                $rand = array_rand($robot_user_list, 1);
                                $answer_item['name']        = $robot_user_list[$rand]['username'];
                                $answer_item['photo']       = $robot_user_list[$rand]['avatar'];
                                $answer_item['target']      = $objPHPExcel->getActiveSheet()->getCell("I" . $i)->getValue();
                                $answer_item['num']         = $objPHPExcel->getActiveSheet()->getCell("K" . $i)->getValue();
                                $answer_item['content']     = $content_arr[$j];
                                $answer_item['create_time'] = date('Y-m-d H:i:s', time());
                                $answer_item['question_id'] = $robot_chat->id;
                                $chat_answer = new LiveRobotChatAnswer();
                                $chat_answer->setAttributes($answer_item);
                                $chat_answer->save();
                                $answer_data[] = $answer_item;
                            }
                        }else
                        {
                            $answer_item['name']    = $robot_user_list[$rand_answer]['username'];
                            $answer_item['photo']   = $robot_user_list[$rand_answer]['avatar'];
                            $answer_item['content'] = $content;
                            $answer_item['target']  = $robot_user_list[$rand_answer]['username'];
                            $answer_item['create_time'] = date('Y-m-d H:i:s', time());
                            $answer_item['question_id'] = $robot_chat->id;
                            $chat_answer = new LiveRobotChatAnswer();
                            $chat_answer->setAttributes($answer_item);
                            $chat_answer->save();
                            $answer_data[] = $answer_item;
                        }
                    }
                }
                $redis = \Yii::$app->cache;
                $name = 'live_robot_chat_' . $live_id;
                $redis->set($name, $data);
                $answer_key = 'live_robot_chat_answer_'.$live_id;
                $redis->set($answer_key, $answer_data);
                unlink($destination);
            }
        }
        $preheat_rate = $start_preheat_rate.'-'.$end_preheat_rate;
        $chat_rate    = $start_chat_rate.'-'.$end_chat_rate;
        $endchat_rate = $start_endchat_rate.'-'.$end_endchat_rate;
        $result = LiveRobotChatSetup::robot_setup($live_id, $preheat_starttime, $preheat_rate,
            $chat_rate, $is_open, $endchat_rate);
        if($result)
        {
            $this->_successData('设置成功');
        }else
        {
            $this->_errorData(0002, '设置失败');
        }
    }

    /**
     * 获取编辑聊天室设置信息
     */
    public function actionEdit()
    {
        $live_id = isset($this->params['live_id']) ? $this->params['live_id'] : '';
        if(!$live_id)
        {
            $this->_errorData(0001, '参数错误');
        }
        $result = LiveRobotChatSetup::find()->where(['live_id'=>$live_id])->asArray()->one();
        $this->_successData($result);
    }
}
