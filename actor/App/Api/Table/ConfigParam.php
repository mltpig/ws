<?php
namespace App\Api\Table;
use Swoole\Table;
use EasySwoole\Component\TableManager;
use EasySwoole\Component\CoroutineSingleTon;
use App\Api\Model\ConfigParam as Model;

class ConfigParam
{
    use CoroutineSingleTon;

    protected $tableName = 'config_param';

    public function create():void
    {
        $columns = [ 'value'  => [ 'type'=> Table::TYPE_STRING ,'size'=> 2000 ] ];

        TableManager::getInstance()->add( $this->tableName , $columns , 500 );

    }

    public function initTable():void
    {
        $table = TableManager::getInstance()->get($this->tableName);

        $tableConfig = Model::create()->all();

        foreach ($tableConfig as $config) 
        {
            $table->set($config['param'],[ 'value' => $config['value'] ]);
        }

    }

    public function getOne(string $field):string
    {
        $table = TableManager::getInstance()->get($this->tableName);
        if(!$table->count()) $this->initTable();
        $data = $table->get($field);

        return $data ? $data['value']  : '';

    }

    public function getFmtParam(string $field)
    {
        $param = null;
        $value = $this->getOne($field);
        switch ($field) 
        {
            case 'DREAM_UPGRADE_SPEEDUP_ITEM_COST'://使用净水瓶花费
            case 'RENAME_COST'://改名花费
            case 'PVP_REFRESH_COST'://斗法刷新花费
            case 'PVP_CHALLENGE_COST'://斗法挑战花费
            case 'DREAM_UPGRADE_SPEEDUP_AD_SKIP'://可以通过50仙玉跳过广告获得奖励
            case 'HOMELAND_PAY_REFRESH_COST'://福地刷新消耗
                list($id,$number) = explode('=',$value);
                $param = ['gid' => $id,'num' => $number];
                break;
            case 'EQUIPMENT_SPECIAL_DROP_LIST'://前十五个装备固定输出
            case 'PVP_ROBOT_LEVEL'://斗法机器人等级区间
            case 'WILDBOSS_REPEAT_COST_PARAM'://妖王挑战快速战斗消耗
            case 'INVADE_MONSTER_ID'://异兽入侵怪物id
            case 'INVADE_CHALLENGE_TIME'://异兽入侵挑战最大次数
            case 'HOMELAND_ENERGY_DIVIDE'://工人状态划分标准
            case 'HOMELAND_ENERGY_SPEED'://工人状态对应附加时长百分比
            case 'HOMELAND_SINGLE_BOX_MOUSE_LIMIT_CONFIG'://福地物品等级对应采取人数
            case 'HOMELAND_AUTO_REFRESH_TIME'://福地物品刷新时间点
            case 'DESTINY_ENERGY_ITEM_PARAM'://使用精气丹获得一点体力
            case 'DESTINY_LEVEL_UP'://贤士升级配置
            case 'HOMELAND_ENERGY_COPE_SPEED'://使用精气丹获得一点体力
                $param = explode('|',$value);
                break;
            case 'DREAM_UPGRADE_SPEEDUP_ITEM_TIME'://使用净水瓶能减少300秒（1个）
            case 'EQUIPMENTCREATE_DROP_EMPTY_WEIGHT'://仙树额外掉落参数（为空的权重）
            case 'RENAME_DAILY_TIMES'://改名卡每日限制次数
            case 'AD_REWARD_CD'://每日福利领取限制周期
            case 'AD_REWARD_DAILY_MAX_NUM'://每日福利限制次数
            case 'PVP_ROBOT_COUNT'://斗法机器人数量
            case 'PVP_CHALLENGE_COST_LIMIT'://砍树产出挑战券最大数量
            case 'PVP_SCORE_CHANGE_RATE'://游戏分数改变率
            case 'WILDBOSS_REPEAT_LIMIT'://妖王挑战最大次数
            case 'INVADE_FIGHT_REWARD'://异兽入侵挑战奖励
            case 'DREAM_UPGRADE_SPEEDUP_AD_TIME'://看广告可以减少1800秒时间
            case 'HOMELAND_FREE_REFRESH_TIME'://福地广告刷新物品观看限制次数
            case 'HOMELAND_AUTO_REFRESH_TIME_PER'://福地失效物品自动刷新时间时长
            case 'HOMELAND_BASIC_WORKER_NUM'://福地工人初始数量
            case 'DESTINY_ENERGY_FREE_REFRESH_TIME'://仙友体力每日广告恢复的次数
            case 'DESTINY_ENERGY_TIME'://体力恢复时间
            case 'HOMELAND_TARGET_REFRESH_TIME'://家园刷新的冷却时间
                $param = $value;
                break;
            case 'HOMELAND_WORKER_COST'://福地工人雇佣费用
                $list = explode(';',$value);
                $param = [];
                foreach ($list as $key => $item) 
                {
                    list($id,$number) = explode('=',$item);
                    $param[] = ['gid' => $id,'num' => $number];
                }
                break;
        }

        return $param;
    }

}
