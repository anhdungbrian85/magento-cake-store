<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\StoreLocator\Model\Export;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\BookmarkManagement;

/**
 * Metadata Provider for grid listing export.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * @var array
     */
    protected $data;
	
	/**
     * @var BookmarkManagement
     */
    protected $_bookmarkManagement;
	
	private $parsedDataSource = [];

    private $currentComponent = null;

    /**
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param string $dateFormat
     * @param array $data
     */
    public function __construct(
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
		BookmarkManagement $bookmarkManagement,
        $dateFormat = 'M j, Y h:i:s A',
        array $data = []
    ) 
	{
        parent::__construct($filter, $localeDate, $localeResolver, $dateFormat, $data);
        $this->_bookmarkManagement = $bookmarkManagement;
    }


    /**
     * Returns columns list
     *
     * @param UiComponentInterface $component
     *
     * @return UiComponentInterface[]
     * @throws Exception
     */	
	 protected function getColumns(UiComponentInterface $component) : array
    {
        if (!isset($this->columns[$component->getName()])) {
            $this->currentComponent = $component;
            $activeColumns = $this->getActiveColumns($component);
            $columnsComponent = $this->getColumnsComponent($component);
            $columns = $columnsComponent->getChildComponents();
            foreach ($activeColumns as $sortedColumn) {
                $this->columns[$component->getName()][$sortedColumn] = $columns[$sortedColumn];
            }
        }
        return $this->columns[$component->getName()];
    }
	
	private function getActiveColumns($component)
    {
        $bookmark = $this->_bookmarkManagement->getByIdentifierNamespace('current', $component->getName());

        $config = $bookmark->getConfig();
        $columnSorting = $config['current']['positions'];
        $columns = $config['current']['columns'];
        $_activeColumns = [];
        foreach ($columnSorting as $column => $sorting) {
            if (true === $columns[$column]['visible'] && $column != 'ids' && $column != 'actions') {
                $_activeColumns[] = $column;
            }
        }
        return $_activeColumns;
    }
	
    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     *
     * @return string[]
     */
    /*public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        $row = [];
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = $key;
                }
            } else {
                $row[] = $document->getCustomAttribute($column)->getValue();
            }
        }

        return $row;
    }*/

		 public function getRowData(DocumentInterface $document, $fields, $options) : array
    {
        // not before here the dataProvider has set the correct filters, so we calculate the dataSource here
        $this->prepareDataSource($this->currentComponent);

        // in contrast to base method, we use the prepared data source instead the data provider
        // the data provider represents the database values, the data source represents the UI data (database + custom columns)

        // getRowData is called in the same order as parsedDataSource is saved (although the grid sort order is not considered in both variants)
        $dataSourceItem = array_shift($this->parsedDataSource);

        $row = [];
        foreach ($fields as $column) {
            $row[] = $dataSourceItem[$column] ?? ""; // there are cased where there is a column, but no data exists
        }
        return $row;
    }
	
	private function prepareDataSource(UiComponentInterface $component)
    {
        if (empty($this->parsedDataSource))
        {
            $context = $component->getContext();
            $dataSourceData = $context->getDataSourceData($component);
            $this->parsedDataSource = $dataSourceData[$context->getDataProvider()->getName()]['config']['data']['items'];
        }
    }
}
