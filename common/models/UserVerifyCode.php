<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use common\models\OauthAccessTokens;

/**
 * This is the model class for table "user_verify_code".
 *
 * @property string $code_id
 * @property string $mobile_phone
 * @property string $verify_code
 * @property string $create_time
 * @property integer $status
 * @property integer $from_type
 */
class UserVerifyCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_verify_code';
    }

    function getUserVerifyCode($countries_regions='', $mobilePhone, $fromType,$is_array=true)
    {
        $where = array("mobile_phone" => $mobilePhone);
        $where['from_type'] = $fromType;
        $where['status'] = 0;
        
        $list = UserVerifyCode::find()->where(["mobile_phone" => $mobilePhone, 'countries_regions'=>$countries_regions, "from_type"=>$fromType,"status"=>0])->orderBy("create_time desc")->limit(1)->asArray($is_array)->all();
        if (!empty($list)) {
            return $list[0];
        }
        return NULL;
    }

    public static function getDb(){
        return Yii::$app->vruser1;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code_id'], 'required'],
            [['code_id', 'status', 'from_type'], 'integer'],
            [['create_time'], 'safe'],
            [['mobile_phone'], 'string', 'max' => 15],
            [['verify_code'], 'string', 'max' => 6],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code_id' => 'Code ID',
            'mobile_phone' => 'Mobile Phone',
            'verify_code' => 'Verify Code',
            'create_time' => 'Create Time',
            'status' => 'Status',
            'from_type' => 'From Type',
        ];
    }
    

    
}
