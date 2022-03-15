<?php
/**
 * @package   Maneza\Brandlogo\Block\Product
 * @author    Ntabethemba Ntshoza
 * @date      15-03-2022
 * @copyright Copyright © 2022 MRP Group IT
 */

namespace Maneza\Brandlogo\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Helper\Product as HelperProduct;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Url\EncoderInterface as UrlEncoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Phrase;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category\Attribute\Backend\Image as ImageBackendModel;
use Magento\Framework\Stdlib\StringUtils;


/**
 * Class View
 * @package Maneza\Brandlogo\Block\Product
 */
class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var FileInfo
     */
    private $fileinfo;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var UrlEncoderInterface
     */
    protected $urlEncoder;

    /**
     * Magento string lib
     *
     * @var StringUtils
     */
    protected $string;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var PriceCurrencyInterface
     * @deprecated 102.0.0
     */

    private $CategoryCollecionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;


    /**
     * @var HelperProduct
     */
    protected $_productHelper;

    /**
     * @var ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $categoryFactory;


    /**
     *
     * @param Context $context
     * @param UrlEncoderInterface $urlEncoder
     * @param PriceCurrencyInterface $priceCurrency
     * @param CollectionFactory $categoryCollecionFactory
     * @param FileInfo $fileinfo
     * @param CategoryRepository $categoryRepository
     * @param EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param HelperProduct $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context                    $context,
        UrlEncoderInterface        $urlEncoder,
        PriceCurrencyInterface     $priceCurrency,
        CollectionFactory          $categoryCollecionFactory,
        FileInfo                   $fileinfo,
        CategoryRepository         $categoryRepository,
        EncoderInterface           $jsonEncoder,
        StringUtils                $string,
        HelperProduct              $productHelper,
        ConfigInterface            $productTypeConfig,
        FormatInterface            $localeFormat,
        Session                    $customerSession,
        ProductRepositoryInterface $productRepository,
        CategoryFactory            $categoryFactory,
        array                      $data = []
    )
    {
        $this->_categoryFactory = $categoryFactory;
        $this->categoryCollecionFactory = $categoryCollecionFactory;
        $this->fileinfo = $fileinfo;
        $this->categoryRepository = $categoryRepository;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );

    }

    /**
     * Get Brand collaction to process the brand image.
     *
     * @param string $categoryTitle
     * @return array
     */
    public function getBrandCollection($categoryTitle = 'Brands')
    {

        $collection = $this->categoryCollecionFactory->create();

        $ids = array();

        if ($collection->getSize()) {
            foreach ($collection as $collect) {
                $category = $this->_categoryFactory->create()->load($collect->getId());
                $categoryName = $category->getName();
                if ($categoryName == $categoryTitle) {
                    $categorySubs = $collect->getChildrenCategories();
                    foreach ($categorySubs as $categorySub) {
                        array_push($ids, $categorySub->getId());
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Get and image from the admin category page
     *
     * @return array
     */
    public function getCategoryImage()
    {
        $categoryData = array();
        $productCategory = $this->getProduct()->getCategoryIds();
        $brandList = $this->getBrandCollection();
        $commonCatergory = array_intersect($productCategory, $brandList);

        if (count($commonCatergory) > 0) {

            foreach ($commonCatergory as $categoryItem) {
                $category = $this->categoryRepository->get($categoryItem);

                foreach ($category->getAttributes() as $attributeCode => $attribute) {

                    if ($attribute->getBackend() instanceof ImageBackendModel) {
                        $fileName = $category->getData($attributeCode);
                        if (!is_null($fileName)) {
                            if ($this->fileinfo->isExist($fileName)) {
                                if (!in_array($category->getId(), $categoryData)) {
                                    $categoryData [$category->getId()]
                                        = array(
                                        'name' => basename($fileName),
                                        'url' => $category->getImageUrl($attributeCode)
                                    );
                                }

                            }
                        }
                    }
                }
            }
        }

        return $categoryData;
    }

}
