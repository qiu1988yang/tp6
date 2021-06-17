<?php


namespace app\common\lib\kuaidi;

use app\common\lib\tools\Http;


class Kuaidis
{
    private $config = [];
    private $key = "";
    private $customer = "";
    private $host = "https://poll.kuaidi100.com/poll/query.do";

    public function __construct()
    {
        $config = [
            'key' => 'RhFrDtMw9239',
            'customer' => '56BEFC05DB3DCC7D9AA10AFF31BDA1A4',
            'secret' => '54c5d85502fd4d1397d387dd399d05d8',
            'userid' => '5d5324b36bb64d7296247b1776319419',
        ];
        $this->config = $config;
        $this->customer = $this->config['customer'];
        $this->key = $this->config['key'];
    }

    public function getExpressInfo($com = "zhongtong", $num = "73147332624921")
    {

        $param['num'] = $num;
        $param['com'] = $com;
        $param = json_encode($param);
        $post['sign'] = $this->getSign($param);
        $post['param'] = $param;
        $post['customer'] = $this->customer;
        $result = $this->doPost($post);


        // $result = $this->descResult($result);
        return json_decode($result, true);;
    }


    public function statusKd()
    {
        return [
            0 => '在途',
            1 => '揽收',
            2 => "疑难",
            3 => '签收',
            4 => '退签',
            5 => '派件',
            6 => '退回',
            7 => '转单',
            8 => '待清关',
            10 => '待清关',
            11 => '已清关',
            12 => '已清关',
            13 => '清关异常',
            14 => '收件人拒签等'
        ];
    }


    private function getSign($param)
    {
        $str = $param . $this->key . $this->customer;
        $str = md5($str);
        $str = strtoupper($str);
        return $str;
    }

    public function doPost($param)
    {
        return $result = Http::http_request($this->host, "POST", $param,
            ["Content-Type:application/x-www-form-urlencoded"]);
    }

}