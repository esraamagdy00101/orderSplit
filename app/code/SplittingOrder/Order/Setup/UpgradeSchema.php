<?php

namespace SplittingOrder\Order\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $orderGridTable = 'sales_order_grid';
        $ordersTable = 'sales_order';
        $quoteTable = 'quote';

    
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quoteTable),
                    'parent_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' =>'parentId'
                    ]
                );
            }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($ordersTable),
                    'parent_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' =>'parentId'
                    ]
                );
            }
        //Order Grid table
        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderGridTable),
                    'parent_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' =>'parentId'
                    ]
                );
            }

        $setup->endSetup();
    }
    
}