<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\DataAccess;

use Piwik\Db;
use Piwik\Db\QueryHelper;

/**
 * Data Access Object that can be used to get metadata information about
 * the MySQL tables Piwik uses.
 */
class TableMetadata
{
    /**
     * Returns the list of column names for a table.
     *
     * @param string $table Prefixed table name.
     * @return string[] List of column names..
     */
    public function getColumns($table)
    {
        $table = str_replace("`", "", $table);

        if (QueryHelper::DEFAULT_SCHEMA == 'Mssql') {
            $columns = Db::fetchAll("SELECT column_name as Field, data_type as [Type], is_nullable as [Null], '' as [Key],  column_default  as [Default], '' as Extra FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = '" . $table . "'");
        } else {
            $columns = Db::fetchAll("SHOW COLUMNS FROM `" . $table . "`");
        }

        $columnNames = array();
        foreach ($columns as $column) {
            $columnNames[] = $column['Field'];
        }

        return $columnNames;
    }

    /**
     * Returns the list of idaction columns in a table. A column is
     * assumed to be an idaction reference if it has `"idaction"` in its
     * name (eg, `"idaction_url"` or `"idaction_content_name"`.
     *
     * @param string $table Prefixed table name.
     * @return string[]
     */
    public function getIdActionColumnNames($table)
    {
        $columns = $this->getColumns($table);

        $columns = array_filter($columns, function ($columnName) {
            return strpos($columnName, 'idaction') !== false;
        });

        return array_values($columns);
    }
}