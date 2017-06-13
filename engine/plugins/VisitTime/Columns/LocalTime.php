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
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Db\QueryHelper;

class LocalTime extends VisitDimension
{
    protected $columnName = 'visitor_localtime';
    protected $columnType = 'TIME NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('visitLocalHour');
        $segment->setName('VisitTime_ColumnLocalTime');
        if (QueryHelper::DEFAULT_SCHEMA == 'Mssql') {
            $segment->setSqlSegment('LEFT(CONVERT(TIME(0), log_conversion.server_time), 2)');
        } else {
            $segment->setSqlSegment('HOUR(log_visit.visitor_localtime)');
        }
        $segment->setAcceptedValues('0, 1, 2, 3, ..., 20, 21, 22, 23');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('VisitTime_ColumnLocalTime');
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $request->getLocalTime();
    }
}