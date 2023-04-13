<?php

/**
 * Created by phpStorm.
 * User: webin
 * Date: 2021/9/23
 * Time: 15:04
 */


namespace webin;


class ModelTpl
{
    //获取tpl
    public static function getTiiTpl()
    {
        $t = <<<__END
<?php

namespace {%namespace%};

/**
 * This is the model class for table "{%trueTableName%}".
{%property%}
*/
class {%className%}{%extend%} {
        
    /**
     * @var string 表名
     */
    public \$tableName = '{%trueTableName%}';
    public \$table = '{%trueTableName%}';
   
    /**
     * @var array 本章表的字段
     */
    public \$fields = {%fields%};
    
}
 
__END;
        return $t;
    }
}
