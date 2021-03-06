<?php 
class Expressdecor_Page_Block_Html_Head extends Mage_Page_Block_Html_Head
{

	/**
     * Get HEAD HTML with CSS/JS/RSS definitions
     * (actually it also renders other elements, TODO: fix it up or rename this method)
     *
     * @return string
     */
	public function getCssJsHtml()
	{
		// separate items by types
		$lines  = array();
		foreach ($this->_data['items'] as $item) {
			if (!is_null($item['cond']) && !$this->getData($item['cond']) || !isset($item['name'])) {
				continue;
			}
			$if     = !empty($item['if']) ? $item['if'] : '';
			$params = !empty($item['params']) ? $item['params'] : '';
			switch ($item['type']) {
				case 'js':        // js/*.js
				case 'skin_js':   // skin/*/*.js
				case 'js_css':    // js/*.css
				case 'skin_css':  // skin/*/*.css
				$lines[$if][$item['type']][$params][$item['name']] = $item['name'];
				break;
				default:
					$this->_separateOtherHtmlHeadElements($lines, $if, $item['type'], $params, $item['name'], $item);
					break;
			}
		}
		// coockie value test
		$id =Mage::getStoreConfig('expressdecor/ses_cook/promo_cookie_param',Mage::app()->getStore());

		$param_discount =Mage::getStoreConfig('expressdecor/ses_cook/discount_param',Mage::app()->getStore());
		$param_discount_session =Mage::getModel('checkout/session')->getDiscountParam();
		$discount_code= $this->getRequest()->getParam($param_discount);

		$promo_src = $this->getRequest()->getParam($id);
		$promo_value = Mage::getModel('core/cookie')->get($id);
		$promo_value_session=Mage::getModel('checkout/session')->getPromoCode();


		if (!$promo_value) {
			if (!empty($promo_src)) {
				$promo_value = $promo_src; // text data
				$time =60*60*24*30; // month
				Mage::getModel('core/cookie')->set($id, $promo_value, $time);
				if (!$promo_value_session) {
					Mage::getModel('checkout/session')->setPromoCode($promo_value);
				}
			} else {
				if ($promo_value_session) {
					Mage::getModel('checkout/session')->unsPromoCode($promo_value);
				}
			}
		} else {
			if (!$promo_value_session){
				Mage::getModel('checkout/session')->setPromoCode($promo_value);
			}
		}

		if ($discount_code) {
//			if (!$param_discount_session)
			Mage::getModel('checkout/session')->setDiscountParam($discount_code);
			$items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
			
			if (count($items) > 0)
				Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($discount_code)->save();
		
		} elseif (Mage::getModel('checkout/session')->getDiscountParam() && !Mage::getSingleton('checkout/cart')->getQuote()->getCouponCode()) { // added at 1/25/2013 applies if sommething iwll appeat in cart
			$discount_code=Mage::getModel('checkout/session')->getDiscountParam();
			
			$items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
			if (count($items) > 0)
				Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($discount_code)->save();
		}
		
		 
 		
		



		//coockie value test
		// prepare HTML
		$shouldMergeJs = Mage::getStoreConfigFlag('dev/js/merge_files');
		$shouldMergeCss = Mage::getStoreConfigFlag('dev/css/merge_css_files');
		$html   = '';
		foreach ($lines as $if => $items) {
			if (empty($items)) {
				continue;
			}
			if (!empty($if)) {
				$html .= '<!--[if '.$if.']>'."\n";
			}

			// static and skin css
			$date=Mage::getStoreConfig('expressdecor/pages/static_css_date',Mage::app()->getStore());
			$html .= $this->_prepareStaticAndSkinElements('<link rel="stylesheet" type="text/css" href="%s?date='.$date.'"%s />' . "\n",
			empty($items['js_css']) ? array() : $items['js_css'],
			empty($items['skin_css']) ? array() : $items['skin_css'],
			$shouldMergeCss ? array(Mage::getDesign(), 'getMergedCssUrl') : null
			);

			// static and skin javascripts
			$html .= $this->_prepareStaticAndSkinElements('<script type="text/javascript" src="%s?date='.$date.'"%s></script>' . "\n",
			empty($items['js']) ? array() : $items['js'],
			empty($items['skin_js']) ? array() : $items['skin_js'],
			$shouldMergeJs ? array(Mage::getDesign(), 'getMergedJsUrl') : null
			);

			// other stuff
			if (!empty($items['other'])) {
				$html .= $this->_prepareOtherHtmlHeadElements($items['other']) . "\n";
			}

			if (!empty($if)) {
				$html .= '<![endif]-->'."\n";
			}
		}
		return $html;
	}
}