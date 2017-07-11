<?php
/**
 * 事件日志类
 * Created by PhpStorm.
 * User: qf19910623
 * Date: 2017/7/11 0011
 * Time: 21:51
 */
namespace Common\Vendor;

class Eventlog {

    /**
     * 记录日志
     * @param $content
     * @param string $join_id
     * @param string $join_table
     * @return bool|mixed
     */
    public static function saveLog($content, $join_id = '', $join_table = '') {
        if (!$_SESSION['authId']) return false;
        $data = array(
            'userid'    =>  $_SESSION['authId'],
            'content'    =>  $content,
            'join_id'    =>  $join_id,
            'join_table'    =>  $join_table,
            'event_time'    =>  time()
        );
        return M('event_log')->add($data);
    }

    /**
     * 获取日志
     * @param $limit
     * @param string $join_table
     * @param array $where
     * @return mixed
     */
    public static function getLog($limit, $join_table = '', $where = array()) {
        if ($join_table) {
            $list = M('event_log e')
                ->field('e.userid,e.content,e.event_time,u.username,u.nickname,t.*')
                ->join('left join crm_user u on u.id=e.userid')
                ->join('left join crm_'.$join_table.' t on t.id=e.join_id')
                ->where($where)
                ->order('e.id desc')
                ->limit($limit)
                ->select();
        }else {
            $list = M('event_log e')
                ->field('e.userid,e.content,e.event_time,u.username,u.nickname')
                ->join('left join crm_user u on u.id=e.userid')
                ->where($where)
                ->order('e.id desc')
                ->limit($limit)
                ->select();
        }
        return $list;
    }
}