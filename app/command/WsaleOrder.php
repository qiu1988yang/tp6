<?php


namespace app\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use app\test\controller\ImportOrder;
use app\test\controller\Kuaidi;
class WsaleOrder extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('WsaleOrder')->setDescription('订单');
    }

    protected function execute(Input $input, Output $output)
    {

        //(new ImportOrder())->index();

        (new Kuaidi())->index();

        $output->writeln('1111111');
    }
}