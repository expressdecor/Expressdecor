<?php
 
abstract class Expressdecor_Sgrid_Model_Pdf_Abstract extends Varien_Object
{
	public $y;
	/**
     * Item renderers with render type key
     *
     * model    => the model name
     * renderer => the renderer model
     *
     * @var array
     */
	protected $_renderers = array();

	const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID = 'sales_pdf/invoice/put_order_id';
	const XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID = 'sales_pdf/shipment/put_order_id';
	const XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID = 'sales_pdf/creditmemo/put_order_id';

	/**
     * Zend PDF object
     *
     * @var Zend_Pdf
     */
	protected $_pdf;

	protected $_defaultTotalModel = 'sales/order_pdf_total_default';

	/**
     * Retrieve PDF
     *
     * @return Zend_Pdf
     */
	abstract public function getPdf();

	/**
     * Returns the total width in points of the string using the specified font and
     * size.
     *
     * This is not the most efficient way to perform this calculation. I'm
     * concentrating optimization efforts on the upcoming layout manager class.
     * Similar calculations exist inside the layout manager class, but widths are
     * generally calculated only after determining line fragments.
     *
     * @param string $string
     * @param Zend_Pdf_Resource_Font $font
     * @param float $fontSize Font size in points
     * @return float
     */
	public function widthForStringUsingFontSize($string, $font, $fontSize)
	{
		$drawingString = '"libiconv"' == ICONV_IMPL ? iconv('UTF-8', 'UTF-16BE//IGNORE', $string) : @iconv('UTF-8', 'UTF-16BE', $string);

		$characters = array();
		for ($i = 0; $i < strlen($drawingString); $i++) {
			$characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
		}
		$glyphs = $font->glyphNumbersForCharacters($characters);
		$widths = $font->widthsForGlyphs($glyphs);
		$stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
		return $stringWidth;

	}

	/**
     * Calculate coordinates to draw something in a column aligned to the right
     *
     * @param string $string
     * @param int $x
     * @param int $columnWidth
     * @param Zend_Pdf_Resource_Font $font
     * @param int $fontSize
     * @param int $padding
     * @return int
     */
	public function getAlignRight($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5)
	{
		$width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
		return $x + $columnWidth - $width - $padding;
	}

	/**
     * Calculate coordinates to draw something in a column aligned to the center
     *
     * @param string $string
     * @param int $x
     * @param int $columnWidth
     * @param Zend_Pdf_Resource_Font $font
     * @param int $fontSize
     * @return int
     */
	public function getAlignCenter($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize)
	{
		$width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
		return $x + round(($columnWidth - $width) / 2);
	}

 
	protected function insertAddress(&$page, $store = null)
	{
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->_setFontRegular($page, 13); 

		$page->setLineWidth(0.5);
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
		$page->drawLine(155, 825, 155, 790);

		$page->setLineWidth(0);
		$this->y = 820;
		foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
			if ($value!=='') {
				$page->drawText(trim(strip_tags($value)), 160, $this->y, 'UTF-8');
				$this->y -=10;
			}
		}
		//return $page;
	}

	/**
     * Format address
     *
     * @param string $address
     * @return array
     */
	protected function _formatAddress($address)
	{
		$return = array();
		foreach (explode('|', $address) as $str) {
			foreach (Mage::helper('core/string')->str_split($str, 65, true, true) as $part) {
				if (empty($part)) {
					continue;
				}
				$return[] = $part;
			}
		}
		return $return;
	}

	protected function insertOrder(&$page, $obj, $putOrderId = true, $type_document='any')
	{
		if ($obj instanceof Mage_Sales_Model_Order) {
			$shipment = null;
			$order = $obj;
		} elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
			$shipment = $obj;
			$order = $shipment->getOrder();
		}

		/* @var $order Mage_Sales_Model_Order */
		$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));  

		$page->drawRectangle(15, 790, 580, 755);

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->_setFontRegular($page,13); 


		if ($putOrderId) {
			$page->drawText(Mage::helper('sales')->__('Order # ').$order->getRealOrderId(), 25, 770, 'UTF-8');
		}
 
		$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
		$page->setLineWidth(0.5);		
		$page->drawRectangle(15, 755, 580, 730); 

		/* Calculate blocks info */


		/* Shipping Address and Method */
	 if (!$order->getIsVirtual()) {
			/* Shipping Address */
		 	$shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));

			$shippingMethod  = $order->getShippingDescription();
		} 

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->_setFontRegular($page);
		
		if (!$order->getIsVirtual()) {
			$page->drawText(Mage::helper('sales')->__('SHIP TO:'), 25, 740 , 'UTF-8'); 
		}

		if (!$order->getIsVirtual()) {
			$y = 730 - (max(count($billingAddress), count($shippingAddress)) * 10 + 5);
		}
		else {
			$y = 730 - (count($billingAddress) * 10 + 5);
		}

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
		$page->drawRectangle(15, 730, 580, $y-40);
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->_setFontRegular($page,13);
		$this->y = 710;

		if (!$order->getIsVirtual()) {
			$this->y = 710;
			foreach ($shippingAddress as $value){
				if ($value!=='') {

					if (strpos(ltrim($value),',') !== false) {
						$page->drawText(substr(strip_tags(ltrim($value)),0,strpos(strip_tags(ltrim($value)),',')+1), 30, $this->y, 'UTF-8');
						$this->y -=10;
						$page->drawText(substr(strip_tags(ltrim($value)),strpos(strip_tags(ltrim($value)),',')+2,strlen(strip_tags(ltrim($value)))-strpos(strip_tags(ltrim($value)),',')+2), 30, $this->y, 'UTF-8');  
					} else {
						$page->drawText(strip_tags(ltrim($value)), 30, $this->y, 'UTF-8');  
					}

					$this->y -=14;
				}

			}

			 
			$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
			$page->setLineWidth(0.5);

			$this->_setFontBold($page,10); 
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

			$this->y -=10;
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

			$this->_setFontRegular($page);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

			$paymentLeft = 35;
			$yPayments   = $this->y - 15;
		}
		else {
			$yPayments   = 720;
			$paymentLeft = 285;
		}

			$yShipments = $this->y;

			$yShipments -=3; //Alex add 7/21
		 
			$yShipments -= 7;
 
			$currentY = min($yPayments, $yShipments);
 
			$this->y = $currentY;
			$this->y -= 15;
 
	}

 
 

	protected function _parseItemDescription($item)
	{
		$matches = array();
		$description = $item->getDescription();
		if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
			return $matches[1];
		}

		return array($description);
	}

	/**
     * Before getPdf processing
     *
     */
	protected function _beforeGetPdf() {
		$translate = Mage::getSingleton('core/translate');
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline(false);
	}

	/**
     * After getPdf processing
     *
     */
	protected function _afterGetPdf() {
		$translate = Mage::getSingleton('core/translate');
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline(true);
	}

	protected function _formatOptionValue($value, $order)
	{
		$resultValue = '';
		if (is_array($value)) {
			if (isset($value['qty'])) {
				$resultValue .= sprintf('%d', $value['qty']) . ' x ';
			}

			$resultValue .= $value['title'];

			if (isset($value['price'])) {
				$resultValue .= " " . $order->formatPrice($value['price']);
			}
			return  $resultValue;
		} else {
			return $value;
		}
	}

	protected function _initRenderer($type)
	{
		$node = Mage::getConfig()->getNode('global/pdf/'.$type);
		foreach ($node->children() as $renderer) {
			$this->_renderers[$renderer->getName()] = array(
			'model'     => (string)$renderer,
			'renderer'  => null
			);
		}
	}

	/**
     * Retrieve renderer model
     *
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
	protected function _getRenderer($type)
	{
		if (!isset($this->_renderers[$type])) {
			$type = 'default';
		}

		if (!isset($this->_renderers[$type])) {
			Mage::throwException(Mage::helper('sales')->__('Invalid renderer model'));
		}

		if (is_null($this->_renderers[$type]['renderer'])) {
			//$this->_renderers[$type]['renderer'] = Mage::getSingleton($this->_renderers[$type]['model']);
			$this->_renderers[$type]['renderer']= Mage::getSingleton('sgrid/pdf_items');
		}

		return $this->_renderers[$type]['renderer'];
	}

	/**
     * Public method of protected @see _getRenderer()
     *
     * Retrieve renderer model
     *
     * @param string $type
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
	public function getRenderer($type)
	{
		return $this->_getRenderer($type);
	}

	/**
     * Draw Item process
     *
     * @param Varien_Object $item
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $order
     * @return Zend_Pdf_Page
     */
	protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order)
	{
		$type = $item->getOrderItem()->getProductType();		 
		$renderer = $this->_getRenderer($type);		
		$renderer->setOrder($order);
		$renderer->setItem($item);
		$renderer->setPdf($this);
		$renderer->setPage($page);
		$renderer->setRenderedModel($this);

		$renderer->draw();

		return $renderer->getPage();
	}

	protected function _setFontRegular($object, $size = 13) 
	{
		$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
		$object->setFont($font, $size);
		return $font;
	}

	protected function _setFontBold($object, $size = 13)  
	{
		$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
		$object->setFont($font, $size);
		return $font;
	}

	protected function _setFontItalic($object, $size = 13)  
	{
		$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
		$object->setFont($font, $size);
		return $font;
	}

	/**
     * Set PDF object
     *
     * @param Zend_Pdf $pdf
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
	protected function _setPdf(Zend_Pdf $pdf)
	{
		$this->_pdf = $pdf;
		return $this;
	}

	/**
     * Retrieve PDF object
     *
     * @throws Mage_Core_Exception
     * @return Zend_Pdf
     */
	protected function _getPdf()
	{
		if (!$this->_pdf instanceof Zend_Pdf) {
			Mage::throwException(Mage::helper('sales')->__('Please define PDF object before using.'));
		}

		return $this->_pdf;
	}

	/**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
	public function newPage(array $settings = array())
	{
		$pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
		$page = $this->_getPdf()->newPage($pageSize);
		$this->_getPdf()->pages[] = $page;
		$this->y = 800;

		return $page;
	}

	/**
     * Draw lines
     *
     * draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parametr), optional left, right
     * height       int;line spacing (default 10)
     *
     * @param Zend_Pdf_Page $page
     * @param array $draw
     * @param array $pageSettings
     * @throws Mage_Core_Exception
     * @return Zend_Pdf_Page
     */
	public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
	{
		foreach ($draw as $itemsProp) {
			if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
				Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.'));
			}
			$lines  = $itemsProp['lines'];
			$height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

			if (empty($itemsProp['shift'])) {
				$shift = 0;
				foreach ($lines as $line) {
					$maxHeight = 0;
					foreach ($line as $column) {
						$lineSpacing = !empty($column['height']) ? $column['height'] : $height;
						if (!is_array($column['text'])) {
							$column['text'] = array($column['text']);
						}
						$top = 0;
						foreach ($column['text'] as $part) {
							$top += $lineSpacing;
						}

						$maxHeight = $top > $maxHeight ? $top : $maxHeight;
					}
					$shift += $maxHeight;
				}
				$itemsProp['shift'] = $shift;
			}

			if ($this->y - $itemsProp['shift'] < 15) {
				$page = $this->newPage($pageSettings);
			}

			foreach ($lines as $line) {
				$maxHeight = 0;
				foreach ($line as $column) {
					$fontSize = empty($column['font_size']) ? 13 : $column['font_size']; 
					if (!empty($column['font_file'])) {
						$font = Zend_Pdf_Font::fontWithPath($column['font_file']);
						$page->setFont($font, $fontSize);
					}
					else {
						$fontStyle = empty($column['font']) ? 'regular' : $column['font'];
						switch ($fontStyle) {
							case 'bold':
								$font = $this->_setFontBold($page, $fontSize);
								break;
							case 'italic':
								$font = $this->_setFontItalic($page, $fontSize);
								break;
							default:
								$font = $this->_setFontRegular($page, $fontSize);
								break;
						}
					}

					if (!is_array($column['text'])) {
						$column['text'] = array($column['text']);
					}

					$lineSpacing = !empty($column['height']) ? $column['height'] : $height;
					$top = 0;
					foreach ($column['text'] as $part) {
						$feed = $column['feed'];
						$textAlign = empty($column['align']) ? 'left' : $column['align'];
						$width = empty($column['width']) ? 0 : $column['width'];
						switch ($textAlign) {
							case 'right':
								if ($width) {
									$feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
								}
								else {
									$feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
								}
								break;
							case 'center':
								if ($width) {
									$feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
								}
								break;
						}
						$page->drawText($part, $feed, $this->y-$top, 'UTF-8');
						$top += $lineSpacing;
					}

					$maxHeight = $top > $maxHeight ? $top : $maxHeight;
				}
				$this->y -= $maxHeight;
			}
		}

		return $page;
	}
	/**
	 * Insert logo to pdf page
	 *
	 * @param Zend_Pdf_Page $page
	 * @param null $store
	 */
	protected function insertLogo(&$page, $store = null)
	{
		$this->y = $this->y ? $this->y : 815;
		$image = Mage::getStoreConfig('sales/identity/logo', $store);
		if ($image) {
			$image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
		//	echo $image;die();
			if (is_file($image)) {
				$image       = Zend_Pdf_Image::imageWithPath($image);
				$top         = 830; //top border of the page
				$widthLimit  = 270; //half of the page width
				$heightLimit = 270; //assuming the image is not a "skyscraper"
				$width       = $image->getPixelWidth()/2;
				$height      = $image->getPixelHeight()/2;
	
				//preserving aspect ratio (proportions)
				$ratio = $width / $height;
				if ($ratio > 1 && $width > $widthLimit) {
					$width  = $widthLimit;
					$height = $width / $ratio;
				} elseif ($ratio < 1 && $height > $heightLimit) {
					$height = $heightLimit;
					$width  = $height * $ratio;
				} elseif ($ratio == 1 && $height > $heightLimit) {
					$height = $heightLimit;
					$width  = $widthLimit;
				}
				$y1 = $top - $height;
				$y2 = $top;
				$x1 = 25;
				$x2 = $x1 + $width;
				
				//coordinates after transformation are rounded by Zend
				$page->drawImage($image, $x1, $y1, $x2, $y2);
				
				$this->y = $y1 - 10;
				}
				}
				}
				
}
