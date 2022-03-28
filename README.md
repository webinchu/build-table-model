# build-table-model 
composer require webin/build-model

根据表自动生成model类

## Thinkphp

    可结合make:command

1. 根目下执行php think make:command gii-model (生成GiiModel类)
2. 编辑生成的GiiModel类(代码如下)
3. (重要)将GiiModel加入config/console.php(没有的话找下是否有command.php),按照已有的格式写进去('app\common\command\GiiModel' //这个是我自己项目的路径(
   按照你自己的路径填写))
4. 执行php think 看看是不是有gii-model
5. 根目录下执行php think gii-model user (user是表名)

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
        //根据自己项目写(自定义)
        $namespace="app\\common\\model";
        //根据自己环境进行配置
        $config = config('test.database.');
        $extend = "\app\common\model\BaseModel"; //继承父类
        $mysql = new mysqli(
            $config['hostname'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['hostport']
        );
        $tablePre = '';
        //根据自己项目写(自定义)
        $basePath = $this->getRootPath() . "application/common/model/";
        $Tii = new BuildModel($mysql, $table, $namespace, $basePath, $tablePre,$extend);
        $Tii->create();
        exit;
    }

    public function getRootPath($path = '')
    {
        return app()->getRootPath() . ($path ? $path . '/' : $path);
    }
}

~~~

### Laravel | Lumen

1. 终端执行: php artian make:command GiiModel
2. 编辑GiiModel(代码如下)
~~~
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use mysqli;
use webin\BuildModel;

class GiiModel extends Command
{
    /**
     * The name and signature of the console command.
     * eg 1: 带目录 php artisan gii-model test@user     test 文件夹 user表名
     * eg 2: 不带目录 php artisan gii-model user         user表名
     * @var string
     */
    protected $signature = 'gii-model {dirAndTable}'; //

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动生成model类';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $params = $this->arguments()['dirAndTable'];
        if (strpos($params, '@')) {
            $newParams = explode('@', $params);
            $dir = $newParams[0];
            $table = $newParams[1];
        } else {
            $table = $params;
        }
        $modelDir = "App\\Models\\";
        if (isset($dir)) {
            $namespace = $modelDir . $dir;
        } else {
            $namespace = $modelDir;
        }
        $mysql = new mysqli(
            env('DB_HOST'),
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_DATABASE'),
            env('DB_PORT')
        );
        $tablePre = ''; // 表前缀,根据自己实际情况
        $fileSavePath = '/app/Models/';  //文件实际保存的路径
        $basePath = base_path() . $fileSavePath;
        if (isset($dir)) {
            $basePath .= $dir . '/';
        }
        $Tii = new BuildModel($mysql, $table, $namespace, $basePath, $tablePre);
        $res = $Tii->create();
        if ($res) {
            echo $fileSavePath . ucwords(str_replace('_', ' ', $table)) . '.php create success';
        } else {
            echo 'Model create fail';
        }
        exit;
    }
}
~~~
3. 终端执行php artisan gii-model user 注意:user 是表名

