<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace SplittingOrder\Order\Model\Product\Attribute\Source;

class Brand extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
        ['value' => '1', 'label' => __('brand1')],
        ['value' => '2', 'label' => __('brand2')],
        ['value' => '3', 'label' => __('brand3')]
        ];
        return $this->_options;
    }
}

