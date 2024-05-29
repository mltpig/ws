<?php

namespace App\Api\Utils;

class Consts
{
    //物品
    const RENSHENTANG = 100011;//人参汤
    const QINPU       = 100016;//琴谱
    const DUKANGJIU   = 100017;//杜康酒
    const WANSHOUTAO  = 100018;//万寿桃
    
    //Arg 标志参数
    const COMRADE_ENERGY   = 116;//贤士探访体力
    const COMRADE_AD_COUNT = 117;//贤士体力广告恢复次数上限
    const COMRADE_ENERGY_TIME = 118;//探访体力缺失开始时间
    const DOUFA_WIN_COUNT = 119;//斗法累计胜利次数

    const ACTIVITY_CHANNEL_TASK_1 = 120;//朋友圈 首次加入朋友圈
    const ACTIVITY_CHANNEL_TASK_2 = 121;//朋友圈 当日点赞
    const ACTIVITY_CHANNEL_TASK_3 = 122;//朋友圈 当日评论
    
    const HOMELAND_TARGET_REFRESH_TIME = 123;//家园刷新的冷却时间
    
    const ACTIVITY_CHANNEL_TASK_4 = 124;//字节侧边栏

    const AES_KEY = '4jsnvOUINGhwwg5o';//
    const AES_IV  = 'AgICbPWjRXh8dX9k';//

    const ACTIVITY_TAG_5 = 125;//登录奖励1
    const ACTIVITY_TAG_6 = 126;//登录奖励2
    
    const ACTIVITY_NEW_YEAR_BEGIN = '2024-02-09';//新年活动开启时间
    const ACTIVITY_NEW_YEAR_END   = '2024-02-17';//新年活动结束时间
    const ACTIVITY_NEW_YEAR_TAG   = [ 1 => 127,2 => 128,3 => 129,4 => 130,5 => 131,6 => 132,7 => 133 ];
    const CHARA_BELONG = 202; // 模型类型
}