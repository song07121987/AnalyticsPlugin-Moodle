<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\VisitTime\Segment;
use Piwik\Db\QueryHelper;

class ServerTime extends VisitDimension
{
    protected $columnName = 'visit_last_action_time';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('visitServerHour');
        $segment->setName('VisitTime_ColumnServerTime');
        if (QueryHelper::DEFAULT_SCHEMA == 'Mssql') {
            $segment->setSqlSegment('LEFT(CONVERT(TIME(0), log_visit.visit_last_action_time), 2)');
        } else {
            $segment->setSqlSegment('HOUR(log_visit.visit_last_action_time)');
        }
        $segment->setAcceptedValues('0, 1, 2, 3, ..., 20, 21, 22, 23');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('VisitTime_ColumnServerTime');
    }
}