<?php
namespace Piwik\Db;

class QueryHelper {
    const DEFAULT_SCHEMA = 'Mssql';

    public static function substr_startswith($haystack, $needle) {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    public static function substr_compare_startswith($haystack, $needle) {
        return substr_compare($haystack, $needle, 0, strlen($needle), true) === 0;
    }

    public static function checkStatement ($sql) {
        try {
            if (stripos($sql, 'piwik_logger_message') !== false)
                return 0;
            if (stripos($sql, 'select') !== false)
            {
                if (stripos($sql, 'piwik_user_language') !== false)
                    return 0;
                if (stripos($sql, 'piwik_user') !== false)
                    return 0;
                if (stripos($sql, 'piwik_site') !== false)
                    return 0;
            }
            if (stripos($sql, 'piwik_option') !== false)
                return 0;
            if (stripos($sql, 'INFORMATION_SCHEMA.COLUMNS') !== false)
                return 0;
            if (stripos($sql, 'piwik_log_profiling') !== false)
                return 0;
            if (stripos($sql, 'SELECT CURRENT_TIMESTAMP') !== false)
                return 0;
            if (stripos($sql, 'ignore') !== false)
                return 2;
            if (stripos($sql, 'limit') !== false)
                return 2;
            if (stripos($sql, 'concat_ws') !== false)
                return 2;
            if (stripos($sql, 'show columns ') !== false)
                return 2;
            if (stripos($sql, 'alter') !== false)
                return 2;
            if (stripos($sql, 'add column') !== false)
                return 2;
            if (stripos($sql, 'case') !== false)
                return 0;
            if (stripos($sql, 'if') !== false)
                return 0;
            if (stripos($sql, 'round') !== false)
                return 0;
            if (stripos($sql, 'group_concat') !== false)
                return 2;
            if (stripos($sql, 'duplicate') !== false)
                return 2;
            if (stripos($sql, 'crc') !== false)
                return 2;
            if (stripos($sql, 'set session') !== false)
                return 2;
            return 1;
        } catch (Exception $e) {
            return 2;
        }
    }

    public static function cleanQuery ($sql)
    {
        if (self::DEFAULT_SCHEMA == 'Mysql') return $sql;
        if (strcasecmp($sql, "select database()") == 0)
            return "SELECT CURRENT_TIMESTAMP";

        /* $issue = self::checkStatement ($sql);
        if ($issue >= 1) {
            $i = 1;
            $issue = self::checkStatement ($sql);
        }
        */

        // Handle DROP TABLE IF EXISTS
        if (self::substr_compare_startswith($sql, "drop table if exists "))
        {
            $newqry = "";
            $tables = substr($sql, 21);
            $tbls = explode (",", $tables);
            foreach ($tbls as $tbl) {
                $tbl = trim($tbl);
                $newqry .= "IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[".$tbl."]') AND TYPE IN (N'U')) DROP TABLE [dbo].[".$tbl."]; ";
            }
            return $newqry;
        }
        $pos = stripos ($sql, ' limit ');
        if ($pos !== false) {
            $limitqry = trim(substr ($sql, $pos + 7));
            $hasparam = strpos ($limitqry, ',');
            if ($hasparam !== false) {
                echo "in";
                $ls = explode (",", $limitqry);
                $start = $ls[0];
                $end = $ls[1];
            }  else {
                $start = 0;
                $end = $limitqry;
            }
            $orgqry = substr ($sql, 0, $pos + 1);
            $sql = $orgqry. ' OFFSET '.$start.' ROWS FETCH NEXT  '.$end.' ROWS ONLY';
        }
        $pos = stripos ($sql, 'insert ignore');
        if ($pos !== false) {
            $sql = str_ireplace("ignore", "", $sql);
        }
        $pos = strpos ($sql, '`');
        $id = 0;
        while ($pos !== false) {
            $char = '[';
            if ($id == 1) $char = ']';
            $sql = substr_replace($sql, $char, $pos, strlen('`'));
            $id++;
            if ($id >= 2) $id = 0;
            $pos = strpos ($sql, '`');
        }
        $sql = str_replace ("AUTO_INCREMENT", "IDENTITY(1,1)", $sql);
        $sql = str_replace ("auto_increment", "IDENTITY(1,1)", $sql);
        $sql = str_replace ("ADD COLUMN", "ADD", $sql);
        $sql = str_replace ("add column", "add", $sql);
        // identity(1,1)

        return $sql;
    }
}
