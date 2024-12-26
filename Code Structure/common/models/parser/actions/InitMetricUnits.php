<?php
/**
 * InitMetricUnits.php
 */

namespace common\models\parser\actions;


use common\helpers\ArrayHelper;
use common\helpers\MetricHelper;
use common\models\parser\actions\base\AbstractAction;

/**
 * Conversion of metric units (weight, volume) in a system
 *
 * Class InitMetricUnits
 * @package common\models\parser\actions
 */
class InitMetricUnits extends AbstractAction
{
    /**
     * @inheritdoc
     */
    protected function applyRow(&$row, $line)
    {
        $row = $this->initDistance($row);
        $row = $this->initVolume($row);
        $row = $this->initWeight($row);
    }

    private function initDistance($row)
    {
        $length = $this->getColumn($row, 'length', 0);
        $width = $this->getColumn($row, 'width', 0);
        $height = $this->getColumn($row, 'height', 0);

        if(!$length && !$width && !$height) {
            $length = $this->getColumnParam($row, 'length', 'fixedLength');
            $width = $this->getColumnParam($row, 'width', 'fixedWidth');
            $height = $this->getColumnParam($row, 'height', 'fixedHeight');
        }

        if ($length && $width && $height) {

            $sizeUnit = ArrayHelper::getSimpleArrayValue($this->options, 'sizeUnit');
            if ($sizeUnit && $sizeUnit != MetricHelper::UNIT_METER) {
                $length = MetricHelper::distance($sizeUnit, $length);
                $width = MetricHelper::distance($sizeUnit, $width);
                $height = MetricHelper::distance($sizeUnit, $height);
            }

            $this->setColumnParam($row, 'length', 'fixedLength', $length);
            $this->setColumnParam($row, 'width', 'fixedWidth', $width);
            $this->setColumnParam($row, 'height', 'fixedHeight', $height);
        }

        return $row;
    }

    private function initVolume($row)
    {
        $volumeUnit = ArrayHelper::getSimpleArrayValue($this->options, 'volumeUnit');
        $volume = $this->getColumn($row, 'volume', 0);

        $length = $this->getColumnParam($row, 'length', 'fixedLength');
        $width = $this->getColumnParam($row, 'width', 'fixedWidth');
        $height = $this->getColumnParam($row, 'height', 'fixedHeight');

        if (!$volume && $length && $width && $height) {
            $volume =  MetricHelper::volume(MetricHelper::UNIT_CUBIC_METER, $length * $width * $height);
        } elseif ($volume && $volumeUnit && $volumeUnit != MetricHelper::UNIT_LITER) {
            $volume = MetricHelper::volume($volumeUnit, $volume);
        }

        if ($volume) {
            $this->setColumnParam($row, 'volume', 'fixedVolume', $volume);
        }

        return $row;
    }

    private function initWeight($row)
    {
        $weightUnit = ArrayHelper::getSimpleArrayValue($this->options, 'weightUnit');
        $weight = floatval($this->getColumn($row, 'weight', 0));

        if ($weight && $weightUnit && $weightUnit != MetricHelper::UNIT_KILOGRAM) {
            $weight = MetricHelper::weight($weightUnit, $weight);
        }

        if ($weight) {
            $this->setColumnParam($row, 'weight', 'fixedWeight', $weight);
        }

        return $row;
    }
}
