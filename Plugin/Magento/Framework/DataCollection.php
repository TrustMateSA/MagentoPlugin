<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Framework;

use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;

class DataCollection
{
    public function beforeAddItem(Collection $subject, DataObject $item)
    {
        if ($subject->getItemById($item->getId()) && !strpos($item->getTitle(), 'TrustMate')) {
            $item->setId($item->getId() . '_' . $item->getStoreId());
        }

        return $item;
    }
}
