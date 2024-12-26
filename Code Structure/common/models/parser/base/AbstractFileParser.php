<?php
/**
 * AbstractFileParser.php
 */

namespace common\models\parser\base;

use common\helpers\ArrayHelper;
use common\helpers\CheckHelper;
use yii\base\Exception;

/**
 * Class AbstractFileParser
 * @package common\models\parser\base
 */
abstract class AbstractFileParser extends AbstractParser
{
    /** @var int $maxLineLength */
    protected $maxLineLength = 1048576; // 1Mb

    /** @var null|string $filePath */
    protected $filePath = null;

    /** @var null|string $tempFilePath */
    protected $tempFilePath = null;

    /** @var int $firstRow */
    protected $firstRow = 0;

    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();

        if ($this->tempFilePath) {
            //echo 'DELETE ' . $this->tempFilePath;
            unlink($this->tempFilePath);
        }
    }

    /**
     * Init parser config
     *
     * @param $config
     *
     * @throws \Exception
     */
    public function init($config)
    {
        parent::init($config);
        $file = ArrayHelper::getValue($config, 'file');

        if ($file) {
            $this->setFile($file);
        }

        $fromEncoding = ArrayHelper::getValue($config, 'changeEncoding');
        $toEncoding = 'UTF-8';
        if ($fromEncoding) {
            $this->iconv(compact('fromEncoding', 'toEncoding'));
        }

        $this->firstRow = ArrayHelper::getValue($config, 'firstRow', 0);
    }

    /**
     * Set file for parse
     *
     * @param $file array
     *
     * @throws Exception
     */
    protected function setFile($file)
    {
        $this->filePath = CheckHelper::getArrayValue($file, 'path');
    }

    /**
     * @param $params
     *
     * @throws Exception
     * @return bool
     */
    private function iconv($params)
    {
        $fromEncoding = ArrayHelper::getValue($params, 'fromEncoding');
        $toEncoding = ArrayHelper::getValue($params, 'toEncoding');
        $deleteBadSymbols = ArrayHelper::getValue($params, 'deleteBadSymbols', false);

        if (!$fromEncoding || !$toEncoding) {
            return false;
        }

        $deleteBadSymbols = $deleteBadSymbols ? ' -c ' : '';
        $ext = '.' . pathinfo($this->filePath, PATHINFO_EXTENSION);
        $this->tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($this->filePath) . $ext;

        $cmd = 'iconv ' . $deleteBadSymbols . '-f ' . $fromEncoding . ' -t ' . $toEncoding
            . ' \'' . $this->filePath . '\' > ' . $this->tempFilePath;
        $res = exec($cmd, $out);

        if ($res) {
            throw new Exception(implode(PHP_EOL, $out));
        }
        $this->filePath = $this->tempFilePath;

        return true;
    }

    /**
     * @return null|string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
