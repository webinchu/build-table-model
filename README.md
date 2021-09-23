# build-table-model

根据表自动生成model类

## Thinkphp

    可结合make:command

1. 根目下执行php think make:command gii-model (生成GiiModel类)
2. 编辑生成的GiiModel类(代码如下)
3. (重要)将GiiModel加入config/console.php(没有的话找下是否有command.php),按照已有的格式写进去

~~~
'app\common\command\GiiModel' //这个是我自己项目的路径(按照你自己的路径填写)
~~~

3-1. 执行php think 看看是不是有gii-model

4. 根目录下执行php think gii-model user (user是表名)

~~~
<?php

namespace app\common\command;

use mysqli;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use webin\BuildModel;

class GiiModel extends Command
{
    /**
     * 自动生成model类  php think gii-model test@user test 文件夹 user表名
     * 自动生成model类  php think gii-model user   user 表名字
     */
    protected function configure()
    {
        // 指令配置
        $this->setName('gii-model')
            ->addArgument('table', Argument::IS_ARRAY, '表名')
            ->setDescription('自动生成model类');
    }

    protected function execute(Input $input, Output $output)
    {
        $params = $input->getArgument('table');
        $table = $params[0];
        $namespace="app\\common\\model";
        //根据自己环境进行配置
        $config = config('test.database.');
        $mysql = new mysqli(
            $config['hostname'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['hostport']
        );
        $tablePre = '';
        $basePath = $this->getRootPath() . "application/common/model/";
        $Tii = new BuildModel($mysql, $table, $namespace, $basePath, $tablePre);
        $Tii->create();
        exit;
    }

    public function getRootPath($path = '')
    {
        return app()->getRootPath() . ($path ? $path . '/' : $path);
    }
}

~~~

