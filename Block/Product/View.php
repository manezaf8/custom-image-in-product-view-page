<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product description block
 *
 * @author     Ntabethemba Maneza Mabetshe <maneza@maneza.co.za>
 */
namespace Maneza\Brandlogo\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Phrase;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Category\Attribute\Backend\Image as ImageBackendModel;


/**
 * View View Class to deplay category images into the frontend.
 */
class View extends  \Magento\Catalog\Block\Product\View
{
    /**
     * @var \Magento\Catalog\Model\Category\FileInfo 
     */
    private $fileinfo;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     * @deprecated 102.0.0
     */

    private $CategoryCollecionFactory;
  
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;


    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $categoryFactory;

    
    /**
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollecionFactory
     * @param \Magento\Catalog\Model\Category\FileInfo $fileinfo
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollecionFactory,
        \Magento\Catalog\Model\Category\FileInfo $fileinfo,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
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
     * @return void
     */
    public function getBrandCollection($categoryTitle='Brands')
    {
        
        $collection = $this->categoryCollecionFactory->create();

        $ids = array();
        
        if ($collection->getSize()) {
            foreach($collection as $collect){
                $category = $this->_categoryFactory->create()->load($collect->getId());
                $categoryName = $category->getName();
                if($categoryName == $categoryTitle){   
                $categorySubs = $collect->getChildrenCategories();
                 foreach ($categorySubs as $categorySub)
            {   
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
     * @return Array
     */
    public function getCategoryImage()
    {
        $categoryData=array();
        $productCategory = $this->getProduct()->getCategoryIds();
        $brandList = $this->getBrandCollection();
        $commonCatergory = array_intersect($productCategory,$brandList);        

        if(count($commonCatergory)>0)
        {
            foreach($commonCatergory as $categoryItem){
                $category = $this->categoryRepository->get($categoryItem);
                //var_dump($category);
                foreach ($category->getAttributes() as $attributeCode => $attribute) {
                    if ($attribute->getBackend() instanceof ImageBackendModel) {
                        $fileName = $category->getData($attributeCode);
                        if(!is_null($fileName)){
                            if ($this->fileinfo->isExist($fileName)) {
                                if(!in_array($category->getId(), $categoryData))
                                {
                                    $categoryData [$category->getId()]
                                    = array(
                                        'name'=> basename($fileName),
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
