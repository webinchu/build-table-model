<?php
namespace webin;
/**
 * Created by phpStorm.
 * User: webin
 * Date: 2021/9/23
 * Time: 10:40
 */
class BuildModel
{
    const TYPE_INT = 'integer';
    const TYPE_STRING = 'string';

    protected $mysql = null;
    protected $tablePre = '';
    protected $dbName = '';
    protected $isHump = true;
    protected $host = '';
    protected $user = '';
    protected $pass = '';
    protected $tableName = '';
    protected $trueTableName = '';
    protected $namespace = '';


    public function __construct($mysql, $tablePre, $tableName,$namespace)
    {
        $this->tableName = $tableName;
        $this->tablePre = $tablePre;
        $this->trueTableName = $this->tablePre . $tableName;//要生成哪张表，完整表名
        $this->mysql = $mysql;
        $this->namespace = $namespace;
    }

    public function getDbName()
    {
        $row = $this->mysql->query("select database();")->fetch_row();
        $this->dbName = $row[0];
        return $this->dbName;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * 小驼峰转大驼峰
     * @param $name
     * @return mixed|string
     */
    public function humpSmallToBig($name)
    {
        $str = str_replace('_', ' ', $name);
        $str = ucwords($str);
        $str = str_replace(' ', '', $str);
        return $str;
    }


    public function getDesc($tableName)
    {
        $sql = "desc " . $this->getTrueTableName($tableName);
        $this->mysql->set_charset('utf-8');
        $query = $this->mysql->query($sql);
        $fetch = array();
        while (is_array($row = $query->fetch_array(1))) {
            $fetch[] = $row;
        }
        return $fetch;
    }


    public function getTiiFields($tableName)
    {
        $this->mysql->set_charset('utf8mb4');

        $noteQuery = $this->mysql->query("SHOW FULL COLUMNS
FROM  " . $this->tablePre . $this->tableName . ";");

        $notes = $noteQuery->fetch_all(1);
//        $notes = Db::query("SHOW FULL COLUMNS
//FROM  " . $this->tablePre . $this->tableName . ";");
        $fetch = $this->getDesc($this->getTrueTableName());
        $fields = [];
        foreach ($fetch as $field) {
            $comment = '';
            foreach ($notes as $note) {
                if (isset($note['Field']) && isset($note['Comment']) && $note['Field'] == $field['Field']) {
                    $comment = $note['Comment'];
                }
            }
            $fields[$field['Field']] = $comment;
        }
        return $fields;
//        $fetch = $this->getDesc($tableName);
//
//        $fields = array();
//        foreach ($fetch as $value) {
//            $fields[] = $value['Field'];
////            if (strpos($value['Key'], 'PRI') !== false) {
////                $fields['_pk'] = $value['Field'];
////            }
////            if ($value['Extra'] == 'auto_increment') {
////                $fields['_autoinc'] = true;
////            }
//        }
//        return $fields;
    }


    public function getAutoFill()
    {
        $fetch = $this->getDesc($this->getTrueTableName());
        $array = array();
        foreach ($fetch as $field) {
            if ($field['Default'] !== null) {
                $array[] = array($field['Field'], $field['Default']);
            }
        }
        return $this->arrayToString($array);
    }


    public function getAutoValidate()
    {
        $fetch = $this->getDesc($this->getTrueTableName());
        $requires = $urls = $emails = $numbers = $array = $numbers = $number1s = array();
        foreach ($fetch as $field) {
            $NotNull = false;
            if ($field['Null'] == "NO" && $field['Default'] === null) {
                $NotNull = true;
                //$array[] = array($field['Field'], 'require', $field['Field'] . ' Can not be a null！', 1);
                $requires[] = $field['Field'];
            }
            if ($field['Key'] == "UNI") {
                $array[] = array($field['Field'], '', '值已经存在！', 1, 'unique');
            }

            switch ($this->getType($field['Type'])) {
                case self::TYPE_INT:
                    if ($NotNull) {
                        $number1s[] = $field['Field'];
                    } else {
                        $numbers[] = $field['Field'];
                    }
                    break;
                case self::TYPE_STRING:
                    if (strpos($field['Field'], 'mail')) {
                        $emails[] = $field['Field'];
                    } elseif (strpos($field['Field'], 'url')) {
                        $urls[] = $field['Field'];
                    }
                    break;
                case 'enum':
                    $string = rtrim(str_replace(array('enum('), '', $field['Type']), ')');
                    $string = explode(',', $string);
                    $_tmp = array();
                    foreach ($string as $str) {
                        $_tmp[] = trim($str, "'");
                    }
                    $array[] = array($field['Field'], $_tmp, '值的范围不正确！', 2, 'in');
                    unset($_tmp);
                    break;
            }
        }
        empty($numbers) or $array[] = array(implode(',', $numbers), 'number', ' 格式不对');
        empty($number1s) or $array[] = array(implode(',', $number1s), 'number', ' 格式不对', 1);
        empty($emails) or $array[] = array(implode(',', $emails), 'email', ' 格式不对');
        empty($urls) or $array[] = array(implode(',', $urls), 'url', ' 格式不对');
        empty($requires) or $array[] = array(implode(',', $requires), 'require', ' Can not be a null！', 1);

        return $this->arrayToString($array);
    }


    public function getProperty()
    {
        $noteQuery = $this->mysql->query("SHOW FULL COLUMNS
FROM  ".$this->tablePre . $this->tableName.";");
        $notes = $noteQuery->fetch_all(1);
        $fetch = $this->getDesc($this->getTrueTableName());
        $property = array();
        foreach ($fetch as $field) {
            $comment = '';
            foreach ($notes as $note) {
                if (isset($note['Field']) && isset($note['Comment']) && $note['Field'] == $field['Field']) {
                    $comment = $note['Comment'];
                }
            }
            $type = $this->getType($field['Type']);
            $type = $type == 'enum' ? self::TYPE_STRING : $type;
            $type = $type == 'tinyint' ? self::TYPE_INT : $type;
            $property[] = " * @property $type \${$field['Field']} $comment";
        }
        return implode("\r\n", $property);
    }


    protected function getType($typeString)
    {
        list($type) = explode('(', $typeString);
        $types = array(
            self::TYPE_INT => array('int', 'bigint', 'tinyint'),
            self::TYPE_STRING => array('text', 'char', 'varchar', 'decimal', 'longtext', 'mediumtext','timestamp')
        );

        foreach ($types as $key => $value) {
            if (in_array($type, $value)) {
                return $key;
            }
        }
        return $type;
    }


    public function getFieldString()
    {
        $fieldString = $this->arrayToString($this->getTiiFields($this->tableName));
        return $fieldString;
    }

    public function arrayToString($array)
    {
        $string = "array( ";

        ksort($array, SORT_NATURAL);
        foreach ($array as $key => $val) {
            $key === 0 and $i = 0;
            reset($array);
        }
//        list($key) = each($array);

        foreach ($array as $key => $value) {
            if (isset($i) && $key == $i && ++$i) {
                $key = '';
            } else {
                $key = var_export($key, true) . " => ";
                unset($i);
            }
            if (is_array($value)) {
                $string .= $key . $this->arrayToString($value) . ', ';
            } else {
                $string .= $key . var_export($value, true) . ', ';
            }
        }
        $string = trim(trim($string, ' '), ',');
        $string .= ")";

        return $string;
    }

    public function getTrueTableName()
    {
        return $this->trueTableName;
    }

    public function getModelClassName()
    {
        if ($this->isHump) {
            $className = $this->humpSmallToBig($this->tableName);
        } else {
            $className = $this->tableName;
        }
        return $className;
    }


    public function getTiiTpl()
    {
        $t = <<<__END
<?php

namespace {%namespace%};

/**
 * This is the model class for table "{%trueTableName%}".
{%property%}
*/
class {%className%} {
        
    /**
     * @var string 表名
     */
    public \$tableName = '{%trueTableName%}';
   
    /**
     * @var array 本章表的字段
     */
    public \$fields = {%fields%};
    
}
 
__END;
        return $t;
    }
}
