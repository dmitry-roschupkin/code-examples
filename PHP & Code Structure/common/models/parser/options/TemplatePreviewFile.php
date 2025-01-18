<?php
/**
 * TemplatePreviewFile.php
 */

namespace common\models\parser\options;

use common\models\parser\actions\ReadRows;

/**
 * TODO: Moved file to module?
 * Class TemplatePreviewFile
 * @package common\models\parser\options
 */
class TemplatePreviewFile
{
    /**
     * @param $file
     * @return mixed
     */
    public static function getOptions($file)
    {
        $option['actions'] = [ReadRows::class];
        $option['file'] = ['path' => $file];
        $option['firstRow'] = 1;
        $option['lastRow'] = 20;
        $option['maxSheet'] = 1;

        return $option;
    }
}
