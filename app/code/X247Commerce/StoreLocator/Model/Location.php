<?php
namespace X247Commerce\StoreLocator\Model;

use Amasty\Storelocator\Ui\DataProvider\Form\ScheduleDataProvider;
class Location extends \Amasty\Storelocator\Model\Location
{
    protected $configProvider;
    protected $serializer;
    protected $dataHelper;
    protected $configHtmlConverter;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Amasty\Storelocator\Model\ConfigProvider $configProvider,
        \Amasty\Storelocator\Model\ConfigHtmlConverter $configHtmlConverter,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory = null,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory = null,
        )
    {
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data, $extensionFactory, $customAttributeFactory, $serializer);
        $this->configProvider = $configProvider;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
        $this->dataHelper = $dataHelper;
        $this->configHtmlConverter = $configHtmlConverter;
    }

    public function getWorkingTime($dayName){
        $scheduleArray = $this->getDaySchedule($dayName);
        $periods = [];
        if (array_shift($scheduleArray) == 0) {
            return [$this->getDayName($dayName) => $this->configProvider->getClosedText()];
        }
        $periods[$this->getDayName($dayName)] = $this->getFromToTime(
            $scheduleArray[ScheduleDataProvider::OPEN_TIME],
            $scheduleArray[ScheduleDataProvider::CLOSE_TIME]
        );

        // not show similar from/to times for break
        if ($scheduleArray[ScheduleDataProvider::START_BREAK_TIME]
            != $scheduleArray[ScheduleDataProvider::END_BREAK_TIME]
        ) {
            $periods[$this->configProvider->getBreakText()] = $this->getFromToTime(
                $scheduleArray[ScheduleDataProvider::START_BREAK_TIME],
                $scheduleArray[ScheduleDataProvider::END_BREAK_TIME]
            );
        }

        return $periods;
    }

    public function getFromToTime($from, $to)
    {
        $from = implode(':', $from);
        $to = implode(':', $to);
        $needConvertTime = $this->configProvider->getConvertTime();
        if ($needConvertTime) {
            $from = date("g:i a", strtotime($from));
            $to = date("g:i a", strtotime($to));
        }

        return implode(' - ', [$from, $to]);
    }
        
    public function getDaySchedule($dayName)
    {
        $weekdays = $this->getWeekdays();
        $schedule = $this->getUnserializedShedule();
        if (array_key_exists($dayName, $schedule)) {
            $scheduleKey = strtolower($weekdays[$dayName]->getText());
        } else {
            // getting day of the week for compatibility with old module versions
            $scheduleKey = date("N", strtotime($dayName));
        }
        return isset($schedule[$scheduleKey]) ? $schedule[$scheduleKey] : [];
    }

    private function getUnserializedShedule()
    {
        if ($this->getScheduleString()) {
            return $this->serializer->unserialize($this->getScheduleString());
        }
        return [];
    }

    public function getWeekdays(){
        return $this->dataHelper->getDaysNames();
    }

    public function getDayName($dayName)
    {
        $weekdays = $this->getWeekDays();
        if (array_key_exists($dayName, $weekdays)) {
            $dayName = $weekdays[$dayName]->getText();
        } else {
            $dayName = date('l', strtotime("Sunday + $dayName days"));
        }

        return $dayName;
    }

    public function setTemplatesHtml()
    {
        $this->getResource()->setAttributesData($this);
        $this->configHtmlConverter->setHtml($this);
    }


}