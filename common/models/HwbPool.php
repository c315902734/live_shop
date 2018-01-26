<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "hwb_pool".
 *
 * @property string $id
 * @property string $operator
 * @property integer $type
 * @property string $recharge_amount
 * @property integer $huiwenbi
 * @property integer $balance
 * @property integer $pool_type
 * @property string $create_time
 * @property string $remarks
 */
class HwbPool extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return yii::$app->vradmin1;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hwb_pool';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'huiwenbi', 'balance', 'pool_type'], 'integer'],
            [['recharge_amount'], 'number'],
            [['create_time'], 'safe'],
            [['operator', 'remarks'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'operator' => 'Operator',
            'type' => 'Type',
            'recharge_amount' => 'Recharge Amount',
            'huiwenbi' => 'Huiwenbi',
            'balance' => 'Balance',
            'pool_type' => 'Pool Type',
            'create_time' => 'Create Time',
            'remarks' => 'Remarks',
        ];
    }

    /**
     * 汇闻币资金池添加记录
     * @param int $pool_type
     * @param int $children_type
     * @param int $hwb
     * @param string $remarks
     * @return bool|string
     */
    public static function addRecord($pool_type = 0, $children_type = 0, $hwb = 0, $remarks = '')
    {
        if (!intval($pool_type) || !intval($children_type) || !intval($hwb)) return false;

        $hwb_pool_balance = yii::$app->db->createCommand(
            'SELECT SUM(huiwenbi) as balance FROM vradmin1.hwb_pool WHERE pool_type = '.$pool_type
            )->queryScalar();

        $hwb_pool_model = new self;
        $hwb_pool_model->operator = '系统';
        $hwb_pool_model->type     = $children_type;
        $hwb_pool_model->huiwenbi = $hwb;
        $hwb_pool_model->balance  = intval($hwb_pool_balance - $hwb);
        $hwb_pool_model->pool_type   = $pool_type;
        $hwb_pool_model->create_time = date('Y-m-d H:i:s');
        $hwb_pool_model->remarks = $remarks;
        if ($hwb_pool_model->save()) {
            return true;
        }
        return false;
    }
}
